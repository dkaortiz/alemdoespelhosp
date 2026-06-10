<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regras e Políticas - Além do Espelho</title>
    <link rel="stylesheet" href="style.css">
    <?php include __DIR__ . '/google_analytics.php'; ?>
    <style>
        .accordion-item {
            border: 1px solid rgba(67, 56, 202, 0.2);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            background: rgba(67, 56, 202, 0.05);
            transition: all 0.3s ease;
        }
        .accordion-item:hover {
            border-color: rgba(67, 56, 202, 0.4);
            background: rgba(67, 56, 202, 0.08);
        }
        .accordion-header {
            background: transparent;
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .accordion-header:hover {
            padding-left: 2rem;
        }
        .accordion-header.active {
            background: rgba(67, 56, 202, 0.1);
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 1.5rem;
        }
        .accordion-content.active {
            max-height: 1500px;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        .accordion-icon {
            font-size: 1.3rem;
            color: var(--primary);
            transition: transform 0.3s ease;
        }
        .accordion-icon.active {
            transform: rotate(180deg);
        }
    </style>
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
        <!-- HERO -->
        <section class="section" style="text-align: center; background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1)); padding: 4rem 0;">
            <div class="container" style="animation: fadeInScaleUp 0.8s ease;">
                <h1 style="
                    background: linear-gradient(135deg, #4338CA, #7c3aed);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    font-size: 3.5rem;
                    font-weight: 700;
                    margin-bottom: 1rem;
                ">
                    📋 Regras e Políticas
                </h1>
                <p style="color: var(--muted); font-size: 1.1rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                    Informações importantes sobre pagamento, convivência e expectativas do evento.
                </p>
            </div>
        </section>

        <!-- PAGAMENTO -->
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>💰 Pagamento</h2>
                    <p>Formas de pagamento e informações sobre inscrição</p>
                </div>

                <div class="glass-strong" style="
                    padding: 3rem;
                    margin-bottom: 2rem;
                    border-radius: 16px;
                    animation: fadeInUp 0.6s ease;
                    border: 1px solid rgba(67, 56, 202, 0.3);
                ">
                    <h3 style="
                        margin-top: 0;
                        color: var(--primary);
                        font-size: 1.5rem;
                        margin-bottom: 1.5rem;
                    ">Valores da Inscrição</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 1.5rem 0;">
                        <div style="background: rgba(67, 56, 202, 0.08); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                            <h4 style="color: var(--primary); margin-top: 0;">👤 Peregrino</h4>
                            <p style="font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #4338CA, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 1rem 0;">R$ 150,00</p>
                            <p style="color: var(--muted); font-size: 0.95rem; margin: 0;">Participação completa no evento com limite de vagas</p>
                        </div>
                        <div style="background: rgba(6, 182, 212, 0.08); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--accent-secondary);">
                            <h4 style="color: var(--accent-secondary); margin-top: 0;">👥 Anfitrião</h4>
                            <p style="font-size: 1.8rem; font-weight: 700; color: var(--accent-secondary); margin: 1rem 0;">R$ 100,00</p>
                            <p style="color: var(--muted); font-size: 0.95rem; margin: 0;">Apoio na organização do evento</p>
                        </div>
                    </div>

                    <h3 style="color: var(--primary); margin: 2rem 0 1rem 0; font-size: 1.3rem;">Formas de Pagamento</h3>
                    <div style="background: rgba(67, 56, 202, 0.1); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                        <p style="color: var(--muted); margin: 0;">
                            💳 <strong style="color: var(--text);">Cartão de Crédito</strong> — processado na tela de confirmação<br>
                            ℹ️ <em style="color: var(--muted);">Sistema de pagamento implementando. Acesse em breve.</em>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONVIVÊNCIA -->
        <section class="section" style="background: linear-gradient(135deg, rgba(45, 27, 105, 0.2), rgba(67, 56, 202, 0.15));">
            <div class="container">
                <div class="section-heading">
                    <h2>🤝 Convivência e Respeito</h2>
                    <p>Princípios fundamentais para uma experiência transformadora</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease;">
                        <h3 style="color: var(--primary); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">🙏</span> Respeito Mútuo
                        </h3>
                        <ul style="color: var(--muted); margin: 1.5rem 0 0 0; list-style: none; padding: 0;">
                            <li>✓ Respeitar cada participante</li>
                            <li>✓ Ouvir com empatia</li>
                            <li>✓ Não julgar histórias alheias</li>
                            <li>✓ Valorizar a diversidade</li>
                        </ul>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease 0.1s backwards;">
                        <h3 style="color: var(--accent); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">⛔</span> Proibições
                        </h3>
                        <ul style="color: var(--muted); margin: 1.5rem 0 0 0; list-style: none; padding: 0;">
                            <li>✗ Drogas e álcool</li>
                            <li>✗ Violência ou agressão</li>
                            <li>✗ Discriminação</li>
                            <li>✗ Comportamento ofensivo</li>
                        </ul>
                    </div>

                    <div class="glass-strong" style="padding: 2.5rem; animation: fadeInUp 0.6s ease 0.2s backwards;">
                        <h3 style="color: var(--accent-secondary); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">🔒</span> Sigilo e Privacidade
                        </h3>
                        <ul style="color: var(--muted); margin: 1.5rem 0 0 0; list-style: none; padding: 0;">
                            <li>✓ Respeitar histórias compartilhadas</li>
                            <li>✓ Confidencialidade absoluta</li>
                            <li>✓ Não divulgar informações</li>
                            <li>✓ Segurança emocional</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>❓ Dúvidas Frequentes</h2>
                    <p>Encontre respostas para suas principais dúvidas</p>
                </div>

                <div style="max-width: 900px; margin: 0 auto;">
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Qual é a diferença entre Peregrino e Anfitrião?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0 0 1rem 0;">
                                <strong style="color: var(--primary);">Peregrino:</strong> Você participa da experiência completa do evento com limite de 15 vagas por gênero. 
                                Ideal para quem quer viver a jornada de transformação pessoal.
                            </p>
                            <p style="color: var(--muted); margin: 0;">
                                <strong style="color: var(--primary);">Anfitrião:</strong> Você ajuda na organização e estrutura do evento (som, palco, logística, etc). 
                                Sem limite de vagas. Geralmente é aberto para ex-peregrinos.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Como funciona o sistema de pagamento?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0;">
                                🔧 <strong style="color: var(--text);">Implementando sistema de pagamento</strong>. Em breve, você poderá realizar sua inscrição e pagamento de forma segura e rápida através do nosso sistema online. Acompanhe as atualizações!
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Qual é a duração do evento?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0; line-height: 1.8;">
                                A Edição O Confronto (2026) tem duração de <strong style="color: var(--text);">2 dias completos</strong>. 
                                ⚠️ <em style="color: var(--muted);">Possibilidade de ajustes nas datas, mas será avisado com antecedência aos participantes</em>. 
                                Também oferecemos a opção "Dia" para quem deseja participar apenas de um dia específico.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Como entro em contato com a organização?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0;">
                                Entre em contato via WhatsApp: <strong>11993813374</strong>. 
                                Também estamos disponíveis para esclarecer dúvidas antes de sua inscrição.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Há restrição de idade?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0;">
                                O evento é para todas as idades. Menores de idade devem ter autorização dos responsáveis. 
                                A experiência é adaptada para ser inclusiva e significativa para qualquer faixa etária.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 6 - NOVO -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.1rem;">Qual é a política de cancelamento?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted); margin: 0; line-height: 1.8;">
                                <strong style="color: var(--text);">Direito de Cancelamento (Lei nº 8.078/1990 - Código de Defesa do Consumidor):</strong><br>
                                Conforme previsto em lei, você pode cancelar sua inscrição em até <strong style="color: var(--accent);">7 (sete) dias corridos</strong> após o pagamento, sem necessidade de justificativa, recebendo reembolso integral do valor pago.
                            </p>
                            <p style="color: var(--muted); margin: 1rem 0 0 0; line-height: 1.8;">
                                <strong style="color: var(--text);">Após este prazo,</strong> o cancelamento não é permitido, exceto em circunstâncias extraordinárias analisadas caso a caso pelo organizador. Para cancelamentos ou dúvidas, entre em contato via WhatsApp.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CHAMADA FINAL -->
        <section class="section" style="text-align: center;">
            <div class="container">
                <h2 style="
                    margin-bottom: 1.5rem;
                    font-size: 2.5rem;
                    background: linear-gradient(135deg, #4338CA, #7c3aed);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                ">
                    Pronto para a Transformação?
                </h2>
                <p style="color: var(--muted); margin-bottom: 2rem; font-size: 1.1rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Para dúvidas sobre pagamento, cancelamento ou participação, entre em contato conosco via WhatsApp: <strong>11993813374</strong>
                </p>
                <a href="inscricao.php" class="btn btn-primary" style="font-size: 1rem; padding: 1rem 2rem;">
                    🚀 Começar Inscrição
                </a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados. | Transformação • Autenticidade • Propósito</p>
        </div>
    </footer>

    <script>
        function toggleAccordion(header) {
            const icon = header.querySelector('.accordion-icon');
            const content = header.nextElementSibling;
            
            header.classList.toggle('active');
            icon.classList.toggle('active');
            content.classList.toggle('active');
        }
    </script>
    <script src="script.js"></script>
</body>
</html>
