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

// Handle logout (Movido para o topo para evitar processamento desnecessário)
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

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
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 0.85rem 1rem; border-radius: 12px; border: 1px solid rgba(67, 56, 202, 0.3); background: rgba(67, 56, 202, 0.05); color: var(--text); }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); background: rgba(67, 56, 202, 0.1); }
    </style>
</head>
<body>
    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="container" style="max-width: 450px;">
            <div class="glass-strong" style="padding: 3.5rem; border-radius: 20px; border: 1px solid rgba(67, 56, 202, 0.3); background: linear-gradient(135deg, rgba(67, 56, 202, 0.08), rgba(124, 58, 237, 0.05));">
                <div style="text-align: center; margin-bottom: 2.5rem;">
                    <h1 style="font-size: 2rem; background: linear-gradient(135deg, #4338CA, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0 0 0.5rem;">🔐 Painel Admin</h1>
                    <p style="color: var(--muted); margin: 0;">Acesso restrito</p>
                </div>
                
                <?php if (isset($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
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
        
        $edicao_atual = $mysqli->query("SELECT id FROM edicoes ORDER BY ano DESC LIMIT 1");
        if (!$edicao_atual) { throw new Exception("Erro na query: " . $mysqli->error); }
        
        $edicao_id_row = $edicao_atual->fetch_assoc();
        if (!$edicao_id_row) { throw new Exception("Nenhuma edição encontrada no banco de dados"); }
        
        $edicao_id = $edicao_id_row['id'];
        
        $stmt = $mysqli->prepare("UPDATE edicoes SET limite_homens = ?, limite_mulheres = ?, limite_anfitrioes = ? WHERE id = ?");
        if (!$stmt) { throw new Exception("Erro ao preparar statement: " . $mysqli->error); }
        
        $stmt->bind_param('iiii', $new_limite_homem, $new_limite_mulher, $new_limite_anfitriao, $edicao_id);
        if (!$stmt->execute()) { throw new Exception("Erro ao executar update: " . $stmt->error); }
        
        $_SESSION['admin_success'] = "✓ Limites de vagas updated com sucesso!";
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
        
        if (!$titulo) { throw new Exception("Título é obrigatório"); }
        if ($edicao_id <= 0) { throw new Exception("Edição não selecionada"); }
        
        $stmt = $mysqli->prepare("UPDATE edicoes SET titulo = ?, descricao = ?, data_inicio = ?, data_fim = ?, local = ?, data_inscricao_inicio = ?, data_inscricao_fim = ?, hora_inicio = NULLIF(?, ''), hora_fim = NULLIF(?, ''), hora_inscricao_inicio = NULLIF(?, ''), hora_inscricao_fim = NULLIF(?, '') WHERE id = ?");
        if (!$stmt) { throw new Exception("Erro ao preparar statement: " . $mysqli->error); }
        
        $stmt->bind_param('sssssssssssi', $titulo, $descricao, $data_inicio, $data_fim, $local, $data_inscricao_inicio, $data_inscricao_fim, $hora_inicio, $hora_fim, $hora_inscricao_inicio, $hora_inscricao_fim, $edicao_id);
        if (!$stmt->execute()) { throw new Exception("Erro ao executar update: " . $stmt->error); }
        
        $_SESSION['admin_success'] = "✓ Edição atualizada com sucesso!";
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "✗ Erro ao atualizar edição: " . $e->getMessage();
    }
    
    header('Location: admin.php#edicoes');
    exit;
}

// Buscar dados iniciais
$edicao = $mysqli->query("SELECT * FROM edicoes ORDER BY ano DESC LIMIT 1");
$edicao_row = $edicao->fetch_assoc();
$limite_homem = $edicao_row['limite_homens'] ?? 15;
$limite_mulher = $edicao_row['limite_mulheres'] ?? 15;
$limite_anfitriao = $edicao_row['limite_anfitrioes'] ?? 999;

$peregrinos = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos");
$total_peregrinos = $peregrinos->fetch_assoc()['total'] ?? 0;

$anfitrioes = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes");
$total_anfitrioes = $anfitrioes->fetch_assoc()['total'] ?? 0;

$peregrinos_confirmed = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'confirmado'");
$total_confirmed = $peregrinos_confirmed->fetch_assoc()['total'] ?? 0;

$vagas_restantes = ($limite_homem + $limite_mulher) - $total_confirmed;

$homens = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE genero = 'masculino' AND payment_status = 'confirmado'");
$homens_count = $homens->fetch_assoc()['total'] ?? 0;

$mulheres = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE genero = 'feminino' AND payment_status = 'confirmado'");
$mulheres_count = $mulheres->fetch_assoc()['total'] ?? 0;

$anfitrioes_confirmed = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'confirmado'");
$total_anfitrioes_confirmed = $anfitrioes_confirmed->fetch_assoc()['total'] ?? 0;

