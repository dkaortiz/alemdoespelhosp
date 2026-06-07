# Instalação - Além do Espelho

## Estrutura do projeto

```
alemdoespelho/
├── .vscode/
│   └── sftp.json           (configuração SFTP para upload via VS Code)
├── inf_site/
│   ├── db.sql              (schema do banco de dados)
│   ├── inf.inf             (informações de acesso FTP e banco)
│   └── uploads/            (pasta para comprovantes de pagamento)
├── index.php               (página principal)
├── payment.php             (tela de pagamento e confirmação)
├── regras.php              (página de regras)
├── admin.php               (painel administrativo)
├── admin_action.php        (backend de ações do admin)
├── submit.php              (processamento de inscrições)
├── payment_confirm.php     (recebimento de comprovantes)
├── config.php              (configuração de banco e constantes)
├── style.css               (estilos)
├── script.js               (animações e interatividade)
├── Panfleto.jpg            (imagem de referência)
└── README.md               (este arquivo)
```

## 1. Configurar banco de dados

Acesse o painel da Locaweb e execute o SQL em `inf_site/db.sql`:

```bash
mysql -u alemdoespelho -p -h alemdoespelho.mysql.dbaas.com.br site < inf_site/db.sql
```

Dados:
- Host: `alemdoespelho.mysql.dbaas.com.br`
- Usuário: `alemdoespelho`
- Senha: `IPM@1347New`
- Banco: `site`

## 2. Fazer upload via SFTP/FTP

### Opção A: Usar VS Code + SFTP Extension

1. Instale a extensão SFTP do VS Code (búsque "SFTP" ou instale `Natizyskunk.sftp`)
2. A configuração já está em `.vscode/sftp.json`
3. Clique com o botão direito na pasta raiz e selecione "Upload Folder to SFTP Server"

### Opção B: Usar cliente FTP manual

Use um cliente FTP (como FileZilla) com:
- Host: `ftp.alemdoespelhosp.com.br`
- Usuário: `alemdoespelhosp1`
- Senha: `01062021Midi@@`
- Porta: `21`
- Caminho: `/home/alemdoespelhosp1/`

Faça upload de **todos os arquivos da raiz** (não da subpasta `inf_site`):
- `*.php`
- `*.css`
- `*.js`
- `*.jpg`
- `uploads/` (criar pasta vazia se não existir)

## 3. Acessar o site

Após upload:
- **Home**: `https://alemdoespelhosp.com.br/`
- **Inscrição**: `https://alemdoespelhosp.com.br/`
- **Regras**: `https://alemdoespelhosp.com.br/regras.php`
- **Admin**: `https://alemdoespelhosp.com.br/admin.php`
  - Usuário: `admin`
  - Senha: `SenhaAdmin@2026`

## 4. Alterações importantes

### config.php

Se precisar alterar:
- **PIX Phone**: `$PIX_PHONE = '11993813374';`
- **Valor da inscrição**: `$PAYMENT_AMOUNT = 150.00;`
- **Credenciais admin**: `$ADMIN_USER = 'admin';` e `$ADMIN_PASS = 'SenhaAdmin@2026';`

### Estrutura de pastas do servidor

```
/home/alemdoespelhosp1/
├── index.php
├── payment.php
├── admin.php
├── ...todos os .php
├── style.css
├── script.js
├── Panfleto.jpg
└── uploads/    (deve existir e ser gravável)
```

## Solução de problemas

- **Erro de conexão ao banco**: Verifique se a Locaweb liberou acesso remoto ao MySQL
- **Erro 500 no upload**: Verifique se a pasta `uploads/` tem permissões de escrita (755)
- **QR Code PIX não aparece**: Use HTTPS, algumas APIs de QR exigem protocolo seguro

## Próximos passos

1. Testar inscrição completa
2. Confirmar pagamento no painel admin
3. Validar envio de comprovantes
4. Configurar emails automáticos (opcional)
