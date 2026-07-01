<?php
require_once 'config.php';
session_start();

$registrationId = $_SESSION['registration_id'] ?? null;
$registrationType = $_SESSION['registration_type'] ?? null;

if ($registrationId && $registrationType) {
    $table = $registrationType === 'peregrino' ? 'peregrinos' : 'anfitrioes';
    $stmt = $mysqli->prepare("SELECT payment_status FROM `$table` WHERE id = ?");
    $stmt->bind_param('i', $registrationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $status = $row['payment_status'] ?? 'pendente';
} else {
    $status = 'pendente';
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retorno do PagBank</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
</head>
<body>
    <main class="section">
        <div class="container" style="max-width: 700px; text-align: center;">
            <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                <h1 style="color: var(--primary);">Pagamento recebido</h1>
                <p style="color: var(--muted); margin-bottom: 1.5rem;">
                    Obrigado! O retorno do PagBank foi recebido. O status da sua inscrição está sendo atualizado.
                </p>
                <div style="padding: 1rem; border-radius: 12px; background: rgba(67, 56, 202, 0.08); border: 1px solid rgba(67, 56, 202, 0.2); color: var(--text);">
                    Status atual: <strong><?php echo htmlspecialchars(ucfirst($status)); ?></strong>
                </div>
                <p style="margin-top: 1.5rem; color: var(--muted);">
                    Em alguns instantes, você poderá ver a confirmação no painel administrativo.
                </p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Voltar para o site</a>
            </div>
        </div>
    </main>
</body>
</html>
