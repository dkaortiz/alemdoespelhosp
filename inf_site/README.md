# Além do Espelho - Site de Inscrições

Site em PHP para inscrição, pagamento e administração do evento **Além do Espelho**.

## Características

- **Home**: Apresentação do evento e inscrições
- **Inscrições**: Separado em `Anfitrião` (sem limite) e `Peregrino` (15 homens + 15 mulheres)
- **Pagamento**: Via PIX (QR code) ou cartão de crédito
- **Comprovantes**: Upload de comprovante após inscrição
- **Painel Admin**: Gerenciamento de pagamentos e confirmações
- **Regras**: Página dedicada com políticas do evento
- **Responsive**: Funciona em mobile, tablet e desktop
- **Animações**: Efeitos visuais e transições suaves

## Estrutura do projeto

```
alemdoespelho/
├── .vscode/
│   └── sftp.json           (configuração SFTP para upload)
├── inf_site/               (documentação e dados do servidor)
│   ├── db.sql              (schema do banco)
│   ├── inf.inf             (credenciais e informações)
│   └── uploads/            (comprovantes de pagamento)
├── index.php               (página inicial)
├── payment.php             (tela de pagamento)
├── payment_confirm.php     (recebimento de comprovantes)
├── regras.php              (página de regras)
├── admin.php               (painel admin)
├── admin_action.php        (ações do admin)
├── submit.php              (inscrições)
├── config.php              (configuração)
├── style.css               (estilos)
├── script.js               (interatividade)
├── Panfleto.jpg            (imagem)
└── README.md               (este arquivo)
```

## Como instalar

Veja [INSTALACAO.md](INSTALACAO.md) para instruções detalhadas:
1. Configurar banco de dados
2. Upload via SFTP/FTP
3. Acessar o site
4. Solução de problemas

## Arquivos de produção

Estes arquivos devem estar na raiz `/home/alemdoespelhosp1/` da hospedagem:

- `index.php`
- `payment.php`
- `payment_confirm.php`
- `regras.php`
- `admin.php`
- `admin_action.php`
- `submit.php`
- `config.php`
- `style.css`
- `script.js`
- `Panfleto.jpg`
- `uploads/` (pasta)

## Dados de acesso

- **FTP**: `ftp.alemdoespelhosp.com.br` (porta 21)
  - Usuário: `alemdoespelhosp1`
  - Senha: `01062021Midi@@`
- **Banco**: `alemdoespelho.mysql.dbaas.com.br`
  - Usuário: `alemdoespelho`
  - Senha: `IPM@1347New`
  - Banco: `site`
- **Domínio**: `alemdoespelhosp.com.br`

## Fluxo de pagamento

1. Usuário se inscreve em `index.php`
2. Sistema salva inscrição no banco
3. Redireciona para `payment.php` com ID da inscrição
4. Usuário vê QR code PIX (para `11993813374`) ou cartão
5. Envia comprovante em `payment_confirm.php`
6. Admin revisa em `admin.php` e confirma/rejeita
7. Status muda para "confirmado" ou "cancelado"

## Valores

- **Peregrino**: R$ 150,00
- **Anfitrião**: R$ 150,00

## Credenciais padrão

**Admin**
- Usuário: `admin`
- Senha: `SenhaAdmin@2026`

**PIX**
- Telefone: `11993813374`

---

**Versão 1.0** | 2026
