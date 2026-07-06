<?php
declare(strict_types=1);
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
$limite_homem = $edition['limite_homens'] ?? 15;
$limite_mulher = $edition['limite_mulheres'] ?? 15;
$remainingM = max(0, $limite_homem - getCountByGender($mysqli, 'peregrinos', 'masculino'));
$remainingF = max(0, $limite_mulher - getCountByGender($mysqli, 'peregrinos', 'feminino'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Além do Espelho - Evento Transformador</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <?php include __DIR__ . '/meta-tags.php'; ?>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="container">
            <div class="header-inner">
                <?php include __DIR__ . '/header_brand.php'; ?>
                <button class="hamburger-menu" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="site-nav">
                    <a href="index.php">Home</a>
                    <a href="edicoes.php">Edições</a>
                    <a href="inscricao.php">Inscrição</a>
                    <a href="regras.php">Regras</a>
                    <a href="user_login.php">Login</a>
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
            <a href="user_login.php">Login</a>
            <a href="admin.php">Admin</a>
        </div>
    </header>

    <main>
        <!-- 1. HERO SECTION COM VÍDEO FULLSCREEN -->
        <section class="hero-video-section">
            <video autoplay loop muted playsinline preload="auto" poster="public/video/DavidOrtiz.jpg" aria-hidden="true">
                <source src="public/video/DavidOrtiz.mp4" type="video/mp4">
            </video>
            <div class="hero-video-overlay"></div>

            <div class="container hero-video-content">
                <div class="hero-video-panel glass-strong">
                    <div class="hero-video-pill">Evento transformador</div>
                    <div class="hero-video-actions">
                        <a href="inscricao.php" class="btn btn-primary">Inscrever-se agora</a>
                        <a href="edicoes.php" class="btn btn-secondary">Conhecer a edição</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="logo-showcase-section">
            <div class="container">
                <div class="logo-showcase-card">
                    <img src="public/Logosemfundo.png" alt="Logo Além do Espelho" loading="eager">
                </div>
            </div>
        </section>

        <!-- 2. POR QUE PARTICIPAR -->
        <section class="section" ">
            <div class="container">
                <div class="section-heading">
                    <h2>🌟 Por Que Participar?</h2>
                    <p>Descubra o poder transformador dessa experiência única</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.8rem;">
                    <div class="glass-strong" style="padding: 2rem; border: 1px solid rgba(216, 178, 119, 0.24); animation: fadeInUp 0.6s ease;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔍</div>
                        <h3 style="color: var(--accent); margin-bottom: 0.8rem; font-size: 1.15rem;">Autossuperação</h3>
                        <p style="color: var(--muted); font-size: 0.95rem;">Vença limites internos e descubra seu verdadeiro potencial.</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; border: 1px solid rgba(216, 178, 119, 0.24); animation: fadeInUp 0.6s ease 0.1s backwards;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;">🤝</div>
                        <h3 style="color: var(--accent); margin-bottom: 0.8rem; font-size: 1.15rem;">Comunidade</h3>
                        <p style="color: var(--muted); font-size: 0.95rem;">Conecte-se com pessoas que buscam transformação genuína.</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; border: 1px solid rgba(216, 178, 119, 0.24); animation: fadeInUp 0.6s ease 0.2s backwards;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;">💪</div>
                        <h3 style="color: var(--accent); margin-bottom: 0.8rem; font-size: 1.15rem;">Força Interior</h3>
                        <p style="color: var(--muted); font-size: 0.95rem;">Desenvolva resiliência emocional e espiritual duradoura.</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; border: 1px solid rgba(216, 178, 119, 0.24); animation: fadeInUp 0.6s ease 0.3s backwards;">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;">🎯</div>
                        <h3 style="color: var(--accent); margin-bottom: 0.8rem; font-size: 1.15rem;">Clareza</h3>
                        <p style="color: var(--muted); font-size: 0.95rem;">Encontre sentido e direção para sua vida.</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="section"  backdrop-filter: blur(10px);">
            <div class="container">
                <div class="section-heading">
                    <h2>⚡ Edição <?php echo $edition['ano'] ?? 2026; ?> - <?php echo htmlspecialchars($edition['titulo'] ?? 'O Confronto'); ?></h2>
                    <p><?php echo htmlspecialchars($edition['descricao'] ?? 'Um encontro que pode mudar toda a sua história.'); ?></p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 3rem;">
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease; border: 2px solid rgba(216, 178, 119, 0.28); background: linear-gradient(135deg, rgba(216, 178, 119, 0.16), rgba(255, 248, 235, 0.06));">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👨</div>
                        <div style="font-size: 3.5rem; color: var(--primary); margin-bottom: 1rem; font-weight: bold; text-shadow: 0 0 20px rgba(67, 56, 202, 0.3); animation: pulse-glow 2s ease-in-out infinite;">
                            <?php echo $remainingM; ?>
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; font-weight: 600;">
                            Vagas Homens
                        </p>
                        <p style="color: var(--accent); font-size: 0.8rem; margin-top: 0.5rem;">Limite: <?php echo $limite_homem; ?> peregrinos</p>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.1s backwards; border: 2px solid rgba(216, 178, 119, 0.28); background: linear-gradient(135deg, rgba(216, 178, 119, 0.16), rgba(255, 248, 235, 0.05));">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👩</div>
                        <div style="font-size: 3.5rem; color: var(--accent); margin-bottom: 1rem; font-weight: bold; text-shadow: 0 0 20px rgba(124, 58, 237, 0.3); animation: pulse-glow 2s ease-in-out infinite;">
                            <?php echo $remainingF; ?>
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; font-weight: 600;">
                            Vagas Mulheres
                        </p>
                        <p style="color: var(--accent); font-size: 0.8rem; margin-top: 0.5rem;">Limite: <?php echo $limite_mulher; ?> peregrinas</p>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.2s backwards; border: 2px solid rgba(216, 178, 119, 0.28); background: linear-gradient(135deg, rgba(216, 178, 119, 0.14), rgba(255, 248, 235, 0.04));">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💳</div>
                        <div style="font-size: 3.5rem; color: var(--accent-secondary); margin-bottom: 1rem; font-weight: bold; text-shadow: 0 0 20px rgba(6, 182, 212, 0.3);">
                            R$ 150
                        </div>
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; font-weight: 600;">
                            Valor Inscrição
                        </p>
                        <p style="color: var(--accent-secondary); font-size: 0.8rem; margin-top: 0.5rem;">PIX + Cartão Crédito</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <?php if ($edition['data_inicio'] || $edition['data_fim']): ?>
                    <div style="padding: 2.5rem; border: 2px solid rgba(216, 178, 119, 0.28); background: linear-gradient(135deg, rgba(216, 178, 119, 0.14), rgba(255, 248, 235, 0.06)); border-radius: 16px; text-align: center; box-shadow: 0 0 30px rgba(216, 178, 119, 0.16); backdrop-filter: blur(16px); animation: fadeInUp 0.6s ease;">
                        <p style="color: #f2dcc0; font-size: 0.8rem; margin: 0 0 1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; text-shadow: 0 0 10px rgba(216, 178, 119, 0.24);">📅 Datas do Evento</p>
                        <p style="color: #ec4899; font-size: 1.6rem; margin: 0 0 0.5rem; font-weight: 800; line-height: 1.2;">
                            <?php 
                            $data_inicio_fmt = formatDatePT($edition['data_inicio']);
                            $data_fim_fmt = formatDatePT($edition['data_fim']);
                            echo $data_inicio_fmt . ' <br/> até <br/> ' . $data_fim_fmt;
                            ?>
                        </p>
                        <?php if ($edition['local']): ?>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(216, 178, 119, 0.24);">
                            <p style="color: #f472b6; font-size: 1.1rem; margin: 0; font-weight: 600;">📍 <?php echo htmlspecialchars($edition['local']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div style="padding: 2.5rem; border: 2px solid rgba(216, 178, 119, 0.28); background: linear-gradient(135deg, rgba(216, 178, 119, 0.14), rgba(255, 248, 235, 0.05)); border-radius: 16px; text-align: center; box-shadow: 0 0 30px rgba(216, 178, 119, 0.16); backdrop-filter: blur(16px); animation: fadeInUp 0.6s ease 0.1s backwards;">
                        <p style="color: #f2dcc0; font-size: 0.8rem; margin: 0 0 1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; text-shadow: 0 0 10px rgba(216, 178, 119, 0.24);">⏰ Inscrições Abertas</p>
                        <p style="color: #22d3ee; font-size: 1.4rem; margin: 0; font-weight: 800;">
                            <?php 
                            $data_insc_inicio = formatDatePT($edition['data_inscricao_inicio'] ?? '2026-06-02');
                            $data_insc_fim = formatDatePT($edition['data_inscricao_fim'] ?? '2026-06-30');
                            $insc_inicio_parts = explode(' ', $data_insc_inicio);
                            $insc_fim_parts = explode(' ', $data_insc_fim);
                            echo $insc_inicio_parts[0] . ' de ' . $insc_inicio_parts[2] . ' <br/> até <br/> ' . $insc_fim_parts[0] . ' de ' . $insc_fim_parts[2];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. COMO FUNCIONA -->
        <section class="section" style="background: linear-gradient(135deg, rgba(45, 27, 105, 0.3), rgba(15, 23, 42, 0.5));">
            <div class="container">
                <div class="section-heading">
                    <h2>🚀 Como Funciona</h2>
                    <p>Um processo simples e transformador</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; max-width: 1200px; margin: 0 auto;">
                    <div class="glass-strong" style="padding: 2rem; text-align: center; animation: fadeInUp 0.6s ease;">
                        <div style="
                            width: 60px;
                            height: 60px;
                            background: linear-gradient(135deg, #4338CA, #7c3aed);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 1.5rem;
                            font-size: 1.8rem;
                            font-weight: bold;
                            color: white;
                        ">
                            1
                        </div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Inscreva-se</h3>
                        <p style="color: var(--muted); font-size: 0.9rem;">Preencha o formulário e escolha seu perfil (Peregrino ou Anfitrião)</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; text-align: center; animation: fadeInUp 0.6s ease 0.1s backwards;">
                        <div style="
                            width: 60px;
                            height: 60px;
                            background: linear-gradient(135deg, #7c3aed, #06b6d4);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 1.5rem;
                            font-size: 1.8rem;
                            font-weight: bold;
                            color: white;
                        ">
                            2
                        </div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Efetue o Pagamento</h3>
                        <p style="color: var(--muted); font-size: 0.9rem;">Pague R$ 150 via PIX ou Cartão de Crédito de forma segura</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; text-align: center; animation: fadeInUp 0.6s ease 0.2s backwards;">
                        <div style="
                            width: 60px;
                            height: 60px;
                            background: linear-gradient(135deg, #06b6d4, #4338CA);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 1.5rem;
                            font-size: 1.8rem;
                            font-weight: bold;
                            color: white;
                        ">
                            3
                        </div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Confirmação</h3>
                        <p style="color: var(--muted); font-size: 0.9rem;">Receba confirmação por email e WhatsApp com todas as informações</p>
                    </div>

                    <div class="glass-strong" style="padding: 2rem; text-align: center; animation: fadeInUp 0.6s ease 0.3s backwards;">
                        <div style="
                            width: 60px;
                            height: 60px;
                            background: linear-gradient(135deg, #4338CA, #7c3aed);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 1.5rem;
                            font-size: 1.8rem;
                            font-weight: bold;
                            color: white;
                        ">
                            4
                        </div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Transformação</h3>
                        <p style="color: var(--muted); font-size: 0.9rem;">Compareça e inicie sua jornada transformadora!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. SEÇÃO DE DEPOIMENTOS -->
        <section class="section" ">
            <div class="container">
                <div class="section-heading">
                    <h2>💬 O Que Dizem Sobre o Evento</h2>
                    <p>Histórias de transformação e impacto</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease; position: relative; border: 1px solid rgba(124, 58, 237, 0.3);">
                        <div style="position: absolute; top: -25px; left: 20px; width: 50px; height: 50px; background: linear-gradient(135deg, #4338CA, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">😊</div>
                        <div style="margin-top: 1rem; margin-bottom: 1.5rem;">
                            <p style="color: var(--muted); font-size: 1rem; line-height: 1.6; font-style: italic;">
                                "Este evento mudou completamente minha perspectiva sobre mim mesmo. Descobri força que não sabia ter."
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--accent); font-weight: 600; margin: 0;">Lucas Silva</p>
                            <p style="color: var(--muted); font-size: 0.85rem; margin: 0;">Peregrino - 1ª Edição</p>
                        </div>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease 0.1s backwards; position: relative; border: 1px solid rgba(124, 58, 237, 0.3);">
                        <div style="position: absolute; top: -25px; left: 20px; width: 50px; height: 50px; background: linear-gradient(135deg, #4338CA, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">🌟</div>
                        <div style="margin-top: 1rem; margin-bottom: 1.5rem;">
                            <p style="color: var(--muted); font-size: 1rem; line-height: 1.6; font-style: italic;">
                                "Que privilégio ser anfitriã e servir a transformação de outras pessoas. Cresci tanto quanto eles."
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--accent); font-weight: 600; margin: 0;">Maria Oliveira</p>
                            <p style="color: var(--muted); font-size: 0.85rem; margin: 0;">Anfitriã - 1ª Edição</p>
                        </div>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease 0.2s backwards; position: relative; border: 1px solid rgba(124, 58, 237, 0.3);">
                        <div style="position: absolute; top: -25px; left: 20px; width: 50px; height: 50px; background: linear-gradient(135deg, #4338CA, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">✨</div>
                        <div style="margin-top: 1rem; margin-bottom: 1.5rem;">
                            <p style="color: var(--muted); font-size: 1rem; line-height: 1.6; font-style: italic;">
                                "Quebrei máscaras que carregava há anos. Agora conheço meu verdadeiro propósito."
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--accent); font-weight: 600; margin: 0;">João Carlos</p>
                            <p style="color: var(--muted); font-size: 0.85rem; margin: 0;">Peregrino - 1ª Edição</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>🎯 O Chamado para Transformação</h2>
                    <p>Pilares fundamentais do evento que mudam vidas</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">❤️</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Confronto com a Verdade</h3>
                        <p style="color: var(--muted);">Encare suas realidades profundas e descubra quem você é além do que os outros veem.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Autoconhecimento genuíno</span>
                        </div>
                    </div>
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.1s backwards; background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">🎭</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Quebra de Máscaras</h3>
                        <p style="color: var(--muted);">Liberte-se das personagens e roleagens que criou para o mundo.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Autenticidade total</span>
                        </div>
                    </div>
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.2s backwards; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">✨</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Encontro Consigo Mesmo</h3>
                        <p style="color: var(--muted);">Momentos profundos de reflexão que transformam perspectivas e abrem novas visões.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Clareza interior</span>
                        </div>
                    </div>
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.3s backwards; background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">✝️</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Encontro com Deus</h3>
                        <p style="color: var(--muted);">Espaço sagrado para reconectar com o propósito maior e a dimensão espiritual.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Transcendência</span>
                        </div>
                    </div>
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.4s backwards; background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">💞</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Cura e Restauração</h3>
                        <p style="color: var(--muted);">Espaço seguro para cicatrizar feridas profundas e reconstruir-se com honra.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Renovação integral</span>
                        </div>
                    </div>
                    <div class="glass-strong" style="padding: 2.5rem; text-align: center; animation: fadeInUp 0.6s ease 0.5s backwards; background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(67, 56, 202, 0.08));">
                        <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(67, 56, 202, 0.4));">👑</div>
                        <h3 style="color: var(--accent); margin-bottom: 1rem; font-size: 1.3rem;">Identidade e Propósito</h3>
                        <p style="color: var(--muted);">Renasça com clareza absoluta sobre quem você realmente é e seu verdadeiro propósito.</p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(67, 56, 202, 0.2);">
                            <span style="color: var(--accent-secondary); font-size: 0.9rem;">💡 Propósito definido</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. SEÇÃO COM IMAGEM FULLSCREEN -->
        <section class="full-image-section">
            <div class="container" style="position: relative; z-index: 1;">
                <div class="image-content-card">
                    <h2 style="font-size: clamp(2rem, 4vw, 3rem);">
                        O chamado para a transformação começa aqui
                    </h2>
                    <p style="font-size: 1.08rem; line-height: 1.8; margin-bottom: 2rem;">
                        Uma experiência profunda, acolhedora e transformadora para quem busca verdade, propósito e renovação.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="inscricao.php" class="btn btn-primary">Inscrever-se agora</a>
                        <a href="regras.php" class="btn btn-secondary">Ver regulamentos</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. CHAMADA FINAL PARA AÇÃO -->
        <section class="section" style="background: linear-gradient(135deg, rgba(216, 178, 119, 0.16), rgba(255, 248, 235, 0.08)); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(216, 178, 119, 0.12), transparent); border-radius: 50%; z-index: 0;"></div>
            <div style="position: absolute; bottom: -50px; left: -80px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(244, 225, 178, 0.12), transparent); border-radius: 50%; z-index: 0;"></div>
            
            <div class="container" style="position: relative; z-index: 1;">
                <div style="text-align: center; max-width: 800px; margin: 0 auto;">
                    <h2 style="font-size: 2.8rem; margin-bottom: 1.5rem; background: linear-gradient(135deg, #f4e1b2, #c9994f, #8a5a24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: fadeInUp 0.6s ease;">
                        🎯 Sua Transformação Começa Agora
                    </h2>
                    
                    <p style="color: var(--muted); font-size: 1.1rem; line-height: 1.8; margin-bottom: 2.5rem; animation: fadeInUp 0.6s ease 0.1s backwards;">
                        Não é coincidência que você está aqui. Você sente que algo deve mudar. Que você é maior do que aquilo que vive. Que existe um propósito maior te chamando.
                    </p>

                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem; animation: fadeInUp 0.6s ease 0.2s backwards;">
                        <a href="inscricao.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1.2rem 2.5rem; min-width: 200px; border: none; cursor: pointer;">
                            🚀 Inscrever-se Agora
                        </a>
                        <a href="regras.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 1.2rem 2.5rem;">
                            📋 Ver Regulamentos
                        </a>
                    </div>

                    <p style="color: var(--accent); font-size: 0.95rem; font-weight: 600; animation: fadeInUp 0.6s ease 0.3s backwards;">
                        ⏰ Inscrições até 30 de Junho de 2026
                    </p>
                </div>

                <div style="margin-top: 4rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div class="glass-strong" style="padding: 1.8rem; text-align: center; animation: fadeInUp 0.6s ease 0.4s backwards; border: 1px solid rgba(216, 178, 119, 0.24);">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🏆</div>
                        <p style="color: var(--accent); font-weight: 600; margin: 0;">Evento Premium</p>
                        <p style="color: var(--muted); font-size: 0.85rem; margin: 0.3rem 0 0;">Experiência de classe internacional</p>
                    </div>

                    <div class="glass-strong" style="padding: 1.8rem; text-align: center; animation: fadeInUp 0.6s ease 0.5s backwards; border: 1px solid rgba(216, 178, 119, 0.24);">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👥</div>
                        <p style="color: var(--accent); font-weight: 600; margin: 0;">Comunidade Exclusiva</p>
                        <p style="color: var(--muted); font-size: 0.85rem; margin: 0.3rem 0 0;">Conecte com pessoas genuínas</p>
                    </div>

                    <div class="glass-strong" style="padding: 1.8rem; text-align: center; animation: fadeInUp 0.6s ease 0.6s backwards; border: 1px solid rgba(216, 178, 119, 0.24);">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✨</div>
                        <p style="color: var(--accent); font-weight: 600; margin: 0;">Transformação Real</p>
                        <p style="color: var(--muted); font-size: 0.85rem; margin: 0.3rem 0 0;">Mudanças que duram a vida toda</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL DE INSCRIÇÃO EM MANUTENÇÃO -->
    <div id="inscriptionModal" class="modal-overlay" style="
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    ">
        <div class="modal-content" style="
            background: linear-gradient(135deg, rgba(216, 178, 119, 0.16), rgba(255, 248, 235, 0.08));
            border: 1px solid rgba(216, 178, 119, 0.24);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: slideInUp 0.4s ease;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        ">
            <div style="font-size: 4rem; margin-bottom: 1.5rem; animation: float 3s ease-in-out infinite;">🔧</div>
            <h2 style="
                font-size: 1.8rem;
                font-weight: 700;
                background: linear-gradient(135deg, #4338CA, #7c3aed);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin-bottom: 1rem;
            ">Sistema em Implementação</h2>
            <p style="color: var(--muted); line-height: 1.8; margin-bottom: 2rem; font-size: 1rem;">
                Estamos preparando nosso sistema de inscrições com as melhores práticas de segurança e qualidade.
            </p>
            <div style="
                background: rgba(216, 178, 119, 0.12);
                border-left: 4px solid var(--accent-secondary);
                padding: 1.5rem;
                border-radius: 12px;
                margin-bottom: 2rem;
                color: var(--text);
                font-weight: 600;
            ">
                ⏳ Sistema de pagamento sendo implementado. Retornaremos em breve!
            </div>
            <p style="color: var(--muted); line-height: 1.8; margin-bottom: 2rem; font-size: 1rem;">
                Para mais informações e dúvidas sobre inscrição, entre em contato conosco pelo WhatsApp:
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                <a href="https://wa.me/5511993813374?text=Olá!%20Gostaria%20de%20informações%20sobre%20inscrição%20no%20evento%20O%20Confronto" target="_blank" style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.85rem 1.5rem;
                    border-radius: 12px;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    font-weight: 600;
                    border: none;
                    cursor: pointer;
                    font-size: 1rem;
                    background: linear-gradient(135deg, #d8b277, #b8864b);
                    color: white;
                ">
                    📱 Fale Conosco no WhatsApp
                </a>
                <button onclick="closeInscriptionModal()" style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.85rem 1.5rem;
                    border-radius: 12px;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    font-weight: 600;
                    border: none;
                    cursor: pointer;
                    font-size: 1rem;
                    background: rgba(216, 178, 119, 0.16);
                    color: var(--text);
                ">
                    ✕ Fechar
                </button>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados. | Transformação • Autenticidade • Propósito</p>
        </div>
    </footer>

    <script src="<?php echo assetVersion('script.js'); ?>"></script>
    <script>
        function toggleMobileMenu() {
            const hamburger = document.querySelector('.hamburger-menu');
            const menu = document.getElementById('mobileMenu');
            hamburger.classList.toggle('active');
            menu.classList.toggle('active');
        }
        
        // Fechar menu ao clicar em um link
        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.hamburger-menu').classList.remove('active');
                document.getElementById('mobileMenu').classList.remove('active');
            });
        });
        
        function openInscriptionModal(type) {
            document.getElementById('inscriptionModal').style.display = 'flex';
        }
        
        function closeInscriptionModal() {
            document.getElementById('inscriptionModal').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora
        document.getElementById('inscriptionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInscriptionModal();
            }
        });
        
        // Fechar modal com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeInscriptionModal();
            }
        });
    </script>
</body>
</html>
