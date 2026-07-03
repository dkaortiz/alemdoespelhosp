<?php
require_once 'config.php';
session_start();

$requestData = array_merge($_GET, $_POST);
$rawInput = file_get_contents('php://input');
if ($rawInput !== '') {
    $decodedInput = json_decode($rawInput, true);
    if (is_array($decodedInput)) {
        $requestData = array_merge($requestData, $decodedInput);
    } else {
        $requestData['raw_body'] = $rawInput;
    }
}

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    'query' => $_GET,
    'post' => $_POST,
    'payload' => $requestData,
];
file_put_contents(__DIR__ . '/pagbank_return.log', json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

$registrationId = $_SESSION['registration_id'] ?? null;
$registrationType = $_SESSION['registration_type'] ?? null;
$checkoutId = $_SESSION['pagbank_checkout_id'] ?? null;
$referenceId = $_SESSION['pagbank_reference_id'] ?? null;

if (empty($checkoutId) && !empty($requestData['checkout_id'])) {
    $checkoutId = $requestData['checkout_id'];
}
if (empty($referenceId) && !empty($requestData['reference_id'])) {
    $referenceId = $requestData['reference_id'];
}
if (empty($checkoutId) && !empty($requestData['id'])) {
    $checkoutId = $requestData['id'];
}

if (empty($registrationType) && !empty($referenceId) && preg_match('/^inscricao-(peregrino|anfitriao)-(\d+)$/', $referenceId, $matches)) {
    $registrationType = $matches[1];
    $registrationId = (int) $matches[2];
}

if (empty($registrationId) && empty($registrationType) && !empty($checkoutId)) {
    foreach (['peregrinos' => 'peregrino', 'anfitrioes' => 'anfitriao'] as $table => $type) {
        $stmt = $mysqli->prepare("SELECT id FROM `$table` WHERE pagbank_checkout_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $checkoutId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if (!empty($row['id'])) {
                $registrationId = (int) $row['id'];
                $registrationType = $type;
                break;
            }
        }
    }
}

if (!empty($registrationId) && !empty($registrationType)) {
    $table = $registrationType === 'peregrino' ? 'peregrinos' : 'anfitrioes';
    $_SESSION['registration_id'] = $registrationId;
    $_SESSION['registration_type'] = $registrationType;
    if (!empty($checkoutId)) {
        $_SESSION['pagbank_checkout_id'] = $checkoutId;
    }
    if (!empty($referenceId)) {
        $_SESSION['pagbank_reference_id'] = $referenceId;
    }

    if (empty($checkoutId)) {
        $stmt = $mysqli->prepare("SELECT pagbank_checkout_id FROM `$table` WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $registrationId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if (!empty($row['pagbank_checkout_id'])) {
                $checkoutId = $row['pagbank_checkout_id'];
                $_SESSION['pagbank_checkout_id'] = $checkoutId;
            }
        }
    }

    $payloadJson = json_encode($requestData, JSON_UNESCAPED_UNICODE);
    $stmt = $mysqli->prepare("UPDATE `$table` SET pagbank_payload = ?, pagbank_reference_id = COALESCE(NULLIF(?,''), pagbank_reference_id), pagbank_checkout_id = COALESCE(NULLIF(?,''), pagbank_checkout_id) WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('sssi', $payloadJson, $referenceId, $checkoutId, $registrationId);
        $stmt->execute();
        $stmt->close();
    }

    if (!empty($checkoutId)) {
        $refresh = refreshPagbankRegistrationStatus($mysqli, $table, $registrationId, $checkoutId);
        if (!empty($refresh['status'])) {
            $status = $refresh['status'];
        }
    }
}

if (empty($status)) {
    $status = 'pendente';
}

if (!empty($registrationId) && !empty($registrationType) && ($status === 'pendente')) {
    $table = $registrationType === 'peregrino' ? 'peregrinos' : 'anfitrioes';
    $stmt = $mysqli->prepare("SELECT payment_status FROM `$table` WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $status = $row['payment_status'] ?? 'pendente';
    }
}

if (empty($status)) {
    $status = 'pendente';
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retorno do PagBank</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
</head>
<body>
    <main class="section">
        <div class="container" style="max-width: 700px; text-align: center;">
            <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                <h1 style="color: var(--primary);">Pagamento recebido</h1>
                <p style="color: var(--muted); margin-bottom: 1.5rem;">
                    Obrigado! O retorno do PagBank foi recebido. O status da sua inscrição está sendo atualizado.
                </p>
                <div style="padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08); border: 1px solid rgba(67, 56, 202, 0.2); color: var(--text);">
                    Status atual: <strong><?php echo htmlspecialchars(ucfirst($status)); ?></strong>
                </div>
                <p style="margin-top: 1.5rem; color: var(--muted);">
                    Em alguns instantes, você poderá ver a confirmação no painel administrativo.
                </p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Voltar para o site</a>
            </div>
        </div>
    </main>
    <script>
        function checkPagbankStatus() {
            fetch('check_status.php', {
                credentials: 'same-origin'
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data && data.status === 'confirmado') {
                        location.reload();
                    }
                })
                .catch(function() {
                    // Ignorar falhas temporárias de rede
                });
        }

        setInterval(checkPagbankStatus, 5000);
    </script>
</body>
</html>
