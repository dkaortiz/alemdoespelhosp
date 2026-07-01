<?php
declare(strict_types=1);
// Configurar diretório de sessão ANTES de iniciar
$session_dir = __DIR__ . '/sessions';
if (!is_dir($session_dir)) {
    @mkdir($session_dir, 0755, true);
}
@session_save_path($session_dir);
session_start();
require_once 'config.php';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (authenticateAdmin($username, $password)) {
        session_regenerate_id(true);
        $_SESSION[SESSION_ADMIN_AUTH] = true;
        $_SESSION['admin_username'] = $username;
        $admin_id = getAdminIdByUsername($username);
        if ($admin_id) $_SESSION['admin_id'] = $admin_id;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Credenciais inválidas';
    }
}

// Ações admin - Criar novo admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action']) && isAdminAuthenticated()) {
    $admin_action = $_POST['admin_action'];
    
    if ($admin_action === 'create_admin') {
        $new_username = $_POST['new_username'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $new_email = $_POST['new_email'] ?? null;
        
        if (strlen($new_username) >= 3 && strlen($new_password) >= 6) {
            if (createAdmin($new_username, $new_password, $new_email ?: null, 'super')) {
                $success = "✓ Admin '{$new_username}' criado com sucesso!";
            } else {
                $error_msg = "✗ Erro ao criar admin. Usuário pode já existir.";
            }
        } else {
            $error_msg = "✗ Usuário deve ter 3+ caracteres e senha 6+ caracteres.";
        }
    } elseif ($admin_action === 'delete_admin') {
        $del_admin_id = intval($_POST['del_admin_id'] ?? 0);
        if ($del_admin_id > 0 && $del_admin_id !== ($_SESSION['admin_id'] ?? null)) {
            if (deleteAdmin($del_admin_id)) {
                $success = "✓ Admin deletado com sucesso!";
            } else {
                $error_msg = "✗ Erro ao deletar admin. Talvez seja o único super admin.";
            }
        }
    }
}

// Ações admin - Aprovar/Rejeitar pagamentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isAdminAuthenticated()) {
    $action = $_POST['action'];
    $target = $_POST['target'] ?? '';
    $target_id = intval($_POST['target_id'] ?? 0);
    $admin_username = $_SESSION['admin_username'] ?? 'admin';

    if ($action === 'approve' && in_array($target, ['peregrinos','anfitrioes'])) {
        $stmt = $mysqli->prepare("UPDATE {$target} SET payment_status = 'confirmado', payment_confirmed_by = ?, payment_confirmed_at = NOW() WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $admin_username, $target_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: admin.php');
    exit;
}

$lookup_result = null;
$lookup_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lookup_registration']) && isAdminAuthenticated()) {
    $lookup_email = trim($_POST['lookup_email'] ?? '');
    $lookup_phone = trim($_POST['lookup_phone'] ?? '');
    $lookup_phone_digits = normalizePhoneForLookup($lookup_phone);

    if ($lookup_email === '' && $lookup_phone_digits === '') {
        $lookup_error = 'Informe o e-mail ou telefone para buscar.';
    } else {
        $search_tables = [
            ['table' => 'peregrinos', 'label' => 'Peregrino'],
            ['table' => 'anfitrioes', 'label' => 'Anfitrião'],
        ];

        foreach ($search_tables as $candidate) {
            $table = $candidate['table'];
            $query = "SELECT id, nome, email, telefone, payment_status, pagbank_checkout_id, pagbank_status, pagbank_payment_id, pagbank_payload FROM `$table` WHERE 1=1";
            $params = [];
            $types = '';

            if ($lookup_email !== '') {
                $query .= " AND email = ?";
                $params[] = $lookup_email;
                $types .= 's';
            }

            if ($lookup_phone_digits !== '') {
                $query .= " AND REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', '') LIKE ?";
                $params[] = '%' . $lookup_phone_digits . '%';
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
                $checkout_id = $row['pagbank_checkout_id'] ?? null;
                $refresh = null;
                if (!empty($checkout_id)) {
                    $refresh = refreshPagbankRegistrationStatus($mysqli, $table, (int) $row['id'], $checkout_id);
                }

                $lookup_result = [
                    'table' => $table,
                    'label' => $candidate['label'],
                    'row' => $row,
                    'refresh' => $refresh,
                ];
                break;
            }
        }

        if (!$lookup_result) {
            $lookup_error = 'Nenhuma inscrição encontrada com esses dados.';
        }
    }
}

if (!isAdminAuthenticated()) {
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Além do Espelho</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(67, 56, 202, 0.3);
            background: rgba(67, 56, 202, 0.05);
            color: var(--text);
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(67, 56, 202, 0.1);
        }
        .form-group select {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(67, 56, 202, 0.3);
            background: rgba(67, 56, 202, 0.05);
            color: var(--text);
        }
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(67, 56, 202, 0.1);
        }
    </style>
