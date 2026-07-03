<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = file_get_contents('php://input');
$logFile = __DIR__ . '/pagbank_webhook.log';
$logMessage = date('Y-m-d H:i:s') . ' - PagBank Webhook received: ' . $input . PHP_EOL;
file_put_contents($logFile, $logMessage, FILE_APPEND);
error_log('PagBank Webhook received: ' . $input);
error_log('PagBank Webhook signature header: ' . ($_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? ''));

$payload = json_decode($input, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Payload inválido']);
    exit;
}

$event = $payload['event'] ?? $payload['type'] ?? null;
$referenceId = $payload['reference_id'] ?? $payload['data']['reference_id'] ?? null;

$paymentId = extractPagbankPaymentId($payload);
$pagbankStatus = extractPagbankStatusValue($payload);

if (empty($referenceId)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'reference_id ausente']);
    exit;
}

$registrationType = null;
$registrationId = null;
if (preg_match('/^inscricao-(peregrino|anfitriao)-([0-9]+)$/', $referenceId, $m)) {
    $registrationType = $m[1];
    $registrationId = (int) $m[2];
}

if (!$registrationType || !$registrationId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'reference_id não corresponde a inscrição']);
    exit;
}

$table = $registrationType === 'peregrino' ? 'peregrinos' : 'anfitrioes';
$mappedStatus = mapPagbankStatusToRegistrationStatus($payload);
if ($mappedStatus === 'pendente' && $event) {
    $lowerEvent = strtolower($event);
    if (strpos($lowerEvent, 'paid') !== false || strpos($lowerEvent, 'approved') !== false || strpos($lowerEvent, 'completed') !== false) {
        $mappedStatus = 'confirmado';
    } elseif (strpos($lowerEvent, 'canceled') !== false || strpos($lowerEvent, 'cancelled') !== false || strpos($lowerEvent, 'chargeback') !== false) {
        $mappedStatus = 'cancelado';
    }
}

try {
    $ok = updateRegistrationPaymentStatus($mysqli, $table, $registrationId, $mappedStatus, $pagbankStatus ?? $event, $paymentId, $payload);
    if (!$ok) {
        error_log('Erro no DB ao atualizar status PagBank para ' . $table . ' #' . $registrationId . ': ' . $mysqli->error);
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Falha ao atualizar status no banco']);
        exit;
    }
} catch (Throwable $e) {
    error_log('Exception ao atualizar status PagBank: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erro interno ao atualizar status']);
    exit;
}

echo json_encode(['ok' => true, 'status' => $mappedStatus, 'event' => $event]);
