<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['registration_id']) || !isset($_SESSION['registration_type'])) {
    header('Location: inscricao.php');
    exit;
}

$reg_id = $_SESSION['registration_id'];
$reg_type = $_SESSION['registration_type'];
$pix_amount = $_SESSION['pix_amount'] ?? 150.00;

// Buscar informações do registro
if ($reg_type === 'peregrino') {
    $stmt = $mysqli->prepare("SELECT nome, email, pix_cents FROM peregrinos WHERE id = ?");
} else {
    $stmt = $mysqli->prepare("SELECT nome, email, pix_cents FROM anfitrioes WHERE id = ?");
}

$stmt->bind_param('i', $reg_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: inscricao.php');
    exit;
}

$nome = htmlspecialchars($row['nome']);
$email = htmlspecialchars($row['email']);
$pix_cents = $row['pix_cents'];

// Gerar QR Code PIX via Google Charts
$pix_phone = urlencode(PIX_PHONE);
$pix_valor = number_format($pix_amount, 2, '.', '');
$qr_text = "00020126360014br.gov.bcb.pix0136{$pix_phone}520400005303986540510.{$pix_cents}5802BR5913ALEM%20DO%20ESPELHO6009SAO%20PAULO63041D3D";
$qr_url = "https://chart.googleapis.com/chart?chs=300x300&chld=M|0&cht=qr&chl=" . urlencode($qr_text);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Além do Espelho</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 700px;
            margin: 2rem auto;
            animation: fadeInUp 0.6s ease;
        }

        .pix-box {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(244, 208, 63, 0.15));
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
            animation: slideInUp 0.6s ease 0.2s backwards;
        }

        .pix-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1rem 0;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .qr-code {
            margin: 2rem 0;
            display: inline-block;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.6s ease 0.4s backwards;
        }

        .qr-code img {
            display: block;
            border-radius: 8px;
        }

        .info-box {
            background: rgba(0, 212, 255, 0.1);
            border-left: 4px solid var(--accent);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            animation: slideInLeft 0.6s ease 0.3s backwards;
        }

        .info-box p {
            margin: 0.5rem 0;
            color: var(--muted);
        }

        .info-box strong {
            color: var(--text);
        }

        .tab-payment {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
            animation: slideInUp 0.6s ease 0.5s backwards;
        }

        .tab-btn-payment {
            flex: 1;
            padding: 1rem;
            border: 2px solid var(--border);
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1rem;
        }

        .tab-btn-payment:hover {
            border-color: var(--primary);
            color: var(--text);
        }

        .tab-btn-payment.active {
            background: rgba(255, 107, 157, 0.2);
            border-color: var(--primary);
            color: var(--primary);
        }

        .tab-content-payment {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .tab-content-payment.active {
            display: block;
        }

        .btn-next {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .btn-next:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 157, 0.3);
        }

        .clock-icon {
            font-size: 3rem;
            animation: float 3s ease-in-out infinite;
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
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="section">
            <div class="payment-container">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div class="clock-icon">💳</div>
                    <h1 style="color: var(--primary); margin: 1rem 0;">Finalize seu Pagamento</h1>
                    <p style="color: var(--muted); font-size: 1.1rem;">Olá, <strong><?= $nome ?></strong>!</p>
                </div>

                <!-- VALOR -->
                <div class="glass-strong pix-box">
                    <p style="color: var(--muted); margin: 0 0 0.5rem;">Valor a pagar</p>
                    <div class="pix-value">R$ <?= number_format($pix_amount, 2, ',', '.') ?></div>
                    <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">Os centavos (<?= str_pad($pix_cents, 2, '0', STR_PAD_LEFT) ?>) identificam sua inscrição</p>
                </div>

                <!-- TABS PIX vs CARTÃO -->
                <div class="tab-payment">
                    <button class="tab-btn-payment active" onclick="switchPaymentTab('pix', this)">🏦 PIX</button>
                    <button class="tab-btn-payment" onclick="switchPaymentTab('cartao', this)">💳 Cartão</button>
                </div>

                <!-- CONTEÚDO PIX -->
                <div id="pix" class="tab-content-payment active">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                        <h3 style="margin-top: 0; color: var(--primary);">Instruções PIX</h3>
                        
                        <div class="info-box">
                            <p><strong>1. Abra seu app bancário</strong></p>
                            <p>Procure pela opção PIX ou QR Code</p>
                        </div>

                        <div class="info-box">
                            <p><strong>2. Opção A: Copie a chave PIX</strong></p>
                            <p style="background: rgba(0, 0, 0, 0.2); padding: 1rem; border-radius: 6px; font-family: monospace; color: var(--primary); margin-top: 0.5rem; word-break: break-all;">
                                <?= PIX_PHONE ?>
                            </p>
                        </div>

                        <div class="info-box">
                            <p><strong>2. Opção B: Escaneie o QR Code</strong></p>
                        </div>

                        <div style="text-align: center;">
                            <div class="qr-code">
                                <img src="<?= $qr_url ?>" alt="QR Code PIX" style="width: 250px; height: 250px;">
                            </div>
                        </div>

                        <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem;">
                            <p style="color: #10b981; margin: 0; font-weight: 600;">✓ Seu pagamento será identificado automaticamente pelos centavos</p>
                        </div>

                        <a href="payment_confirm.php" class="btn btn-next">Enviar Comprovante ✓</a>
                    </div>
                </div>

                <!-- CONTEÚDO CARTÃO -->
                <div id="cartao" class="tab-content-payment">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 12px;">
                        <h3 style="margin-top: 0; color: var(--primary);">Pagamento com Cartão</h3>
                        
                        <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; padding: 1.5rem; border-radius: 8px; margin: 1rem 0;">
                            <p style="color: #f59e0b; margin: 0; font-weight: 600;">Recurso em desenvolvimento</p>
                            <p style="color: var(--muted); margin: 0.5rem 0 0;">Por enquanto, use PIX para finalizar seu pagamento.</p>
                        </div>

                        <div class="info-box">
                            <p><strong>📞 Suporte</strong></p>
                            <p>WhatsApp: <strong>11993813374</strong></p>
                            <p>Fale conosco para usar cartão de crédito</p>
                        </div>

                        <a href="payment_confirm.php" class="btn btn-next">Voltar para Comprovante ✓</a>
                    </div>
                </div>

                <!-- RODAPÉ -->
                <div style="text-align: center; margin-top: 3rem; color: var(--muted);">
                    <p>Após enviar o PIX, você precisará fazer upload do comprovante</p>
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
    <script>
        function switchPaymentTab(tabName, btn) {
            // Hide all tabs
            document.querySelectorAll('.tab-content-payment').forEach(tab => {
                tab.classList.remove('active');
            });
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn-payment').forEach(b => {
                b.classList.remove('active');
            });
            
            // Show selected tab and mark button
            document.getElementById(tabName).classList.add('active');
            btn.classList.add('active');
        }
    </script>
