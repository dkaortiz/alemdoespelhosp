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
$checkout_url = $_SESSION['pagbank_checkout_url'] ?? null;
$valor_formatado = number_format($payment_amount, 2, ',', '.');
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

                <div class="glass-strong" style="padding: 2rem; border-radius: 16px; text-align: center;">
                    <h3 style="color: var(--primary); margin-top: 0;">Pagamento via PagBank Checkout</h3>
                    <p style="color: var(--muted); margin-bottom: 1.4rem;">
                        Você será direcionado para o checkout seguro do PagBank para concluir sua inscrição.
                    </p>

                    <?php if (!empty($checkout_url)): ?>
                        <a id="pagbank-btn" href="<?php echo htmlspecialchars($checkout_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" style="width: 100%;">
                            Ir para o PagBank
                        </a>
                        <p style="color: var(--muted); font-size: 0.95rem; margin-top: 1rem;">
                            Se o redirecionamento não acontecer automaticamente, clique no botão acima.
                        </p>
                    <?php else: ?>
                        <div style="padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08); border: 1px solid rgba(67, 56, 202, 0.2); color: var(--muted); margin-bottom: 1rem;">
                            O link do checkout ainda não foi gerado. Tente novamente em instantes.
                        </div>
                        <a href="javascript:history.back()" class="btn btn-secondary" style="width: 100%;">← Voltar</a>
                    <?php endif; ?>

                    <p style="color: var(--muted); font-size: 0.9rem; margin-top: 1.4rem;">
                        Após o pagamento, a inscrição será atualizada automaticamente no painel administrativo.
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

        const pagbankButton = document.getElementById('pagbank-btn');
        if (pagbankButton) {
            setTimeout(() => {
                window.location.href = pagbankButton.getAttribute('href');
            }, 1000);
        }
    </script>
    <script src="<?php echo assetVersion('script.js'); ?>"></script>
</body>
</html>
