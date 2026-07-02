<?php
declare(strict_types=1);
// Header brand include — mostra nome do projeto e edição atual
if (!isset($mysqli)) {
    require_once __DIR__ . '/config.php';
}
?>
<div style="display: flex; align-items: center; gap: 1rem;">
    <a href="index.php" class="brand" style="display:flex; flex-direction: column; line-height:1; text-decoration:none;">
        <span>Além do Espelho</span>
        <small>O Confronto</small>
    </a>
</div>
