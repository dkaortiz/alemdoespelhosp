<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $phoneDigits = normalizePhoneForLookup($telefone);

    if ($email === '' && $phoneDigits === '') {
        $error = 'Informe o e-mail ou telefone.';
    } else {
        $found = null;
        $tables = ['peregrinos' => 'peregrino', 'anfitrioes' => 'anfitriao'];

        foreach ($tables as $table => $type) {
            $query = "SELECT id, nome, email, telefone, payment_status, pagbank_checkout_id, pagbank_status, pagbank_payment_id, pagbank_payload FROM `$table` WHERE 1=1";
            $params = [];
            $types = '';

            if ($email !== '') {
                $query .= " AND email = ?";
                $params[] = $email;
                $types .= 's';
            }

            if ($phoneDigits !== '') {
                $query .= " AND REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', '') LIKE ?";
                $params[] = '%' . $phoneDigits . '%';
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
                $found = ['table' => $table, 'type' => $type, 'row' => $row];
                break;
            }
        }

        if ($found) {
            $row = $found['row'];
            $table = $found['table'];
            $id = (int) $row['id'];
            $checkoutId = $row['pagbank_checkout_id'] ?? null;

            if (!empty($checkoutId)) {
                $refresh = refreshPagbankRegistrationStatus($mysqli, $table, $id, $checkoutId);
            } else {
                $refresh = ['ok' => false, 'message' => 'Sem checkout associado.'];
            }

            $_SESSION['user_access'] = [
                'id' => $id,
                'table' => $table,
                'type' => $found['type'],
                'nome' => $row['nome'],
                'email' => $row['email'],
                'telefone' => $row['telefone'],
                'payment_status' => $refresh['status'] ?? $row['payment_status'] ?? 'pendente',
                'pagbank_status' => $refresh['pagbank_status'] ?? $row['pagbank_status'] ?? null,
                'pagbank_payment_id' => $refresh['payment_id'] ?? $row['pagbank_payment_id'] ?? null,
            ];

            header('Location: user_area.php');
            exit;
        }

        $error = 'Nenhuma inscrição encontrada com esses dados.';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Participante</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
</head>
<body>
    <main class="section">
        <div class="container" style="max-width: 560px;">
            <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                <h2 style="margin-top: 0; color: var(--primary);">Acesso da inscrição</h2>
                <p style="color: var(--muted);">Informe o e-mail e o telefone usados na inscrição para ver o status do pagamento.</p>

                <?php if (!empty($error)): ?>
                    <div style="padding: 1rem; border-radius: 12px; background: rgba(220, 38, 38, 0.12); color: #b91c1c; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="seu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" inputmode="numeric" pattern="[0-9]{10,11}" placeholder="11999999999" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
