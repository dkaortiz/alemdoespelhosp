<?php
declare(strict_types=1);

require_once 'config.php';

$DB_HOST = trim((string) getenv('DB_HOST'));
$DB_USER = trim((string) getenv('DB_USER'));
$DB_PASS = trim((string) getenv('DB_PASS'));
$DB_NAME = trim((string) getenv('DB_NAME'));

if ($DB_HOST === '' || $DB_USER === '' || $DB_PASS === '' || $DB_NAME === '') {
    die('Configuração do banco ausente. Defina DB_HOST, DB_USER, DB_PASS e DB_NAME no .env.');
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Falha ao conectar ao banco de dados: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

echo "🔍 Verificando estrutura da tabela edicoes...\n";

// Verificar colunas existentes
$result = $mysqli->query("DESCRIBE edicoes");
$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

echo "Colunas atuais: " . implode(', ', $existing_columns) . "\n\n";

// Verificar e adicionar colunas faltantes
$columns_to_add = [
    'limite_homens' => "INT DEFAULT 15 COMMENT 'Limite de vagas masculinas para esta edição'",
    'limite_mulheres' => "INT DEFAULT 15 COMMENT 'Limite de vagas femininas para esta edição'",
    'limite_anfitrioes' => "INT DEFAULT 999 COMMENT 'Limite de anfitriões (geralmente sem limite)'",
    'data_inicio' => "DATE COMMENT 'Data de início do evento'",
    'data_fim' => "DATE COMMENT 'Data de término do evento'",
    'local' => "VARCHAR(255) COMMENT 'Local do evento'",
    'data_inscricao_inicio' => "DATE COMMENT 'Data de início das inscrições (formatada em PT: DD de MÊS de YYYY)'",
    'data_inscricao_fim' => "DATE COMMENT 'Data de término das inscrições (formatada em PT: DD de MÊS de YYYY)'"
];

foreach ($columns_to_add as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        echo "➕ Adicionando coluna: $column\n";
        $sql = "ALTER TABLE edicoes ADD COLUMN $column $definition";
        if ($mysqli->query($sql)) {
            echo "   ✅ Coluna $column adicionada com sucesso!\n";
        } else {
            echo "   ❌ Erro ao adicionar coluna $column: " . $mysqli->error . "\n";
        }
    } else {
        echo "✅ Coluna $column já existe\n";
    }
}

echo "\n🎉 Estrutura do banco de dados corrigida!\n";

$mysqli->close();

