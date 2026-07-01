<?php
require_once 'config.php';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar status do pagamento</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
</head>
<body>
    <main class="section">
        <div class="container" style="max-width: 560px;">
            <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                <h2 style="margin-top: 0; color: var(--primary);">Consultar pagamento</h2>
                <p style="color: var(--muted);">Informe o e-mail ou telefone da inscrição para consultar o status no PagBank.</p>
                <form id="status-form">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" placeholder="DDD + número">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Consultar</button>
                </form>
                <div id="status-result" style="margin-top: 1.5rem; color: var(--text);"></div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('status-form').addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            const result = document.getElementById('status-result');
            result.innerHTML = 'Consultando...';

            const response = await fetch('payment_status.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.ok) {
                result.innerHTML = '<strong>Erro:</strong> ' + (data.message || 'Não foi possível consultar.');
                return;
            }

            result.innerHTML = `
                <strong>Nome:</strong> ${data.nome || '-'}<br>
                <strong>E-mail:</strong> ${data.email || '-'}<br>
                <strong>Telefone:</strong> ${data.telefone || '-'}<br>
                <strong>Status no sistema:</strong> ${data.status || '-'}<br>
                <strong>Status no PagBank:</strong> ${data.pagbank_status || '-'}
            `;
        });
    </script>
</body>
</html>
