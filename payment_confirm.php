<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscricao.php');
    exit;
}

$reg_id = $_SESSION['registration_id'] ?? null;
$reg_type = $_SESSION['registration_type'] ?? null;

if (!$reg_id || !$reg_type) {
    header('Location: inscricao.php');
    exit;
}

// Validar upload
if (empty($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    header("Location: payment.php?error=Falha%20no%20upload");
    exit;
}

// Validar tipo de arquivo
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
$file_type = mime_content_type($_FILES['receipt']['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    header("Location: payment.php?error=Tipo%20de%20arquivo%20inválido");
    exit;
}

// Salvar comprovante
$filename = sprintf('%s_%d_%s.%s', $reg_type, $reg_id, uniqid(), pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
$destination = UPLOAD_DIR . '/' . $filename;

if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $destination)) {
    header("Location: payment.php?error=Erro%20ao%20salvar%20arquivo");
    exit;
}

// Atualizar status
$table = ($reg_type === 'peregrino') ? 'peregrinos' : 'anfitrioes';
$status = 'comprovante_enviado';

$stmt = $mysqli->prepare("UPDATE {$table} SET payment_receipt = ?, payment_status = ? WHERE id = ?");
$stmt->bind_param('ssi', $filename, $status, $reg_id);
$stmt->execute();
$stmt->close();

// Limpar sessão
unset($_SESSION['registration_id']);
unset($_SESSION['registration_type']);
unset($_SESSION['pix_amount']);

// Redirecionar para confirmação
header('Location: confirmation.php');
exit;
