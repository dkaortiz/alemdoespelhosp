<?php
require_once 'config.php';
session_start();

if (empty($_SESSION['user_access'])) {
    header('Location: user_login.php');
    exit;
}

$user = $_SESSION['user_access'];

$paymentStatus = strtolower((string) ($user['payment_status'] ?? 'pendente'));
$pagbankStatus = strtolower((string) ($user['pagbank_status'] ?? ''));
$isActive = $user['is_active'] ?? true;

$showPaymentButton = !empty($user['checkout_url'])
    && !in_array($paymentStatus, ['confirmado', 'cancelado', 'aprovado', 'comprovante_enviado'], true)
    && !in_array($pagbankStatus, ['paid', 'approved', 'authorized', 'confirmed', 'settled', 'success', 'succeeded', 'canceled', 'cancelled', 'expired', 'failed', 'declined', 'rejected'], true);

$retryPaymentUrl = !empty($user['checkout_url']) ? $user['checkout_url'] : 'payment.php';
$retryPaymentLabel = !empty($user['checkout_url']) ? '💳 Realizar Pagamento' : '💳 Tentar Pagamento Novamente';

// Função para obter cor do status
function getStatusColor($status) {
    $status = strtolower($status);
    if (in_array($status, ['confirmado', 'aprovado', 'paid', 'success', 'confirmed'])) {
        return '#10b981'; // Verde
    } elseif (in_array($status, ['comprovante_enviado'])) {
        return '#f59e0b'; // Amber
    } elseif (in_array($status, ['cancelado', 'failed'])) {
        return '#ef4444'; // Vermelho
    }
    return '#6366f1'; // Roxo padrão
}

