<?php
// PHP 8.3 Compatibility - Maintenance Page
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Além do Espelho - Processando</title>
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
            background: linear-gradient(135deg, #0f1419 0%, #1a2332 50%, #0d1621 100%);
            color: #E0E6F0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* BACKGROUND ANIMADO COM NOVO TEMA */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(100, 200, 255, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(150, 100, 255, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 50% 0%, rgba(100, 150, 255, 0.08) 0%, transparent 40%);
            animation: gradientShift 25s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                background: 
                    radial-gradient(circle at 20% 50%, rgba(100, 200, 255, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 80% 80%, rgba(150, 100, 255, 0.12) 0%, transparent 50%),
                    radial-gradient(circle at 50% 0%, rgba(100, 150, 255, 0.08) 0%, transparent 40%);
            }
            50% {
                background: 
                    radial-gradient(circle at 80% 30%, rgba(100, 200, 255, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 20% 70%, rgba(150, 100, 255, 0.12) 0%, transparent 50%),
                    radial-gradient(circle at 50% 100%, rgba(100, 150, 255, 0.08) 0%, transparent 40%);
            }
        }

        /* PARTÍCULAS FLUTUANTES */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(100, 200, 255, 0.6);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                opacity: 0;
                transform: translateY(100vh) translateX(0);
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-100vh) translateX(50px);
            }
        }

        /* ÍCONES FLUTUANTES FESTIVOS */
        .floating-icons {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-icon {
            position: absolute;
            font-size: 3rem;
            opacity: 0.3;
            animation: float-icon 20s infinite ease-in-out;
        }

        @keyframes float-icon {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0.5);
                opacity: 0;
            }
            10% {
                opacity: 0.4;
            }
            90% {
                opacity: 0.4;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg) scale(1);
                opacity: 0;
            }
        }

        /* SPIN ANIMATION PARA ÍCONES */
        .icon-spin {
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* BOUNCE ANIMATION */
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            50% {
                transform: translateY(-20px) translateX(-50%);
            }
        }

        @keyframes bounce-left {
            0%, 100% {
                transform: translateY(-50%);
            }
            50% {
                transform: translateX(-20px) translateY(-50%);
            }
        }

        /* HEADER */
        header {
            position: relative;
            z-index: 100;
            padding: 2rem 1rem;
            text-align: center;
            backdrop-filter: blur(10px);
            background: rgba(15, 20, 25, 0.4);
            border-bottom: 1px solid rgba(100, 200, 255, 0.1);
        }

        .site-brand {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(90deg, #64C8FF 0%, #6B7FFF 50%, #A855F7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
            filter: drop-shadow(0 0 15px rgba(100, 200, 255, 0.3));
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
            max-width: 1100px;
            animation: fadeInScale 1s ease;
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

        /* LOADING SPINNER PRINCIPAL */
        .loading-spinner {
            width: 280px;
            height: 280px;
            margin: 0 auto 2rem;
            position: relative;
            animation: spinRotate 3s linear infinite;
        }

        @keyframes spinRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #64C8FF;
            border-right-color: #6B7FFF;
            border-radius: 50%;
            animation: spinRotate 2s linear infinite;
        }

        .spinner-circle:nth-child(2) {
            width: 85%;
            height: 85%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-top-color: #A855F7;
            border-left-color: #64C8FF;
            animation: spinRotate 2.5s linear infinite reverse;
        }

        .spinner-circle:nth-child(3) {
            width: 70%;
            height: 70%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-bottom-color: #6B7FFF;
            border-right-color: #A855F7;
            animation: spinRotate 3s linear infinite;
        }

        .spinner-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, rgba(100, 200, 255, 0.3), transparent);
            border-radius: 50%;
            border: 2px solid rgba(100, 200, 255, 0.5);
            animation: pulsCenter 1.5s ease-in-out infinite;
        }

        @keyframes pulsCenter {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.5;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 1;
            }
        }

        /* TÍTULO */
        .coming-soon-container h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #E0E6F0;
            font-weight: 800;
            letter-spacing: 2px;
            animation: slideDownTitle 0.8s ease 0.1s both;
            background: linear-gradient(90deg, #64C8FF, #6B7FFF, #A855F7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        /* SUBTITLE */
        .subtitle {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 1rem 0 2.5rem;
            background: linear-gradient(90deg, #64C8FF 0%, #6B7FFF 50%, #A855F7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 1px;
            animation: slideDownTitle 0.8s ease 0.2s both;
            filter: drop-shadow(0 0 20px rgba(100, 200, 255, 0.3));
        }

        /* PROCESSING ITEMS */
        .processing-items {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin: 3rem 0;
            animation: slideDownTitle 0.8s ease 0.3s both;
        }

        .process-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: rgba(100, 200, 255, 0.08);
            border: 2px solid rgba(100, 200, 255, 0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .process-item:hover {
            background: rgba(100, 200, 255, 0.15);
            border-color: rgba(100, 200, 255, 0.4);
            transform: translateX(10px);
        }

        .process-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #64C8FF, #6B7FFF);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(100, 200, 255, 0.5); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(100, 200, 255, 0); }
        }

        .process-text {
            text-align: left;
        }

        .process-text h3 {
            font-size: 1.1rem;
            color: #E0E6F0;
            margin-bottom: 0.3rem;
        }

        .process-text p {
            font-size: 0.95rem;
            color: #A0AEC0;
            line-height: 1.4;
        }

        /* COUNTDOWN */
        .countdown {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin: 4rem 0;
            padding: 3rem;
            background: rgba(100, 200, 255, 0.08);
            border: 2px solid rgba(100, 200, 255, 0.2);
            border-radius: 20px;
            backdrop-filter: blur(15px);
            box-shadow: 0 0 50px rgba(100, 200, 255, 0.15);
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
            gap: 0.8rem;
        }

        .countdown-number {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(180deg, #64C8FF 0%, #6B7FFF 50%, #A855F7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            min-height: 5.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulseGlow 1.5s ease-in-out infinite;
            font-variant-numeric: tabular-nums;
            filter: drop-shadow(0 0 20px rgba(100, 200, 255, 0.4));
        }

        @keyframes pulseGlow {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 15px rgba(100, 200, 255, 0.3));
            }
            50% {
                transform: scale(1.08);
                filter: drop-shadow(0 0 40px rgba(100, 200, 255, 0.6));
            }
        }

        .countdown-label {
            font-size: 1rem;
            color: #64C8FF;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.4);
        }

        /* MENSAGEM */
        .deadline-msg {
            color: #A0AEC0;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            letter-spacing: 0.5px;
            animation: slideDownTitle 0.8s ease 0.5s both;
            line-height: 1.6;
        }

        .deadline-msg strong {
            color: #64C8FF;
            font-weight: 700;
        }

        /* FOOTER */
        footer {
            position: relative;
            z-index: 100;
            background: rgba(15, 20, 25, 0.6);
            border-top: 1px solid rgba(100, 200, 255, 0.1);
            padding: 2rem;
            text-align: center;
            color: #6B7280;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
        }

        footer p {
            margin-bottom: 0.5rem;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.2rem;
                padding: 2rem;
                margin: 3rem 0;
            }

            .countdown-number {
                font-size: 3rem;
            }

            .countdown-label {
                font-size: 0.9rem;
            }

            .processing-items {
                gap: 1rem;
                margin: 2rem 0;
            }
        }

        @media (max-width: 768px) {
            .loading-spinner {
                width: 200px;
                height: 200px;
                margin-bottom: 1.5rem;
            }

            .coming-soon-container h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.5rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                padding: 1.5rem;
                margin: 2rem 0;
            }

            .countdown-number {
                font-size: 2.5rem;
                min-height: 3.5rem;
            }

            .countdown-label {
                font-size: 0.85rem;
            }

            .process-item {
                padding: 1rem;
            }

            .process-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .deadline-msg {
                font-size: 1rem;
            }
        }

        @media (max-width: 600px) {
            .loading-spinner {
                width: 160px;
                height: 160px;
                margin-bottom: 1.2rem;
            }

            .coming-soon-container h1 {
                font-size: 1.6rem;
                margin-bottom: 0.3rem;
            }

            .subtitle {
                font-size: 1.3rem;
                margin: 0.8rem 0 1.5rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
                padding: 1.2rem;
                margin: 1.5rem 0;
                border-radius: 15px;
            }

            .countdown-number {
                font-size: 2rem;
                min-height: 2.5rem;
            }

            .countdown-label {
                font-size: 0.75rem;
            }

            .process-item {
                padding: 1rem;
                gap: 1rem;
            }

            .process-text h3 {
                font-size: 1rem;
            }

            .process-text p {
                font-size: 0.9rem;
            }

            .deadline-msg {
                font-size: 0.95rem;
                margin-bottom: 1.5rem;
            }

            .processing-items {
                gap: 0.8rem;
                margin: 1.5rem 0;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1rem;
            }

            header {
                padding: 1.2rem;
            }

            .site-brand {
                font-size: 1.1rem;
            }

            .loading-spinner {
                width: 140px;
                height: 140px;
                margin-bottom: 1rem;
            }

            .coming-soon-container {
                max-width: 95%;
            }

            .coming-soon-container h1 {
                font-size: 1.4rem;
            }

            .subtitle {
                font-size: 1.2rem;
                margin: 0.6rem 0 1.2rem;
            }

            .countdown {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.6rem;
                padding: 1rem;
                margin: 1.2rem 0;
            }

            .countdown-number {
                font-size: 1.8rem;
                min-height: 2rem;
            }

            .countdown-label {
                font-size: 0.7rem;
                letter-spacing: 1px;
            }

            .process-item {
                padding: 0.8rem;
                gap: 0.8rem;
            }

            .process-icon {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .process-text h3 {
                font-size: 0.95rem;
            }

            .process-text p {
                font-size: 0.85rem;
            }

            .deadline-msg {
                font-size: 0.9rem;
            }

            footer {
                padding: 1.2rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 360px) {
            .loading-spinner {
                width: 120px;
                height: 120px;
            }

            .coming-soon-container h1 {
                font-size: 1.2rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .countdown-number {
                font-size: 1.5rem;
            }

            .countdown-label {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>
    <!-- BACKGROUND ANIMADO -->
    <div class="bg-animated"></div>

    <!-- PARTÍCULAS -->
    <div class="particles" id="particles-container"></div>

    <!-- ÍCONES FLUTUANTES FESTIVOS -->
    <div class="floating-icons" id="floating-icons-container"></div>

    <!-- HEADER -->
    <header>
        <div class="site-brand">✨ ALÉM DO ESPELHO - AJUSTES EM ANDAMENTO ✨</div>
    </header>

    <!-- MAIN CONTENT -->
    <main>
        <div class="coming-soon-container">
            <!-- LOADING SPINNER COM ÍCONES FESTIVOS -->
            <div style="position: relative; display: inline-block; margin: 0 auto 2rem;">
                <div class="loading-spinner">
                    <div class="spinner-circle"></div>
                    <div class="spinner-circle"></div>
                    <div class="spinner-circle"></div>
                    <div class="spinner-center"></div>
                </div>
                <!-- ÍCONES DECORATIVOS ANIMADOS AO REDOR DO SPINNER -->
                <div style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); font-size: 2.5rem; animation: bounce 1s ease-in-out infinite;">🎉</div>
                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); font-size: 2.5rem; animation: bounce 1s ease-in-out infinite 0.2s;">🎊</div>
                <div style="position: absolute; top: 50%; right: -50px; transform: translateY(-50%); font-size: 2.5rem; animation: bounce 1s ease-in-out infinite 0.4s;">⏰</div>
                <div style="position: absolute; top: 50%; left: -50px; transform: translateY(-50%); font-size: 2.5rem; animation: bounce 1s ease-in-out infinite 0.6s;">✨</div>
            </div>

            <h1>Processando Atualizações</h1>
            <p class="subtitle">🔧 Sistema em Manutenção 🔧</p>

            <!-- PROCESSING ITEMS -->
            <div class="processing-items">
                <div class="process-item">
                    <div class="process-icon">💳</div>
                    <div class="process-text">
                        <h3>Processamento de Pagamentos</h3>
                        <p>Otimizando sistema de cobranças e formas de pagamento</p>
                    </div>
                </div>

                <div class="process-item">
                    <div class="process-icon">🔐</div>
                    <div class="process-text">
                        <h3>Validação de Segurança</h3>
                        <p>Aprimorando protocolos de autenticação e dados</p>
                    </div>
                </div>

                <div class="process-item">
                    <div class="process-icon">⚙️</div>
                    <div class="process-text">
                        <h3>Configuração de Servidores</h3>
                        <p>Estruturando infraestrutura para melhor desempenho</p>
                    </div>
                </div>

                <div class="process-item">
                    <div class="process-icon">✅</div>
                    <div class="process-text">
                        <h3>Testes Finais</h3>
                        <p>Validando todas as funcionalidades da plataforma</p>
                    </div>
                </div>
            </div>

            <!-- COUNTDOWN -->
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
                Voltaremos com tudo em <strong>sábado, 13 de junho de 2026 às 12:00</strong> (Horário de Brasília) <br>
                Inscrições abertas e sistema operacional!
            </p>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 Além do Espelho. Todos os direitos reservados.</p>
        <p>Obrigado pela paciência enquanto otimizamos sua experiência.</p>
    </footer>

    <script>
        // CRIAR ÍCONES FLUTUANTES FESTIVOS
        function createFloatingIcons() {
            const container = document.getElementById('floating-icons-container');
            const icons = ['🎉', '🎊', '⏰', '✨', '🎁', '🌟', '💫', '🔧', '⚙️', '🎯'];
            const iconCount = window.innerWidth > 768 ? 20 : 10;

            for (let i = 0; i < iconCount; i++) {
                const icon = document.createElement('div');
                icon.className = 'floating-icon';
                icon.textContent = icons[Math.floor(Math.random() * icons.length)];
                icon.style.left = Math.random() * 100 + '%';
                icon.style.animationDuration = (Math.random() * 15 + 20) + 's';
                icon.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(icon);
            }
        }

        // GERAR PARTÍCULAS FLUTUANTES
        function createParticles() {
            const container = document.getElementById('particles-container');
            const particleCount = window.innerWidth > 768 ? 30 : 15;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = (Math.random() * 3 + 2) + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.opacity = Math.random() * 0.5 + 0.3;
                container.appendChild(particle);
            }
        }

        // CRIAR CONFETES FESTIVOS
        function createConfetti() {
            const canvas = document.createElement('canvas');
            canvas.id = 'confetti-canvas';
            canvas.style.position = 'fixed';
            canvas.style.top = '0';
            canvas.style.left = '0';
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            canvas.style.pointerEvents = 'none';
            canvas.style.zIndex = '1000';
            document.body.appendChild(canvas);

            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const confettis = [];
            const confettiCount = 150;
            const colors = ['#64C8FF', '#6B7FFF', '#A855F7', '#FFD700', '#FF69B4', '#00FF88'];

            class Confetti {
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height - canvas.height;
                    this.size = Math.random() * 5 + 3;
                    this.speedX = Math.random() * 8 - 4;
                    this.speedY = Math.random() * 5 + 5;
                    this.rotation = Math.random() * 360;
                    this.color = colors[Math.floor(Math.random() * colors.length)];
                    this.opacity = 1;
                }

                update() {
                    this.x += this.speedX;
                    this.y += this.speedY;
                    this.rotation += 5;
                    this.opacity -= 0.01;
                }

                draw() {
                    ctx.save();
                    ctx.globalAlpha = this.opacity;
                    ctx.fillStyle = this.color;
                    ctx.translate(this.x, this.y);
                    ctx.rotate((this.rotation * Math.PI) / 180);
                    ctx.fillRect(-this.size / 2, -this.size / 2, this.size, this.size);
                    ctx.restore();
                }
            }

            for (let i = 0; i < confettiCount; i++) {
                confettis.push(new Confetti());
            }

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                confettis.forEach((conf, index) => {
                    conf.update();
                    conf.draw();
                    
                    if (conf.opacity <= 0 || conf.y > canvas.height) {
                        confettis.splice(index, 1);
                    }
                });

                if (confettis.length > 0) {
                    requestAnimationFrame(animate);
                } else {
                    canvas.remove();
                }
            }

            animate();
        }

        // COUNTDOWN
        function updateCountdown() {
            // Data alvo: 13 de junho de 2026 às 12:00 (BRT = UTC-3)
            const targetDate = new Date('2026-06-13T12:00:00-03:00').getTime();
            
            const now = new Date().getTime();
            const distance = targetDate - now;

            // Se chegou ao zero
            if (distance <= 0) {
                // Criar confetes festivos
                for (let i = 0; i < 5; i++) {
                    setTimeout(() => createConfetti(), i * 200);
                }
                
                // Redirecionar após um tempo
                setTimeout(() => {
                    window.location.href = 'old-index.php';
                }, 3000);
                
                return;
            }

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
        }

        // Inicializar
        createFloatingIcons();
        createParticles();
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
<?php // PHP 8.3 Compatibility - End of document
