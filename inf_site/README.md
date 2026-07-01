# Além do Espelho - Site de Inscrições

Site em PHP para inscrição, pagamento e administração do evento **Além do Espelho**.

## Características

- **Home**: Apresentação do evento e inscrições
- **Inscrições**: Separado em `Anfitrião` e `Peregrino`
- **Cadastro**: Coleta nome, telefone, endereço, problemas de saúde (sim/não + detalhes), remédios (sim/não + qual e horários)
- **Pagamento**: Checkout PagBank (substituindo o fluxo antigo de PIX/Cartão)
- **Comprovantes**: Upload de comprovante após inscrição, quando necessário
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
4. Configurar o fluxo de cadastro e pagamento PagBank
5. Solução de problemas

Para a integração do checkout PagBank, consulte também [PAGBANK.md](PAGBANK.md).

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

1. Usuário escolhe o tipo de inscrição: `Anfitrião` ou `Peregrino`
2. Preenche o formulário com nome, telefone, endereço e informações de saúde/remédios
3. Sistema salva a inscrição no banco
4. O usuário é encaminhado para o checkout PagBank
5. O pagamento é processado no ambiente PagBank (sandbox ou produção)
6. O retorno do pagamento atualiza o status da inscrição
7. O admin revisa em `admin.php` e confirma/rejeita
8. Status muda para "confirmado" ou "cancelado"

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
