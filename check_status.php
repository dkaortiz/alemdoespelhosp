<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

$registrationId = $_SESSION['registration_id'] ?? null;
$registrationType = $_SESSION['registration_type'] ?? null;

$status = 'pendente';
if ($registrationId && $registrationType) {
    $table = $registrationType === 'peregrino' ? 'peregrinos' : 'anfitrioes';
    $stmt = $mysqli->prepare("SELECT payment_status FROM `$table` WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $status = $row['payment_status'] ?? 'pendente';
    }
}

echo json_encode(['status' => $status]);
