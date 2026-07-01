<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$phoneDigits = normalizePhoneForLookup($telefone);

if ($email === '' && $phoneDigits === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Informe email ou telefone.']);
    exit;
}

$found = null;
$tables = [
    'peregrinos' => 'peregrino',
    'anfitrioes' => 'anfitriao',
];

foreach ($tables as $table => $type) {
    $query = "SELECT id, nome, email, telefone, pagbank_checkout_id, payment_status, pagbank_status FROM `$table` WHERE 1=1";
    $params = [];
    $types = '';

    if ($email !== '') {
        $query .= " AND email = ?";
        $params[] = $email;
        $types .= 's';
    }

    if ($phoneDigits !== '') {
        $query .= " AND REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', '') LIKE ?";
        $params[] = '%' . $phoneDigits . '%';
        $types .= 's';
    }

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        continue;
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $found = ['table' => $table, 'type' => $type, 'row' => $row];
        break;
    }
}

if (!$found) {
    echo json_encode(['ok' => false, 'message' => 'Nenhuma inscrição encontrada com esses dados.']);
    exit;
}

$row = $found['row'];
$table = $found['table'];
$id = (int) $row['id'];
$checkoutId = $row['pagbank_checkout_id'] ?? null;

if (!empty($checkoutId)) {
    $refresh = refreshPagbankRegistrationStatus($mysqli, $table, $id, $checkoutId);
} else {
    $refresh = ['ok' => false, 'message' => 'Sem checkout do PagBank associado a esta inscrição.'];
}

echo json_encode([
    'ok' => $refresh['ok'] ?? false,
    'message' => $refresh['message'] ?? null,
    'status' => $refresh['status'] ?? $row['payment_status'] ?? 'pendente',
    'pagbank_status' => $refresh['pagbank_status'] ?? $row['pagbank_status'] ?? null,
    'nome' => $row['nome'],
    'email' => $row['email'],
    'telefone' => $row['telefone'],
]);
