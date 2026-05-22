CREATE DATABASE vacinacao_animal;
USE vacinacao_animal;

-- =========================
-- TABELA USUÁRIOS
-- =========================
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('admin','veterinario') DEFAULT 'veterinario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA ANIMAIS
-- =========================
CREATE TABLE animais (
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
    status_animal ENUM('Saudavel','Doente','Tratamento') DEFAULT 'Saudavel',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA VACINAS
-- =========================
CREATE TABLE vacinas (
    id_vacina INT PRIMARY KEY AUTO_INCREMENT,
    nome_vacina VARCHAR(100) NOT NULL,
    tipo_vacina VARCHAR(100),
    fabricante VARCHAR(100),
    intervalo_doses INT,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA MEDICAMENTOS
-- =========================
CREATE TABLE medicamentos (
    id_medicamento INT PRIMARY KEY AUTO_INCREMENT,
    nome_medicamento VARCHAR(100) NOT NULL,
    tipo VARCHAR(100),
    principio_ativo VARCHAR(100),
    data_fabricacao DATE,
    intervalo_uso INT,
    lote VARCHAR(50),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABELA LOTES
-- =========================
CREATE TABLE lotes (
    id_lote INT PRIMARY KEY AUTO_INCREMENT,
    codigo_lote VARCHAR(50) UNIQUE NOT NULL,
    tipo_animal VARCHAR(50),
    quantidade_animais INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- RELAÇÃO ANIMAL x LOTE
-- =========================
CREATE TABLE animal_lote (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT,
    id_lote INT,

    FOREIGN KEY (id_animal) REFERENCES animais(id_animal),
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote)
);

-- =========================
-- TABELA APLICAÇÕES
-- =========================
CREATE TABLE aplicacoes (
    id_aplicacao INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT,
    id_vacina INT,
    id_medicamento INT,
    data_aplicacao DATE,
    proxima_aplicacao DATE,
    observacoes TEXT,

    FOREIGN KEY (id_animal) REFERENCES animais(id_animal),
    FOREIGN KEY (id_vacina) REFERENCES vacinas(id_vacina),
    FOREIGN KEY (id_medicamento) REFERENCES medicamentos(id_medicamento)
);

-- =========================
-- TABELA NOTIFICAÇÕES
-- =========================
CREATE TABLE notificacoes (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150),
    mensagem TEXT,
    status_notificacao ENUM('pendente','lida') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CONFIGURAÇÕES
-- =========================
CREATE TABLE configuracoes (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    tema_escuro BOOLEAN DEFAULT FALSE,
    notificacoes BOOLEAN DEFAULT TRUE,
    idioma VARCHAR(30) DEFAULT 'pt-BR'
);