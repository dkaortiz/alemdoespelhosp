<?php
require_once 'config.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Hash para 'admin123': " . $hash . "\n";

// Atualizar no banco
$stmt = $mysqli->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
$stmt->bind_param('s', $hash);
if ($stmt->execute()) {
    echo "✓ Senha atualizada com sucesso!\n";
} else {
    echo "✗ Erro ao atualizar: " . $stmt->error . "\n";
}
$stmt->close();
?>