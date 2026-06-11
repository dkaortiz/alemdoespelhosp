<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function getEditions($mysqli): array {
    $stmt = $mysqli->prepare("SELECT * FROM edicoes ORDER BY ano DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC) ?? [];
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
    <?php $page_title = 'Edições - Além do Espelho'; $page_description = 'Conheça todas as edições do evento: 1ª Edição - O Confronto. 6 pilares de transformação: Confronto, Máscaras, Encontro, Deus, Cura e Identidade.'; $page_url = 'https://alemdoespelho.com.br/edicoes.php'; include __DIR__ . '/meta-tags.php'; ?>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
</head>
<body>
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
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
            <a href="admin.php">Admin</a>
        </div>
    </header>

    <main>
        <!-- HERO EDIÇÕES -->
        <section class="section" style="min-height: 500px; display: flex; align-items: center; background: linear-gradient(135deg, rgba(45, 27, 105, 0.3), rgba(67, 56, 202, 0.2)); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 80% 20%, rgba(124, 58, 237, 0.1), transparent 50%); z-index: 1;"></div>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div style="animation: fadeInUp 0.8s ease; text-align: center;">
                    <h1 style="
                        font-size: 3.5rem;
                        margin-bottom: 1.5rem;
                        background: linear-gradient(135deg, #4338CA, #7c3aed, #06b6d4);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        font-weight: 800;
                    ">
                        ✨ Todas as Edições
                    </h1>
                    <p style="
                        font-size: 1.2rem;
                        color: var(--muted);
                        max-width: 700px;
                        margin: 0 auto;
                        line-height: 1.8;
                    ">
                        Cada edição é um novo convite para transformação profunda. Conheça as edições e escolha a que mais ressoa com você.
                    </p>
                </div>
            </div>
        </section>

        <!-- EDIÇÃO ATUAL -->
        <?php if ($currentEdition): ?>
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>1ª Edição — <?php echo htmlspecialchars($currentEdition['titulo'] ?? 'O Confronto'); ?></h2>
                    <p><?php echo htmlspecialchars($currentEdition['descricao']); ?></p>
                </div>

                <!-- HERO DA EDIÇÃO ATUAL -->
                <div class="glass-strong" style="
                    text-align: center;
                    padding: 4rem 3rem;
                    border-radius: 20px;
                    margin-bottom: 3rem;
                    animation: fadeInScaleUp 0.8s ease;
                    background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1));
                    border: 1px solid rgba(67, 56, 202, 0.3);
                ">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem; animation: float 3s ease-in-out infinite;">🪞</div>
                    <h3 style="
                        background: linear-gradient(135deg, #4338CA, #7c3aed, #06b6d4);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        font-size: 2.5rem;
                        font-weight: 700;
                        margin-bottom: 1rem;
                    ">
                        <?php echo htmlspecialchars($currentEdition['titulo']); ?>
                    </h3>
                    <p style="font-size: 1.3rem; color: var(--accent-secondary); margin-bottom: 2rem; font-style: italic; font-weight: 600;">
                        "Um encontro que pode mudar toda a sua história"
                    </p>
                    
                    <?php if ($currentEdition['data_inicio'] || $currentEdition['data_fim']): ?>
                    <div style="background: linear-gradient(135deg, rgba(217, 70, 239, 0.2), rgba(168, 85, 247, 0.1)); border: 2px solid rgba(217, 70, 239, 0.6); padding: 2.5rem; border-radius: 16px; margin-bottom: 2rem; box-shadow: 0 0 30px rgba(217, 70, 239, 0.3); backdrop-filter: blur(16px);">
                        <p style="color: #d946ef; font-size: 0.8rem; margin: 0 0 1.2rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; text-shadow: 0 0 10px rgba(217, 70, 239, 0.4);">📅 Data e Local do Evento</p>
                        <p style="color: #ec4899; font-size: 1.5rem; margin: 0 0 1rem; font-weight: 800; line-height: 1.3;">
                            <?php 
                            $data_inicio = formatDatePT($currentEdition['data_inicio']);
                            $data_fim = formatDatePT($currentEdition['data_fim']);
                            echo $data_inicio . ' <br/> até <br/> ' . $data_fim;
                            ?>
                        </p>
                        <?php if ($currentEdition['local']): ?>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(217, 70, 239, 0.3);">
                            <p style="color: #f472b6; font-size: 1.15rem; margin: 0; font-weight: 600;">📍 <?php echo htmlspecialchars($currentEdition['local']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0s backwards;"><span style="font-size: 2.5rem;">❤️</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Confronto com Verdade</p></div>
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0.1s backwards;"><span style="font-size: 2.5rem;">🎭</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Quebra de Máscaras</p></div>
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0.2s backwards;"><span style="font-size: 2.5rem;">🪞</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Encontro Consigo</p></div>
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0.3s backwards;"><span style="font-size: 2.5rem;">✝️</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Encontro com Deus</p></div>
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0.4s backwards;"><span style="font-size: 2.5rem;">💞</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Cura</p></div>
                        <div style="text-align: center; animation: fadeInUp 0.6s ease 0.5s backwards;"><span style="font-size: 2.5rem;">👑</span><p style="margin: 0.5rem 0 0 0; color: var(--muted);">Identidade</p></div>
                    </div>
                    <p style="margin: 1.5rem 0; font-size: 1.2rem; color: var(--accent); font-weight: 600;">
                        ⭐ 30 Vagas Limitadas | 💳 R$ 150,00 | 
                        <?php 
                        if ($currentEdition['data_inscricao_inicio'] || $currentEdition['data_inscricao_fim']) {
                            $data_insc_inicio = formatDatePT($currentEdition['data_inscricao_inicio']);
                            $data_insc_fim = formatDatePT($currentEdition['data_inscricao_fim']);
                            echo '🎯 Inscrições: ' . $data_insc_inicio . ' até ' . $data_insc_fim;
                        } else {
                            echo '🎯 Inscrições Abertas';
                        }
                        ?>
                    </p>
                    <button onclick="openInscriptionModal()" class="btn btn-primary" style="margin-top: 2rem; padding: 1.2rem 3rem; font-size: 1.1rem; animation: bounce-smooth 2s ease-in-out infinite; border: none; cursor: pointer;">
                        🚀 Inscrever-se Agora
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- EDIÇÕES ANTERIORES -->
        <?php if (count($editions) > 1): ?>
        <section class="section" style="background: linear-gradient(135deg, rgba(45, 27, 105, 0.15), rgba(67, 56, 202, 0.08));">
            <div class="container">
                <div class="section-heading">
                    <h2>📖 Edições Anteriores</h2>
                    <p>Reviva os momentos marcantes que já transformaram vidas</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <?php foreach (array_slice($editions, 1) as $index => $edition): ?>
                    <div class="glass-strong" style="
                        padding: 2.5rem;
                        border-radius: 16px;
                        animation: fadeInUp 0.6s ease <?php echo ($index * 0.1) ?>s backwards;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--glow)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                        <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">🎆</div>
                        <h3 style="
                            background: linear-gradient(135deg, #4338CA, #7c3aed);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;
                            font-weight: 700;
                            margin: 0 0 1rem;
                            font-size: 1.5rem;
                        ">
                            <?php echo htmlspecialchars($edition['titulo']); ?>
                        </h3>
                        <p style="color: var(--accent); font-size: 0.95rem; margin: 1rem 0; font-weight: 600;">
                            📅 Edição <?php echo htmlspecialchars($edition['ano']); ?>
                        </p>
                        <p style="margin: 1rem 0; color: var(--muted); line-height: 1.7;">
                            <?php echo htmlspecialchars($edition['descricao']); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- CHAMADA PARA AÇÃO -->
        <section class="section" style="text-align: center;">
            <div class="container">
                <h2 style="margin-bottom: 1.5rem; font-size: 2.5rem; background: linear-gradient(135deg, #4338CA, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    Junte-se à Nossa Comunidade
                </h2>
                <p style="color: var(--muted); margin-bottom: 2rem; font-size: 1.1rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Cada edição é uma oportunidade de transformação. Escolha sua edição e comece sua jornada hoje.
                </p>
                <a href="inscricao.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1.2rem 2.5rem;">
                    🚀 Começar Inscrição
                </a>
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
            background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1));
            border: 1px solid rgba(67, 56, 202, 0.3);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: slideInUp 0.4s ease;
            box-shadow: 0 20px 60px rgba(67, 56, 202, 0.3);
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
                background: rgba(67, 56, 202, 0.1);
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
                    background: linear-gradient(135deg, #06b6d4, #0891b2);
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
                    background: rgba(67, 56, 202, 0.2);
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
