<?php
require_once __DIR__ . '/config.php';

function getEditions($mysqli): array {
    $stmt = $mysqli->prepare("SELECT * FROM edicoes ORDER BY ano DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$editions = getEditions($mysqli);
$currentEdition = $editions[0] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edições - Além do Espelho</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="container">
                <div class="header-inner">
                <?php include __DIR__ . '/header_brand.php'; ?>
                <div class="site-nav">
                    <a href="index.php">Inscrição</a>
                    <a href="edicoes.php">Edições</a>
                    <a href="regras.php">Regras</a>
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <!-- HERO EDIÇÕES -->
        <section class="hero" style="min-height: 400px; display: flex; align-items: center; background: linear-gradient(135deg, rgba(212, 175, 55, 0.08), rgba(244, 208, 63, 0.04));">
            <div class="container">
                <div class="hero-copy" style="animation: slideInLeft 0.8s ease;">
                    <h1 style="background: linear-gradient(135deg, #D4AF37, #F4D03F); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem; font-weight: 800;">
                        ✨ TODAS AS EDIÇÕES
                    </h1>
                    <p class="hero-note" style="font-size: 1.15rem; color: var(--muted); margin-top: 1rem;">
                        "Cada edição é um novo convite para transformação profunda" — Conheça as edições do retiro "Além do Espelho" e escolha a que mais ressoa com você.
                    </p>
                </div>
            </div>
        </section>

        <!-- EDIÇÃO ATUAL -->
        <?php if ($currentEdition): ?>
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h1 style="font-size:1.6rem; margin-bottom:0.25rem;">Alem do Espelho</h1>
                    <h2>✨ Edição: <?php echo htmlspecialchars($currentEdition['titulo'] ?? 'O Confronto'); ?> — <?php echo htmlspecialchars($currentEdition['ano']); ?></h2>
                    <p><?php echo htmlspecialchars($currentEdition['descricao']); ?></p>
                </div>

                <div class="cards-grid" style="grid-template-columns: 1fr;">
                    <div class="glass-strong" style="text-align: center; padding: 3rem; border-radius: 16px; animation: fadeInScaleUp 0.8s ease;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🔴</div>
                        <h3 style="background: linear-gradient(135deg, #D4AF37, #F4D03F); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($currentEdition['titulo']); ?>
                        </h3>
                        <p style="margin: 1rem 0; font-size: 1.1rem; color: var(--accent);">
                            ⭐ Vagas Limitadas | 🎯 Inscrições Abertas
                        </p>
                        <a href="inscricao.php" class="btn btn-primary" style="margin-top: 1.5rem; animation: bounce-smooth 2s ease-in-out infinite;">
                            ➜ Inscrever-se Agora
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- EDIÇÕES ANTERIORES -->
        <section class="section" style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.04), rgba(244, 208, 63, 0.02));">
            <div class="container">
                <div class="section-heading">
                    <h2>📖 Edições Anteriores</h2>
                    <p>Reviva os momentos marcantes que já transformaram vidas</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                    <?php foreach (array_slice($editions, 1) as $index => $edition): ?>
                    <div class="glass" style="padding: 2rem; border-radius: 12px; animation: fadeInUp 0.6s ease <?= ($index * 0.1) ?>s backwards;">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem; text-align: center;">🎆</div>
                        <h3 style="background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700; margin-top: 0;">
                            <?php echo htmlspecialchars($edition['titulo']); ?>
                        </h3>
                        <p style="color: var(--muted); font-size: 0.95rem; margin: 1rem 0; font-weight: 600;">
                            📅 Edição <?php echo htmlspecialchars($edition['ano']); ?>
                        </p>
                        <p style="margin: 1rem 0; color: var(--text); line-height: 1.6;">
                            <?php echo htmlspecialchars($edition['descricao']); ?>
                        </p>
                        <p style="color: var(--muted); font-size: 0.85rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(212, 175, 55, 0.1);">
                            <strong>Criada em:</strong> 
                            <?php echo date('d/m/Y', strtotime($edition['criado_em'])); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($editions) <= 1): ?>
                <div style="text-align: center; padding: 2rem; color: var(--muted);">
                    <p>Primeira edição! Histórico será atualizado com as próximas edições.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ESTATÍSTICAS -->
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>Por Números</h2>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div class="card" style="text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--primary); font-weight: bold;">
                            <?php echo count($editions); ?>
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem;">
                            Total de Edições
                        </p>
                    </div>

                    <div class="card" style="text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--primary); font-weight: bold;">
                            2026
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem;">
                            Ano de Início
                        </p>
                    </div>

                    <div class="card" style="text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--primary); font-weight: bold;">
                            ∞
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem;">
                            Histórias Transformadas
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
