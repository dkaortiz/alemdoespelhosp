<?php
require_once 'config.php';
session_start();

// Tipo de inscrição selecionado
$type = $_GET['type'] ?? null;
$validTypes = ['peregrino', 'anfitriao'];

if ($type && !in_array($type, $validTypes)) {
    $type = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrição - Além do Espelho</title>
    <link rel="stylesheet" href="style.css">
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
        <!-- ESCOLHA DE TIPO -->
        <?php if (!$type): ?>
        <section class="section" style="min-height: 600px; display: flex; align-items: center;">
            <div class="container">
                <div class="section-heading">
                    <h1>📝 ESCOLHA SUA PARTICIPAÇÃO</h1>
                    <p>Escolha como deseja participar: <strong>Peregrino</strong> ou <strong>Anfitrião</strong>.</p>
                </div>

                <div class="cards-grid" style="grid-template-columns: repeat(2, 1fr); gap: 3rem; max-width: 1000px; margin: 3rem auto;">
                    <!-- PEREGRINO -->
                    <a href="?type=peregrino" style="text-decoration: none;">
                        <div class="glass-strong choice-card" style="padding: 3rem; text-align: center; border: 2px solid transparent; transition: all 0.3s ease; animation: fadeInUp 0.6s ease;">
                            <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.5));">🧘</div>
                            <h2 style="color: var(--primary); font-size: 1.8rem; margin-bottom: 1rem;">Peregrino</h2>
                            <p style="color: var(--muted); margin-bottom: 1.5rem; font-size: 1.05rem; line-height: 1.8;">
                                Aprendizado profundo e participação em todas as atividades. Vagas limitadas: 15 homens e 15 mulheres.
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
                    <a href="?type=anfitriao" style="text-decoration: none;">
                        <div class="glass-strong choice-card" style="padding: 3rem; text-align: center; border: 2px solid transparent; transition: all 0.3s ease; animation: fadeInUp 0.6s ease 0.15s both;">
                            <div style="font-size: 4rem; margin-bottom: 1rem; filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.5));">👥</div>
                            <h2 style="color: var(--accent); font-size: 1.8rem; margin-bottom: 1rem;">Anfitrião</h2>
                            <p style="color: var(--muted); margin-bottom: 1.5rem; font-size: 1.05rem; line-height: 1.8;">
                                Apoio e suporte aos participantes. Recomendado para quem já participou como Acampante ou recebeu convite especial.
                            </p>
                            <ul style="list-style: none; color: var(--muted); text-align: left; display: inline-block;">
                                <li style="margin-bottom: 0.5rem;">✓ Experiência na organização</li>
                                <li style="margin-bottom: 0.5rem;">✓ Networking profissional</li>
                                <li style="margin-bottom: 0.5rem;">✓ Função definida</li>
                                <li>✓ R$ 150,00</li>
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
                            <label>Telefone</label>
                            <input type="tel" name="telefone" placeholder="DDD + número">
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
                            <label>Categoria *</label>
                            <select name="categoria" required>
                                <option value="">Selecione</option>
                                <option value="completo">Peregrino Completo</option>
                                <option value="dia">Peregrino Dia</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="tel" name="whatsapp" placeholder="DDD + número">
                        </div>

                        <div class="form-group">
                            <label>Forma de Pagamento *</label>
                            <select name="payment_method" required>
                                <option value="">Selecione</option>
                                <option value="pix">PIX</option>
                                <option value="cartao">Cartão de Crédito</option>
                            </select>
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
                            <label>Telefone</label>
                            <input type="tel" name="telefone" placeholder="DDD + número">
                        </div>

                        <div class="form-group">
                            <label>Função / Equipe *</label>
                            <input type="text" name="funcao" placeholder="Ex: Som, Palco, Logística" required>
                        </div>

                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="tel" name="whatsapp" placeholder="DDD + número">
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="foi_peregrino">
                                Fui Peregrino em edição anterior
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Forma de Pagamento *</label>
                            <select name="payment_method" required>
                                <option value="">Selecione</option>
                                <option value="pix">PIX</option>
                                <option value="cartao">Cartão de Crédito</option>
                            </select>
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

    <script src="script.js"></script>
</body>
</html>
