<?php
declare(strict_types=1);
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

$action = $_POST['action'] ?? '';
$admin_username = $_SESSION['admin_username'] ?? 'admin';

// CADASTRAR PEREGRINO VIA ADMIN
if ($action === 'admin_cadastro_peregrino') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $payment_confirmed = isset($_POST['payment_confirmed']) ? 1 : 0;

    if ($nome && $email && $genero) {
        $payment_status = $payment_confirmed ? 'confirmado' : 'pendente';
        $payment_amount = 150.00;
        $pix_cents = rand(0, 99);
        $confirmed_at = $payment_confirmed ? date('Y-m-d H:i:s') : null;

        $stmt = $mysqli->prepare(
            "INSERT INTO peregrinos (nome, email, genero, telefone, payment_status, payment_amount, pix_cents, payment_confirmed_by, payment_confirmed_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param('ssssdiss', $nome, $email, $genero, $telefone, $payment_amount, $pix_cents, $payment_status, $admin_username, $confirmed_at);
            
            if ($stmt->execute()) {
                $_SESSION['admin_success'] = "✓ Peregrino '{$nome}' cadastrado com sucesso!";
            } else {
                $_SESSION['admin_error'] = "✗ Erro ao cadastrar peregrino.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['admin_error'] = "✗ Preencha todos os campos obrigatórios.";
    }

    header('Location: admin.php#cadastro');
    exit;
}

// CADASTRAR ANFITRIÃO VIA ADMIN
if ($action === 'admin_cadastro_anfitriao') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $funcao = $_POST['funcao'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $payment_confirmed = isset($_POST['payment_confirmed']) ? 1 : 0;

    if ($nome && $email && $funcao) {
        $payment_status = $payment_confirmed ? 'confirmado' : 'pendente';
        $payment_amount = 150.00;
        $pix_cents = rand(0, 99);
        $confirmed_at = $payment_confirmed ? date('Y-m-d H:i:s') : null;

        $stmt = $mysqli->prepare(
            "INSERT INTO anfitrioes (nome, email, funcao, telefone, payment_status, payment_amount, pix_cents, payment_confirmed_by, payment_confirmed_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param('ssssdiss', $nome, $email, $funcao, $telefone, $payment_amount, $pix_cents, $payment_status, $admin_username, $confirmed_at);
            
            if ($stmt->execute()) {
                $_SESSION['admin_success'] = "✓ Anfitrião '{$nome}' cadastrado com sucesso!";
            } else {
                $_SESSION['admin_error'] = "✗ Erro ao cadastrar anfitrião.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['admin_error'] = "✗ Preencha todos os campos obrigatórios.";
    }

    header('Location: admin.php#cadastro');
    exit;
}

// APROVAR/REJEITAR PENDENTES (código original)
$id = (int) ($_POST['id'] ?? 0);
$tipo = $_POST['tipo'] ?? '';

if ($id && in_array($tipo, ['peregrino', 'anfitriao'])) {
    $action = $_POST['action'] ?? '';
    
    if (!in_array($action, ['approve', 'reject'])) {
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
    $stmt->bind_param('sssi', $new_status, $admin_username, $now, $id);
    $stmt->execute();
    $stmt->close();

    header('Location: admin.php?success=' . ($action === 'approve' ? 'confirmado' : 'rejeitado'));
    exit;
}

header('Location: admin.php');
exit;
