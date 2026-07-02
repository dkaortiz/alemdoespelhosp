<?php
require_once 'config.php';
session_start();

if (empty($_SESSION['user_access'])) {
    header('Location: user_login.php');
    exit;
}

$user = $_SESSION['user_access'];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha inscrição</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
</head>
<body>
    <main class="section">
        <div class="container" style="max-width: 760px;">
            <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                <h2 style="margin-top: 0; color: var(--primary);">Olá, <?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p style="color: var(--muted);">Aqui estão os dados da sua inscrição e o status do pagamento.</p>

                <div style="display: grid; gap: 1rem; margin-top: 1.5rem;">
                    <div style="padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08);">
                        <strong>Tipo:</strong> <?php echo htmlspecialchars(ucfirst($user['type']), ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>E-mail:</strong> <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>Telefone:</strong> <?php echo htmlspecialchars($user['telefone'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                    <div style="padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08);">
                        <strong>Status no sistema:</strong> <?php echo htmlspecialchars(ucfirst($user['payment_status']), ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>Status no PagBank:</strong> <?php echo htmlspecialchars($user['pagbank_status'] ?? 'Não consultado', ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>ID do pagamento:</strong> <?php echo htmlspecialchars($user['pagbank_payment_id'] ?? 'Ainda não disponível', ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>ID do checkout:</strong> <?php echo htmlspecialchars($user['pagbank_checkout_id'] ?? 'Não disponível', ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php if (!empty($user['checkout_url']) && ($user['payment_status'] === 'pendente' || strtolower($user['pagbank_status'] ?? '') === 'pending')): ?>
                            <div style="margin-top:0.5rem;">
                                <a href="<?php echo htmlspecialchars($user['checkout_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" target="_blank">Ir para pagamento</a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($user['pagbank_payload'])): ?>
                            <div style="margin-top:0.5rem; font-size:0.9rem; color:#666;">Dados do pagamento disponíveis para suporte.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="user_login.php" class="btn btn-secondary" style="margin-top: 1.5rem; display: inline-block;">Voltar</a>
            </div>
        </div>
    </main>
</body>
</html>
