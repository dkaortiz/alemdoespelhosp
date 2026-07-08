<?php
declare(strict_types=1);
// Header brand include — mostra nome do projeto e edição atual
if (!isset($mysqli)) {
    require_once __DIR__ . '/config.php';
}
?>
<div style="display: flex; align-items: center; gap: 1rem;">
    <a href="index.php" class="brand" style="display:flex; align-items:center; justify-content:center; text-decoration:none;">
        <div style="width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.08); border: 2px solid rgba(255, 215, 112, 0.35); display: flex; align-items: center; justify-content: center; box-shadow: 0 15px 30px rgba(255, 199, 77, 0.18); backdrop-filter: blur(12px); overflow: hidden;">
            <img src="public/Logosemfundo.png" alt="Além do Espelho" style="height: 100px; width: auto; display: block;">
        </div>
    </a>
</div>
