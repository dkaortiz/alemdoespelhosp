<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regras - Além do Espelho</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .accordion-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .accordion-header {
            background: var(--surface);
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .accordion-header:hover {
            background: rgba(255, 107, 157, 0.1);
        }
        .accordion-header.active {
            background: rgba(255, 107, 157, 0.2);
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .accordion-content.active {
            max-height: 1000px;
            padding: 0 1.5rem 1.5rem;
        }
        .accordion-icon {
            font-size: 1.5rem;
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
        <section class="section" style="text-align: center; background: linear-gradient(135deg, rgba(212, 175, 55, 0.08), rgba(244, 208, 63, 0.04)); padding: 3rem 0;">
            <div class="container" style="animation: fadeInScaleUp 0.8s ease;">
                <h1 style="background: linear-gradient(135deg, #D4AF37, #F4D03F); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem; font-weight: 700; margin-bottom: 1rem;">
                    📋 Regras e Políticas
                </h1>
                <p style="color: var(--muted); font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
                    Informações importantes sobre pagamento, convivência e expectativas do evento.
                </p>
            </div>
        </section>

        <!-- PAGAMENTO -->
        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <h2>💰 Pagamento</h2>
                </div>

                <div class="glass-strong" style="padding: 2.5rem; margin-bottom: 2rem; border-radius: 12px; animation: fadeInUp 0.6s ease;">
                    <h3 style="margin-top: 0; color: var(--primary);">Valor da Inscrição</h3>
                    <p style="font-size: 1.3rem; color: var(--accent); font-weight: 700;"><strong>R$ 150,00</strong> por participante (Peregrino ou Anfitrião).</p>

                    <h3 style="color: var(--primary); margin-top: 2rem;">Formas de Pagamento</h3>
                    <ul style="color: var(--muted); line-height: 2;">
                        <li>🏦 <strong>PIX</strong> via telefone: 11993813374</li>
                        <li>💳 <strong>Cartão de Crédito</strong> (processado na tela de confirmação)</li>
                    </ul>

                    <h3 style="color: var(--primary); margin-top: 2rem;">Sistema de Identificação PIX (Centavos)</h3>
                    <p style="color: var(--muted); background: rgba(212, 175, 55, 0.05); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--primary);">
                        Para identificar seu pagamento via PIX, cada inscrição recebe um valor único com centavos adicionados (ex: R$ 150,24). 
                        Isso facilita a conciliação automática do seu pagamento.
                    </p>

                    <h3 style="color: var(--primary); margin-top: 2rem;">Comprovante de Pagamento</h3>
                    <p style="color: var(--muted);">
                        Após enviar o PIX ou cartão, você receberá um link para enviar o comprovante. 
                        Isso é essencial para confirmar sua inscrição.
                    </p>
                </div>
            </div>
        </section>

        <!-- CONVIVÊNCIA -->
        <section class="section" style="background: linear-gradient(135deg, rgba(25, 15, 45, 0.5), rgba(45, 15, 65, 0.5));">
            <div class="container">
                <h2 style="color: var(--accent); margin-bottom: 2rem;">🤝 Convivência e Respeito</h2>

                <div class="cards-grid" style="grid-template-columns: 1fr;">
                    <div class="glass" style="padding: 2rem;">
                        <h3 style="color: var(--primary); margin-top: 0;">Código de Conduta</h3>
                        <ul style="color: var(--muted);">
                            <li>Respeito mútuo entre todos os participantes</li>
                            <li>Proibido: drogas, álcool, violência e comportamentos agressivos</li>
                            <li>Ambiente inclusivo: nenhuma discriminação é tolerada</li>
                            <li>Sigilo: respeite histórias e privacidade alheias</li>
                            <li>Ponctualidade: chegar no horário agendado</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="section">
            <div class="container">
                <h2 style="color: var(--primary); margin-bottom: 2rem; text-align: center;">❓ Dúvidas Frequentes (FAQ)</h2>

                <div style="max-width: 800px; margin: 0 auto;">
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">Qual é a diferença entre Peregrino e Anfitrião?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                <strong>Peregrino:</strong> Você participa da experiência completa do evento com limite de 15 vagas por gênero. 
                                Ideal para quem quer viver a jornada de transformação pessoal.
                            </p>
                            <p style="color: var(--muted);">
                                <strong>Anfitrião:</strong> Você ajuda na organização e estrutura do evento (som, palco, logística, etc). 
                                Sem limite de vagas. Geralmente é aberto para ex-peregrinos.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">Como funciona o pagamento via PIX?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                Cada inscrição recebe um valor único (ex: R$ 150,24). Você envia esse valor para a chave PIX 11993813374. 
                                Os centavos adicionados (24) identificam sua inscrição automaticamente no sistema.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">E se eu não consigo pagar agora?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                Você pode aguardar, mas sua vaga permanece em "pendente" até que o pagamento seja confirmado. 
                                As vagas de Peregrino são limitadas (15 de cada gênero), então quanto mais rápido você pagar, mais segura sua inscrição.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">Posso mudar de Peregrino para Anfitrião?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                Sim, entre em contato via WhatsApp antes de confirmar o pagamento e informaremos como fazer a alteração. 
                                Após o pagamento ser confirmado, mudanças podem ter restrições.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">Como recebo a confirmação de inscrição?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                Você receberá confirmação por email e/ou WhatsApp (se fornecido). 
                                A confirmação final só ocorre após o pagamento ser validado pelo admin.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h3 style="margin: 0; color: var(--text);">O que fazer em caso de problemas com pagamento?</h3>
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <p style="color: var(--muted);">
                                Envie uma mensagem no WhatsApp (11993813374) com comprovante do pagamento ou error de cartão. 
                                A equipe resolverá em breve!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- POLÍTICA DE CANCELAMENTO -->
        <section class="section" style="background: linear-gradient(135deg, rgba(25, 15, 45, 0.5), rgba(45, 15, 65, 0.5));">
            <div class="container">
                <h2 style="color: var(--accent); margin-bottom: 2rem;">📋 Cancelamento e Reembolso</h2>

                <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                    <p style="color: var(--muted); margin-bottom: 1rem;">
                        Cancelamentos devem ser feitos com pelo menos 7 dias de antecedência por email ou WhatsApp.
                    </p>
                    <ul style="color: var(--muted);">
                        <li><strong>Até 7 dias antes do evento:</strong> Reembolso integral</li>
                        <li><strong>Menos de 7 dias:</strong> Sem reembolso (vaga não transferível)</li>
                        <li><strong>Após o evento:</strong> Sem reembolso</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CONTATO -->
        <section class="section">
            <div class="container" style="text-align: center;">
                <h2 style="margin-bottom: 1rem;">Dúvidas? Fale Conosco</h2>
                <p style="color: var(--muted); margin-bottom: 2rem;">
                    WhatsApp: <strong>11993813374</strong> | Email será disponibilizado em breve
                </p>
                <a href="inscricao.php" class="btn btn-primary">Voltar para Inscrição</a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        function toggleAccordion(header) {
            header.classList.toggle('active');
            header.querySelector('.accordion-icon').classList.toggle('active');
            header.nextElementSibling.classList.toggle('active');
        }
    </script>
</body>
</html>
            <p>PIX ou cartão de crédito.</p>
          </div>
          <div class="info-card">
            <strong>Confirmação</strong>
            <p>Email e/ou WhatsApp após inscrição.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container section-heading">
        <span class="eyebrow">Regras gerais</span>
        <h2>Respeito e segurança para todos</h2>
        <p>As regras foram pensadas para proteger cada participante e manter uma experiência harmoniosa. Leia com atenção antes de se inscrever.</p>
      </div>
      <div class="container cards-grid">
        <article class="card">
          <h3>Respeito mútuo</h3>
          <p>Atitudes de respeito e gentileza são obrigatórias. Não toleramos discriminação, violência ou desrespeito contra qualquer pessoa.</p>
        </article>
        <article class="card">
          <h3>Ambiente limpo</h3>
          <p>Leve seu lixo e cuide do espaço. A área deve permanecer limpa e organizada durante todo o evento.</p>
        </article>
        <article class="card">
          <h3>Segurança</h3>
          <p>Siga as orientações da organização e respeite os locais de circulação, alimentação e descanso.</p>
        </article>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container two-columns">
        <div>
          <h3>Regras para Peregrino</h3>
          <ul class="feature-list">
            <li>Somente 15 homens e 15 mulheres podem se inscrever como Peregrino.</li>
            <li>Traga itens pessoais, roupa de cama e materiais de higiene.</li>
            <li>Siga as regras de horário e conservação do acampamento.</li>
          </ul>
        </div>
        <div>
          <h3>Regras para Anfitrião</h3>
          <p>Anfitrião não têm limite de vagas, mas devem ser aprovados pela organização.</p>
          <p>No futuro, somente quem já foi acampante ou recebeu liberação do administrador poderá participar como equipante.</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container section-heading">
        <span class="eyebrow">Prioridade e comportamento</span>
        <h2>O que esperamos de você</h2>
        <p>Cada participante é responsável por sua atitude. Os visitantes devem agir com cordialidade, colaboração e cuidado com o coletivo.</p>
      </div>
      <div class="container cards-grid">
        <article class="card">
          <h3>Pontualidade</h3>
          <p>Chegue nos horários definidos para atividades, reuniões e horários de entrada no local.</p>
        </article>
        <article class="card">
          <h3>Responsabilidade</h3>
          <p>Cumpra os compromissos combinados com a organização e as regras de espaço público.</p>
        </article>
        <article class="card">
          <h3>Comunicação</h3>
          <p>Mantenha o WhatsApp e email atualizados para receber avisos de última hora.</p>
        </article>
      </div>
    </section>

    <section class="section section-contact" id="contato">
      <div class="container section-heading">
        <span class="eyebrow">Dúvidas e contato</span>
        <h2>Fale com a organização</h2>
        <p>Se tiver qualquer dúvida sobre regras, pagamento ou inscrição, entre em contato antes de finalizar sua inscrição.</p>
      </div>
      <div class="container contact-grid">
        <div class="contact-card">
          <h3>Email</h3>
          <p>contato@alemdoespelho.com.br</p>
        </div>
        <div class="contact-card">
          <h3>WhatsApp</h3>
          <p>Enviaremos informações pelo número informado na inscrição.</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>© 2026 Além do Espelho. Leia todas as regras antes de se inscrever.</p>
    </div>
  </footer>
</body>
</html>
