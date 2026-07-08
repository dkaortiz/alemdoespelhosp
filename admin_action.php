<?php
declare(strict_types=1);
require_once 'config.php';

$session_dir = __DIR__ . '/sessions';
if (!is_dir($session_dir)) {
    @mkdir($session_dir, 0755, true);
}
@session_save_path($session_dir);
session_start();

// Detectar se é uma requisição AJAX
$is_ajax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
) || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

function sendJsonResponse(array $data, int $status = 200): void {
    if (ob_get_level()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isAdminAuthenticated()) {
    if ($is_ajax) {
        sendJsonResponse(['success' => false, 'message' => 'Não autorizado'], 401);
    }
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        sendJsonResponse(['success' => false, 'message' => 'Método inválido'], 405);
    }
    header('Location: admin.php');
    exit;
}

$action = $_POST['action'] ?? '';
$admin_username = $_SESSION['admin_username'] ?? 'admin';

// DEBUG: Log da ação recebida
error_log("ADMIN_ACTION: action={$action}, id=" . ($_POST['id'] ?? 'vazio') . ", is_ajax=" . ($is_ajax ? 'sim' : 'não'));

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
            $stmt->bind_param('ssssdisss', $nome, $email, $genero, $telefone, $payment_status, $payment_amount, $pix_cents, $admin_username, $confirmed_at);
            
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
            $stmt->bind_param('ssssdisss', $nome, $email, $funcao, $telefone, $payment_status, $payment_amount, $pix_cents, $admin_username, $confirmed_at);
            
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

// DELETAR PEREGRINO
if ($action === 'delete_peregrino') {
    $id = (int) ($_POST['id'] ?? 0);
    $success_delete = false;
    $error_msg = '';
    
    if ($id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM peregrinos WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $success_delete = $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if (!$success_delete) {
                $error_msg = $mysqli->error;
            }
        } else {
            $error_msg = $mysqli->error;
        }
    } else {
        $error_msg = "ID inválido";
    }
    
    // Se for AJAX, retorna JSON
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success_delete && $affected_rows > 0,
            'message' => $success_delete && $affected_rows > 0 ? 'Peregrino deletado com sucesso!' : ('Erro ao deletar: ' . $error_msg)
        ]);
        exit;
    }
    
    // Se for requisição normal, redireciona
    if ($success_delete) {
        $_SESSION['admin_success'] = "✓ Peregrino deletado com sucesso!";
    } else {
        $_SESSION['admin_error'] = "✗ Erro ao deletar peregrino: " . $error_msg;
    }
    
    header('Location: admin.php#peregrinos');
    exit;
}

// DELETAR ANFITRIÃO
if ($action === 'delete_anfitriao') {
    $id = (int) ($_POST['id'] ?? 0);
    $success_delete = false;
    $error_msg = '';
    
    if ($id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM anfitrioes WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $success_delete = $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if (!$success_delete) {
                $error_msg = $mysqli->error;
            }
        } else {
            $error_msg = $mysqli->error;
        }
    } else {
        $error_msg = "ID inválido";
    }
    
    // Se for AJAX, retorna JSON
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success_delete && $affected_rows > 0,
            'message' => $success_delete && $affected_rows > 0 ? 'Anfitrião deletado com sucesso!' : ('Erro ao deletar: ' . $error_msg)
        ]);
        exit;
    }
    
    // Se for requisição normal, redireciona
    if ($success_delete) {
        $_SESSION['admin_success'] = "✓ Anfitrião deletado com sucesso!";
    } else {
        $_SESSION['admin_error'] = "✗ Erro ao deletar anfitrião: " . $error_msg;
    }
    
    header('Location: admin.php#anfitrioes');
    exit;
}

