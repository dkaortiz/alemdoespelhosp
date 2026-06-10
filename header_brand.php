<?php
declare(strict_types=1);
// Header brand include — mostra nome do projeto e edição atual
if (!isset($mysqli)) {
    require_once __DIR__ . '/config.php';
}
?>
<div style="display: flex; align-items: center; gap: 1.5rem;">
    <div style="display: flex; flex-direction: column;">
        <a href="index.php" class="brand" style="text-decoration: none;">
            <span style="
                font-weight: 800;
                letter-spacing: -0.02em;
                font-size: 1.4rem;
                background: linear-gradient(135deg, #4338CA, #7c3aed, #06b6d4);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            ">Além do Espelho</span>
        </a>
    </div>
    <div style="
        width: 1px;
        height: 32px;
        background: linear-gradient(180deg, transparent, rgba(124, 58, 237, 0.5), transparent);
    "></div>
    <span style="
        color: #06b6d4;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    ">O Confronto</span>
</div>
