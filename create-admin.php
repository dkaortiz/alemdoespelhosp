<?php
/**
 * Script para criar o primeiro admin.
 * Execute via CLI: php create-admin.php
 */

require_once __DIR__ . '/config.php';

// Verificar se há args de CLI
if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via CLI.\n");
}

echo "=== Criar Admin do Painel ===\n";
echo "Digite o nome do usuário admin (padrão: admin): ";
$username = trim(fgets(STDIN)) ?: 'admin';

echo "Digite a senha (mínimo 6 caracteres): ";
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n";

if (strlen($password) < 6) {
    die("Erro: Senha deve ter no mínimo 6 caracteres.\n");
}

echo "Digite o email (opcional): ";
$email = trim(fgets(STDIN)) ?: null;

// Tentar criar o admin
if (createAdmin($username, $password, $email, 'super')) {
    echo "✓ Admin criado com sucesso!\n";
    echo "Usuário: $username\n";
    if ($email) echo "Email: $email\n";
    echo "\nVocê pode agora fazer login em /admin.php com essas credenciais.\n";
} else {
    die("✗ Erro ao criar admin. Verifique se o usuário já existe.\n");
}
