<?php
declare(strict_types=1);
require_once 'config.php';
session_start();

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (authenticateAdmin($username, $password)) {
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

// Ações admin
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
$peregrinos = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos");
$peregrinos_row = $peregrinos->fetch_assoc();
$total_peregrinos = $peregrinos_row['total'];

$anfitrioes = $mysqli->query("SELECT COUNT(*) as total FROM anfitrioes");
$anfitrioes_row = $anfitrioes->fetch_assoc();
$total_anfitrioes = $anfitrioes_row['total'];

$peregrinos_confirmed = $mysqli->query("SELECT COUNT(*) as total FROM peregrinos WHERE payment_status = 'confirmado'");
$peregrinos_confirmed_row = $peregrinos_confirmed->fetch_assoc();
$total_confirmed = $peregrinos_confirmed_row['total'];

// Limites configuráveis (salvar em session ou arquivo de config)
$limite_homem = 15;
$limite_mulher = 15;
$limite_anfitriao = 999; // sem limite prático

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

        .logout-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .logout-btn:hover {
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
    <!-- HEADER -->
    <header>
        <nav class="container">
            <div class="header-inner">
                <?php include __DIR__ . '/header_brand.php'; ?>
                <button class="hamburger-menu" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="site-nav">
                    <a href="index.php">Home</a>
                    <a href="edicoes.php">Edições</a>
                    <a href="inscricao.php">Inscrição</a>
                    <a href="regras.php">Regras</a>
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
            <a href="admin.php">Admin</a>
            <a href="admin.php?logout=1" style="color: var(--danger);">Sair</a>
        </div>
        <a href="admin.php?logout=1" class="logout-btn">Sair</a>
    </header>

    <main>
        <!-- DASHBOARD HEADER -->
        <section class="section">
            <div class="container">
                <h1 style="margin-bottom: 3rem; animation: fadeInUp 0.6s ease;">Dashboard Admin</h1>

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
                <div class="glass-strong" style="padding: 2rem; border-radius: 12px; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.05), rgba(244, 208, 63, 0.03));">
                    <h3 style="color: var(--primary); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                        ⚙️ Configurações
                    </h3>
                    <div class="cards-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Homens</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" value="<?= $limite_homem ?>" min="0" style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600;">Salvar</button>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Mulheres</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" value="<?= $limite_mulher ?>" min="0" style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600;">Salvar</button>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Limite de Anfitriões</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" value="<?= $limite_anfitriao ?>" min="0" style="flex: 1; padding: 0.75rem; border-radius: 8px; background: rgba(212, 175, 55, 0.1); border: 1px solid var(--primary); color: var(--text);">
                                <button style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none; border-radius: 8px; color: #0a0805; cursor: pointer; font-weight: 600;">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TABS E TABELAS -->
        <section class="section">
            <div class="container">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('pendentes')">⏳ Pendentes de Confirmação</button>
                    <button class="tab-btn" onclick="switchTab('peregrinos')">🧘 Peregrinos</button>
                    <button class="tab-btn" onclick="switchTab('anfitrioes')">👥 Anfitriões</button>
                    <button class="tab-btn" onclick="switchTab('admins')">🔐 Gerenciar Admins</button>
                </div>

                <!-- TAB: PENDENTES -->
                <div id="pendentes" class="tab-content active">
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

                <!-- TAB: GERENCIAR ADMINS -->
                <div id="admins" class="tab-content">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <!-- CRIAR NOVO ADMIN -->
                        <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                            <h3 style="color: var(--primary); margin-top: 0;">➕ Criar Novo Admin</h3>
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
        
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
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