</head>
<body>
    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="container" style="max-width: 450px;">
            <div class="glass-strong" style="
                padding: 3.5rem;
                border-radius: 20px;
                animation: fadeInUp 0.8s ease;
                border: 1px solid rgba(67, 56, 202, 0.3);
                background: linear-gradient(135deg, rgba(67, 56, 202, 0.08), rgba(124, 58, 237, 0.05));
            ">
                <div style="text-align: center; margin-bottom: 2.5rem;">
                    <h1 style="
                        font-size: 2rem;
                        background: linear-gradient(135deg, #4338CA, #7c3aed);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        margin: 0 0 0.5rem;
                    ">🔐 Painel Admin</h1>
                    <p style="color: var(--muted); margin: 0;">Acesso restrito</p>
                </div>
                
                <?php if (isset($error)): ?>
                <div style="
                    background: rgba(239, 68, 68, 0.15);
                    border: 1px solid rgba(239, 68, 68, 0.3);
                    color: #fecaca;
                    padding: 1rem;
                    border-radius: 12px;
                    margin-bottom: 1.5rem;
                    font-size: 0.95rem;
                ">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Usuário</label>
                        <input type="text" name="username" placeholder="Seu usuário" required autofocus>
                    </div>

                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="password" placeholder="Sua senha" required>
                    </div>

                    <input type="hidden" name="admin_login" value="1">
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">
                        🔓 Entrar no Painel
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
    <?php
    exit;
}

// Buscar estatísticas
$success_msg = $_SESSION['admin_success'] ?? null;
$error_msg_session = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// Atualizar configurações de limites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_limits']) && isAdminAuthenticated()) {
    try {
        $new_limite_homem = intval($_POST['limite_homem'] ?? 15);
        $new_limite_mulher = intval($_POST['limite_mulher'] ?? 15);
        $new_limite_anfitriao = intval($_POST['limite_anfitriao'] ?? 999);
        
        // Primeiro, buscar o ID da edição atual
        $edicao_atual = $mysqli->query("SELECT id FROM edicoes ORDER BY ano DESC LIMIT 1");
        if (!$edicao_atual) {
            throw new Exception("Erro na query: " . $mysqli->error);
        }
        
        $edicao_id_row = $edicao_atual->fetch_assoc();
        if (!$edicao_id_row) {
            throw new Exception("Nenhuma edição encontrada no banco de dados");
        }
        
        $edicao_id = $edicao_id_row['id'];
        
        // Agora atualizar usando o ID
        $stmt = $mysqli->prepare("UPDATE edicoes SET limite_homens = ?, limite_mulheres = ?, limite_anfitrioes = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $mysqli->error);
        }
        
        $stmt->bind_param('iiii', $new_limite_homem, $new_limite_mulher, $new_limite_anfitriao, $edicao_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar update: " . $stmt->error);
        }
        
        $_SESSION['admin_success'] = "✓ Limites de vagas atualizados com sucesso!";
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "✗ Erro ao atualizar limites: " . $e->getMessage();
    }
    
    header('Location: admin.php#config');
    exit;
}

