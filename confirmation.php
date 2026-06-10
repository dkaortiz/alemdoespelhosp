<?php
require_once 'config.php';
session_start();

$pending_admin = isset($_GET['pending_admin']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação - Além do Espelho</title>
    <?php $page_title = 'Confirmação - Além do Espelho'; $page_description = 'Sua inscrição foi confirmada com sucesso! Prepare-se para uma jornada transformadora.'; $page_url = 'https://alemdoespelho.com.br/confirmation.php'; include __DIR__ . '/meta-tags.php'; ?>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 2rem auto;
            animation: fadeInUp 0.6s ease;
            text-align: center;
        }

        .success-icon {
            font-size: 5rem;
            animation: bounce-smooth 2s ease-in-out infinite;
            display: block;
            margin-bottom: 1rem;
        }

        .checkmark {
            font-size: 4rem;
            color: var(--primary);
            animation: slideInDown 0.6s ease;
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.4));
        }

        .success-box {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 208, 63, 0.05));
            border: 2px solid var(--primary);
            border-radius: 16px;
            padding: 2.5rem;
            margin: 2rem 0;
            animation: fadeInScaleUp 0.8s ease;
        }

        .success-box h2 {
            color: var(--primary);
            margin-top: 0;
            font-size: 2rem;
            background: linear-gradient(135deg, #D4AF37, #F4D03F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-section {
            background: rgba(212, 175, 55, 0.08);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
            animation: slideInLeft 0.6s ease 0.2s backwards;
        }

        .info-section h3 {
            color: var(--accent);
            margin-top: 0;
            font-weight: 700;
        }

        .next-steps {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.08), rgba(244, 208, 63, 0.04));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            animation: fadeInScaleUp 0.8s ease 0.3s backwards;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #0a0805;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .step-content {
            text-align: left;
        }

        .step-content p {
            margin: 0;
            color: var(--muted);
        }

        .step-content strong {
            color: var(--text);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            animation: slideInUp 0.6s ease 0.5s backwards;
        }

        .btn-primary-lg {
            flex: 1;
            padding: 1.2rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary-lg:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 157, 0.3);
        }

        .btn-secondary-lg {
            flex: 1;
            padding: 1.2rem;
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-secondary-lg:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .animation-float {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
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
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="confirmation-container">
                <div class="checkmark">✓</div>
                
                <?php if ($pending_admin): ?>
                    <!-- ANFITRIÃO NOVO (AGUARDANDO ADMIN) -->
                    <div class="success-box">
                        <h2>Inscrição Recebida!</h2>
                        <p style="color: var(--muted); font-size: 1.05rem; margin: 0;">
                            Sua inscrição como <strong>Anfitrião</strong> foi registrada com sucesso
                        </p>
                    </div>

                    <div class="next-steps">
                        <h3 style="margin-top: 0; color: var(--primary);">⏳ Próximos Passos</h3>
                        
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <p><strong>Revisão Administrativa</strong></p>
                                <p>Você foi selecionado como Anfitrião novo. A equipe de administração fará uma revisão de sua inscrição.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <p><strong>Aprovação ou Contato</strong></p>
                                <p>Você receberá um email ou WhatsApp com atualizações sobre sua inscrição.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <p><strong>Pagamento</strong></p>
                                <p>Após aprovação, você receberá as instruções de pagamento (R$ 150,00)</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0;">
                        <p style="color: #f59e0b; margin: 0; font-weight: 600;">📧 Fique atento ao seu email e WhatsApp para atualizações!</p>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn-primary-lg">Voltar ao Home</a>
                    </div>

                <?php else: ?>
                    <!-- PEREGRINO OU ANFITRIÃO EX-PEREGRINO (AGUARDANDO COMPROVANTE) -->
                    <div class="success-box">
                        <h2>Comprovante Enviado!</h2>
                        <p style="color: var(--muted); font-size: 1.05rem; margin: 0;">
                            Seu comprovante de pagamento foi recebido com sucesso
                        </p>
                    </div>

                    <div class="next-steps">
                        <h3 style="margin-top: 0; color: var(--primary);">⏳ Próximos Passos</h3>
                        
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <p><strong>Verificação do Comprovante</strong></p>
                                <p>A equipe de administração analisará seu comprovante de pagamento em breve.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <p><strong>Confirmação de Inscrição</strong></p>
                                <p>Você receberá um email ou WhatsApp confirmando sua inscrição.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <p><strong>Informações do Evento</strong></p>
                                <p>Você receberá detalhes sobre horários, local e instruções para o evento.</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0;">
                        <p style="color: #10b981; margin: 0; font-weight: 600;">✓ Você receberá confirmação por email e WhatsApp em breve</p>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn-primary-lg">Voltar ao Home</a>
                        <a href="regras.php" class="btn-secondary-lg">Ver Regras</a>
                    </div>
                <?php endif; ?>

                <!-- CONTATO -->
                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                    <p style="color: var(--muted); margin: 0;">Dúvidas? Fale conosco pelo WhatsApp</p>
                    <p style="font-size: 1.2rem; color: var(--primary); font-weight: 600; margin: 0.5rem 0 0;">11993813374</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        </div>
    </footer>

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
    </script>
    <script src="<?php echo assetVersion('script.js'); ?>"></script>
</body>
</html>
