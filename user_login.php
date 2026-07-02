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
                'pagbank_checkout_id' => $row['pagbank_checkout_id'] ?? null,
                'checkout_url' => $refresh['checkout_url'] ?? null,
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
    <main class="section section-vignette">
        <div class="container">
            <div class="login-card glass-strong">
                <div style="padding: 2.5rem 2rem 1.75rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                        <div>
                            <span style="display:block; color: rgba(255,255,255,0.72); letter-spacing:0.16em; text-transform:uppercase; font-size:0.8rem; margin-bottom:0.75rem;">Área do Participante</span>
                            <h1 style="margin:0; font-size: clamp(2rem, 4vw, 2.8rem); font-family: 'Playfair Display', serif; line-height:1.05; color: #f8f1d3;">Acompanhe sua inscrição</h1>
                        </div>
                        <span style="font-size:0.85rem; color: rgba(255,255,255,0.72); font-weight:600;">Verifique o status do seu pagamento</span>
                    </div>

                    <p style="margin:1.5rem 0 0; color: rgba(255,255,255,0.78); line-height:1.8; max-width: 42rem;">
                        Use os mesmos dados da inscrição para acessar sua área. Caso o pagamento já tenha sido confirmado, você verá o status imediatamente.
                    </p>
                </div>

                <div style="border-top: 1px solid rgba(255,255,255,0.08);
                            padding: 2rem 2rem 2.5rem;">
                    <?php if (!empty($error)): ?>
                        <div class="form-error">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="form-card">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input id="email" type="email" name="email" placeholder="seu@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input id="telefone" type="tel" name="telefone" inputmode="numeric" pattern="[0-9]{10,11}" placeholder="11999999999" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </form>

                    <p class="form-note">Se preferir, use o telefone ou o e-mail cadastrado na inscrição.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
