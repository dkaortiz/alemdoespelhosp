-- Schema para Além do Espelho - Versão 2.0
-- Refatorado com Peregrinos e Anfitriões

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
CREATE TABLE IF NOT EXISTS edicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    ano INT NOT NULL UNIQUE,
    data_inicio DATE,
    data_fim DATE,
    local VARCHAR(255),
    limite_homens INT DEFAULT 15 COMMENT 'Limite de vagas masculinas para esta edição',
    limite_mulheres INT DEFAULT 15 COMMENT 'Limite de vagas femininas para esta edição',
    limite_anfitrioes INT DEFAULT 999 COMMENT 'Limite de anfitriões (geralmente sem limite)',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ano (ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir edição inicial
INSERT IGNORE INTO edicoes (titulo, descricao, ano, local) VALUES 
('Além do Espelho', 'Um encontro que pode mudar toda a sua história', 2026, 'Local a definir');

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

