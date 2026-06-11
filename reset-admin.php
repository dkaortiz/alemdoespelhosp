<?php
require_once 'config.php';

// Gerar novo hash válido
$password = 'admin123';
$new_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<pre>";
echo "Nova senha hash para 'admin123':\n";
echo $new_hash . "\n\n";

// Atualizar
$stmt = $mysqli->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
$stmt->bind_param('s', $new_hash);

if ($stmt->execute()) {
    echo "✓ Hash atualizado com sucesso!\n";
    
    // Verificar
    $verify_stmt = $mysqli->prepare("SELECT password_hash FROM admins WHERE username = 'admin'");
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    echo "\nVerificando novo hash:\n";
    $test = password_verify('admin123', $verify_row['password_hash']);
    echo "password_verify('admin123', hash): " . ($test ? "✓ VÁLIDA" : "✗ INVÁLIDA") . "\n";
} else {
    echo "✗ Erro ao atualizar: " . $stmt->error . "\n";
}
$stmt->close();
echo "</pre>";
?>
