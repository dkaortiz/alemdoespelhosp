## 🗂️ INSTRUÇÕES PASSO A PASSO - EXECUTAR SQL

### ⚠️ NOTA IMPORTANTE SOBRE O ERRO:

O erro que você recebeu:
```
Error Code: 1064. You have an error in your SQL syntax; 
check the manual that corresponds to your MySQL server version 
for the right syntax to use near 'IF NOT EXISTS pagbank_payment_link VARCHAR(500)'
```

**Motivo:** MySQL não suporta `IF NOT EXISTS` em comando `ALTER TABLE`.

**Solução:** Use a sintaxe correta (sem IF NOT EXISTS).

---

### ✅ MÉTODO 1: Via Terminal (Linux/Mac)

```bash
# Acesse a pasta do projeto
cd /Users/davidortiz/Documents/Retiro-Site\ -\ Novo/alemdoespelhosp

# Execute o arquivo SQL
mysql -u root -p alemdoespelho < inf_site/ALTER_ADD_PAYMENT_LINK.sql
```

Digite sua senha MySQL quando pedido.

---

### ✅ MÉTODO 2: Via phpMyAdmin

1. Acesse seu phpMyAdmin
2. Clique em **alemdoespelho** (seu banco)
3. Vá para **SQL** (no menu superior)
4. **Cole este código** (execute um por um se der erro):

```sql
ALTER TABLE peregrinos 
ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL COMMENT 'Link de pagamento PagBank utilizado';

ALTER TABLE peregrinos 
ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL COMMENT 'Tipo: peregrino ou anfitriao';

ALTER TABLE anfitrioes 
ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL COMMENT 'Link de pagamento PagBank utilizado';

ALTER TABLE anfitrioes 
ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL COMMENT 'Tipo: peregrino ou anfitriao';

ALTER TABLE peregrinos ADD INDEX idx_payment_link_type (payment_link_type);
ALTER TABLE anfitrioes ADD INDEX idx_payment_link_type (payment_link_type);
```

5. Clique em **Executar**

---

### ✅ MÉTODO 3: Via CLI MySQL (Mais Seguro)

```bash
# Conecte ao MySQL
mysql -u root -p

# Digite sua senha e pressione Enter

# Selecione o banco
USE alemdoespelho;

# Cole cada comando:
ALTER TABLE peregrinos ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL;
ALTER TABLE peregrinos ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL;
ALTER TABLE anfitrioes ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL;
ALTER TABLE anfitrioes ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL;

# Verifique se funcionou:
DESC peregrinos;
DESC anfitrioes;

# Sair
EXIT;
```

---

### ✅ MÉTODO 4: Se Já Existe a Coluna (Evita Erro)

Se a coluna já existe e você quer ignorar o erro:

```sql
-- Verificar se coluna já existe (retorna 1 se existe, 0 se não)
SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'peregrinos' AND COLUMN_NAME = 'pagbank_payment_link';

-- Se não existir (resultado = 0), execute:
ALTER TABLE peregrinos ADD COLUMN pagbank_payment_link VARCHAR(500);
ALTER TABLE peregrinos ADD COLUMN payment_link_type VARCHAR(20);
ALTER TABLE anfitrioes ADD COLUMN pagbank_payment_link VARCHAR(500);
ALTER TABLE anfitrioes ADD COLUMN payment_link_type VARCHAR(20);
```

---

### 🔍 VERIFICAR SE FUNCIONOU:

```sql
-- Execute para confirmar as colunas foram criadas
DESCRIBE peregrinos;
DESCRIBE anfitrioes;

-- Deve aparecer as duas colunas novas:
-- pagbank_payment_link | varchar(500)
-- payment_link_type    | varchar(20)
```

---

### ❌ ERROS COMUNS E SOLUÇÕES:

| Erro | Solução |
|------|---------|
| `IF NOT EXISTS` não funciona | Use a sintaxe sem `IF NOT EXISTS` |
| Coluna já existe | Use `ADD COLUMN IF NOT EXISTS` ❌ ou execute `SELECT` para verificar antes |
| Access Denied | Verifique usuário/senha MySQL |
| Database not found | Use `USE alemdoespelho;` antes |
| Syntax error | Copie e cole cada linha separada |

---

**Após executar o SQL, os arquivos PHP já estão atualizados e prontos para usar!**
