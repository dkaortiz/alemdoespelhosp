<?php
declare(strict_types=1);
require_once 'config.php';
session_start();

// Tipo de inscrição selecionado
$type = $_GET['type'] ?? null;
$validTypes = ['peregrino', 'anfitriao'];

if ($type && !in_array($type, $validTypes)) {
    $type = null;
}

// Buscar edição atual com limites
$edicao = $mysqli->query("SELECT * FROM edicoes ORDER BY ano DESC LIMIT 1");
$edicao_row = $edicao->fetch_assoc();
$limite_homem = $edicao_row['limite_homens'] ?? 15;
$limite_mulher = $edicao_row['limite_mulheres'] ?? 15;

// Função para obter vagas
function getVagas($mysqli, $genero, $limite) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM peregrinos WHERE genero = ? AND payment_status = 'confirmado'");
    $stmt->bind_param('s', $genero);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return max(0, $limite - ($row['cnt'] ?? 0));
}

$vagasM = getVagas($mysqli, 'masculino', $limite_homem);
$vagasF = getVagas($mysqli, 'feminino', $limite_mulher);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrição - Além do Espelho</title>
    <?php $page_title = 'Inscrição - Além do Espelho'; $page_description = 'Escolha sua participação: Peregrino ou Anfitrião. Vagas limitadas. Preço: R$ 150,00 ou R$ 100,00.'; $page_url = 'https://alemdoespelho.com.br/inscricao.php'; include __DIR__ . '/meta-tags.php'; ?>
    <link rel="stylesheet" href="<?php echo assetVersion('style.css'); ?>">
    <?php include __DIR__ . '/google_analytics.php'; ?>
    <style>
        .choice-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .choice-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--glow);
        }
        .form-container {
            animation: fadeInUp 0.5s ease;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(67, 56, 202, 0.3);
            background: rgba(67, 56, 202, 0.05);
            color: var(--text);
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(67, 56, 202, 0.1);
            box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.1);
        }
        
        /* Modal Styles */
        .modal-overlay {
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
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(67, 56, 202, 0.15), rgba(124, 58, 237, 0.1));
            border: 1px solid rgba(67, 56, 202, 0.3);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 3rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            text-align: center;
            animation: slideInUp 0.4s ease;
            box-shadow: 0 20px 60px rgba(67, 56, 202, 0.3);
        }
        
        .modal-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
        }
        
        .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4338CA, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .modal-text {
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .modal-highlight {
            background: rgba(67, 56, 202, 0.1);
            border-left: 4px solid var(--accent-secondary);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            color: var(--text);
            font-weight: 600;
        }
        
        .modal-cta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .modal-btn {
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
        }
        
        .modal-btn-whats {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
        }
        
        .modal-btn-whats:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(6, 182, 212, 0.3);
        }
        
        .modal-btn-close {
            background: rgba(67, 56, 202, 0.2);
            color: var(--text);
        }
        
        .modal-btn-close:hover {
            background: rgba(67, 56, 202, 0.3);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
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
        <!-- ESCOLHA DE TIPO -->
        <?php if (!$type): ?>
        <section class="section" style="min-height: 600px; display: flex; align-items: center;">
            <div class="container">
                <div class="section-heading">
                    <h1>📝 ESCOLHA SUA PARTICIPAÇÃO</h1>
                    <p>Escolha como deseja participar: <strong>Peregrino</strong> ou <strong>Anfitrião</strong>.</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; max-width: 1000px; margin: 3rem auto;">
                    <!-- PEREGRINO -->
                    <a href="inscricao.php?type=peregrino" style="text-decoration: none; display: block;">
                        <div class="glass-strong choice-card" style="padding: 3rem; text-align: center; border: 2px solid transparent; transition: all 0.3s ease; animation: fadeInUp 0.6s ease;">
                            <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.5));">🧘</div>
                            <h2 style="color: var(--primary); font-size: 1.8rem; margin-bottom: 1rem;">Peregrino</h2>
                            <p style="color: var(--muted); margin-bottom: 1.5rem; font-size: 1.05rem; line-height: 1.8;">
                                Aprendizado profundo e participação em todas as atividades. Vagas limitadas: <?= $limite_homem ?> homens e <?= $limite_mulher ?> mulheres.
                            </p>
                            <ul style="list-style: none; color: var(--muted); text-align: left; display: inline-block;">
                                <li style="margin-bottom: 0.5rem;">✓ Imersão completa no evento</li>
                                <li style="margin-bottom: 0.5rem;">✓ Workshops e atividades</li>
                                <li style="margin-bottom: 0.5rem;">✓ Comunidade acolhedora</li>
                                <li>✓ R$ 150,00</li>
                            </ul>
                        </div>
                    </a>

                    <!-- ANFITRIÃO -->
                    <a href="inscricao.php?type=anfitriao" style="text-decoration: none; display: block;">
                        <div class="glass-strong choice-card" style="padding: 3rem; text-align: center; border: 2px solid transparent; transition: all 0.3s ease; animation: fadeInUp 0.6s ease 0.15s both;">
                            <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 15px rgba(6, 182, 212, 0.5));">👥</div>
                            <h2 style="color: var(--accent-secondary); font-size: 1.8rem; margin-bottom: 1rem;">Anfitrião</h2>
                            <p style="color: var(--muted); margin-bottom: 1.5rem; font-size: 1.05rem; line-height: 1.8;">
                                Apoio e suporte aos participantes. <strong style="color: var(--accent-secondary);">Recomendado para quem já participou como Peregrino</strong> e quer contribuir com a experiência.
                            </p>
                            <ul style="list-style: none; color: var(--muted); text-align: left; display: inline-block;">
                                <li style="margin-bottom: 0.5rem;">✓ Experiência na organização</li>
                                <li style="margin-bottom: 0.5rem;">✓ Networking profissional</li>
                                <li style="margin-bottom: 0.5rem;">✓ Função definida</li>
                                <li>✓ <strong style="color: var(--accent-secondary);">R$ 100,00</strong></li>
                            </ul>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- PEREGRINO FORM -->
        <?php elseif ($type === 'peregrino'): ?>
        <section class="section">
            <div class="container" style="max-width: 600px;">
                <div class="section-heading">
                    <h2>Inscrição de Peregrino</h2>
                    <p>Preencha o formulário e comece sua jornada</p>
                </div>

                <div class="glass-strong form-container" style="padding: 2.5rem; border-radius: 16px;">
                    <form action="submit.php" method="post">
                        <input type="hidden" name="form_type" value="peregrino">

                        <div class="form-group">
                            <label>Nome Completo *</label>
                            <input type="text" name="nome" placeholder="Nome completo" required>
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" placeholder="seu@email.com" required>
                        </div>

                        <div class="form-group">
                            <label>Gênero *</label>
                            <select name="genero" required>
                                <option value="">Selecione</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Endereço Completo *</label>
                            <input type="text" name="endereco" placeholder="Rua, número, bairro, cidade" required>
                        </div>

                        <div class="form-group">
                            <label>Tem algum problema de saúde? *</label>
                            <select name="problema_saude" required>
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Se sim, quais problemas?</label>
                            <textarea name="problema_saude_descricao" placeholder="Descreva os problemas de saúde"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Usa algum remédio? *</label>
                            <select name="usa_remedio" required>
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Se sim, qual remédio e em que horários?</label>
                            <textarea name="remedio_descricao" placeholder="Ex.: Dipirona, 8h e 20h"></textarea>
                        </div>

                        <div class="form-group">
                            <label>WhatsApp *</label>
                            <input type="tel" name="whatsapp" placeholder="DDD + número" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                            Continuar
                        </button>
                    </form>
                </div>

                <p style="text-align: center; color: var(--muted); margin-top: 2rem;">
                    <a href="inscricao.php" style="color: var(--accent);">← Voltar para escolha</a>
                </p>
            </div>
        </section>

        <!-- ANFITRIÃO FORM -->
        <?php elseif ($type === 'anfitriao'): ?>
        <section class="section">
            <div class="container" style="max-width: 600px;">
                <div class="section-heading">
                    <h2>Inscrição de Anfitrião</h2>
                    <p>Junte-se à equipe organizadora</p>
                </div>

                <div class="glass-strong form-container" style="padding: 2.5rem; border-radius: 16px;">
                    <form action="submit.php" method="post">
                        <input type="hidden" name="form_type" value="anfitriao">

                        <div class="form-group">
                            <label>Nome Completo *</label>
                            <input type="text" name="nome" placeholder="Nome completo" required>
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" placeholder="seu@email.com" required>
                        </div>

                        <div class="form-group">
                            <label>Função / Equipe *</label>
                            <input type="text" name="funcao" placeholder="Ex: Som, Palco, Logística" required>
                        </div>

                        <div class="form-group">
                            <label>Endereço Completo *</label>
                            <input type="text" name="endereco" placeholder="Rua, número, bairro, cidade" required>
                        </div>

                        <div class="form-group">
                            <label>Tem algum problema de saúde? *</label>
                            <select name="problema_saude" required>
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Se sim, quais problemas?</label>
                            <textarea name="problema_saude_descricao" placeholder="Descreva os problemas de saúde"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Usa algum remédio? *</label>
                            <select name="usa_remedio" required>
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Se sim, qual remédio e em que horários?</label>
                            <textarea name="remedio_descricao" placeholder="Ex.: Dipirona, 8h e 20h"></textarea>
                        </div>

                        <div class="form-group">
                            <label>WhatsApp *</label>
                            <input type="tel" name="whatsapp" placeholder="DDD + número" required>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="foi_peregrino">
                                Fui Peregrino em edição anterior
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                            Continuar
                        </button>
                    </form>
                </div>

                <p style="text-align: center; color: var(--muted); margin-top: 2rem;">
                    <a href="inscricao.php" style="color: var(--accent);">← Voltar para escolha</a>
                </p>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
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
        

    </script>
</body>
</html>
