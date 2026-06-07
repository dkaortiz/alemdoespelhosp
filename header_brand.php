<?php
// Header brand include — mostra nome do projeto e edição atual
if (!isset($mysqli)) {
    require_once __DIR__ . '/config.php';
}
$editionTitle = 'Edição Confronto';
$stmt = $mysqli->prepare("SELECT titulo FROM edicoes ORDER BY ano DESC LIMIT 1");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row && !empty($row['titulo'])) {
        $editionTitle = $row['titulo'];
    }
}
?>
<a href="index.php" class="brand"><img src="assets/icons/mirror.svg" alt="Espelho" class="icon-inline"> Alem do Espelho</a>
<span style="color:var(--muted); font-size:0.95rem; margin-left:0.85rem; font-weight:600;">Edição: <?php echo htmlspecialchars($editionTitle); ?></span>
