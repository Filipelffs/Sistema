-- =====================================================
-- MIGRAÇÃO: Estoque de Vacinas/Medicamentos
-- Sistema de Vacinação Animal
-- Execute em: vacinacao_animal
-- =====================================================

USE vacinacao_animal;

-- =====================================================
-- 1. NOVA TABELA: estoque_itens
-- Unifica vacinas e medicamentos com controle de qtd
-- =====================================================
CREATE TABLE IF NOT EXISTS estoque_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('vacina','medicamento') NOT NULL,
    descricao TEXT,
    data_fabricacao DATE,
    data_vencimento DATE NOT NULL,
    quantidade_atual INT NOT NULL DEFAULT 0,
    quantidade_inicial INT NOT NULL DEFAULT 0,
    criado_por INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- =====================================================
-- 2. ALTERAR TABELA: aplicacoes
-- Adicionar referência ao estoque_itens e ao usuário
-- responsável (verifica antes de adicionar)
-- =====================================================

-- Adiciona coluna id_estoque_item se não existir
SET @col_estoque = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'vacinacao_animal'
    AND TABLE_NAME = 'aplicacoes'
    AND COLUMN_NAME = 'id_estoque_item'
);

SET @sql_estoque = IF(@col_estoque = 0,
    'ALTER TABLE aplicacoes ADD COLUMN id_estoque_item INT NULL AFTER id_medicamento',
    'SELECT "coluna id_estoque_item ja existe" AS info'
);
PREPARE stmt FROM @sql_estoque;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona coluna id_usuario se não existir
SET @col_usuario = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'vacinacao_animal'
    AND TABLE_NAME = 'aplicacoes'
    AND COLUMN_NAME = 'id_usuario'
);

SET @sql_usuario = IF(@col_usuario = 0,
    'ALTER TABLE aplicacoes ADD COLUMN id_usuario INT NULL AFTER observacoes',
    'SELECT "coluna id_usuario ja existe" AS info'
);
PREPARE stmt FROM @sql_usuario;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona coluna quantidade_utilizada se não existir
SET @col_qtd = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'vacinacao_animal'
    AND TABLE_NAME = 'aplicacoes'
    AND COLUMN_NAME = 'quantidade_utilizada'
);

SET @sql_qtd = IF(@col_qtd = 0,
    'ALTER TABLE aplicacoes ADD COLUMN quantidade_utilizada INT DEFAULT 1 AFTER id_usuario',
    'SELECT "coluna quantidade_utilizada ja existe" AS info'
);
PREPARE stmt FROM @sql_qtd;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK: id_estoque_item
SET @fk_estoque = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'vacinacao_animal'
    AND TABLE_NAME = 'aplicacoes'
    AND COLUMN_NAME = 'id_estoque_item'
    AND REFERENCED_TABLE_NAME IS NOT NULL
);

SET @sql_fk_estoque = IF(@fk_estoque = 0,
    'ALTER TABLE aplicacoes ADD CONSTRAINT fk_aplicacoes_estoque FOREIGN KEY (id_estoque_item) REFERENCES estoque_itens(id) ON DELETE SET NULL',
    'SELECT "fk id_estoque_item ja existe" AS info'
);
PREPARE stmt FROM @sql_fk_estoque;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK: id_usuario
SET @fk_usuario = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'vacinacao_animal'
    AND TABLE_NAME = 'aplicacoes'
    AND COLUMN_NAME = 'id_usuario'
    AND REFERENCED_TABLE_NAME IS NOT NULL
    AND REFERENCED_TABLE_NAME = 'usuarios'
);

SET @sql_fk_usuario = IF(@fk_usuario = 0,
    'ALTER TABLE aplicacoes ADD CONSTRAINT fk_aplicacoes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL',
    'SELECT "fk id_usuario ja existe" AS info'
);
PREPARE stmt FROM @sql_fk_usuario;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migração concluída com sucesso!' AS resultado;