// ATUALIZAR STATUS DO PAGBANK VIA ADMIN
if ($action === 'refresh_pagbank_status') {
    $table = $_POST['table'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if (!in_array($table, ['peregrinos', 'anfitrioes'], true) || $id <= 0) {
        sendJsonResponse(['success' => false, 'message' => 'Tabela ou ID inválido'], 400);
    }

    $stmt = $mysqli->prepare("SELECT pagbank_checkout_id FROM `$table` WHERE id = ? LIMIT 1");
    if (!$stmt) {
        sendJsonResponse(['success' => false, 'message' => 'Erro ao preparar consulta de checkout'], 500);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (empty($row['pagbank_checkout_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Checkout do PagBank não encontrado para essa inscrição.'], 404);
    }

    $refresh = refreshPagbankRegistrationStatus($mysqli, $table, $id, $row['pagbank_checkout_id']);
    if (!$refresh['ok']) {
        sendJsonResponse(['success' => false, 'message' => $refresh['message'] ?? 'Não foi possível atualizar o status do PagBank.']);
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Status do PagBank atualizado com sucesso.',
        'data' => [
            'pagbank_status' => $refresh['pagbank_status'] ?? null,
            'payment_id' => $refresh['payment_id'] ?? null,
            'checkout_url' => $refresh['checkout_url'] ?? null,
            'body' => $refresh['body'] ?? null,
        ],
    ]);
}

// ATIVAR CHECKOUT DO PAGBANK VIA ADMIN
if ($action === 'activate_pagbank_checkout') {
    $table = $_POST['table'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if (!in_array($table, ['peregrinos', 'anfitrioes'], true) || $id <= 0) {
        sendJsonResponse(['success' => false, 'message' => 'Tabela ou ID inválido'], 400);
    }

    $stmt = $mysqli->prepare("SELECT pagbank_checkout_id FROM `$table` WHERE id = ? LIMIT 1");
    if (!$stmt) {
        sendJsonResponse(['success' => false, 'message' => 'Erro ao preparar consulta de checkout'], 500);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (empty($row['pagbank_checkout_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Checkout do PagBank não encontrado para essa inscrição.'], 404);
    }

    $activate = activatePagbankCheckout($row['pagbank_checkout_id']);
    if (!$activate['ok']) {
        sendJsonResponse(['success' => false, 'message' => $activate['message'] ?? 'Não foi possível ativar o checkout.']);
    }

    $refresh = refreshPagbankRegistrationStatus($mysqli, $table, $id, $row['pagbank_checkout_id']);
    sendJsonResponse([
        'success' => true,
        'message' => 'Checkout ativado e status atualizado com sucesso.',
        'data' => [
            'pagbank_status' => $refresh['pagbank_status'] ?? null,
            'payment_id' => $refresh['payment_id'] ?? null,
            'checkout_url' => $refresh['checkout_url'] ?? null,
            'body' => $refresh['body'] ?? null,
        ],
    ]);
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

// ATIVAR/DESATIVAR INSCRIÇÃO
if ($action === 'toggle_active') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $user_table = $_POST['user_table'] ?? '';

    if ($user_id <= 0 || !in_array($user_table, ['peregrinos', 'anfitrioes'])) {
        $_SESSION['admin_error'] = "✗ Dados inválidos";
        header('Location: admin.php');
        exit;
    }

    // Buscar status atual
    $stmt = $mysqli->prepare("SELECT is_active FROM `$user_table` WHERE id = ?");
    if (!$stmt) {
        $_SESSION['admin_error'] = "✗ Erro ao buscar registro";
        header('Location: admin.php');
        exit;
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $_SESSION['admin_error'] = "✗ Registro não encontrado";
        header('Location: admin.php');
        exit;
    }

    $new_active = !$row['is_active']; // Toggle

    // Atualizar
    $stmt = $mysqli->prepare("UPDATE `$user_table` SET is_active = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['admin_error'] = "✗ Erro ao atualizar registro";
        header('Location: admin.php');
        exit;
    }
    $stmt->bind_param('ii', $new_active, $user_id);
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = $new_active ? "✓ Inscrição ativada com sucesso!" : "✓ Inscrição desativada com sucesso!";
    } else {
        $_SESSION['admin_error'] = "✗ Erro ao atualizar inscrição";
    }
    $stmt->close();

    header('Location: admin.php');
    exit;
}

// APROVAR COMPROVANTE
if ($action === 'approve_receipt') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $user_table = $_POST['user_table'] ?? '';

    if ($user_id <= 0 || !in_array($user_table, ['peregrinos', 'anfitrioes'])) {
        $_SESSION['admin_error'] = "✗ Dados inválidos";
        header('Location: admin.php');
        exit;
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("
        UPDATE `$user_table` 
        SET payment_status = 'confirmado', payment_confirmed_by = ?, payment_confirmed_at = ? 
        WHERE id = ?
    ");

    if (!$stmt) {
        $_SESSION['admin_error'] = "✗ Erro ao aprovar comprovante";
        header('Location: admin.php');
        exit;
    }

    $stmt->bind_param('ssi', $admin_username, $now, $user_id);
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = "✓ Comprovante aprovado e inscrição confirmada!";
    } else {
        $_SESSION['admin_error'] = "✗ Erro ao aprovar comprovante";
    }
    $stmt->close();

    header('Location: admin.php');
    exit;
}

if ($is_ajax) {
    sendJsonResponse(['success' => false, 'message' => 'Ação inválida ou não reconhecida'], 400);
}

header('Location: admin.php');
exit;