function getStatusLabel($status) {
    $status = strtolower($status);
    $labels = [
        'pendente' => '⏳ Pendente',
        'comprovante_enviado' => '📤 Comprovante Enviado',
        'confirmado' => '✅ Confirmado',
        'aprovado' => '✅ Aprovado',
        'cancelado' => '❌ Cancelado',
        'paid' => '✅ Pago',
        'success' => '✅ Sucesso',
        'confirmed' => '✅ Confirmado'
    ];
    return $labels[$status] ?? '❓ ' . ucfirst($status);
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Inscrição - Além do Espelho</title>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <style>
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        .info-card {
            padding: 1.5rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(67, 56, 202, 0.08), rgba(124, 58, 237, 0.05));
            border: 1px solid rgba(67, 56, 202, 0.2);
        }
        .info-label {
            color: var(--muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: block;
        }
        .info-value {
            color: var(--text);
            font-size: 1.1rem;
            font-weight: 600;
            word-break: break-all;
        }
        .payment-section {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(124, 58, 237, 0.08));
            border: 2px solid rgba(6, 182, 212, 0.3);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        .btn-group .btn {
            flex: 1;
            min-width: 150px;
        }
    </style>
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
                    <a href="user_login.php" style="color: var(--accent-secondary);">Minha Área</a>
                </div>
            </div>
        </nav>
        <div class="mobile-menu" id="mobileMenu">
            <a href="index.php">Home</a>
            <a href="edicoes.php">Edições</a>
            <a href="inscricao.php">Inscrição</a>
            <a href="regras.php">Regras</a>
            <a href="user_login.php">Minha Área</a>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="container" style="max-width: 900px;">
                <!-- HEADER -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #4338CA, #7c3aed); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <?php echo $user['type'] === 'peregrino' ? '🧘' : '👥'; ?>
                        </div>
                        <div>
                            <h1 style="margin: 0; font-size: 2rem; color: var(--text);">Olá, <?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p style="margin: 0.25rem 0 0; color: var(--muted);">Acompanhe o status de sua inscrição</p>
                        </div>
                    </div>
                </div>

                <!-- DADOS PESSOAIS -->
                <div class="glass-strong" style="padding: 2rem; border-radius: 16px; margin-bottom: 2rem;">
                    <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1.5rem;">📋 Dados da Inscrição</h3>
                    
                    <!-- Status de Ativação -->
                    <?php if (!$isActive): ?>
                        <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <p style="margin: 0; color: #ef4444; font-weight: 600;">
                                ⚠️ Sua inscrição foi desativada
                            </p>
                            <p style="margin: 0.5rem 0 0; color: var(--muted); font-size: 0.95rem;">
                                Contacte o administrador para mais informações sobre o motivo da desativação.
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="background: rgba(16, 185, 129, 0.1); border: 2px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <p style="margin: 0; color: #10b981; font-weight: 600;">
                                ✅ Sua inscrição está ativa
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-grid">
                        <div class="info-card">
                            <span class="info-label">Tipo de Participação</span>
                            <span class="info-value"><?php echo htmlspecialchars(ucfirst($user['type']), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="info-card">
                            <span class="info-label">E-mail</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="info-card">
                            <span class="info-label">WhatsApp</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['telefone'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- STATUS DE PAGAMENTO -->
                <div class="payment-section">
                    <h3 style="margin-top: 0; color: var(--text); margin-bottom: 1.5rem;">💳 Status de Pagamento</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <!-- Status no Sistema -->
                        <div>
                            <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Sistema</p>
                            <div class="status-badge" style="background: rgba(<?php 
                                $color = getStatusColor($paymentStatus);
                                $rgb = sscanf($color, "#%02x%02x%02x");
                                echo implode(',', $rgb) . ',0.2';
                            ?>); color: <?php echo getStatusColor($paymentStatus); ?>;">
                                <?php echo getStatusLabel($paymentStatus); ?>
                            </div>
                        </div>

                        <!-- Status PagBank -->
                        <div>
                            <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 0.5rem;">PagBank</p>
                            <div class="status-badge" style="background: rgba(99,102,241,0.2); color: #6366f1;">
                                <?php echo htmlspecialchars($pagbankStatus ?? '⏳ Não consultado', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Comprovante Enviado (se existir) -->
                    <?php if (!empty($user['payment_receipt'])): ?>
                        <div id="uploadedReceipt" style="margin-top: 1.5rem; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3);">
                            <p style="margin: 0 0 0.75rem; font-weight: 600; color: #10b981;">✅ Comprovante Enviado</p>
                            <?php 
                                $receiptPath = htmlspecialchars($user['payment_receipt'], ENT_QUOTES, 'UTF-8');
                                $ext = strtolower(pathinfo($receiptPath, PATHINFO_EXTENSION));
                            ?>
                            <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                <a href="<?php echo $receiptPath; ?>" target="_blank" rel="noopener noreferrer">
                                    <img src="<?php echo $receiptPath; ?>" style="max-width: 100%; height: auto; border-radius: 8px; display: block; margin-top: 0.75rem;">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $receiptPath; ?>" target="_blank" rel="noopener noreferrer" style="color: #10b981; font-weight: 600; text-decoration: none;">
                                    📄 Abrir comprovante (PDF)
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <p style="margin: 1.5rem 0 0; color: var(--muted); font-size: 0.95rem; line-height: 1.6;">
                        Se você ainda não conseguiu concluir o pagamento ou o link anterior falhou, use o botão abaixo para tentar novamente.
                    </p>

                    <!-- Botões de Ação -->
                    <div class="btn-group">
                        <?php if ($isActive && !in_array($paymentStatus, ['confirmado', 'cancelado', 'aprovado', 'comprovante_enviado'], true)): ?>
                            <a href="<?php echo htmlspecialchars($retryPaymentUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars($retryPaymentLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($isActive): ?>
                            <button class="btn btn-secondary" onclick="toggleUploadForm()">
                                📤 Enviar Comprovante
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;" title="Sua inscrição foi desativada">
                                📤 Enviar Comprovante
                            </button>
                        <?php endif; ?>

                        <a href="user_login.php" class="btn" style="background: rgba(67, 56, 202, 0.1); color: var(--text);">
                            ← Voltar
                        </a>
                    </div>
                </div>

                <!-- FORMULÁRIO DE UPLOAD (oculto por padrão) -->
                <div id="uploadForm" style="display: none; margin-top: 2rem;">
                    <div class="glass-strong" style="padding: 2rem; border-radius: 16px;">
                        <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1rem;">📤 Enviar Comprovante de Pagamento</h3>
                        
                        <p style="color: var(--muted); line-height: 1.6; margin-bottom: 1.5rem;">
                            Anexe o comprovante do pagamento realizado. Aceitamos imagens (JPG, PNG) ou PDF. 
                            <strong>Máximo 5MB.</strong> Após enviar, um administrador validará o comprovante.
                        </p>

                        <form id="receiptForm" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user['table'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.75rem; color: var(--text); font-weight: 600;">Selecione o arquivo *</label>
                                <div style="border: 2px dashed rgba(67, 56, 202, 0.3); border-radius: 12px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s ease; background: rgba(67, 56, 202, 0.05);" id="dropZone">
                                    <input type="file" id="receiptFile" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required style="display: none;">
                                    <p style="margin: 0; color: var(--text); font-weight: 600;">
                                        📎 Clique aqui ou arraste o arquivo
                                    </p>
                                    <p style="margin: 0.5rem 0 0; color: var(--muted); font-size: 0.9rem;">
                                        JPG, PNG ou PDF (máx. 5MB)
                                    </p>
                                    <p style="margin: 1rem 0 0; color: var(--muted); font-size: 0.85rem;">
                                        ✓ Comprovante de transferência<br>
                                        ✓ Comprovante de PIX<br>
                                        ✓ Recibo de pagamento
                                    </p>
                                </div>
                                <p id="fileName" style="margin-top: 0.75rem; color: var(--accent-secondary); font-weight: 600; display: none;"></p>
                            </div>

                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <button type="submit" class="btn btn-primary" style="flex: 1; min-width: 150px;">
                                    Enviar Comprovante
                                </button>
                                <button type="button" class="btn" style="background: rgba(67, 56, 202, 0.1); color: var(--text); flex: 1; min-width: 150px;" onclick="toggleUploadForm()">
                                    Cancelar
                                </button>
                            </div>

                            <p id="uploadMessage" style="margin-top: 1rem; padding: 1rem; border-radius: 12px; display: none;"></p>
                        </form>
                    </div>
                </div>

                <!-- INFORMAÇÕES ADICIONAIS -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(67, 56, 202, 0.08); border-radius: 12px; border-left: 4px solid var(--primary);">
                    <p style="margin: 0; color: var(--text); font-size: 0.95rem; line-height: 1.6;">
                        <strong>ℹ️ Informação importante:</strong> Seu comprovante será armazenado de forma segura e temporária enquanto um administrador valida a inscrição. Após a validação, você receberá uma confirmação por e-mail.
                    </p>
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

        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.hamburger-menu').classList.remove('active');
                document.getElementById('mobileMenu').classList.remove('active');
            });
        });

        function toggleUploadForm() {
            const form = document.getElementById('uploadForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        const receiptFile = document.getElementById('receiptFile');
        const fileName = document.getElementById('fileName');

        dropZone.addEventListener('click', () => receiptFile.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary)';
            dropZone.style.background = 'rgba(67, 56, 202, 0.15)';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = 'rgba(67, 56, 202, 0.3)';
            dropZone.style.background = 'rgba(67, 56, 202, 0.05)';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'rgba(67, 56, 202, 0.3)';
            dropZone.style.background = 'rgba(67, 56, 202, 0.05)';
            if (e.dataTransfer.files.length) {
                receiptFile.files = e.dataTransfer.files;
                updateFileName();
            }
        });

        receiptFile.addEventListener('change', updateFileName);

        function updateFileName() {
            if (receiptFile.files.length > 0) {
                fileName.textContent = '✓ Arquivo selecionado: ' + receiptFile.files[0].name;
                fileName.style.display = 'block';
            } else {
                fileName.style.display = 'none';
            }
        }

        // Submit form
        document.getElementById('receiptForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const messageEl = document.getElementById('uploadMessage');

            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Enviando...';

            try {
                const response = await fetch('upload_receipt.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageEl.style.background = 'rgba(16, 185, 129, 0.1)';
                    messageEl.style.color = '#10b981';
                    messageEl.style.borderLeft = '4px solid #10b981';
                    messageEl.textContent = '✅ ' + data.message;
                    messageEl.style.display = 'block';

                    // Mostrar comprovante imediatamente
                    if (data.receipt_path) {
                        const parent = document.querySelector('.payment-section');
                        let preview = document.getElementById('uploadedReceipt');
                        if (!preview) {
                            preview = document.createElement('div');
                            preview.id = 'uploadedReceipt';
                            preview.style.marginTop = '1rem';
                            parent.insertBefore(preview, parent.querySelector('.btn-group'));
                        }
                        const ext = data.receipt_path.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            preview.innerHTML = `<p style="margin:0 0 0.5rem; font-weight:600">Comprovante enviado:</p><a href="${data.receipt_path}" target="_blank" rel="noopener noreferrer"><img src="${data.receipt_path}" style="max-width:100%; height:auto; border-radius:8px; display:block; margin-top:0.5rem;"></a>`;
                        } else {
                            preview.innerHTML = `<p style="margin:0 0 0.5rem; font-weight:600">Comprovante enviado:</p><a href="${data.receipt_path}" target="_blank" rel="noopener noreferrer">Abrir comprovante (PDF)</a>`;
                        }
                    }

                    // Resetar formulário
                    setTimeout(() => {
                        form.reset();
                        fileName.style.display = 'none';
                        toggleUploadForm();
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Enviar Comprovante';
                        messageEl.style.display = 'none';
                    }, 1200);
                } else {
                    messageEl.style.background = 'rgba(239, 68, 68, 0.1)';
                    messageEl.style.color = '#ef4444';
                    messageEl.style.borderLeft = '4px solid #ef4444';
                    messageEl.textContent = '❌ ' + (data.message || 'Erro ao enviar arquivo');
                    messageEl.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar Comprovante';
                }
            } catch (error) {
                messageEl.style.background = 'rgba(239, 68, 68, 0.1)';
                messageEl.style.color = '#ef4444';
                messageEl.style.borderLeft = '4px solid #ef4444';
                messageEl.textContent = '❌ Erro de conexão: ' + error.message;
                messageEl.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Comprovante';
            }
        });
    </script>
    <script src="<?php echo assetVersion('script.js'); ?>"></script>
</body>
</html>
