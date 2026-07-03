<?php
declare(strict_types=1);
session_start();
require_once 'config.php';

// Verificar autenticação
if (!isAdminAuthenticated()) {
    die('Não autenticado - faça login em admin.php');
}

echo "<h2>Status da Conexão MySQL</h2>";
echo "<p><strong>MySQLi Conectado:</strong> " . ($mysqli ? "✓ Sim" : "✗ Não") . "</p>";
echo "<p><strong>Usuário:</strong> " . htmlspecialchars($_SESSION['admin_username'] ?? 'indefinido') . "</p>";

// Teste simples de DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $table = $_POST['table'] ?? '';
    
    echo "<h2>Resultado do DELETE</h2>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    echo "ID recebido: {$id}\n";
    echo "Tabela recebida: {$table}\n";
    echo "POST completo: " . print_r($_POST, true) . "\n";
    
    if ($id > 0 && in_array($table, ['peregrinos', 'anfitrioes'])) {
        // Primeiro, busca o registro antes de deletar
        $stmt = $mysqli->prepare("SELECT nome FROM {$table} WHERE id = ?");
        if (!$stmt) {
            echo "ERRO ao preparar SELECT: " . $mysqli->error . "\n";
        } else {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row) {
                echo "✓ Registro encontrado: {$row['nome']}\n";
                echo "Executando DELETE...\n";
                
                $stmt = $mysqli->prepare("DELETE FROM {$table} WHERE id = ?");
                if (!$stmt) {
                    echo "ERRO ao preparar DELETE: " . $mysqli->error . "\n";
                } else {
                    $stmt->bind_param('i', $id);
                    
                    if ($stmt->execute()) {
                        echo "✓ DELETE executado com sucesso!\n";
                        echo "Linhas afetadas: " . $stmt->affected_rows . "\n";
                    } else {
                        echo "✗ Erro ao deletar: " . $stmt->error . "\n";
                    }
                    $stmt->close();
                }
            } else {
                echo "✗ Registro não encontrado na tabela '{$table}' com ID={$id}\n";
            }
        }
    } else {
        echo "✗ ID ou tabela inválidos (ID={$id}, TABLE={$table})\n";
    }
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teste DELETE</title>
</head>
<body>
    <h1>Teste de DELETE</h1>
    
    <h2>Peregrinos</h2>
    <table border="1">
        <tr><th>ID</th><th>Nome</th><th>Email</th><th>Ação</th></tr>
        <?php
        $result = $mysqli->query("SELECT id, nome, email FROM peregrinos LIMIT 5");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>";
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='{$row['id']}'>";
            echo "<input type='hidden' name='table' value='peregrinos'>";
            echo "<button type='submit' onclick='return confirm(\"Deletar?\")'>Deletar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h2>Anfitriões</h2>
    <table border="1">
        <tr><th>ID</th><th>Nome</th><th>Email</th><th>Ação</th></tr>
        <?php
        $result = $mysqli->query("SELECT id, nome, email FROM anfitrioes LIMIT 5");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>";
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='{$row['id']}'>";
            echo "<input type='hidden' name='table' value='anfitrioes'>";
            echo "<button type='submit' onclick='return confirm(\"Deletar?\")'>Deletar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    
    <p><a href="admin.php">Voltar ao Admin</a></p>
</body>
</html>
