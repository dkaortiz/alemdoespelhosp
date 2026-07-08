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
$endereco = trim($_POST['endereco'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$problema_saude = $_POST['problema_saude'] ?? 'nao';
$problema_saude_descricao = trim($_POST['problema_saude_descricao'] ?? '');
$usa_remedio = $_POST['usa_remedio'] ?? 'nao';
$remedio_descricao = trim($_POST['remedio_descricao'] ?? '');
$payment_method = 'pagbank';

// Validação básica
if (empty($nome) || empty($email) || empty($whatsapp) || empty($endereco) || empty($problema_saude) || empty($usa_remedio)) {
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

    if (empty($genero)) {
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
    $pagbank_payment_link = 'https://pag.ae/81XVfHnnR';
    $payment_link_type = 'peregrino';

    $stmt = $mysqli->prepare("
        INSERT INTO peregrinos 
        (nome, email, endereco, whatsapp, genero, problema_saude, problema_saude_descricao, usa_remedio, remedio_descricao, payment_method, payment_status, payment_amount, valor, criado_em, pagbank_payment_link, payment_link_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        header('Location: inscricao.php?error=Erro%20ao%20salvar%20inscrição');
        exit;
    }

    $types = str_repeat('s', 11) . 'ddss';
    $stmt->bind_param($types, $nome, $email, $endereco, $whatsapp, $genero, $problema_saude, $problema_saude_descricao, $usa_remedio, $remedio_descricao, $payment_method, $payment_status, $payment_amount, $payment_amount, $criado_em, $pagbank_payment_link, $payment_link_type);
    $stmt->execute();
    $id = $mysqli->insert_id;
    $stmt->close();

    // Calcular valor e criar checkout PagBank
    $amountCents = (int) round($payment_amount * 100);
    $checkoutResult = createPagbankCheckout($id, 'peregrino', $nome, $email, $amountCents);
    if ($checkoutResult['success'] && !empty($checkoutResult['checkout_url'])) {
        savePagbankCheckoutData($mysqli, 'peregrinos', $id, $checkoutResult);
    }

    $_SESSION['registration_id'] = $id;
    $_SESSION['registration_type'] = 'peregrino';
    $_SESSION['pagbank_checkout_url'] = $checkoutResult['checkout_url'] ?? null;
    $_SESSION['pagbank_checkout_id'] = $checkoutResult['checkout_id'] ?? null;
    $_SESSION['pagbank_reference_id'] = $checkoutResult['reference_id'] ?? null;

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
    $payment_amount = 100.00;
    $payment_status = 'pendente';
    $criado_em = date('Y-m-d H:i:s');
    $pagbank_payment_link = 'https://pag.ae/81XVgCdNJ';
    $payment_link_type = 'anfitriao';

    $stmt = $mysqli->prepare("
        INSERT INTO anfitrioes 
        (nome, email, endereco, whatsapp, funcao, peregrino_anterior, problema_saude, problema_saude_descricao, usa_remedio, remedio_descricao, payment_method, payment_status, payment_amount, valor, criado_em, pagbank_payment_link, payment_link_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        header('Location: inscricao.php?error=Erro%20ao%20salvar%20inscrição');
        exit;
    }

    $types = str_repeat('s', 5) . 'i' . str_repeat('s', 5) . 'ddss' . 's';
    $stmt->bind_param($types, $nome, $email, $endereco, $whatsapp, $funcao, $foi_peregrino, $problema_saude, $problema_saude_descricao, $usa_remedio, $remedio_descricao, $payment_method, $payment_status, $payment_amount, $payment_amount, $criado_em, $pagbank_payment_link, $payment_link_type);
    $stmt->execute();
    $id = $mysqli->insert_id;
    $stmt->close();

    // Calcular valor e criar checkout PagBank
    $amountCents = (int) round($payment_amount * 100);
    $checkoutResult = createPagbankCheckout($id, 'anfitriao', $nome, $email, $amountCents);
    if ($checkoutResult['success'] && !empty($checkoutResult['checkout_url'])) {
        savePagbankCheckoutData($mysqli, 'anfitrioes', $id, $checkoutResult);
    }

    $_SESSION['registration_id'] = $id;
    $_SESSION['registration_type'] = 'anfitriao';
    $_SESSION['pagbank_checkout_url'] = $checkoutResult['checkout_url'] ?? null;
    $_SESSION['pagbank_checkout_id'] = $checkoutResult['checkout_id'] ?? null;
    $_SESSION['pagbank_reference_id'] = $checkoutResult['reference_id'] ?? null;

    // Redirecionar anfitrião para página de pagamento
    header('Location: payment.php');
    exit;
} else {
    header('Location: inscricao.php?error=Tipo%20inválido');
    exit;
}
