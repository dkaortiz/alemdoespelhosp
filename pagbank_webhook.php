<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Payload inválido']);
    exit;
}

$event = $payload['event'] ?? $payload['type'] ?? null;
$referenceId = $payload['reference_id'] ?? $payload['data']['reference_id'] ?? null;

// Tentar extrair o ID da transação/pagamento a partir de várias estruturas possíveis
$paymentId = null;
if (!empty($payload['payment_id'])) $paymentId = $payload['payment_id'];
if (!$paymentId && !empty($payload['data']['payment_id'])) $paymentId = $payload['data']['payment_id'];
if (!$paymentId && !empty($payload['id'])) $paymentId = $payload['id'];
if (!$paymentId && !empty($payload['data']['id'])) $paymentId = $payload['data']['id'];
if (!$paymentId && !empty($payload['data']['charges']) && is_array($payload['data']['charges'])) {
    $first = $payload['data']['charges'][0] ?? null;
    if ($first && !empty($first['id'])) $paymentId = $first['id'];
}

// Algumas notificações retornam o objeto completo em 'data' ou 'object'
if (!$paymentId && !empty($payload['data']['object']['id'])) {
    $paymentId = $payload['data']['object']['id'];
}

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
$mappedStatus = 'pendente';
if ($event && strpos(strtolower($event), 'paid') !== false) {
    $mappedStatus = 'confirmado';
} elseif ($event && strpos(strtolower($event), 'canceled') !== false) {
    $mappedStatus = 'cancelado';
} elseif ($event && strpos(strtolower($event), 'chargeback') !== false) {
    $mappedStatus = 'cancelado';
}

updateRegistrationPaymentStatus($mysqli, $table, $registrationId, $mappedStatus, $event, $paymentId, $payload);

echo json_encode(['ok' => true, 'status' => $mappedStatus, 'event' => $event]);
