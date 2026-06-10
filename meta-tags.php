<?php
/**
 * Meta Tags para SEO e Redes Sociais
 * Incluir este arquivo no <head> de todas as páginas
 */

// Definir meta tags específicas por página (se não definido, usar padrão)
$page_title = $page_title ?? 'Além do Espelho - Evento Transformador';
$page_description = $page_description ?? 'Um encontro transformador que pode mudar toda a sua história. Programa de desenvolvimento pessoal com imersão completa e comunidade acolhedora.';
$page_url = $page_url ?? 'https://alemdoespelho.com.br/';
$page_image = $page_image ?? 'https://alemdoespelho.com.br/assets/og-image.jpg';
$page_type = $page_type ?? 'website';

// Palavras-chave
$keywords = 'evento transformador, desenvolvimento pessoal, retiro, encontro, auto-conhecimento, confronto, máscaras, identidade, comunidade';
?>

<!-- META TAGS ESSENCIAIS PARA SEO -->
<meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta name="keywords" content="<?php echo $keywords; ?>">
<meta name="author" content="Além do Espelho">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">

<!-- OPEN GRAPH (Facebook, WhatsApp, Telegram) -->
<meta property="og:type" content="<?php echo $page_type; ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($page_url); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($page_image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="Além do Espelho">
<meta property="og:locale" content="pt_BR">

<!-- TWITTER CARD -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?php echo htmlspecialchars($page_url); ?>">
<meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($page_image); ?>">

<!-- CANONICAL URL (evitar duplicate content) -->
<link rel="canonical" href="<?php echo htmlspecialchars($page_url); ?>">

<!-- WHATSAPP -->
<meta property="og:phone_number" content="+55 11 99381-3374">

<!-- DUBLIN CORE META TAGS -->
<meta name="DC.title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta name="DC.description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta name="DC.date" content="<?php echo date('Y-m-d'); ?>">
<meta name="DC.creator" content="Além do Espelho">

<!-- MOBILE APP META TAGS -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Além do Espelho">

<!-- FAVICON -->
<link rel="icon" type="image/x-icon" href="https://alemdoespelho.com.br/assets/favicon.ico">
<link rel="apple-touch-icon" href="https://alemdoespelho.com.br/assets/apple-touch-icon.png">

<!-- GOOGLE VERIFICATION (adicione seu código aqui) -->
<!-- <meta name="google-site-verification" content="YOUR_VERIFICATION_CODE_HERE"> -->

<!-- BING VERIFICATION (adicione seu código aqui) -->
<!-- <meta name="msvalidate.01" content="YOUR_VERIFICATION_CODE_HERE"> -->

<!-- SCHEMA.ORG STRUCTURED DATA (JSON-LD) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "LocalBusiness",
  "name": "Além do Espelho",
  "description": "<?php echo htmlspecialchars($page_description); ?>",
  "url": "https://alemdoespelho.com.br/",
  "telephone": "+55 11 99381-3374",
  "image": "https://alemdoespelho.com.br/assets/og-image.jpg",
  "address": {
    "@type": "PostalAddress",
    "addressCountry": "BR",
    "addressLocality": "São Paulo",
    "addressRegion": "SP"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "Customer Service",
    "telephone": "+55 11 99381-3374",
    "email": "contato@alemdoespelho.com.br"
  },
  "sameAs": [
    "https://www.facebook.com/alemdoespelho",
    "https://www.instagram.com/alemdoespelho",
    "https://www.whatsapp.com"
  ]
}
</script>

<!-- PRECONNECT PARA PERFORMANCE -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://www.google-analytics.com">
<link rel="dns-prefetch" href="https://alemdoespelho.mysql.dbaas.com.br">

<!-- SECURITY HEADERS -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="referrer" content="strict-origin-when-cross-origin">
