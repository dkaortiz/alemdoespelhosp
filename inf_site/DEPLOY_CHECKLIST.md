# Checklist de Deploy

Siga este checklist antes de fazer upload para a hospedagem Locaweb.

## ✅ Preparação local

- [ ] Verificar que todos os arquivos `.php` estão na raiz (não em subpastas)
- [ ] Pasta `uploads/` criada na raiz
- [ ] Arquivo `config.php` contém credenciais corretas
- [ ] Arquivo `.vscode/sftp.json` com dados FTP corretos
- [ ] `INSTALACAO.md` atualizado com instruções
- [ ] `README.md` na raiz com estrutura do projeto

## ✅ Banco de dados

### Campos novos do formulário

- [ ] Adicionar campos para endereço, problema de saúde e remédios no formulário de inscrição
- [ ] Confirmar que o backend salva esses dados na tabela correspondente
- [ ] Validar o fluxo para `Anfitrião` e `Peregrino`

### Antes de fazer upload

- [ ] Acessar PhpMyAdmin da Locaweb
- [ ] Executar SQL do arquivo `inf_site/db.sql`
- [ ] Verificar que 3 tabelas foram criadas: `Anfitrião`, `Peregrino`, `edicoes`
- [ ] Testar conexão MySQL: `mysql -u alemdoespelho -p -h alemdoespelho.mysql.dbaas.com.br site`

### Credenciais MySQL

```
Host: alemdoespelho.mysql.dbaas.com.br
Usuário: alemdoespelho
Senha: IPM@1347New
Banco: site
```

## ✅ PagBank Checkout

- [ ] Criar app no portal do PagBank
- [ ] Habilitar ambiente `sandbox`
- [ ] Gerar `client_id` e `client_secret`
- [ ] Configurar URL de retorno e webhook
- [ ] Inserir credenciais no `config.php`
- [ ] Validar um pagamento de teste no checkout PagBank
- [ ] Trocar para `production` somente após testes aprovados

## ✅ Upload SFTP

### Via VS Code SFTP

- [ ] Instalar extensão SFTP (`Natizyskunk.sftp`)
- [ ] Clicar botão direito em `alemdoespelho/` → "Upload Folder to SFTP Server"
- [ ] Aguardar conclusão (pode levar alguns minutos)

### Via FileZilla ou cliente FTP

- [ ] Conectar com credenciais:
  - Host: `ftp.alemdoespelhosp.com.br`
  - Usuário: `alemdoespelhosp1`
  - Senha: `01062021Midi@@`
  - Porta: `21`
- [ ] Navegar para `/home/alemdoespelhosp1/`
- [ ] Fazer upload de todos os arquivos raiz (exceto `inf_site/`)
- [ ] Fazer upload da pasta `uploads/` (vazia)

## ✅ Verificação pós-upload

### Arquivos no servidor

Verificar que estes arquivos existem em `/home/alemdoespelhosp1/`:

```
index.php
payment.php
payment_confirm.php
regras.php
admin.php
admin_action.php
submit.php
config.php
style.css
script.js
Panfleto.jpg
uploads/  (pasta)
```

### Testar URLs

- [ ] https://alemdoespelhosp.com.br/ — carrega página inicial
- [ ] https://alemdoespelhosp.com.br/regras.php — carrega regras
- [ ] https://alemdoespelhosp.com.br/admin.php — carrega admin (com login)

### Testar fluxo completo

1. [ ] Abrir https://alemdoespelhosp.com.br/
2. [ ] Escolher inscrição de `Anfitrião` ou `Peregrino`
3. [ ] Preencher nome, telefone, endereço e dados de saúde/remédios
4. [ ] Submeter formulário
5. [ ] Verificar se o fluxo redireciona para o checkout PagBank
6. [ ] Validar a transação de teste no ambiente sandbox
7. [ ] Verificar retorno e atualização do status da inscrição
8. [ ] Acessar admin.php (usuário: `admin`, senha: `SenhaAdmin@2026`)
9. [ ] Verificar se inscrição aparece na tabela
10. [ ] Clicar em "Confirmar" ou "Rejeitar" e verificar se status muda

## ✅ Permissões no servidor

- [ ] Pasta `uploads/` com permissões `755` ou `775` (deve ser gravável)
  - Pode ser feito via FTP: clique direito na pasta → Propriedades → Modo: 755

## ✅ Configurações opcionais

### Se precisar alterar valores

Editar `config.php` no servidor:

- **Valor da inscrição**: linha `define('PAYMENT_AMOUNT', 150.00);`
- **Telefone PIX**: linha `define('PIX_PHONE', '11993813374');`
- **Credenciais admin**: linhas `define('ADMIN_USER', 'admin');` e `define('ADMIN_PASS', '...');`
- **Conexão MySQL**: linhas de `mysqli_connect()`

### Depois de alterar

- [ ] Limpar cache do navegador (Ctrl+Shift+Del)
- [ ] Abrir site em janela anônima para validar

## ✅ Troubleshooting

### Erro "Connection refused" no MySQL

- [ ] Verificar se Locaweb liberou acesso remoto ao MySQL
- [ ] Contatar suporte Locaweb e pedir para liberar acesso

### Erro 500 ao enviar comprovante

- [ ] SSH para servidor e executar: `chmod 755 /home/alemdoespelhosp1/uploads`
- [ ] Verificar logs de erro: `/home/alemdoespelhosp1/logs/` ou `/home/alemdoespelhosp1/error_log`

### QR Code PIX não aparece

- [ ] Verificar se está usando HTTPS (necessário para algumas APIs)
- [ ] Verificar console do navegador (F12) para erros de rede

### Admin login não funciona

- [ ] Verificar credenciais em `config.php`
- [ ] Verificar se sessões estão habilitadas no servidor
- [ ] Tentar acessar `/admin.php` em navegador anônimo

## ✅ Próximos passos pós-launch

- [ ] Informar URL do site em redes sociais
- [ ] Começar a receber inscrições
- [ ] Verificar painel admin regularmente para aprovar pagamentos
- [ ] Responder dúvidas de inscritos
- [ ] Após evento: fazer backup da base de dados

---

**Dúvidas?** Consulte [INSTALACAO.md](INSTALACAO.md) ou [README.md](README.md)
