-- Script SQL para adicionar rastreamento de link de pagamento
-- Execute este arquivo se as colunas ainda não existem no banco

-- Verificar e adicionar coluna em peregrinos (execute uma por uma)
ALTER TABLE peregrinos ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL COMMENT 'Link de pagamento PagBank utilizado (Peregrino)';
ALTER TABLE peregrinos ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL COMMENT 'Tipo de link: peregrino ou anfitriao';

-- Verificar e adicionar coluna em anfitrioes (execute uma por uma)
ALTER TABLE anfitrioes ADD COLUMN pagbank_payment_link VARCHAR(500) DEFAULT NULL COMMENT 'Link de pagamento PagBank utilizado (Anfitrião)';
ALTER TABLE anfitrioes ADD COLUMN payment_link_type VARCHAR(20) DEFAULT NULL COMMENT 'Tipo de link: peregrino ou anfitriao';

-- Adicionar índice para facilitar consultas
ALTER TABLE peregrinos ADD INDEX idx_payment_link_type (payment_link_type);
ALTER TABLE anfitrioes ADD INDEX idx_payment_link_type (payment_link_type);

-- Verificar as alterações
DESC peregrinos;
DESC anfitrioes;