// Atualizar informações da edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edition']) && isAdminAuthenticated()) {
    try {
        $edicao_id = intval($_POST['edicao_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_inicio = $_POST['data_inicio'] ?? null;
        $data_fim = $_POST['data_fim'] ?? null;
        $local = trim($_POST['local'] ?? '');
        $data_inscricao_inicio = $_POST['data_inscricao_inicio'] ?? null;
        $data_inscricao_fim = $_POST['data_inscricao_fim'] ?? null;
        $hora_inicio = trim((string)($_POST['hora_inicio'] ?? ''));
        $hora_fim = trim((string)($_POST['hora_fim'] ?? ''));
        $hora_inscricao_inicio = trim((string)($_POST['hora_inscricao_inicio'] ?? ''));
        $hora_inscricao_fim = trim((string)($_POST['hora_inscricao_fim'] ?? ''));
        
        if (!$titulo) {
            throw new Exception("Título é obrigatório");
        }
        
        if ($edicao_id <= 0) {
            throw new Exception("Edição não selecionada");
        }
        
        $stmt = $mysqli->prepare("UPDATE edicoes SET titulo = ?, descricao = ?, data_inicio = ?, data_fim = ?, local = ?, data_inscricao_inicio = ?, data_inscricao_fim = ?, hora_inicio = NULLIF(?, ''), hora_fim = NULLIF(?, ''), hora_inscricao_inicio = NULLIF(?, ''), hora_inscricao_fim = NULLIF(?, '') WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $mysqli->error);
        }
        
        $stmt->bind_param('sssssssssssi', $titulo, $descricao, $data_inicio, $data_fim, $local, $data_inscricao_inicio, $data_inscricao_fim, $hora_inicio, $hora_fim, $hora_inscricao_inicio, $hora_inscricao_fim, $edicao_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar update: " . $stmt->error);
        }
        
        $_SESSION['admin_success'] = "✓ Edição atualizada com sucesso! Mudanças visíveis em index.php e edicoes.php";
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "✗ Erro ao atualizar edição: " . $e->getMessage();
    }
    
    header('Location: admin.php#edicoes');
    exit;
}

// Buscar limites da edição atual
$edicao = $mysqli->query("SELECT * FROM edicoes ORDER BY ano DESC LIMIT 1");
$edicao_row = $edicao->fetch_assoc();
$limite_homem = $edicao_row['limite_homens'] ?? 15;
$limite_mulher = $edicao_row['limite_mulheres'] ?? 15;
$limite_anfitriao = $edicao_row['limite_anfitrioes'] ?? 999;

$peregrinos = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos");
$peregrinos_row = $peregrinos->fetch_assoc();
$total_peregrinos = $peregrinos_row['total'];

$anfitrioes = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes");
$anfitrioes_row = $anfitrioes->fetch_assoc();
$total_anfitrioes = $anfitrioes_row['total'];

$peregrinos_confirmed = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'confirmado'");
$peregrinos_confirmed_row = $peregrinos_confirmed->fetch_assoc();
$total_confirmed = $peregrinos_confirmed_row['total'];

// Vagas restantes
$total_vagas_peregrino = $limite_homem + $limite_mulher;
$vagas_restantes = $total_vagas_peregrino - $total_confirmed;

// Homens vs Mulheres
$homens = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE genero = 'masculino' AND payment_status = 'confirmado'");
$homens_row = $homens->fetch_assoc();
$homens_count = $homens_row['total'];

$mulheres = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE genero = 'feminino' AND payment_status = 'confirmado'");
$mulheres_row = $mulheres->fetch_assoc();
$mulheres_count = $mulheres_row['total'];

// Total anfitriões confirmados
$anfitrioes_confirmed = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'confirmado'");
$anfitrioes_confirmed_row = $anfitrioes_confirmed->fetch_assoc();
$total_anfitrioes_confirmed = $anfitrioes_confirmed_row['total'];

// Total arrecadado
$result1 = $mysqli->query("SELECT SUM(payment_amount) as total FROM peregrinos WHERE payment_status = 'confirmado'");
$result2 = $mysqli->query("SELECT SUM(payment_amount) as total FROM anfitrioes WHERE payment_status = 'confirmado'");
$row1 = $result1->fetch_assoc();
$row2 = $result2->fetch_assoc();
$total_arrecadado = ($row1['total'] ?? 0) + ($row2['total'] ?? 0);

// Pendentes de confirmação
   $pendentes = $mysqli->query(
       "(SELECT 'peregrino' as tipo, id, nome, email, payment_status, payment_amount, pix_cents FROM peregrinos WHERE payment_status = 'comprovante_enviado')"
       . " UNION ALL "
       . "(SELECT 'anfitriao' as tipo, id, nome, email, payment_status, payment_amount, pix_cents FROM anfitrioes WHERE payment_status = 'comprovante_enviado' OR (payment_status = 'pendente' AND peregrino_anterior = 0))"
       . " ORDER BY id DESC"
   );

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Além do Espelho</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
    <style>
        .stat-card {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(255, 107, 157, 0.2);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border);
            animation: slideInUp 0.6s ease;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--muted);
            padding: 1rem 1.5rem;
            cursor: pointer;
            font-size: 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-btn:hover {
            color: var(--text);
        }

        .tab-content {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            animation: fadeInUp 0.6s ease;
        }

        th {
            background: var(--surface);
            color: var(--text);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tr:hover {
            background: rgba(255, 107, 157, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pendente {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .status-enviado {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .status-confirmado {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .status-cancelado {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-approve {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid #10b981;
        }

        .btn-approve:hover {
            background: rgba(16, 185, 129, 0.3);
            transform: scale(1.05);
        }

        .btn-reject {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .btn-reject:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: scale(1.05);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- ADMIN HEADER -->
    <header style="background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1)); border-bottom: 2px solid rgba(67, 56, 202, 0.3); backdrop-filter: blur(10px);">
        <nav class="container">
            <div class="header-inner" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
                <!-- BRAND ADMIN -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 1.8rem;">🔐</div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.3rem; color: var(--primary); font-weight: 700;">Painel Admin</h2>
                        <p style="margin: 0; font-size: 0.8rem; color: var(--muted);">Usuário: <span style="color: var(--accent); font-weight: 600;"><?= htmlspecialchars($_SESSION['admin_username']) ?></span></p>
                    </div>
                </div>

                <!-- ADMIN MENU (Desktop) -->
                <div class="site-nav" style="gap: 0; flex: 0; display: none;">
                    <!-- Items here will be shown only on desktop -->
                </div>

                <!-- LOGOUT BUTTON -->
                <a href="admin.php?logout=1" style="
                    padding: 0.7rem 1.5rem;
                    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
                    border: 1px solid rgba(239, 68, 68, 0.4);
                    color: #ef4444;
                    border-radius: 8px;
                    text-decoration: none;
                    cursor: pointer;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                " onmouseover="this.style.background='linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(239, 68, 68, 0.15))'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1))'; this.style.transform='scale(1)'">
                    🚪 Sair
                </a>
            </div>
        </nav>

        <!-- ADMIN STATS BAR (abaixo do header) -->
        <div style="background: rgba(67, 56, 202, 0.05); border-top: 1px solid rgba(67, 56, 202, 0.2); padding: 1rem 0;">
            <div class="container">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <p style="margin: 0; color: var(--muted); font-size: 0.85rem;">📊 Total Inscritos</p>
                        <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: 700; color: var(--primary);"><?= $total_peregrinos + $total_anfitrioes ?></p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--muted); font-size: 0.85rem;">✅ Confirmados</p>
                        <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: 700; color: #10b981;"><?= $total_confirmed + $total_anfitrioes_confirmed ?></p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--muted); font-size: 0.85rem;">⏳ Pendentes</p>
                        <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: 700; color: #f59e0b;"><?= $pendentes->num_rows ?></p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--muted); font-size: 0.85rem;">💰 Arrecadado</p>
                        <p style="margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: 700; color: var(--accent);">R$ <?= number_format($total_arrecadado, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- DASHBOARD HEADER -->
        <section class="section">
            <div class="container">
                <h1 style="margin-bottom: 3rem; animation: fadeInUp 0.6s ease;">Dashboard Admin</h1>

                <div class="glass-strong" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
                    <h3 style="margin-top: 0; color: var(--primary);">🔎 Buscar inscrição por e-mail ou telefone</h3>
                    <form method="post" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>E-mail</label>
                            <input type="email" name="lookup_email" placeholder="seu@email.com">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Telefone</label>
                            <input type="tel" name="lookup_phone" placeholder="11999999999">
                        </div>
                        <div>
                            <input type="hidden" name="lookup_registration" value="1">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Buscar</button>
                        </div>
                    </form>

                    <?php if (!empty($lookup_error)): ?>
                        <div style="margin-top: 1rem; padding: 0.9rem 1rem; border-radius: 12px; background: rgba(239, 68, 68, 0.14); color: #fecaca;">
                            <?php echo htmlspecialchars($lookup_error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($lookup_result): ?>
                        <?php $lookup_row = $lookup_result['row']; ?>
                        <div style="margin-top: 1rem; padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08);">
                            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($lookup_result['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($lookup_row['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($lookup_row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($lookup_row['telefone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Status no sistema:</strong> <?php echo htmlspecialchars(ucfirst($lookup_row['payment_status'] ?? 'pendente'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Status no PagBank:</strong> <?php echo htmlspecialchars($lookup_result['refresh']['pagbank_status'] ?? $lookup_row['pagbank_status'] ?? 'Não consultado', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>ID do pagamento:</strong> <?php echo htmlspecialchars($lookup_result['refresh']['payment_id'] ?? $lookup_row['pagbank_payment_id'] ?? 'Não disponível', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ESTATÍSTICAS -->
                <div class="cards-grid" style="grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
                    <div class="glass-strong stat-card" style="padding: 2rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0 0 0.5rem;">Total Peregrinos</p>
                        <div class="stat-value"><?= $total_peregrinos ?></div>
                        <p style="color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem;">Inscritos</p>
                    </div>

                    <div class="glass-strong stat-card" style="padding: 2rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0 0 0.5rem;">Confirmados</p>
                        <div class="stat-value"><?= $total_confirmed ?>/30</div>
                        <p style="color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem;">Pagamento OK</p>
                    </div>

                    <div class="glass-strong stat-card" style="padding: 2rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0 0 0.5rem;">Vagas Restantes</p>
                        <div class="stat-value" style="color: <?= $vagas_restantes <= 5 ? '#ef4444' : '#10b981' ?>;"><?= $vagas_restantes ?></div>
                        <p style="color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem;">Peregrinos</p>
                    </div>

                    <div class="glass-strong stat-card" style="padding: 2rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0 0 0.5rem;">Arrecadado</p>
                        <div class="stat-value">R$ <?= number_format($total_arrecadado, 2, ',', '.') ?></div>
                        <p style="color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem;">Confirmado</p>
                    </div>
                </div>

                <!-- MINI STATS -->
                <div class="cards-grid" style="grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
                    <div class="glass" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0;">👨 Homens: <?= $homens_count ?>/<?= $limite_homem ?></p>
                        <div style="width: 100%; height: 8px; background: rgba(212, 175, 55, 0.1); border-radius: 4px; margin-top: 0.5rem; overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); width: <?= min(100, ($homens_count / $limite_homem) * 100) ?>%;"></div>
                        </div>
                    </div>

                    <div class="glass" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0;">👩 Mulheres: <?= $mulheres_count ?>/<?= $limite_mulher ?></p>
                        <div style="width: 100%; height: 8px; background: rgba(212, 175, 55, 0.1); border-radius: 4px; margin-top: 0.5rem; overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); width: <?= min(100, ($mulheres_count / $limite_mulher) * 100) ?>%;"></div>
                        </div>
                    </div>

                    <div class="glass" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0;">👥 Anfitriões: <?= $total_anfitrioes_confirmed ?></p>
                        <p style="font-size: 2rem; color: var(--accent); font-weight: 700; margin: 0.5rem 0 0;">✓ Confirmados</p>
                    </div>

                    <div class="glass" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                        <p style="color: var(--muted); margin: 0;">⏳ Pendentes</p>
                        <p style="font-size: 2rem; color: #f59e0b; font-weight: 700; margin: 0.5rem 0 0;"><?= $pendentes->num_rows ?></p>
                    </div>
                </div>

                <!-- SEÇÃO DE CONFIGURAÇÕES -->
                <div id="config" class="glass-strong" style="padding: 2rem; border-radius: 12px; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.05), rgba(244, 208, 63, 0.03));">
                    <h3 style="color: var(--primary); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                        ⚙️ Configurações - Edição <?= $edicao_row['ano'] ?? 2026 ?>
                    </h3>
                    
                    <?php if (isset($success_msg)): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                        ✓ <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_msg_session)): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                        ⚠️ <?php echo htmlspecialchars($error_msg_session); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                        <input type="hidden" name="save_limits" value="1">
                        
                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Homens</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" name="limite_homem" value="<?= $limite_homem ?>" min="0" required style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; white-space: nowrap;">✓ OK</button>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Mulheres</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" name="limite_mulher" value="<?= $limite_mulher ?>" min="0" required style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; white-space: nowrap;">✓ OK</button>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Anfitriões</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" name="limite_anfitriao" value="<?= $limite_anfitriao ?>" min="0" required style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; white-space: nowrap;">✓ OK</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- TABS E TABELAS -->
        <section class="section">
            <div class="container">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('cadastro', this)">➕ Cadastro</button>
                    <button class="tab-btn" onclick="switchTab('pendentes', this)">⏳ Pendentes</button>
                    <button class="tab-btn" onclick="switchTab('peregrinos', this)">🧘 Peregrinos</button>
                    <button class="tab-btn" onclick="switchTab('anfitrioes', this)">👥 Anfitriões</button>
                    <button class="tab-btn" onclick="switchTab('edicoes', this)">📅 Edições</button>
                    <button class="tab-btn" onclick="switchTab('admins', this)">🔐 Usuários</button>
                </div>

                <!-- TAB: CADASTRAR INTEGRANTE -->
                <div id="cadastro" class="tab-content active">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                        <h2 style="color: var(--primary); margin-top: 0;">➕ Cadastrar Novo Integrante</h2>
                        <p style="color: var(--muted); margin-bottom: 2rem;">Cadastre um peregrino ou anfitrião diretamente no painel</p>

                        <?php if (isset($success_msg)): ?>
                        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.95rem;">
                            ✓ <?php echo htmlspecialchars($success_msg); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_msg_session)): ?>
                        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.95rem;">
                            ⚠️ <?php echo htmlspecialchars($error_msg_session); ?>
                        </div>
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <!-- CADASTRO PEREGRINO -->
                            <div style="background: rgba(67, 56, 202, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                                <h3 style="color: var(--primary); margin-top: 0;">🧘 Cadastrar Peregrino</h3>
                                <form method="post" action="admin_action.php">
                                    <input type="hidden" name="action" value="admin_cadastro_peregrino">
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Nome Completo *</label>
                                        <input type="text" name="nome" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Email *</label>
                                        <input type="email" name="email" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Gênero *</label>
                                        <select name="genero" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                            <option value="">Selecione...</option>
                                            <option value="masculino">Masculino ♂️</option>
                                            <option value="feminino">Feminino ♀️</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Telefone</label>
                                        <input type="tel" name="telefone" style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">
                                            <input type="checkbox" name="payment_confirmed" value="1"> ✓ Marcar como pagamento confirmado
                                        </label>
                                    </div>
                                    <button type="submit" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; margin-top: 1rem;">Cadastrar Peregrino</button>
                                </form>
                            </div>

                            <!-- CADASTRO ANFITRIÃO -->
                            <div style="background: rgba(244, 208, 63, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                                <h3 style="color: var(--accent); margin-top: 0;">👥 Cadastrar Anfitrião</h3>
                                <form method="post" action="admin_action.php">
                                    <input type="hidden" name="action" value="admin_cadastro_anfitriao">
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Nome Completo *</label>
                                        <input type="text" name="nome" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Email *</label>
                                        <input type="email" name="email" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Função *</label>
                                        <input type="text" name="funcao" placeholder="ex: Cozinheiro, Coordenador..." required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">Telefone</label>
                                        <input type="tel" name="telefone" style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--muted); font-size: 0.9rem;">
                                            <input type="checkbox" name="payment_confirmed" value="1"> ✓ Marcar como pagamento confirmado
                                        </label>
                                    </div>
                                    <button type="submit" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, var(--accent), var(--primary)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; margin-top: 1rem;">Cadastrar Anfitrião</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: PENDENTES -->
                <div id="pendentes" class="tab-content">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px; overflow-x: auto;">
                        <?php if ($pendentes->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Valor PIX</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pendentes->fetch_assoc()): ?>
                                    <tr style="animation: fadeInUp 0.5s ease;">
                                        <td><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span style="color: <?= $row['tipo'] === 'peregrino' ? 'var(--primary)' : 'var(--accent)'; ?>">
                                                <?= ucfirst($row['tipo']) ?>
                                            </span>
                                        </td>
                                        <td><strong>R$ 150,<?= str_pad($row['pix_cents'], 2, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td>
                                            <span class="status-badge status-enviado">
                                                Comprovante Enviado
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" action="admin_action.php" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="tipo" value="<?= $row['tipo'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-action btn-approve">✓ Aprovar</button>
                                            </form>
                                            <form method="post" action="admin_action.php" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="tipo" value="<?= $row['tipo'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-action btn-reject">✗ Rejeitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--muted); padding: 2rem;">Nenhum pendente de confirmação 🎉</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB: PEREGRINOS -->
                <div id="peregrinos" class="tab-content">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px; overflow-x: auto;">
                        <?php 
                        $peregrinos_all = $mysqli->query("
                            SELECT id, nome, email, genero, payment_status, payment_amount, criado_em 
                            FROM peregrinos 
                            ORDER BY criado_em DESC
                        ");
                        ?>
                        <?php if ($peregrinos_all->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Gênero</th>
                                        <th>Status</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $peregrinos_all->fetch_assoc()): ?>
                                    <tr style="animation: fadeInUp 0.5s ease;">
                                        <td><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span style="color: <?= $row['genero'] === 'masculino' ? '#3b82f6' : '#ec4899'; ?>;">
                                                <?= $row['genero'] === 'masculino' ? '👨' : '👩' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $row['payment_status'] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $row['payment_status'])) ?>
                                            </span>
                                        </td>
                                        <td>R$ <?= number_format($row['payment_amount'] ?? 150, 2, ',', '.') ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['criado_em'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--muted); padding: 2rem;">Nenhum peregrino inscrito ainda</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB: ANFITRIÕES -->
                <div id="anfitrioes" class="tab-content">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px; overflow-x: auto;">
                        <?php 
                        $anfitrioes_all = $mysqli->query("
                            SELECT id, nome, email, funcao, payment_status, payment_amount, criado_em 
                            FROM anfitrioes 
                            ORDER BY criado_em DESC
                        ");
                        ?>
                        <?php if ($anfitrioes_all->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Função</th>
                                        <th>Status</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $anfitrioes_all->fetch_assoc()): ?>
                                    <tr style="animation: fadeInUp 0.5s ease;">
                                        <td><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['funcao']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $row['payment_status'] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $row['payment_status'])) ?>
                                            </span>
                                        </td>
                                        <td>R$ <?= number_format($row['payment_amount'] ?? 150, 2, ',', '.') ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['criado_em'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--muted); padding: 2rem;">Nenhum anfitrião inscrito ainda</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB: GERENCIAR EDIÇÕES -->
                <div id="edicoes" class="tab-content">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                        <h2 style="color: var(--primary); margin-top: 0;">📅 Gerenciar Edições do Evento</h2>
                        <p style="color: var(--muted); margin-bottom: 2rem;">Edite as informações das edições. As mudanças aparecerão automaticamente em todo o site.</p>

                        <?php if (isset($success_msg)): ?>
                        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.95rem;">
                            ✓ <?php echo htmlspecialchars($success_msg); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_msg_session)): ?>
                        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                            ⚠️ <?php echo htmlspecialchars($error_msg_session); ?>
                        </div>
                        <?php endif; ?>

                        <?php 
                        $result_editions = $mysqli->query("SELECT * FROM edicoes ORDER BY ano DESC");
                        $editions_list = $result_editions->fetch_all(MYSQLI_ASSOC);
                        ?>

                        <?php if (!empty($editions_list)): ?>
                        <form method="post" style="display: grid; gap: 2rem;">
                            <input type="hidden" name="save_edition" value="1">
                            
                            <div class="form-group">
                                <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Selecione a Edição</label>
                                <select name="edicao_id" id="edicaoSelect" required style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%; cursor: pointer; font-size: 1rem;" onchange="loadEditionData()">
                                    <option value="">-- Selecione uma edição --</option>
                                    <?php foreach ($editions_list as $ed): ?>
                                    <option value="<?= $ed['id'] ?>" data-titulo="<?= htmlspecialchars($ed['titulo']) ?>" data-descricao="<?= htmlspecialchars($ed['descricao']) ?>" data-data_inicio="<?= $ed['data_inicio'] ?>" data-data_fim="<?= $ed['data_fim'] ?>" data-local="<?= htmlspecialchars($ed['local']) ?>" data-data_inscricao_inicio="<?= $ed['data_inscricao_inicio'] ?? '' ?>" data-data_inscricao_fim="<?= $ed['data_inscricao_fim'] ?? '' ?>" data-hora_inicio="<?= htmlspecialchars((string)($ed['hora_inicio'] ?? '')) ?>" data-hora_fim="<?= htmlspecialchars((string)($ed['hora_fim'] ?? '')) ?>" data-hora_inscricao_inicio="<?= htmlspecialchars((string)($ed['hora_inscricao_inicio'] ?? '')) ?>" data-hora_inscricao_fim="<?= htmlspecialchars((string)($ed['hora_inscricao_fim'] ?? '')) ?>">
                                        <?= htmlspecialchars($ed['titulo']) ?> (<?= $ed['ano'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Título da Edição</label>
                                    <input type="text" name="titulo" id="titulo" required style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;" placeholder="Ex: O Confronto">
                                </div>

                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Local do Evento</label>
                                    <input type="text" name="local" id="local" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;" placeholder="Ex: São Paulo - SP">
                                </div>
                            </div>

                            <div class="form-group">
                                <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Descrição</label>
                                <textarea name="descricao" id="descricao" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%; min-height: 120px; resize: vertical; font-family: inherit;" placeholder="Descrição da edição..."></textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Data de Início</label>
                                    <input type="date" name="data_inicio" id="data_inicio" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;">
                                </div>

                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Data de Término</label>
                                    <input type="date" name="data_fim" id="data_fim" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Hora de Início</label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;">
                                </div>

                                <div class="form-group">
                                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-weight: 600;">Hora de Término</label>
                                    <input type="time" name="hora_fim" id="hora_fim" style="padding: 0.85rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text); width: 100%;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; background: linear-gradient(135deg, rgba(217, 70, 239, 0.1), rgba(168, 85, 247, 0.05)); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(217, 70, 239, 0.2);">
                                <div class="form-group">
                                    <label style="display: block; color: #d946ef; margin-bottom: 0.5rem; font-weight: 600;">⏰ Data Início Inscrições</label>
                                    <input type="date" name="data_inscricao_inicio" id="data_inscricao_inicio" style="padding: 0.85rem; border-radius: 8px; background: rgba(217, 70, 239, 0.15); border: 1px solid #d946ef; color: var(--text); width: 100%;">
                                </div>

                                <div class="form-group">
                                    <label style="display: block; color: #d946ef; margin-bottom: 0.5rem; font-weight: 600;">⏰ Data Término Inscrições</label>
                                    <input type="date" name="data_inscricao_fim" id="data_inscricao_fim" style="padding: 0.85rem; border-radius: 8px; background: rgba(217, 70, 239, 0.15); border: 1px solid #d946ef; color: var(--text); width: 100%;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label style="display: block; color: #d946ef; margin-bottom: 0.5rem; font-weight: 600;">⏰ Hora Início Inscrições</label>
                                    <input type="time" name="hora_inscricao_inicio" id="hora_inscricao_inicio" style="padding: 0.85rem; border-radius: 8px; background: rgba(217, 70, 239, 0.15); border: 1px solid #d946ef; color: var(--text); width: 100%;">
                                </div>

                                <div class="form-group">
                                    <label style="display: block; color: #d946ef; margin-bottom: 0.5rem; font-weight: 600;">⏰ Hora Término Inscrições</label>
                                    <input type="time" name="hora_inscricao_fim" id="hora_inscricao_fim" style="padding: 0.85rem; border-radius: 8px; background: rgba(217, 70, 239, 0.15); border: 1px solid #d946ef; color: var(--text); width: 100%;">
                                </div>
                            </div>

                            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                                <button type="submit" style="flex: 1; padding: 1rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; font-size: 1rem;">💾 Salvar Mudanças</button>
                                <button type="reset" onclick="document.getElementById('edicaoSelect').value=''; resetForm();" style="flex: 1; padding: 1rem; background: rgba(67, 56, 202, 0.2); border: 1px solid var(--primary); border-radius: 8px; color: var(--text); cursor: pointer; font-weight: 600; font-size: 1rem;">⟲ Limpar</button>
                            </div>
                        </form>
                        <?php else: ?>
                        <p style="text-align: center; color: var(--muted); padding: 2rem;">Nenhuma edição encontrada. Crie uma primeira edição no banco de dados.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB: GERENCIAR ADMINS -->
                <div id="admins" class="tab-content">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <!-- CRIAR NOVO ADMIN -->
                        <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                            <h3 style="color: var(--primary); margin-top: 0;">➕ Criar Novo Admin</h3>
                            
                            <?php if (isset($success)): ?>
                            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                                ✓ <?php echo htmlspecialchars($success); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_msg)): ?>
                            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <input type="hidden" name="admin_action" value="create_admin">
                                <div class="form-group">
                                    <label style="color: var(--muted); font-size: 0.9rem;">Usuário (mínimo 3 caracteres)</label>
                                    <input type="text" name="new_username" placeholder="novo_admin" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                </div>
                                <div class="form-group">
                                    <label style="color: var(--muted); font-size: 0.9rem;">Senha (mínimo 6 caracteres)</label>
                                    <input type="password" name="new_password" placeholder="SenhaSegura@123" required style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                </div>
                                <div class="form-group">
                                    <label style="color: var(--muted); font-size: 0.9rem;">Email (opcional)</label>
                                    <input type="email" name="new_email" placeholder="admin@exemplo.com" style="padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                </div>
                                <button type="submit" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600; margin-top: 1rem;">Criar Admin</button>
                            </form>
                        </div>

                        <!-- LISTA DE ADMINS -->
                        <div class="glass-strong" style="padding: 2rem; border-radius: 12px; overflow: auto; max-height: 500px;">
                            <h3 style="color: var(--primary); margin-top: 0;">👥 Admins Cadastrados</h3>
                            <?php 
                            $all_admins = getAllAdmins();
                            if (count($all_admins) > 0):
                            ?>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <?php foreach ($all_admins as $admin): ?>
                                    <div style="background: rgba(212, 175, 55, 0.08); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--primary); display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <p style="margin: 0 0 0.25rem; color: var(--text); font-weight: 600;"><?= htmlspecialchars($admin['username']) ?></p>
                                            <p style="margin: 0 0 0.25rem; color: var(--muted); font-size: 0.85rem;"><?= htmlspecialchars($admin['email'] ?? '-') ?></p>
                                            <p style="margin: 0; color: var(--muted); font-size: 0.8rem;">Role: <span style="color: var(--accent);"><?= htmlspecialchars($admin['role']) ?></span></p>
                                        </div>
                                        <?php if ($admin['id'] !== ($_SESSION['admin_id'] ?? null)): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="admin_action" value="delete_admin">
                                            <input type="hidden" name="del_admin_id" value="<?= $admin['id'] ?>">
                                            <button type="submit" onclick="return confirm('Tem certeza?')" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(239, 68, 68, 0.3)';" onmouseout="this.style.background='rgba(239, 68, 68, 0.2)';">Deletar</button>
                                        </form>
                                        <?php else: ?>
                                        <span style="color: var(--accent); font-size: 0.85rem; font-weight: 600;">Você</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: var(--muted); text-align: center;">Nenhum admin cadastrado</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="<?php echo assetVersion('script.js'); ?>"></script>
    <script>
        function toggleMobileMenu() {
            const hamburger = document.querySelector('.hamburger-menu');
            const menu = document.getElementById('mobileMenu');
            hamburger.classList.toggle('active');
            menu.classList.toggle('active');
        }
        
        // Fechar menu ao clicar em um link
        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.hamburger-menu').classList.remove('active');
                document.getElementById('mobileMenu').classList.remove('active');
            });
        });
        
        function switchTab(tabName, button) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const targetTab = document.getElementById(tabName);
            if (targetTab) {
                targetTab.classList.add('active');
            }
            if (button) {
                button.classList.add('active');
            }
        }

        function loadEditionData() {
            const select = document.getElementById('edicaoSelect');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('titulo').value = option.getAttribute('data-titulo') || '';
                document.getElementById('descricao').value = option.getAttribute('data-descricao') || '';
                document.getElementById('data_inicio').value = option.getAttribute('data-data_inicio') || '';
                document.getElementById('data_fim').value = option.getAttribute('data-data_fim') || '';
                document.getElementById('local').value = option.getAttribute('data-local') || '';
                document.getElementById('data_inscricao_inicio').value = option.getAttribute('data-data_inscricao_inicio') || '';
                document.getElementById('data_inscricao_fim').value = option.getAttribute('data-data_inscricao_fim') || '';
                document.getElementById('hora_inicio').value = option.getAttribute('data-hora_inicio') || '';
                document.getElementById('hora_fim').value = option.getAttribute('data-hora_fim') || '';
                document.getElementById('hora_inscricao_inicio').value = option.getAttribute('data-hora_inscricao_inicio') || '';
                document.getElementById('hora_inscricao_fim').value = option.getAttribute('data-hora_inscricao_fim') || '';
            } else {
                resetForm();
            }
        }

        function resetForm() {
            document.getElementById('titulo').value = '';
            document.getElementById('descricao').value = '';
            document.getElementById('data_inicio').value = '';
            document.getElementById('data_fim').value = '';
            document.getElementById('local').value = '';
            document.getElementById('data_inscricao_inicio').value = '';
            document.getElementById('data_inscricao_fim').value = '';
            document.getElementById('hora_inicio').value = '';
            document.getElementById('hora_fim').value = '';
            document.getElementById('hora_inscricao_inicio').value = '';
            document.getElementById('hora_inscricao_fim').value = '';
        }
    </script>
</body>
</html>
<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>
