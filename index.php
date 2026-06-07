<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Além do Espelho - Em Breve</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #D4AF37;
            --accent: #F4D03F;
            --success: #27AE60;
            --warning: #E74C3C;
            --text: #F5F5F5;
            --muted: #A89968;
            --bg: #0a0805;
            --bg-secondary: #1a1410;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0a0805, #1a1410);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: rgba(10, 8, 5, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .site-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #D4AF37, #F4D03F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .site-nav {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .site-nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .site-nav a:hover {
            color: var(--primary);
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* BACKGROUND ANIMADO */
        .bg-animated {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(244, 208, 63, 0.05) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
            z-index: 0;
        }

        @keyframes gradientShift {
            0%, 100% {
                background: radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                            radial-gradient(circle at 80% 80%, rgba(244, 208, 63, 0.05) 0%, transparent 50%);
            }
            50% {
                background: radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                            radial-gradient(circle at 20% 80%, rgba(244, 208, 63, 0.05) 0%, transparent 50%);
            }
        }

        .coming-soon-container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 800px;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .coming-soon-container h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #D4AF37, #F4D03F, #B8860B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            line-height: 1.2;
        }

        .coming-soon-container p {
            font-size: 1.2rem;
            color: var(--muted);
            margin-bottom: 3rem;
            font-weight: 500;
            line-height: 1.6;
        }

        /* CRONÔMETRO */
        .countdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
            padding: 2rem;
            background: rgba(212, 175, 55, 0.08);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .countdown-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
            animation: pulse 1s ease-in-out infinite;
            min-height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .countdown-label {
            font-size: 0.9rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        .mirror-icon {
            width: 200px;
            height: 200px;
            margin: 2rem auto;
            opacity: 0.6;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #0a0805;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-secondary {
            background: rgba(212, 175, 55, 0.1);
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: rgba(212, 175, 55, 0.2);
            transform: translateY(-2px);
        }

        footer {
            background: rgba(10, 8, 5, 0.9);
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            padding: 2rem;
            text-align: center;
            color: var(--muted);
            margin-top: auto;
        }

        .subtitle {
            font-size: 1.3rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .coming-soon-container h1 {
                font-size: 2rem;
            }

            .countdown-number {
                font-size: 2rem;
            }

            .coming-soon-container p {
                font-size: 1rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                padding: 1.5rem;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- BACKGROUND ANIMADO -->
    <div class="bg-animated"></div>

    <!-- HEADER -->
    <header>
        <nav class="container">
            <div class="header-inner">
                <div class="site-brand">✨ Além do Espelho</div>
                <div class="site-nav">
                    <a href="index.php">Home</a>
                    <a href="edicoes.php">Edições</a>
                    <a href="regras.php">Regras</a>
                    <a href="admin.php">Admin</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main>
        <div class="coming-soon-container">
            <!-- ÍCONE ESPELHO -->
            <svg class="mirror-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="mirrorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:0.8" />
                        <stop offset="50%" style="stop-color:#F4D03F;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#B8860B;stop-opacity:0.8" />
                    </linearGradient>
                </defs>
                <!-- Moldura do espelho -->
                <rect x="20" y="20" width="60" height="60" rx="8" fill="none" stroke="url(#mirrorGradient)" stroke-width="2"/>
                <!-- Vidro do espelho -->
                <rect x="25" y="25" width="50" height="50" fill="rgba(212, 175, 55, 0.2)" rx="6"/>
                <!-- Reflexo -->
                <circle cx="50" cy="45" r="15" fill="url(#mirrorGradient)" opacity="0.4"/>
            </svg>

            <h1>🌟 Algo Extraordinário se Aproxima</h1>
            
            <p class="subtitle">1ª Edição — O Confronto</p>

            <p>
                "Antes do propósito, existe o confronto."<br>
                Um encontro transformador que pode mudar toda a sua história. 🙏
            </p>

            <!-- CRONÔMETRO -->
            <div class="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days">00</div>
                    <div class="countdown-label">Dias</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">00</div>
                    <div class="countdown-label">Horas</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">00</div>
                    <div class="countdown-label">Minutos</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">00</div>
                    <div class="countdown-label">Segundos</div>
                </div>
            </div>

            <p style="color: var(--muted); font-size: 0.95rem; margin-bottom: 2rem;">
                Inscrições abrem em 8 de junho de 2026 às 15:00 (Horário de Brasília)
            </p>

            <!-- BOTÕES DE AÇÃO -->
            <div class="cta-buttons">
                <a href="edicoes.php" class="btn btn-secondary">
                    Saiba Mais sobre a Edição
                </a>
                <a href="regras.php" class="btn btn-secondary">
                    Leia as Regras
                </a>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
    </footer>

    <script>
        function updateCountdown() {
            // Data alvo: 8 de junho de 2026 às 15:00 (BRT = UTC-3)
            // Criar a data em UTC e depois ajustar para BRT
            const targetDate = new Date('2026-06-08T15:00:00-03:00').getTime();
            
            const now = new Date().getTime();
            const distance = targetDate - now;

            // Calcular unidades de tempo
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Atualizar elementos
            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

            // Se acabou o tempo, mostrar mensagem
            if (distance < 0) {
                document.querySelector('.countdown').innerHTML = `
                    <div style="grid-column: 1 / -1; color: var(--success); font-size: 1.5rem; font-weight: 800;">
                        🎉 As inscrições estão abertas! 🎉
                    </div>
                `;
                document.querySelector('.cta-buttons').innerHTML = `
                    <a href="inscricao.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2.5rem;">
                        Comece Sua Inscrição
                    </a>
                `;
            }
        }

        // Atualizar a cada segundo
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