$result1 = $mysqli->query("SELECT SUM(payment_amount) as total FROM peregrinos WHERE payment_status = 'confirmado'");
$result2 = $mysqli->query("SELECT SUM(payment_amount) as total FROM anfitrioes WHERE payment_status = 'confirmado'");
$total_arrecadado = ($result1->fetch_assoc()['total'] ?? 0) + ($result2->fetch_assoc()['total'] ?? 0);

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
        .stat-card { animation: fadeInUp 0.6s ease forwards; opacity: 0; transition: all 0.3s; }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(255, 107, 157, 0.2); }
        .stat-value { font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        
        /* Sistema Elegante de Abas */
        .tabs { display: flex; gap: 0.6rem; margin-bottom: 1.5rem; padding: 0.4rem; border-radius: 12px; background: rgba(255,255,255,0.03); align-items: center; box-shadow: 0 6px 20px rgba(15, 15, 15, 0.03); overflow-x: auto; }
        .tab-btn { background: transparent; color: var(--muted); padding: 0.7rem 1.2rem; cursor: pointer; font-size: 1.02rem; transition: all 260ms ease; font-weight: 700; border-radius: 999px; display: inline-flex; gap: 0.6rem; align-items: center; border: 1px solid transparent; font-family: 'Playfair Display', Georgia, serif; letter-spacing: 0.6px; }
        .tab-btn:hover { transform: translateY(-2px); color: var(--text); background: rgba(255,255,255,0.02); }
        .tab-btn.active { color: #0a0805; background: linear-gradient(90deg, rgba(255,215,128,0.98), rgba(255,200,80,0.95)); box-shadow: 0 10px 30px rgba(255,180,60,0.1), inset 0 -2px 8px rgba(0,0,0,0.04); border-color: rgba(255,215,128,0.15); transform: translateY(-2px) scale(1.02); }
        
        .tab-content { display: none; animation: fadeInUp 0.5s ease; }
        .tab-content.active { display: block; }

        table { width: 100%; border-collapse: collapse; }
        th { background: var(--surface); color: var(--text); padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border); }
        td { padding: 1rem; border-bottom: 1px solid var(--border); color: var(--text); }
        tr:hover { background: rgba(255, 107, 157, 0.05); }
        .status-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-pendente { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .status-enviado { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .status-confirmado { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .btn-action { padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; font-size: 0.85rem; transition: all 0.3s ease; font-weight: 500; margin-right: 5px; }
        .btn-approve { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .btn-reject { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
        .btn-approve:hover, .btn-reject:hover { transform: scale(1.05); background-opacity: 0.4; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <header style="background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1)); border-bottom: 2px solid rgba(67, 56, 202, 0.3); backdrop-filter: blur(10px);">
        <nav class="container">
            <div class="header-inner" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 1.8rem;">🔐</div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.3rem; color: var(--primary); font-weight: 700;">Painel Admin</h2>
                        <p style="margin: 0; font-size: 0.8rem; color: var(--muted);">Usuário: <span style="color: var(--accent); font-weight: 600;"><?= htmlspecialchars($_SESSION['admin_username']) ?></span></p>
                    </div>
                </div>
                <a href="admin.php?logout=1" style="padding: 0.7rem 1.5rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1)); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                    🚪 Sair
                </a>
            </div>
        </nav>

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

    <main class="container" style="margin-top: 2rem;">
        <section>
            <h1 style="margin-bottom: 2rem;">Dashboard Admin</h1>

            <?php if (!empty($success_msg)): ?>
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                ✅ <?php echo htmlspecialchars($success_msg); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_msg_session)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.95rem;">
                ⚠️ <?php echo htmlspecialchars($error_msg_session); ?>
            </div>
            <?php endif; ?>

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
                        <?= htmlspecialchars($lookup_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($lookup_result): $lookup_row = $lookup_result['row']; $lookup_refresh = $lookup_result['refresh'] ?? null; ?>
                    <div style="margin-top: 1rem; padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08);">
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($lookup_result['label']); ?></p>
                        <p><strong>Nome:</strong> <?= htmlspecialchars($lookup_row['nome'] ?? ''); ?></p>
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($lookup_row['email'] ?? ''); ?></p>
                        <p><strong>Telefone:</strong> <?= htmlspecialchars($lookup_row['telefone'] ?? ''); ?></p>
                        <p><strong>Status Pagamento:</strong> <span class="status-badge status-<?= str_replace('_', '-', $lookup_row['payment_status'] ?? 'pendente'); ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $lookup_row['payment_status'] ?? 'pendente'))); ?></span></p>
                        <p><strong>Status no PagBank:</strong> <?= htmlspecialchars($lookup_refresh['pagbank_status'] ?? $lookup_row['pagbank_status'] ?? 'Não consultado'); ?></p>
                        <p><strong>ID do checkout:</strong> <?= htmlspecialchars($lookup_row['pagbank_checkout_id'] ?? 'Não disponível'); ?></p>
                        <p><strong>ID do pagamento:</strong> <?= htmlspecialchars($lookup_refresh['payment_id'] ?? $lookup_row['pagbank_payment_id'] ?? 'Ainda não disponível'); ?></p>
                        
                        <!-- Link de Pagamento Usado -->
                        <div style="margin: 1rem 0; padding: 1rem; background: rgba(212, 175, 55, 0.1); border-radius: 8px;">
                            <p style="margin: 0 0 0.5rem; color: var(--muted); font-weight: 600;">💳 Link de Pagamento Usado:</p>
                            <p style="margin: 0; color: #f4d27a; word-break: break-all;">
                                <?php if (!empty($lookup_row['pagbank_payment_link'])): ?>
                                    <a href="<?= htmlspecialchars($lookup_row['pagbank_payment_link'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="color: #fbbf24; text-decoration: underline;">
                                        <?= htmlspecialchars($lookup_row['pagbank_payment_link'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                    <br><small style="color: var(--muted);">(Tipo: <?= htmlspecialchars($lookup_row['payment_link_type'] ?? 'indefinido', ENT_QUOTES, 'UTF-8'); ?>)</small>
                                <?php else: ?>
                                    <em>Não rastreado</em>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <!-- Status Ativo/Inativo -->
                        <div style="margin: 1rem 0; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <p style="margin: 0 0 0.5rem; color: var(--muted); font-weight: 600;">Status de Ativação:</p>
                            <p style="margin: 0; color: <?= $lookup_row['is_active'] ? '#10b981' : '#ef4444' ?>; font-weight: 600;">
                                <?= $lookup_row['is_active'] ? '✅ Ativo' : '❌ Desativado' ?>
                            </p>
                        </div>
                        
                        <!-- Log de Aprovação -->
                        <?php if (!empty($lookup_row['payment_confirmed_by'])): ?>
                            <div style="margin: 1rem 0; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px;">
                                <p style="margin: 0 0 0.5rem; color: var(--muted); font-weight: 600;">✅ Aprovado por:</p>
                                <p style="margin: 0; color: #86efac;">
                                    <strong><?= htmlspecialchars($lookup_row['payment_confirmed_by'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if (!empty($lookup_row['payment_confirmed_at'])): ?>
                                        <br><small style="color: var(--muted);">em <?= date('d/m/Y H:i:s', strtotime($lookup_row['payment_confirmed_at'])); ?></small>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Comprovante de Pagamento -->
                        <?php if (!empty($lookup_row['payment_receipt'])): ?>
                            <div style="margin: 1rem 0; padding: 1rem; background: rgba(244, 208, 63, 0.1); border-radius: 8px;">
                                <p style="margin: 0 0 0.5rem; color: var(--muted); font-weight: 600;">📄 Comprovante:</p>
                                <p style="margin: 0; color: #f59e0b;">
                                    <a href="<?= htmlspecialchars($lookup_row['payment_receipt'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="color: #fbbf24; text-decoration: underline;">
                                        Ver comprovante
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($lookup_refresh['checkout_url'] ?? null)): ?>
                            <p><strong>URL do Checkout:</strong> <a href="<?= htmlspecialchars($lookup_refresh['checkout_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">Abrir checkout</a></p>
                        <?php endif; ?>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-top: 1.5rem;">
                            <button type="button" class="btn btn-primary" onclick="refreshPagbankStatus(<?= (int)$lookup_row['id']; ?>, '<?= htmlspecialchars($lookup_result['table'], ENT_QUOTES, 'UTF-8'); ?>')">🔄 Atualizar Status</button>
                            
                            <?php if (!empty($lookup_row['pagbank_checkout_id'])): ?>
                                <button type="button" class="btn btn-secondary" onclick="activatePagbankCheckout(<?= (int)$lookup_row['id']; ?>, '<?= htmlspecialchars($lookup_result['table'], ENT_QUOTES, 'UTF-8'); ?>')">🔗 Ativar Checkout</button>
                            <?php endif; ?>

                            <!-- Botão Ativar/Desativar -->
                            <form method="post" action="admin_action.php" style="display: contents;">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="user_id" value="<?= (int)$lookup_row['id']; ?>">
                                <input type="hidden" name="user_table" value="<?= htmlspecialchars($lookup_result['table'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn" style="background: <?= $lookup_row['is_active'] ? 'rgba(239, 68, 68, 0.2); color: #ef4444;' : 'rgba(16, 185, 129, 0.2); color: #10b981;' ?>">
                                    <?= $lookup_row['is_active'] ? '🔒 Desativar' : '🔓 Ativar' ?>
                                </button>
                            </form>

                            <!-- Botão Aprovar Comprovante (se existe e está pendente) -->
                            <?php if (!empty($lookup_row['payment_receipt']) && $lookup_row['payment_status'] === 'comprovante_enviado'): ?>
                                <form method="post" action="admin_action.php" style="display: contents;">
                                    <input type="hidden" name="action" value="approve_receipt">
                                    <input type="hidden" name="user_id" value="<?= (int)$lookup_row['id']; ?>">
                                    <input type="hidden" name="user_table" value="<?= htmlspecialchars($lookup_result['table'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">
                                        ✅ Aprovar Comprovante
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="glass-strong stat-card" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                    <p style="color: var(--muted); margin: 0;">Total Peregrinos</p>
                    <div class="stat-value"><?= $total_peregrinos ?></div>
                </div>
                <div class="glass-strong stat-card" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                    <p style="color: var(--muted); margin: 0;">Confirmados</p>
                    <div class="stat-value"><?= $total_confirmed ?>/30</div>
                </div>
                <div class="glass-strong stat-card" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                    <p style="color: var(--muted); margin: 0;">Vagas Restantes</p>
                    <div class="stat-value" style="color: <?= $vagas_restantes <= 5 ? '#ef4444' : '#10b981' ?>;"><?= $vagas_restantes ?></div>
                </div>
                <div class="glass-strong stat-card" style="padding: 1.5rem; text-align: center; border-radius: 12px;">
                    <p style="color: var(--muted); margin: 0;">Arrecadado</p>
                    <div class="stat-value">R$ <?= number_format($total_arrecadado, 2, ',', '.') ?></div>
                </div>
            </div>
        </section>

        <div id="config" class="glass-strong" style="padding: 2rem; border-radius: 12px; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.05), rgba(244, 208, 63, 0.03));">
            <h3 style="color: var(--primary); margin-top: 0;">⚙️ Configurações - Edição <?= $edicao_row['ano'] ?? 2026 ?></h3>
            <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <input type="hidden" name="save_limits" value="1">
                <div>
                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem;">Limite de Homens</label>
                    <input type="number" name="limite_homem" value="<?= $limite_homem ?>" min="0" required style="padding: 0.75rem; border-radius: 8px; width:100%; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                </div>
                <div>
                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem;">Limite de Mulheres</label>
                    <input type="number" name="limite_mulher" value="<?= $limite_mulher ?>" min="0" required style="padding: 0.75rem; border-radius: 8px; width:100%; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                </div>
                <div>
                    <label style="display: block; color: var(--muted); margin-bottom: 0.5rem;">Limite de Anfitriões</label>
                    <input type="number" name="limite_anfitriao" value="<?= $limite_anfitriao ?>" min="0" required style="padding: 0.75rem; border-radius: 8px; width:100%; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding:0.85rem;">Salvar Limites</button>
                </div>
            </form>
        </div>

        <nav class="tabs">
            <button class="tab-btn active" data-target="cadastro">➕ Cadastro</button>
            <button class="tab-btn" data-target="aprovacoes">✅ Aprovações</button>
            <button class="tab-btn" data-target="pendentes">⏳ Pendentes</button>
            <button class="tab-btn" data-target="peregrinos">🧘 Peregrinos</button>
            <button class="tab-btn" data-target="anfitrioes">👥 Anfitriões</button>
            <button class="tab-btn" data-target="edicoes">📅 Edições</button>
            <button class="tab-btn" data-target="admins">🔐 Usuários</button>
        </nav>

        <div id="cadastro" class="tab-content active">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h2 style="color: var(--primary); margin-top: 0;">➕ Cadastrar Novo Integrante</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div style="background: rgba(67, 56, 202, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                        <h3>🧘 Cadastrar Peregrino</h3>
                        <form method="post" action="admin_action.php">
                            <input type="hidden" name="action" value="admin_cadastro_peregrino">
                            <div class="form-group"><label>Nome Completo *</label><input type="text" name="nome" required></div>
                            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                            <div class="form-group">
                                <label>Gênero *</label>
                                <select name="genero" required>
                                    <option value="">Selecione...</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Cadastrar</button>
                        </form>
                    </div>
                    <div style="background: rgba(244, 208, 63, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                        <h3>👥 Cadastrar Anfitrião</h3>
                        <form method="post" action="admin_action.php">
                            <input type="hidden" name="action" value="admin_cadastro_anfitriao">
                            <div class="form-group"><label>Nome Completo *</label><input type="text" name="nome" required></div>
                            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                            <div class="form-group"><label>Função *</label><input type="text" name="funcao" placeholder="Cozinheiro..." required></div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="aprovacoes" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h2 style="color: var(--primary); margin-top: 0;">✅ Painel de Aprovações</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <!-- Peregrinos Pendentes -->
                    <div style="background: rgba(67, 56, 202, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                        <h3 style="margin-top: 0; color: var(--primary);">🧘 Peregrinos Pendentes</h3>
                        <?php 
                            $peregrinos_pending = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'pendente'");
                            $count_perg_pending = $peregrinos_pending->fetch_assoc()['total'] ?? 0;
                        ?>
                        <p style="margin: 0.5rem 0 1rem; font-size: 1.5rem; font-weight: 700; color: #3b82f6;"><?= $count_perg_pending ?></p>
                        <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">Aguardando comprovante ou pagamento</p>
                    </div>
                    
                    <!-- Anfitriões Pendentes -->
                    <div style="background: rgba(244, 208, 63, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                        <h3 style="margin-top: 0; color: var(--accent);">👥 Anfitriões Pendentes</h3>
                        <?php 
                            $anfitrioes_pending = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'pendente'");
                            $count_anf_pending = $anfitrioes_pending->fetch_assoc()['total'] ?? 0;
                        ?>
                        <p style="margin: 0.5rem 0 1rem; font-size: 1.5rem; font-weight: 700; color: #f4d27a;"><?= $count_anf_pending ?></p>
                        <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">Aguardando comprovante ou pagamento</p>
                    </div>
                    
                    <!-- Comprovantes Enviados -->
                    <div style="background: rgba(245, 158, 11, 0.05); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #f59e0b;">
                        <h3 style="margin-top: 0; color: #f59e0b;">📄 Comprovantes Enviados</h3>
                        <?php 
                            $comprovantes_enviados = $mysqli->query("
                                SELECT COUNT(*) as total FROM (
                                    SELECT id FROM peregrinos WHERE payment_status = 'comprovante_enviado'
                                    UNION ALL
                                    SELECT id FROM anfitrioes WHERE payment_status = 'comprovante_enviado'
                                ) as t
                            ");
                            $count_compr = $comprovantes_enviados->fetch_assoc()['total'] ?? 0;
                        ?>
                        <p style="margin: 0.5rem 0 1rem; font-size: 1.5rem; font-weight: 700; color: #f59e0b;"><?= $count_compr ?></p>
                        <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">Aguardando análise do admin</p>
                    </div>
                </div>

                <h3 style="color: var(--primary); margin-top: 2rem;">Histórico de Aprovações Recentes</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Link Pagamento</th>
                                <th>Aprovado por</th>
                                <th>Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $aprovados = $mysqli->query("
                                    (SELECT 'peregrino' as tipo, nome, email, pagbank_payment_link, payment_link_type, payment_confirmed_by, payment_confirmed_at FROM peregrinos WHERE payment_status = 'confirmado' AND payment_confirmed_by IS NOT NULL ORDER BY payment_confirmed_at DESC LIMIT 10)
                                    UNION ALL
                                    (SELECT 'anfitriao' as tipo, nome, email, pagbank_payment_link, payment_link_type, payment_confirmed_by, payment_confirmed_at FROM anfitrioes WHERE payment_status = 'confirmado' AND payment_confirmed_by IS NOT NULL ORDER BY payment_confirmed_at DESC LIMIT 10)
                                    ORDER BY payment_confirmed_at DESC LIMIT 20
                                ");
                                if ($aprovados->num_rows > 0):
                                    while ($row = $aprovados->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><span class="status-badge" style="background: <?= $row['tipo'] === 'peregrino' ? 'rgba(67, 56, 202, 0.2)' : 'rgba(244, 208, 63, 0.2)' ?>; color: <?= $row['tipo'] === 'peregrino' ? '#3b82f6' : '#f4d27a' ?>;"><?= ucfirst($row['tipo']) ?></span></td>
                                <td style="font-size: 0.85rem;">
                                    <?= htmlspecialchars($row['payment_link_type'] ?? 'indefinido') ?>
                                </td>
                                <td><?= htmlspecialchars($row['payment_confirmed_by'] ?? '-') ?></td>
                                <td><?= !empty($row['payment_confirmed_at']) ? date('d/m/y H:i', strtotime($row['payment_confirmed_at'])) : '-' ?></td>
                            </tr>
                            <?php 
                                    endwhile;
                                else:
                            ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--muted);">Nenhuma aprovação registrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="pendentes" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px; overflow-x: auto;">
                <?php if ($pendentes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pendentes->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= ucfirst($row['tipo']) ?></td>
                                <td>
                                    <form method="post" action="admin_action.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve">✓ Aprovar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: var(--muted);">Nenhum pendente de confirmação 🎉</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="peregrinos" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h2 style="color: var(--primary); margin-top: 0;">🧘 Peregrinos - Total <?php echo $total_peregrinos; ?></h2>
                
                <!-- Contadores -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: rgba(67, 56, 202, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Todos</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: var(--primary);"><?= $total_peregrinos ?></p>
                    </div>
                    <?php 
                        $pending = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'pendente'");
                        $confirmed = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'confirmado'");
                        $enviado = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'comprovante_enviado'");
                    ?>
                    <div style="background: rgba(59, 130, 246, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Pendentes</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #3b82f6;"><?= $pending->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Comprovante</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #f59e0b;"><?= $enviado->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">✅ Confirmados</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #10b981;"><?= $confirmed->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                </div>

                <!-- Filtros -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <button class="tab-btn status-filter" data-filter="todos" style="background: linear-gradient(90deg, rgba(255,215,128,0.98), rgba(255,200,80,0.95)); color: #0a0805; border: none; cursor: pointer;" onclick="filterPeregrinosStatus('todos')">Todos</button>
                    <button class="tab-btn status-filter" data-filter="pendente" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: none; cursor: pointer;" onclick="filterPeregrinosStatus('pendente')">⏳ Pendentes</button>
                    <button class="tab-btn status-filter" data-filter="comprovante_enviado" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: none; cursor: pointer;" onclick="filterPeregrinosStatus('comprovante_enviado')">📄 Comprovante</button>
                    <button class="tab-btn status-filter" data-filter="confirmado" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: none; cursor: pointer;" onclick="filterPeregrinosStatus('confirmado')">✅ Confirmados</button>
                </div>

                <!-- Tabela -->
                <div style="overflow-x: auto;">
                <?php 
                $peregrinos_all = $mysqli->query("SELECT id, nome, email, genero, payment_status, payment_link_type, pagbank_payment_link, criado_em, payment_confirmed_by, payment_confirmed_at FROM peregrinos ORDER BY criado_em DESC");
                if ($peregrinos_all && $peregrinos_all->num_rows > 0):
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Gênero</th>
                            <th>Status Pagamento</th>
                            <th>Data Inscrição</th>
                            <th>Aprovado por</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $peregrinos_all->fetch_assoc()): ?>
                        <tr id="row-peregrino-<?= $row['id'] ?>" class="status-row" data-status="<?= $row['payment_status'] ?>">
                            <td><strong><?= htmlspecialchars($row['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['genero'] == 'masculino' ? '👨 Masculino' : '👩 Feminino' ?></td>
                            <td>
                                <span class="status-badge status-<?= str_replace('_', '-', $row['payment_status']) ?>">
                                    <?php 
                                        if ($row['payment_status'] === 'pendente') echo '⏳ Pendente';
                                        elseif ($row['payment_status'] === 'comprovante_enviado') echo '📄 Enviado';
                                        elseif ($row['payment_status'] === 'confirmado') echo '✅ Confirmado';
                                        else echo ucfirst($row['payment_status']);
                                    ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['criado_em'])) ?></td>
                            <td><?= !empty($row['payment_confirmed_by']) ? htmlspecialchars($row['payment_confirmed_by']) . ' em ' . date('d/m/y', strtotime($row['payment_confirmed_at'])) : '-' ?></td>
                            <td style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn-action" style="background: rgba(67, 56, 202, 0.2); color: #3b82f6; border: 1px solid #3b82f6; cursor: pointer;" onclick="viewPeregrinoDetails(<?= $row['id'] ?>)">👁️ Ver</button>
                                <?php if ($row['payment_status'] !== 'confirmado'): ?>
                                <form method="post" style="display: contents;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="target" value="peregrinos">
                                    <input type="hidden" name="target_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-action" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; cursor: pointer;">✅ Aprovar</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="text-align: center; color: var(--muted);">Nenhum peregrino inscrito.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="anfitrioes" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h2 style="color: var(--accent); margin-top: 0;">👥 Anfitriões - Total <?php echo $total_anfitrioes; ?></h2>
                
                <!-- Contadores -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: rgba(244, 208, 63, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Todos</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: var(--accent);"><?= $total_anfitrioes ?></p>
                    </div>
                    <?php 
                        $pending_anf = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'pendente'");
                        $confirmed_anf = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'confirmado'");
                        $enviado_anf = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes WHERE payment_status = 'comprovante_enviado'");
                    ?>
                    <div style="background: rgba(59, 130, 246, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Pendentes</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #3b82f6;"><?= $pending_anf->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">Comprovante</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #f59e0b;"><?= $enviado_anf->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 0.5rem; color: var(--muted); font-size: 0.9rem;">✅ Confirmados</p>
                        <p style="margin: 0; font-size: 1.8rem; font-weight: 700; color: #10b981;"><?= $confirmed_anf->fetch_assoc()['total'] ?? 0 ?></p>
                    </div>
                </div>

                <!-- Filtros -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <button class="tab-btn status-filter" data-filter="todos" style="background: linear-gradient(90deg, rgba(255,215,128,0.98), rgba(255,200,80,0.95)); color: #0a0805; border: none; cursor: pointer;" onclick="filterAnfitriaoStatus('todos')">Todos</button>
                    <button class="tab-btn status-filter" data-filter="pendente" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: none; cursor: pointer;" onclick="filterAnfitriaoStatus('pendente')">⏳ Pendentes</button>
                    <button class="tab-btn status-filter" data-filter="comprovante_enviado" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: none; cursor: pointer;" onclick="filterAnfitriaoStatus('comprovante_enviado')">📄 Comprovante</button>
                    <button class="tab-btn status-filter" data-filter="confirmado" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: none; cursor: pointer;" onclick="filterAnfitriaoStatus('confirmado')">✅ Confirmados</button>
                </div>

                <!-- Tabela -->
                <div style="overflow-x: auto;">
                <?php 
                $anfitrioes_all = $mysqli->query("SELECT id, nome, email, funcao, payment_status, payment_link_type, pagbank_payment_link, criado_em, payment_confirmed_by, payment_confirmed_at FROM anfitrioes ORDER BY criado_em DESC");
                if ($anfitrioes_all && $anfitrioes_all->num_rows > 0):
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Função</th>
                            <th>Status Pagamento</th>
                            <th>Data Inscrição</th>
                            <th>Aprovado por</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $anfitrioes_all->fetch_assoc()): ?>
                        <tr id="row-anfitriao-<?= $row['id'] ?>" class="status-row" data-status="<?= $row['payment_status'] ?>">
                            <td><strong><?= htmlspecialchars($row['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['funcao']) ?></td>
                            <td>
                                <span class="status-badge status-<?= str_replace('_', '-', $row['payment_status']) ?>">
                                    <?php 
                                        if ($row['payment_status'] === 'pendente') echo '⏳ Pendente';
                                        elseif ($row['payment_status'] === 'comprovante_enviado') echo '📄 Enviado';
                                        elseif ($row['payment_status'] === 'confirmado') echo '✅ Confirmado';
                                        else echo ucfirst($row['payment_status']);
                                    ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['criado_em'])) ?></td>
                            <td><?= !empty($row['payment_confirmed_by']) ? htmlspecialchars($row['payment_confirmed_by']) . ' em ' . date('d/m/y', strtotime($row['payment_confirmed_at'])) : '-' ?></td>
                            <td style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn-action" style="background: rgba(244, 208, 63, 0.2); color: #f4d27a; border: 1px solid #f4d27a; cursor: pointer;" onclick="viewAnfitriaoDetails(<?= $row['id'] ?>)">👁️ Ver</button>
                                <?php if ($row['payment_status'] !== 'confirmado'): ?>
                                <form method="post" style="display: contents;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="target" value="anfitrioes">
                                    <input type="hidden" name="target_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-action" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; cursor: pointer;">✅ Aprovar</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="text-align: center; color: var(--muted);">Nenhum anfitrião inscrito.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="edicoes" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h2>📅 Gerenciar Edições</h2>
                <?php 
                $result_editions = $mysqli->query("SELECT * FROM edicoes ORDER BY ano DESC");
                $editions_list = $result_editions ? $result_editions->fetch_all(MYSQLI_ASSOC) : [];
                if (!empty($editions_list)):
                ?>
                <form method="post">
                    <input type="hidden" name="save_edition" value="1">
                    <div class="form-group">
                        <label>Selecione a Edição</label>
                        <select name="edicao_id" id="edicaoSelect" required onchange="loadEditionData()">
                            <option value="">-- Selecione uma edição --</option>
                            <?php foreach ($editions_list as $ed): ?>
                                <option value="<?= $ed['id'] ?>" data-titulo="<?= htmlspecialchars($ed['titulo']) ?>" data-descricao="<?= htmlspecialchars($ed['descricao']) ?>" data-local="<?= htmlspecialchars($ed['local']) ?>">
                                    <?= htmlspecialchars($ed['titulo']) ?> (<?= $ed['ano'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Título da Edição</label>
                        <input type="text" name="titulo" id="titulo" required>
                    </div>
                    <div class="form-group">
                        <label>Local</label>
                        <input type="text" name="local" id="local">
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea name="descricao" id="descricao" style="width:100%; min-height:100px; border-radius:8px; padding:10px; background:rgba(67,56,202,0.05); color:white; border:1px solid rgba(67,56,202,0.3);"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div id="admins" class="tab-content">
            <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                <h3>🔐 Criar Novo Admin</h3>
                <form method="post">
                    <input type="hidden" name="admin_action" value="create_admin">
                    <div class="form-group"><label>Usuário</label><input type="text" name="new_username" required></div>
                    <div class="form-group"><label>Senha</label><input type="password" name="new_password" required></div>
                    <button type="submit" class="btn btn-primary">Criar Admin</button>
                </form>
            </div>
        </div>
    </main>

    <footer style="margin-top: 5rem; padding: 2rem 0; text-align: center; color: var(--muted); border-top: 1px solid var(--border);">
        <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            function switchTab(targetId) {
                // Remove active de todos
                tabs.forEach(tab => tab.classList.remove('active'));
                contents.forEach(content => content.classList.remove('active'));

                // Adiciona active no alvo correto
                const activeTab = document.querySelector(`.tab-btn[data-target="${targetId}"]`);
                const activeContent = document.getElementById(targetId);

                if (activeTab && activeContent) {
                    activeTab.classList.add('active');
                    activeContent.classList.add('active');
                    // Atualiza a URL de forma elegante
                    history.pushState(null, null, '#' + targetId);
                }
            }

            // Ouvinte de clique nos botões das abas
            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = this.getAttribute('data-target');
                    switchTab(target);
                });
            });

            // Verificar se já existe uma hash na URL ao carregar a página (Ex: admin.php#peregrinos)
            const currentHash = window.location.hash.substring(1);
            if (currentHash && document.getElementById(currentHash)) {
                switchTab(currentHash);
            }
        });

        // Função obrigatória para evitar quebras no carregamento de dados da edição
        function loadEditionData() {
            const select = document.getElementById('edicaoSelect');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!selectedOption.value) return;

            document.getElementById('titulo').value = selectedOption.getAttribute('data-titulo') || '';
            document.getElementById('local').value = selectedOption.getAttribute('data-local') || '';
            document.getElementById('descricao').value = selectedOption.getAttribute('data-descricao') || '';
        }

        // Função de DELETE com AJAX para Peregrinos
        function deletePeregrino(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir ${nome}?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'delete_peregrino');
            formData.append('ajax', '1');

            fetch('admin_action.php', {
                method: 'POST',
                body: formData,
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    throw new Error('Requisição redirecionada para ' + response.url);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        const row = document.getElementById('row-peregrino-' + id);
                        if (row) {
                            row.style.animation = 'fadeOut 0.3s ease';
                            setTimeout(() => row.remove(), 300);
                        }
                        showNotification('✓ ' + data.message, 'success');
                    } else {
                        showNotification('✗ ' + data.message, 'error');
                    }
                } catch (err) {
                    console.error('Resposta inválida:', text);
                    showNotification('✗ Resposta inválida do servidor', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('✗ Erro na requisição: ' + error, 'error');
            });
        }

        // Função de DELETE com AJAX para Anfitriões
        function deleteAnfitriao(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir ${nome}?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'delete_anfitriao');
            formData.append('ajax', '1');

            fetch('admin_action.php', {
                method: 'POST',
                body: formData,
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    throw new Error('Requisição redirecionada para ' + response.url);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        const row = document.getElementById('row-anfitriao-' + id);
                        if (row) {
                            row.style.animation = 'fadeOut 0.3s ease';
                            setTimeout(() => row.remove(), 300);
                        }
                        showNotification('✓ ' + data.message, 'success');
                    } else {
                        showNotification('✗ ' + data.message, 'error');
                    }
                } catch (err) {
                    console.error('Resposta inválida:', text);
                    showNotification('✗ Resposta inválida do servidor', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('✗ Erro na requisição: ' + error, 'error');
            });
        }

        function refreshPagbankStatus(id, table) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('table', table);
            formData.append('action', 'refresh_pagbank_status');
            formData.append('ajax', '1');

            fetch('admin_action.php', {
                method: 'POST',
                body: formData,
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✓ ' + data.message, 'success');
                } else {
                    showNotification('✗ ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('✗ Erro ao atualizar status do PagBank.', 'error');
            });
        }

        function activatePagbankCheckout(id, table) {
            if (!confirm('Deseja ativar este checkout do PagBank?')) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('table', table);
            formData.append('action', 'activate_pagbank_checkout');
            formData.append('ajax', '1');

            fetch('admin_action.php', {
                method: 'POST',
                body: formData,
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✓ ' + data.message, 'success');
                } else {
                    showNotification('✗ ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('✗ Erro ao ativar checkout do PagBank.', 'error');
            });
        }

        // Funções de Filtro por Status
        function filterPeregrinosStatus(status) {
            const rows = document.querySelectorAll('#peregrinos .status-row');
            rows.forEach(row => {
                if (status === 'todos' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            event.target.classList.add('active');
        }

        function filterAnfitriaoStatus(status) {
            const rows = document.querySelectorAll('#anfitrioes .status-row');
            rows.forEach(row => {
                if (status === 'todos' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            event.target.classList.add('active');
        }

        // Funções de Visualização de Detalhes
        function viewPeregrinoDetails(id) {
            alert('Detalhes do Peregrino ID: ' + id);
        }

        function viewAnfitriaoDetails(id) {
            alert('Detalhes do Anfitrião ID: ' + id);
        }

        // Função para mostrar notificações
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                font-weight: 600;
                z-index: 9999;
                animation: slideIn 0.3s ease;
                max-width: 400px;
            `;
            
            if (type === 'success') {
                notification.style.background = 'rgba(16, 185, 129, 0.2)';
                notification.style.color = '#86efac';
                notification.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            } else {
                notification.style.background = 'rgba(239, 68, 68, 0.2)';
                notification.style.color = '#fecaca';
                notification.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Adicionar animações CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(-20px); }
            }
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(400px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(400px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>