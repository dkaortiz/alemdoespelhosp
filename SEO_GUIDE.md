# 📊 Guia de Submissão do Site aos Mecanismos de Busca

## ✅ Já Configurado:

### 1. **robots.txt** ✓
- Localização: `https://alemdoespelho.com.br/robots.txt`
- Permite rastreamento de todos os bots
- Aponta para sitemap.xml

### 2. **sitemap.xml** ✓
- Localização: `https://alemdoespelho.com.br/sitemap.xml`
- Listada todas as páginas principais
- Atualizada dinamicamente com data do dia

### 3. **Meta Tags** ✓
- Open Graph (Facebook, WhatsApp, Telegram)
- Twitter Card
- Schema.org Structured Data (JSON-LD)
- Canonical URLs
- Mobile Meta Tags
- Security Headers

### 4. **Cache Control** ✓
- Headers HTTPS forçado
- Cache inteligente
- Versioning automático de assets

---

## 🔧 PRÓXIMOS PASSOS - Registre seu site:

### **GOOGLE SEARCH CONSOLE** (mais importante)
1. Acesse: https://search.google.com/search-console
2. Clique em "Propriedade"
3. Cole: `https://alemdoespelho.com.br`
4. Escolha um método de verificação:
   - **HTML Tag** (mais fácil): Copie o código em `meta-tags.php` linha ~86
   - **Google Analytics**: Já temos implementado (G-XSSC9E8KB6)
   - **Arquivo HTML**: Fazer upload na raiz
5. Envie seu sitemap: `https://alemdoespelho.com.br/sitemap.xml`
6. Monitore: Cliques, impressões, palavras-chave, erros

**Arquivo a editar:** `/meta-tags.php` linha ~86
```php
<!-- Descomente e adicione seu código:
<meta name="google-site-verification" content="SEU_CODIGO_AQUI">
-->
```

---

### **BING WEBMASTER TOOLS**
1. Acesse: https://www.bing.com/webmaster/home
2. Adicione: `https://alemdoespelho.com.br`
3. Verifique usando o mesmo método do Google
4. Envie sitemap.xml
5. Monitore performance

**Arquivo a editar:** `/meta-tags.php` linha ~88
```php
<!-- Descomente e adicione seu código:
<meta name="msvalidate.01" content="SEU_CODIGO_AQUI">
-->
```

---

### **FACEBOOK DOMAIN VERIFICATION**
1. Acesse: https://www.facebook.com/settings/apps-and-websites
2. Vá para "Configurações" → "Básico"
3. Adicione domínio: `alemdoespelho.com.br`
4. Escolha método: Meta Tag ou arquivo DNS
5. Copie o meta tag e adicione em `meta-tags.php`

**Usar Open Graph tags já existentes em meta-tags.php**

---

### **INSTAGRAM SEO**
1. Crie uma conta empresarial (se não tiver)
2. Vincule seu site no perfil
3. Publique conteúdo com hashtags relevantes:
   - #EventoTransformador
   - #DesenvolvimentoPessoal
   - #AutoConhecimento
   - #Retiro
   - #Comunidade

---

### **YOUTUBE/VIMEO** (se tiver vídeos)
1. Adicione vídeos sobre o evento
2. Inclua links para o site na descrição
3. Use palavras-chave nos títulos e descrições

**Embed de vídeo em meta-tags.php:**
```php
<meta property="og:video" content="https://youtube.com/embed/VIDEO_ID">
```

---

### **LOCALIZAÇÃO (Google Maps)**
1. Acesse: https://www.google.com/business/
2. Reclame seu negócio: "Além do Espelho"
3. Adicione:
   - Endereço (se físico)
   - Telefone: +55 11 99381-3374
   - Horário de funcionamento
   - Fotos
   - Descrição completa

---

## 📱 REDES SOCIAIS - Compartilhamento Automático

Com Open Graph implementado:
- ✅ Facebook: Preview automático
- ✅ WhatsApp: Título, descrição e imagem
- ✅ Telegram: Funcionando
- ✅ Twitter: Card setup completo
- ✅ LinkedIn: Compatível

---

## 🔍 PALAVRAS-CHAVE RECOMENDADAS

Adicione estas no conteúdo:
- "evento transformador"
- "desenvolvimento pessoal"
- "retiro imersivo"
- "auto-conhecimento"
- "confronto emocional"
- "comunidade acolhedora"
- "São Paulo"
- "2026"

---

## 📈 MONITORAMENTO

### Métricas importantes:
- **Google Search Console**: Cliques, impressões, CTR
- **Google Analytics**: Tráfego, sessões, conversões
- **Bing Webmaster**: Compatibilidade
- **Facebook Domain Insights**: Compartilhamentos

---

## ⚠️ IMPORTANTE

Após adicionar códigos de verificação:
1. Limpar cache do site (CTRL+SHIFT+DELETE)
2. Aguardar 24-48h para Google indexar
3. Testar em: https://search.google.com/test/rich-results
4. Validar XML: https://validator.w3.org/
5. Validar OpenGraph: https://opengraph.xyz/

---

## 📋 CHECKLIST

- [ ] Google Search Console registrado
- [ ] Bing Webmaster registrado
- [ ] Facebook Domain verificado
- [ ] Meta tags verificadas no navegador (F12)
- [ ] Sitemap.xml acessível
- [ ] robots.txt acessível
- [ ] HTTPS ativo em todas as páginas
- [ ] Google Analytics funcionando
- [ ] Redes sociais vinculadas
- [ ] Google Local Business preenchido

---

**Próximo passo:** Vá para Google Search Console e adicione o código de verificação!
