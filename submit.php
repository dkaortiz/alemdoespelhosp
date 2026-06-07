<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscricao.php');
    exit;
}

$form_type = $_POST['form_type'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';

// Validação básica
if (empty($nome) || empty($email) || empty($payment_method)) {
    header('Location: inscricao.php?error=Dados%20incompletos');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: inscricao.php?error=Email%20inválido');
    exit;
}

if ($form_type === 'peregrino') {
    $genero = $_POST['genero'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    if (empty($genero) || empty($categoria)) {
        header('Location: inscricao.php?error=Dados%20incompletos');
        exit;
    }

    // Verificar limite de vagas por gênero (usar limites da edição atual)
    $limitsStmt = $mysqli->prepare("SELECT limite_homens, limite_mulheres FROM edicoes ORDER BY ano DESC LIMIT 1");
    $limit_homens = 15;
    $limit_mulheres = 15;
    if ($limitsStmt) {
        $limitsStmt->execute();
        $limitsRes = $limitsStmt->get_result();
        $limitsRow = $limitsRes->fetch_assoc();
        if ($limitsRow) {
            $limit_homens = (int)($limitsRow['limite_homens'] ?? 15);
            $limit_mulheres = (int)($limitsRow['limite_mulheres'] ?? 15);
        }
        $limitsStmt->close();
    }

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM peregrinos WHERE genero = ? AND payment_status = 'confirmado'");
    $stmt->bind_param('s', $genero);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $currentCount = (int)($row['count'] ?? 0);
    $limitForGender = ($genero === 'masculino') ? $limit_homens : $limit_mulheres;

    if ($currentCount >= $limitForGender) {
        header('Location: inscricao.php?error=Vagas%20cheias%20para%20este%20gênero');
        exit;
    }

    // Verificar email duplicado
    $stmt = $mysqli->prepare("SELECT id FROM peregrinos WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header('Location: inscricao.php?error=Email%20já%20registrado');
        exit;
    }
    $stmt->close();

    // Inserir peregrino
    $payment_amount = 150.00;
    $payment_status = 'pendente';
    $criado_em = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("
        INSERT INTO peregrinos 
        (nome, email, telefone, whatsapp, genero, categoria, payment_method, payment_status, payment_amount, valor, criado_em)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssssssssds', $nome, $email, $telefone, $whatsapp, $genero, $categoria, $payment_method, $payment_status, $payment_amount, $payment_amount, $criado_em);
    $stmt->execute();
    $id = $mysqli->insert_id;
    $stmt->close();

    // Calcular centavos PIX
    $pix_cents = calculatePixCents($id);
    $pix_amount = calculatePixAmount($id);

    // Atualizar com centavos
    $stmt = $mysqli->prepare("UPDATE peregrinos SET pix_cents = ?, payment_amount = ? WHERE id = ?");
    $stmt->bind_param('idi', $pix_cents, $pix_amount, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['registration_id'] = $id;
    $_SESSION['registration_type'] = 'peregrino';
    $_SESSION['pix_amount'] = $pix_amount;

    header('Location: payment.php');
    exit;

} elseif ($form_type === 'anfitriao') {
    $funcao = trim($_POST['funcao'] ?? '');
    $foi_peregrino = isset($_POST['foi_peregrino']) ? 1 : 0;

    if (empty($funcao)) {
        header('Location: inscricao.php?error=Dados%20incompletos');
        exit;
    }

    // Verificar email duplicado
    $stmt = $mysqli->prepare("SELECT id FROM anfitrioes WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header('Location: inscricao.php?error=Email%20já%20registrado');
        exit;
    }
    $stmt->close();

    // Inserir anfitrião
    $payment_amount = 150.00;
    // Sempre usar 'pendente' no cadastro; anfitriões sem histórico ficarão sinalizados por peregrino_anterior = 0
    $payment_status = 'pendente';
    $criado_em = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("
        INSERT INTO anfitrioes 
        (nome, email, telefone, whatsapp, funcao, peregrino_anterior, payment_method, payment_status, payment_amount, valor, criado_em)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssssissdssd', $nome, $email, $telefone, $whatsapp, $funcao, $foi_peregrino, $payment_method, $payment_status, $payment_amount, $payment_amount, $criado_em);
    $stmt->execute();
    $id = $mysqli->insert_id;
    $stmt->close();

    // Calcular centavos PIX
    $pix_cents = calculatePixCents($id);
    $pix_amount = calculatePixAmount($id);

    // Atualizar com centavos
    $stmt = $mysqli->prepare("UPDATE anfitrioes SET pix_cents = ?, payment_amount = ? WHERE id = ?");
    $stmt->bind_param('idi', $pix_cents, $pix_amount, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['registration_id'] = $id;
    $_SESSION['registration_type'] = 'anfitriao';
    $_SESSION['pix_amount'] = $pix_amount;

    if ($foi_peregrino) {
        header('Location: payment.php');
    } else {
        header('Location: confirmation.php?pending_admin=1');
    }
    exit;
} else {
    header('Location: inscricao.php?error=Tipo%20inválido');
    exit;
}
