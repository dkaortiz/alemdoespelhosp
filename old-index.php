<?php
require_once 'config.php';

function getCurrentEdition($mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM edicoes ORDER BY ano DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getCountByGender($mysqli, $table, $genero) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM $table WHERE genero = ? AND payment_status = 'confirmado'");
    $stmt->bind_param('s', $genero);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['cnt'] ?? 0;
}

$edition = getCurrentEdition($mysqli);
$remainingM = max(0, 15 - getCountByGender($mysqli, 'peregrinos', 'masculino'));
$remainingF = max(0, 15 - getCountByGender($mysqli, 'peregrinos', 'feminino'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Além do Espelho - Evento Transformador</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="container">
            <div class="header-inner">
                <?php include __DIR__ . '/header_brand.php'; ?>
                <div class="site-nav">
                    <a href="index.php">Home</a>
                    <a href="edicoes.php">Edições</a>
                    <a href="inscricao.php">Inscrição</a>
                    <a href="regras.php">Regras</a>
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <!-- HERO COM ESPELHO VISUAL -->
        <section class="hero glass-strong" style="min-height: 600px; display: flex; align-items: center; position: relative; overflow: hidden;">
            <!-- ESPELHO ANIMADO AO FUNDO -->
            <img src="assets/icons/mirror.svg" alt="Espelho" class="bg-mirror">
            
            <div class="container" style="position: relative; z-index: 2;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
                    <!-- TEXTO HERO -->
                    <div class="hero-content" style="animation: slideInLeft 0.8s ease;">
                        <h1 style="background: linear-gradient(135deg, #D4AF37, #F4D03F, #B8860B); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem; margin-bottom: 0.5rem; font-weight: 800; line-height: 1.15;">
                            ✨ 1ª Edição - O Confronto<br>Além do Espelho
                        </h1>
                        <p class="subtitle" style="font-size: 1.15rem; color: var(--primary); margin-bottom: 1rem; font-weight: 700;">
                            "Antes do propósito, existe o confronto." — Um encontro que pode mudar toda a sua história.
                        </p>
                        <p style="font-size: 1rem; color: var(--muted); max-width: 600px; line-height: 1.8; margin-bottom: 1rem;">
                            Um retiro transformador para todas as idades. Confronte verdades, quebre máscaras e descubra quem você realmente é em Cristo. "Sonda-me, ó Deus, e conhece o meu coração." — Salmos 139:23
                        </p>
                        <div class="hero-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="inscricao.php" class="btn btn-primary" style="animation: bounce-smooth 2s ease-in-out infinite;">
                                Inscrever-se Agora
                            </a>
                            <a href="edicoes.php" class="btn btn-secondary">Saiba Mais</a>
                        </div>
                    </div>
                    
                    <!-- ESPELHO DECORATIVO -->
                    <div style="text-align: center; animation: slideInRight 0.8s ease 0.2s backwards;">
                        <div style="
                            width: 320px;
                            height: 380px;
                            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 208, 63, 0.05));
                            border: 3px solid #D4AF37;
                            border-radius: 20px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            animation: pulse-glow 3s ease-in-out infinite;
                            box-shadow: 0 0 60px rgba(212, 175, 55, 0.3), inset 0 0 40px rgba(212, 175, 55, 0.1);
                            margin: 0 auto;
                        ">
                            <img src="assets/icons/mirror.svg" alt="Espelho" class="hero-mirror">
                        </div>
                        <p style="color: var(--primary); margin-top: 1.5rem; font-size: 1rem; font-weight: 600; letter-spacing: 2px;">
                            AUTENTICIDADE
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- EDIÇÃO ATUAL -->
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>Edição <?php echo $edition['ano'] ?? 2026; ?></h2>
                    <p><?php echo $edition['descricao'] ?? 'Um encontro que pode mudar toda a sua história.'; ?></p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                    <div class="glass-strong" style="padding: 2rem; text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem; font-weight: bold;">
                            <?php echo $remainingM; ?>
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">
                            Vagas Homens
                        </p>
                    </div>
                    <div class="glass-strong" style="padding: 2rem; text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--accent); margin-bottom: 0.5rem; font-weight: bold;">
                            <?php echo $remainingF; ?>
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">
                            Vagas Mulheres
                        </p>
                    </div>
                    <div class="glass-strong" style="padding: 2rem; text-align: center;">
                        <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem; font-weight: bold;">
                            R$ 150
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">
                            Valor Inscrição
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROPÓSITO -->
        <section class="section" style="background: linear-gradient(135deg, rgba(20, 15, 10, 0.8), rgba(30, 22, 15, 0.8));">
            <div class="container">
                <div class="section-heading">
                    <h2>O Propósito</h2>
                    <p>Descubra quem você é além da imagem que todos veem</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.1s both;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));">❤️💔</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Confronto com a Verdade</h3>
                        <p style="color: var(--muted);">Encare suas realidades e descubra quem você é além do que os outros veem.</p>
                    </div>
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.2s both;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));">🎭</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Quebra de Máscaras</h3>
                        <p style="color: var(--muted);">Liberte-se das personagens que você criou para o mundo.</p>
                    </div>
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.3s both;">
                        <div style="margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));"><img src="assets/icons/mirror.svg" alt="Espelho" class="card-icon"></div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Encontro Consigo Mesmo</h3>
                        <p style="color: var(--muted);">Momentos de reflexão que transformam perspectivas.</p>
                    </div>
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.1s both;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));">✝️</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Encontro com Deus</h3>
                        <p style="color: var(--muted);">Espaço sagrado para reconectar com o propósito maior.</p>
                    </div>
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.2s both;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));">💞</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Cura e Restauração</h3>
                        <p style="color: var(--muted);">Espaço seguro para cicatrizar feridas e reconstruir-se.</p>
                    </div>
                    <div class="glass" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.3s both;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));">👑</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.2rem;">Identidade e Propósito</h3>
                        <p style="color: var(--muted);">Renasça com clareza sobre quem você realmente é.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CHAMADA PARA AÇÃO -->
        <section class="section" style="text-align: center;">
            <div class="container">
                <h2 style="margin-bottom: 1.5rem;">Pronto para Transformar?</h2>
                <p style="color: var(--muted); margin-bottom: 2rem; font-size: 1.1rem;">
                    Inscreva-se como Peregrino ou Anfitrião e seja parte dessa jornada.
                </p>
                <a href="inscricao.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2.5rem;">
                    Começar Inscrição
                </a>
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