</body>
</html>
</head>
<body>
  <?php if ($message !== ''): ?>
    <div class="page-alert <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <header class="site-header">
    <div class="container header-inner">
      <a href="index.php" class="brand">Além do Espelho</a>
      <nav class="site-nav">
        <a href="index.php#home">Home</a>
        <a href="index.php#inscricao">Inscrição</a>
        <a href="regras.php">Regras</a>
        <a href="admin.php">Admin</a>
      </nav>
      <a href="index.php#inscricao" class="btn btn-primary">Inscrever-se</a>
    </div>
  </header>

  <main>
    <section class="section">
      <div class="container section-heading">
        <span class="eyebrow">Pagamento</span>
        <h2>Confirmação de pagamento</h2>
        <p>Finalize o pagamento de R$<?= number_format($row['valor'], 2, ',', '.') ?> e envie o comprovante de pagamento para confirmação.</p>
      </div>

      <div class="container payment-panel">
        <div class="payment-summary">
          <div><strong>Nome:</strong> <?= htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') ?></div>
          <div><strong>Email:</strong> <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php if ($row['telefone']): ?><div><strong>Telefone:</strong> <?= htmlspecialchars($row['telefone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if ($row['whatsapp']): ?><div><strong>WhatsApp:</strong> <?= htmlspecialchars($row['whatsapp'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if ($formType === 'Peregrino'): ?>
            <div><strong>Gênero:</strong> <?= htmlspecialchars($row['genero'], ENT_QUOTES, 'UTF-8') ?></div>
            <div><strong>Categoria:</strong> <?= htmlspecialchars($row['categoria'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php else: ?>
            <div><strong>Função:</strong> <?= htmlspecialchars($row['funcao'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div><strong>Forma de pagamento:</strong> <?= htmlspecialchars(strtoupper($row['payment_method']), ENT_QUOTES, 'UTF-8') ?></div>
          <div><strong>Status:</strong> <span class="status-pill <?= htmlspecialchars($row['payment_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($row['payment_status'])), ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>

        <?php if ($row['payment_method'] === 'pix'): ?>
          <div style="display:grid; gap:1rem; align-items:center; justify-items:center;">
            <img src="<?= $qrUrl ?>" alt="QR Code PIX" style="max-width:100%; border-radius:20px; border:1px solid rgba(255,255,255,0.12);" />
            <p><strong>PIX:</strong> <?= htmlspecialchars($PIX_PHONE, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Valor:</strong> R$<?= number_format($row['valor'], 2, ',', '.') ?></p>
            <p class="upload-note">Escaneie o QR code ou copie o número para pagar. Em seguida, envie o comprovante abaixo.</p>
          </div>
        <?php else: ?>
          <div>
            <p>Você escolheu <strong>cartão de crédito</strong>. O pagamento será processado manualmente e sua inscrição seguirá para confirmação após envio do comprovante.</p>
          </div>
        <?php endif; ?>

        <?php if ($row['payment_receipt']): ?>
          <div>
            <strong>Comprovante enviado:</strong>
            <p><a href="uploads/<?= htmlspecialchars($row['payment_receipt'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Ver comprovante</a></p>
          </div>
        <?php endif; ?>

        <?php if ($row['payment_status'] !== 'confirmado'): ?>
          <form action="payment_confirm.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="<?= htmlspecialchars($formType, ENT_QUOTES, 'UTF-8') ?>" />
            <input type="hidden" name="id" value="<?= $row['id'] ?>" />
            <label>
              Enviar comprovante de pagamento
              <input type="file" name="receipt" accept="image/*,.pdf" required />
            </label>
            <button type="submit" class="btn btn-primary">Enviar comprovante</button>
            <p class="upload-note">O comprovante será analisado pelo administrador e o status será atualizado em breve.</p>
          </form>
        <?php else: ?>
          <p>Pagamento confirmado! Agradecemos sua inscrição.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
