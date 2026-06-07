// ===== TAB SWITCHING =====
const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');
function setActiveTab(targetId) {
  tabButtons.forEach((button) => {
    button.classList.toggle('active', button.dataset.target === targetId);
  });
  tabContents.forEach((content) => {
    content.classList.toggle('active', content.id === targetId);
  });
}

tabButtons.forEach((button) => {
  button.addEventListener('click', () => setActiveTab(button.dataset.target));
});

const defaultTab = document.querySelector('.tabs-panel')?.dataset.defaultTab || 'Anfitrião';
setActiveTab(defaultTab);

// ===== SCROLL REVEAL ANIMATIONS =====
const revealElements = document.querySelectorAll(
  '.hero-copy, .hero-card, .card, .info-card, .contact-card, .form-card, ' +
  '.section-heading, .glass, .glass-strong, .stat-card, .choice-card, ' +
  '.cards-grid > div, table, .tabs'
);

revealElements.forEach((element) => {
  element.classList.add('reveal');
});

const scrollObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

revealElements.forEach((element) => {
  scrollObserver.observe(element);
});

// ===== TAB SWITCHING (PAYMENT) =====
function switchPaymentTab(tabName, btn) {
  const tabs = document.querySelectorAll('.payment-tab');
  const buttons = document.querySelectorAll('.payment-tab-btn');
  
  tabs.forEach(tab => tab.classList.remove('active'));
  buttons.forEach(b => b.classList.remove('active'));
  
  document.getElementById(tabName)?.classList.add('active');
  btn.classList.add('active');
}

// ===== ACCORDION TOGGLE =====
function toggleAccordion(header) {
  const content = header.nextElementSibling;
  header.classList.toggle('active');
  
  if (header.classList.contains('active')) {
    content.style.maxHeight = content.scrollHeight + 'px';
  } else {
    content.style.maxHeight = '0';
  }
}

// ===== SMOOTH SCROLL PARALLAX EFFECT =====
window.addEventListener('scroll', () => {
  const scrollPos = window.scrollY;
  const heroSection = document.querySelector('.hero, .hero-section');
  
  if (heroSection) {
    heroSection.style.transform = `translateY(${scrollPos * 0.5}px)`;
  }
});

// ===== ANIMATED COUNTER FOR STATS =====
function animateCounter(element, target, duration = 2000) {
  if (element.dataset.animated) return;
  element.dataset.animated = 'true';
  
  let current = 0;
  const increment = target / (duration / 16);
  const timer = setInterval(() => {
    current += increment;
    if (current >= target) {
      element.textContent = target;
      clearInterval(timer);
    } else {
      element.textContent = Math.floor(current);
    }
  }, 16);
}

// Inicia animação de contadores quando entram em vista
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting && entry.target.classList.contains('stat-value')) {
      const targetNum = parseInt(entry.target.textContent.match(/\d+/)?.[0] || 0);
      if (targetNum > 0) {
        animateCounter(entry.target, targetNum);
      }
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-value').forEach(el => counterObserver.observe(el));

// ===== MOUSE GLOW EFFECT =====
document.addEventListener('mousemove', (e) => {
  const glowElements = document.querySelectorAll('.glass-strong, .btn-primary');
  glowElements.forEach(el => {
    const rect = el.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    el.style.setProperty('--mouse-x', `${x}px`);
    el.style.setProperty('--mouse-y', `${y}px`);
  });
});
