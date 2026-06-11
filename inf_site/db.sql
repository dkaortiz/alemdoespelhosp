-- Schema para Além do Espelho - Versão 2.0
-- Refatorado com Peregrinos e Anfitriões
-- Atualização 11/06/2026: Datas formatadas em português com visual destacado

USE alemdoespelho;

-- TABELA: PEREGRINOS (Peregrino com limite de 15 homens + 15 mulheres)
CREATE TABLE IF NOT EXISTS peregrinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(50),
    whatsapp VARCHAR(50),
    genero ENUM('masculino', 'feminino') NOT NULL,
    categoria VARCHAR(100),
    payment_method ENUM('pix', 'cartao') NOT NULL DEFAULT 'pix',
    payment_status ENUM('pendente', 'comprovante_enviado', 'confirmado', 'cancelado') NOT NULL DEFAULT 'pendente',
    payment_amount DECIMAL(8,2) DEFAULT 150.00,
    pix_cents INT COMMENT 'Centavos para identificar no PIX (ex: 24 = R$ 150,24)',
    payment_receipt VARCHAR(255),
    payment_confirmed_by VARCHAR(100),
    payment_confirmed_at DATETIME,
    valor DECIMAL(8,2) DEFAULT 150.00,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (payment_status),
    INDEX idx_genero (genero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA: ANFITRIÕES (Sem limite de vagas)
CREATE TABLE IF NOT EXISTS anfitrioes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(50),
    whatsapp VARCHAR(50),
    funcao VARCHAR(255),
    peregrino_anterior BOOLEAN DEFAULT FALSE COMMENT 'Se foi peregrino em edições anteriores',
    payment_method ENUM('pix', 'cartao') NOT NULL DEFAULT 'pix',
    payment_status ENUM('pendente', 'comprovante_enviado', 'confirmado', 'cancelado') NOT NULL DEFAULT 'pendente',
    payment_amount DECIMAL(8,2) DEFAULT 150.00,
    pix_cents INT COMMENT 'Centavos para identificar no PIX (ex: 24 = R$ 150,24)',
    payment_receipt VARCHAR(255),
    payment_confirmed_by VARCHAR(100),
    payment_confirmed_at DATETIME,
    valor DECIMAL(8,2) DEFAULT 150.00,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA: EDIÇÕES
-- Campos de data (data_inicio, data_fim) são armazenados em formato YYYY-MM-DD
-- e exibidos em português via função formatDatePT() em config.php
-- Exemplo: 2026-06-15 é exibido como "15 de junho de 2026"
CREATE TABLE IF NOT EXISTS edicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL COMMENT 'Título da edição (exibido em index.php, edicoes.php)',
    descricao TEXT COMMENT 'Descrição da edição (exibida em index.php)',
    ano INT NOT NULL UNIQUE COMMENT 'Ano do evento (identificador único)',
    data_inicio DATE COMMENT 'Data de início (formatada em PT: DD de MÊS de YYYY)',
    data_fim DATE COMMENT 'Data de término (formatada em PT: DD de MÊS de YYYY)',
    local VARCHAR(255) COMMENT 'Local/Cidade do evento (exibida em card destacado)',
    limite_homens INT DEFAULT 15 COMMENT 'Limite de vagas masculinas para esta edição',
    limite_mulheres INT DEFAULT 15 COMMENT 'Limite de vagas femininas para esta edição',
    limite_anfitrioes INT DEFAULT 999 COMMENT 'Limite de anfitriões (geralmente sem limite)',
    data_inscricao_inicio DATE COMMENT 'Data de início das inscrições (formatada em PT: DD de MÊS de YYYY)',
    data_inscricao_fim DATE COMMENT 'Data de término das inscrições (formatada em PT: DD de MÊS de YYYY)',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ano (ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir edição inicial
INSERT IGNORE INTO edicoes (titulo, descricao, ano, data_inicio, data_fim, local, data_inscricao_inicio, data_inscricao_fim) VALUES 
('O Confronto', 'Um encontro que pode mudar toda a sua história', 2026, '2026-06-15', '2026-06-16', 'São Paulo - SP', '2026-06-02', '2026-06-30');

-- TABELA: ADMINS (para futuras migrações - credenciais podem ser migradas do config.php)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role ENUM('super','editor') DEFAULT 'super',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exemplo: inserir um admin (use PHP password_hash() para gerar o hash)
-- INSERT INTO admins (username, password_hash, email) VALUES ('admin', '<PASSWORD_HASH_HERE>', 'you@example.com');

-- TABELA: admin_actions (registro de aprovações/ações manuais realizadas pelo admin)
CREATE TABLE IF NOT EXISTS admin_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action_type VARCHAR(100) NOT NULL,
    target_table VARCHAR(100),
    target_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MIGRATION: Se você já tinha as tabelas antigas, use estes comandos:
-- RENAME TABLE Anfitrião TO anfitrioes_backup;
-- RENAME TABLE Peregrino TO peregrinos_backup;

-- OBSERVAÇÕES SOBRE FORMATAÇÃO DE DATAS (Atualizado 11/06/2026):
-- 1. Datas são armazenadas no banco em formato YYYY-MM-DD (ex: 2026-06-15)
-- 2. Função formatDatePT() em config.php converte para português (ex: "15 de junho de 2026")
-- 3. Uso: <?php echo formatDatePT($edition['data_inicio']); ?>
-- 4. Exibição em index.php: Card com design destacado (gradient rosa #d946ef, text #ec4899)
-- 5. Exibição em edicoes.php: Card com design destacado, antes da grid dos 6 pilares
-- 6. Visual melhorado: Borders, box-shadow, animações, cores harmoniZadas
-- 7. Campos de inscrição (data_inscricao_inicio e data_inscricao_fim) são dinâmicos e gerenciáveis no admin
-- 8. Datas de inscrição exibidas em cards com destaque especial (gradient amber/laranja)
-- 9. Formatação automática em português: "DD de MÊS - DD de MÊS de YYYY"

