<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Além do Espelho - Em Breve</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, #0a0805 0%, #1a1410 50%, #0f0d0a 100%);
            color: #F5F5F5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* BACKGROUND ANIMADO PREMIUM */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(184, 134, 11, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 50% 0%, rgba(139, 105, 20, 0.06) 0%, transparent 40%);
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                background: 
                    radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.12) 0%, transparent 40%),
                    radial-gradient(circle at 80% 80%, rgba(184, 134, 11, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 50% 0%, rgba(139, 105, 20, 0.06) 0%, transparent 40%);
            }
            50% {
                background: 
                    radial-gradient(circle at 80% 30%, rgba(212, 175, 55, 0.12) 0%, transparent 40%),
                    radial-gradient(circle at 20% 70%, rgba(184, 134, 11, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 50% 100%, rgba(139, 105, 20, 0.06) 0%, transparent 40%);
            }
        }

        /* HEADER MINIMAL */
        header {
            position: relative;
            z-index: 100;
            padding: 1.5rem 1rem;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .site-brand {
            font-size: 1.1rem;
            font-weight: 800;
            background: linear-gradient(90deg, #E8D4A2 0%, #D4AF37 50%, #B8860B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
            filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.15));
            word-break: break-word;
        }

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 3rem 2rem;
        }

        .coming-soon-container {
            text-align: center;
            max-width: 1000px;
            animation: fadeInScale 0.8s ease;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* MIRROR ICON GRANDE */
        .mirror-icon {
            width: 250px;
            height: 250px;
            margin: 0 auto 1.5rem;
            filter: drop-shadow(0 0 40px rgba(212, 175, 55, 0.3));
            animation: floatMirror 4s ease-in-out infinite, rotateMirror 30s linear infinite;
        }

        @keyframes floatMirror {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes rotateMirror {
            from { transform: rotateY(0deg) rotateZ(0deg); }
            to { transform: rotateY(360deg) rotateZ(5deg); }
        }

        /* TÍTULO */
        .coming-soon-container h1 {
            font-size: 2rem;
            margin-bottom: 0.3rem;
            color: #F5F5F5;
            font-weight: 700;
            letter-spacing: 1px;
            animation: slideDownTitle 0.8s ease 0.1s both;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
        }

        @keyframes slideDownTitle {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* SUBTITLE DESTACADO COM OUTRA COR */
        .subtitle {
            font-size: 2.8rem;
            font-weight: 900;
            margin: 1.5rem 0 2rem;
            background: linear-gradient(90deg, #E8D4A2 0%, #D4AF37 25%, #B8860B 50%, #8B6914 75%, #6B5A0F 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
            animation: slideDownTitle 0.8s ease 0.2s both;
            text-shadow: 0 0 30px rgba(184, 134, 11, 0.3);
            filter: drop-shadow(0 0 25px rgba(212, 175, 55, 0.25));
        }

        .subtitle::before {
            content: "✨ ";
        }
        
        .subtitle::after {
            content: " ✨";
        }

        /* QUOTE */
        .quote {
            font-size: 1rem;
            color: #C9B88B;
            margin-bottom: 2rem;
            font-style: italic;
            animation: slideDownTitle 0.8s ease 0.3s both;
            line-height: 1.6;
            letter-spacing: 0.5px;
        }

        /* COUNTDOWN GRANDE E ELEGANTE */
        .countdown {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2.5rem;
            margin: 5rem 0;
            padding: 4rem 3rem;
            background: rgba(212, 175, 55, 0.05);
            border: 3px solid rgba(212, 175, 55, 0.25);
            border-radius: 25px;
            backdrop-filter: blur(15px);
            box-shadow: 
                0 0 60px rgba(212, 175, 55, 0.25),
                0 8px 32px rgba(0, 0, 0, 0.4),
                inset 0 1px 1px rgba(255, 255, 255, 0.05);
            animation: slideUpCountdown 0.8s ease 0.4s both;
        }

        @keyframes slideUpCountdown {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .countdown-number {
            font-size: 6.5rem;
            font-weight: 900;
            background: linear-gradient(180deg, #FFE680 0%, #FFD700 25%, #DAA520 50%, #B8860B 75%, #8B6914 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 
                0 0 10px rgba(255, 215, 0, 0.8),
                0 0 20px rgba(255, 215, 0, 0.6),
                0 0 40px rgba(255, 215, 0, 0.4),
                0 0 60px rgba(212, 175, 55, 0.5);
            min-height: 7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulseGlow 1.5s ease-in-out infinite;
            font-variant-numeric: tabular-nums;
            filter: drop-shadow(0 0 35px rgba(255, 215, 0, 0.4));
        }

        @keyframes pulseGlow {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 25px rgba(212, 175, 55, 0.4));
            }
            50% {
                transform: scale(1.08);
                filter: drop-shadow(0 0 50px rgba(212, 175, 55, 0.6));
            }
        }

        .countdown-label {
            font-size: 1.2rem;
            color: #DAA520;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 900;
            text-shadow: 0 0 15px rgba(218, 165, 32, 0.5);
        }

        .countdown-divider {
            width: 2px;
            height: 150px;
            background: linear-gradient(180deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            margin: 0 0.5rem;
        }

        /* MENSAGEM */
        .deadline-msg {
            color: #C9B88B;
            font-size: 1rem;
            margin-bottom: 2rem;
            letter-spacing: 1px;
            animation: slideDownTitle 0.8s ease 0.5s both;
        }

        /* FOOTER */
        footer {
            position: relative;
            z-index: 100;
            background: rgba(10, 8, 5, 0.9);
            border-top: 1px solid rgba(212, 175, 55, 0.15);
            padding: 1.5rem;
            text-align: center;
            color: #8B7355;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
                padding: 2rem;
            }

            .countdown-number {
                font-size: 3.5rem;
            }

            .countdown-label {
                font-size: 0.85rem;
            }

            .countdown-divider {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .mirror-icon {
                width: 180px;
                height: 180px;
            }

            .coming-soon-container h1 {
                font-size: 1.6rem;
            }

            .subtitle {
                font-size: 2rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
                padding: 2rem;
                margin: 3rem 0;
            }

            .countdown-number {
                font-size: 4rem;
            }

            .countdown-label {
                font-size: 1rem;
            }

            .quote {
                font-size: 0.95rem;
            }

            .deadline-msg {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 600px) {
            .mirror-icon {
                width: 160px;
                height: 160px;
                margin-bottom: 1.2rem;
            }

            .coming-soon-container h1 {
                font-size: 1.5rem;
                margin-bottom: 0.3rem;
            }

            .subtitle {
                font-size: 1.8rem;
                margin: 1rem 0 1.2rem;
                letter-spacing: 1px;
            }

            .quote {
                font-size: 0.95rem;
                margin-bottom: 1.8rem;
                line-height: 1.7;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.2rem;
                padding: 2rem 1.5rem;
                margin: 2.5rem 0;
                border-radius: 20px;
            }

            .countdown-number {
                font-size: 3.2rem;
                min-height: 4rem;
            }

            .countdown-label {
                font-size: 0.9rem;
                letter-spacing: 2px;
            }

            .deadline-msg {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1.2rem;
            }

            header {
                padding: 1rem;
            }

            .site-brand {
                font-size: 1rem;
                letter-spacing: 1px;
            }

            .mirror-icon {
                width: 130px;
                height: 130px;
                margin-bottom: 0.8rem;
            }

            .coming-soon-container {
                max-width: 95%;
            }

            .coming-soon-container h1 {
                font-size: 1.3rem;
                margin-bottom: 0.2rem;
                line-height: 1.3;
            }

            .subtitle {
                font-size: 1.6rem;
                margin: 0.6rem 0 0.8rem;
            }

            .quote {
                font-size: 0.85rem;
                margin-bottom: 1.5rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
                padding: 1.2rem;
                margin: 1.5rem 0;
                border-radius: 16px;
            }

            .countdown-number {
                font-size: 2.5rem;
                min-height: 3rem;
            }

            .countdown-label {
                font-size: 0.75rem;
                letter-spacing: 1px;
                margin-top: 0.3rem;
            }

            .deadline-msg {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 360px) {
            main {
                padding: 0.8rem;
            }

            header {
                padding: 0.8rem;
            }

            .site-brand {
                font-size: 0.9rem;
            }

            .mirror-icon {
                width: 100px;
                height: 100px;
                margin-bottom: 0.5rem;
            }

            .coming-soon-container h1 {
                font-size: 1.1rem;
            }

            .subtitle {
                font-size: 1.4rem;
                margin: 0.5rem 0 0.7rem;
            }

            .quote {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .countdown {
                gap: 0.6rem;
                padding: 1rem;
                margin: 1.2rem 0;
            }

            .countdown-number {
                font-size: 2rem;
            }

            .countdown-label {
                font-size: 0.65rem;
            }

            .deadline-msg {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- BACKGROUND ANIMADO -->
    <div class="bg-animated"></div>

    <!-- HEADER MINIMAL -->
    <header>
        <div class="site-brand">✨ ALÉM DO ESPELHO ✨</div>
    </header>

    <!-- MAIN CONTENT -->
    <main>
        <div class="coming-soon-container">
            <!-- MIRROR ICON GRANDE -->
            <svg class="mirror-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="mirrorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:1" />
                        <stop offset="50%" style="stop-color:#F4D03F;stop-opacity:0.9" />
                        <stop offset="100%" style="stop-color:#B8860B;stop-opacity:1" />
                    </linearGradient>
                    <filter id="mirrorGlow">
                        <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                <circle cx="50" cy="50" r="45" fill="none" stroke="url(#mirrorGradient)" stroke-width="3" opacity="0.8" filter="url(#mirrorGlow)"/>
                <circle cx="50" cy="50" r="35" fill="rgba(212, 175, 55, 0.15)"/>
                <circle cx="50" cy="50" r="35" fill="none" stroke="url(#mirrorGradient)" stroke-width="2" opacity="0.6"/>
                <circle cx="38" cy="38" r="12" fill="url(#mirrorGradient)" opacity="0.6"/>
                <circle cx="62" cy="62" r="8" fill="url(#mirrorGradient)" opacity="0.4"/>
            </svg>

            <h1>Algo Extraordinário se Aproxima</h1>

            <p class="subtitle">1ª Edição — O Confronto</p>

            <p class="quote">
                "Antes do propósito, existe o confronto."<br>
                Um encontro transformador que pode mudar toda a sua história.
            </p>

            <!-- COUNTDOWN GRANDE -->
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

            <p class="deadline-msg">
                Inscrições abrem em 8 de junho de 2026 às 15:00 (Horário de Brasília)
            </p>
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
                document.querySelector('.coming-soon-container').innerHTML = `
                    <div style="animation: fadeInScale 0.8s ease;">
                        <h1 style="color: #2ECC71; font-size: 3rem; margin-bottom: 2rem; animation: slideDownTitle 0.8s ease 0.1s both;">🎉 Inscrições Abertas! 🎉</h1>
                        <p style="color: #A89968; font-size: 1.3rem; margin-bottom: 3rem; animation: slideDownTitle 0.8s ease 0.2s both;">Sua jornada de transformação começa agora.</p>
                        <a href="inscricao.php" style="
                            display: inline-block;
                            padding: 1.5rem 3.5rem;
                            background: linear-gradient(135deg, #2ECC71, #27AE60);
                            color: white;
                            text-decoration: none;
                            border-radius: 12px;
                            font-weight: 800;
                            font-size: 1.3rem;
                            letter-spacing: 2px;
                            box-shadow: 0 0 40px rgba(46, 204, 113, 0.5);
                            transition: all 0.3s ease;
                            animation: slideDownTitle 0.8s ease 0.3s both;
                        " onmouseover="this.style.boxShadow='0 0 60px rgba(46, 204, 113, 0.7)'; this.style.transform='scale(1.08)';" onmouseout="this.style.boxShadow='0 0 40px rgba(46, 204, 113, 0.5)'; this.style.transform='scale(1)';">
                            Comece Sua Inscrição
                        </a>
                    </div>
                `;
            }
        }

        // Atualizar a cada segundo
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
