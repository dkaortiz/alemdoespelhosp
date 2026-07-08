<?php
declare(strict_types=1);
require_once 'config.php';
session_start();

if (!isset($_SESSION['registration_id']) || !isset($_SESSION['registration_type'])) {
    header('Location: inscricao.php');
    exit;
}

$reg_id = (int)$_SESSION['registration_id'];
$reg_type = $_SESSION['registration_type'] === 'peregrino' ? 'peregrino' : 'anfitriao';
$table = $reg_type === 'peregrino' ? 'peregrinos' : 'anfitrioes';

$stmt = $mysqli->prepare("SELECT nome, email, payment_amount, valor, payment_status FROM `$table` WHERE id = ?");
if (!$stmt) {
    header('Location: inscricao.php');
    exit;
}

$stmt->bind_param('i', $reg_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: inscricao.php');
    exit;
}

$nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
$payment_amount = (float)($row['payment_amount'] ?? $row['valor'] ?? 150.00);
$payment_status = $row['payment_status'] ?? 'pendente';
$valor_formatado = number_format($payment_amount, 2, ',', '.');

// Links de pagamento diretos
$payment_links = [
    'peregrino' => 'https://pag.ae/81XVgCdNJ',
    'anfitriao' => 'https://pag.ae/81XVfHnnR'
];
$payment_url = $payment_links[$reg_type] ?? '#';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Além do Espelho</title>
    <?php $page_title = 'Pagamento - Além do Espelho'; $page_description = 'Complete seu pagamento via PagBank Checkout.'; $page_url = 'https://alemdoespelho.com.br/payment.php'; include __DIR__ . '/meta-tags.php'; ?>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
</head>
<body>
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
                    <a href="user_login.php">Login</a>
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
            <a href="user_login.php">Login</a>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="container" style="max-width: 760px;">
                <div class="section-heading">
                    <h2>💳 Confirmação de Pagamento</h2>
                    <p>Complete seu pagamento para confirmar a inscrição</p>
                </div>

                <div class="glass-strong" style="padding: 2rem; margin-bottom: 1.5rem; border-radius: 16px;">
                    <h3 style="margin-top: 0; color: var(--primary);">Seus Dados</h3>
                    <p><strong>Nome:</strong> <?php echo $nome; ?></p>
                    <p><strong>Email:</strong> <?php echo $email; ?></p>
                    <p><strong>Tipo:</strong> <?php echo ucfirst($reg_type); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($payment_status), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <div class="glass-strong" style="padding: 2.2rem; margin-bottom: 1.5rem; border-radius: 16px; text-align: center;">
                    <p style="margin: 0 0 0.5rem; color: var(--muted);">Valor da inscrição</p>
                    <div style="font-size: 2.6rem; font-weight: 700; color: var(--primary);">R$ <?php echo $valor_formatado; ?></div>
                </div>

                <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                    <h3 style="color: var(--primary); margin-top: 0; text-align: center;">💳 Pagamento via PagBank</h3>
                    
                    <!-- Mensagem em Destaque com Duas Opções -->
                    <div style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(124, 58, 237, 0.1)); border: 2px solid rgba(6, 182, 212, 0.4); border-radius: 16px; padding: 1.8rem; margin-bottom: 2rem; text-align: center;">
                        <p style="color: var(--text); font-size: 1.1rem; font-weight: 600; margin: 0 0 1.5rem; line-height: 1.6;">
                            ✉️ <strong>Após realizar o pagamento</strong>, você pode enviar o comprovante de duas formas:
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                            <!-- Opção 1: Email -->
                            <div style="padding: 1.2rem; background: rgba(255,255,255,0.05); border-radius: 12px; border: 1px solid rgba(67, 56, 202, 0.2);">
                                <p style="margin: 0 0 0.5rem; color: var(--accent-secondary); font-weight: 600;">📧 Por Email</p>
                                <p style="color: var(--text); font-size: 0.95rem; margin: 0 0 0.5rem; word-break: break-all; font-weight: 600;">
                                    comprovante@alemdoespelhosp.com.br
                                </p>
                                <p style="color: var(--muted); font-size: 0.85rem; margin: 0;">
                                    Inclua seu nome e CPF
                                </p>
                            </div>
                            
                            <!-- Opção 2: Sistema -->
                            <div style="padding: 1.2rem; background: rgba(255,255,255,0.05); border-radius: 12px; border: 1px solid rgba(67, 56, 202, 0.2);">
                                <p style="margin: 0 0 0.5rem; color: var(--accent-secondary); font-weight: 600;">📤 Pelo Sistema</p>
                                <p style="color: var(--text); font-size: 0.95rem; margin: 0;">
                                    Acesse sua área do participante e anexe o comprovante
                                </p>
                            </div>
                        </div>
                        
                        <p style="color: var(--muted); font-size: 0.85rem; margin: 0; font-style: italic;">
                            *Armazenamento temporário. Aguarde validação do admin.
                        </p>
                    </div>

                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <p style="color: var(--muted); margin-bottom: 1.5rem; line-height: 1.7;">
                            Clique no botão abaixo para proceder ao pagamento de forma segura.
                        </p>
                        <a href="<?php echo htmlspecialchars($payment_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="width: 100%; display: inline-block; text-align: center; padding: 1rem;">
                            Efetuar Pagamento
                        </a>
                    </div>

                    <p style="color: var(--muted); font-size: 0.9rem; text-align: center; margin: 0; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                        Você será redirecionado para o PagBank para concluir a transação de forma segura.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const hamburger = document.querySelector('.hamburger-menu');
            const menu = document.getElementById('mobileMenu');
            hamburger.classList.toggle('active');
            menu.classList.toggle('active');
        }

        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.hamburger-menu').classList.remove('active');
                document.getElementById('mobileMenu').classList.remove('active');
            });
        });
    </script>
    <script src="<?php echo assetVersion('script.js'); ?>"></script>
</body>
</html>
