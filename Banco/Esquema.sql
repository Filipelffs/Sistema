CREATE DATABASE IF NOT EXISTS vacinacao_animal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vacinacao_animal;

-- =========================
-- TABELA USUÁRIOS
-- =========================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('admin','veterinario') DEFAULT 'veterinario',
    foto VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA LOTES
-- =========================
CREATE TABLE IF NOT EXISTS lotes (
    id_lote INT PRIMARY KEY AUTO_INCREMENT,
    codigo_lote VARCHAR(50) UNIQUE NOT NULL,
    tipo_animal VARCHAR(50),
    quantidade_animais INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA ANIMAIS
-- =========================
CREATE TABLE IF NOT EXISTS animais (
    id_animal INT PRIMARY KEY AUTO_INCREMENT,
    nome_animal VARCHAR(100) NOT NULL,
    numero_brinco VARCHAR(50) UNIQUE,
    especie VARCHAR(50),
    raca VARCHAR(50),
    sexo ENUM('Macho','Fêmea'),
    data_nascimento DATE,
    pai VARCHAR(100),
    mae VARCHAR(100),
    peso DECIMAL(10,2),
    id_lote INT,
    status_animal ENUM('Saudavel','Doente','Tratamento') DEFAULT 'Saudavel',
    foto_animal VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE SET NULL
);

-- =========================
-- TABELA VACINAS E MEDICAMENTOS (Unificada)
-- =========================
CREATE TABLE IF NOT EXISTS vacinas_medicamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('vacina', 'medicamento') NOT NULL,
    quantidade INT DEFAULT 0,
    data_fabricacao DATE,
    data_vencimento DATE,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS aplicacoes (
    id_aplicacao INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT,
    id_vacina_medicamento INT,
    quantidade_aplicada INT DEFAULT 1,
    data_aplicacao DATE,
    proxima_aplicacao DATE,
    dose VARCHAR(100) DEFAULT NULL,
    tecnico VARCHAR(100) DEFAULT NULL,
    status_aplicacao ENUM('Concluído', 'Pendente', 'Atrasada') DEFAULT 'Concluído',
    observacoes TEXT,
    FOREIGN KEY (id_animal) REFERENCES animais(id_animal) ON DELETE CASCADE,
    FOREIGN KEY (id_vacina_medicamento) REFERENCES vacinas_medicamentos(id) ON DELETE SET NULL
);

-- =========================
-- TABELA NOTIFICAÇÕES
-- =========================
CREATE TABLE IF NOT EXISTS notificacoes (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150),
    mensagem TEXT,
    status_notificacao ENUM('pendente','lida') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA CRONOGRAMA DE VACINAÇÃO
-- =========================
CREATE TABLE IF NOT EXISTS cronograma_vacinacao (
    id_cronograma INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    id_vacina_medicamento INT NOT NULL,
    data_prevista DATE NOT NULL,
    dose VARCHAR(100) DEFAULT '1ª dose',
    id_usuario_responsavel INT DEFAULT NULL,
    status_cronograma ENUM('Agendada', 'Hoje', 'Aplicada', 'Atrasada', 'Pendente') DEFAULT 'Agendada',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_animal) REFERENCES animais(id_animal) ON DELETE CASCADE,
    FOREIGN KEY (id_vacina_medicamento) REFERENCES vacinas_medicamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_responsavel) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- =========================
-- CONFIGURAÇÕES
-- =========================
CREATE TABLE IF NOT EXISTS configuracoes (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    tema_escuro BOOLEAN DEFAULT FALSE,
    notificacoes BOOLEAN DEFAULT TRUE,
    idioma VARCHAR(30) DEFAULT 'pt-BR'
);