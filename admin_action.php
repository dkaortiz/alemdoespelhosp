<?php
session_start();
require_once 'config.php';

if (!isAdminAuthenticated()) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$tipo = $_POST['tipo'] ?? '';
$action = $_POST['action'] ?? '';

if (!$id || !in_array($tipo, ['peregrino', 'anfitriao']) || !in_array($action, ['approve', 'reject'])) {
    header('Location: admin.php');
    exit;
}

$table = ($tipo === 'peregrino') ? 'peregrinos' : 'anfitrioes';
$new_status = ($action === 'approve') ? 'confirmado' : 'cancelado';
$now = date('Y-m-d H:i:s');

$stmt = $mysqli->prepare("
    UPDATE {$table} 
    SET payment_status = ?, payment_confirmed_by = ?, payment_confirmed_at = ? 
    WHERE id = ?
");
$admin_username = $_SESSION['admin_username'] ?? 'admin';
$stmt->bind_param('sssi', $new_status, $admin_username, $now, $id);
$stmt->execute();
$stmt->close();
// Log admin action if available
$admin_id = $_SESSION['admin_id'] ?? null;
if ($admin_id) {
    if ($action === 'approve') {
        logAdminAction($admin_id, 'aprovar_pagamento', $table, $id, 'Aprovado via painel');
    } else {
        logAdminAction($admin_id, 'rejeitar_pagamento', $table, $id, 'Rejeitado via painel');
    }
}

header('Location: admin.php?success=' . ($action === 'approve' ? 'confirmado' : 'rejeitado'));
exit;
