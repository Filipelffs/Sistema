<?php
$host = "localhost";
$usuario = "root";
$senha = "";

// Connect to MySQL server
$conn = new mysqli($host, $usuario, $senha);
if ($conn->connect_error) {
    die("Falha na conexão com o MySQL: " . $conn->connect_error);
}

// 1. Create database
$sql = "CREATE DATABASE IF NOT EXISTS vacinacao_animal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Banco de dados 'vacinacao_animal' criado ou já existente.<br>";
} else {
    die("Erro ao criar banco de dados: " . $conn->error);
}

// Select database
$conn->select_db("vacinacao_animal");

// 2. Create 'usuarios' table with 'admin' and 'veterinario' roles
$sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('admin','veterinario') DEFAULT 'veterinario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_usuarios) === TRUE) {
    echo "Tabela 'usuarios' criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela 'usuarios': " . $conn->error);
}

// 3. Create other tables if they don't exist
$sql_animais = "CREATE TABLE IF NOT EXISTS animais (
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
)";
$conn->query($sql_animais);

$sql_vacinas = "CREATE TABLE IF NOT EXISTS vacinas (
    id_vacina INT PRIMARY KEY AUTO_INCREMENT,
    nome_vacina VARCHAR(100) NOT NULL,
    tipo_vacina VARCHAR(100),
    fabricante VARCHAR(100),
    intervalo_doses INT,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_vacinas);

$sql_medicamentos = "CREATE TABLE IF NOT EXISTS medicamentos (
    id_medicamento INT PRIMARY KEY AUTO_INCREMENT,
    nome_medicamento VARCHAR(100) NOT NULL,
    tipo VARCHAR(100),
    principio_ativo VARCHAR(100),
    data_fabricacao DATE,
    intervalo_uso INT,
    lote VARCHAR(50),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_medicamentos);

$sql_lotes = "CREATE TABLE IF NOT EXISTS lotes (
    id_lote INT PRIMARY KEY AUTO_INCREMENT,
    codigo_lote VARCHAR(50) UNIQUE NOT NULL,
    tipo_animal VARCHAR(50),
    quantidade_animais INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_lotes);

$sql_animal_lote = "CREATE TABLE IF NOT EXISTS animal_lote (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT,
    id_lote INT,
    FOREIGN KEY (id_animal) REFERENCES animais(id_animal) ON DELETE CASCADE,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE CASCADE
)";
$conn->query($sql_animal_lote);

$sql_aplicacoes = "CREATE TABLE IF NOT EXISTS aplicacoes (
    id_aplicacao INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT,
    id_vacina INT,
    id_medicamento INT,
    data_aplicacao DATE,
    proxima_aplicacao DATE,
    observacoes TEXT,
    FOREIGN KEY (id_animal) REFERENCES animais(id_animal) ON DELETE CASCADE,
    FOREIGN KEY (id_vacina) REFERENCES vacinas(id_vacina) ON DELETE SET NULL,
    FOREIGN KEY (id_medicamento) REFERENCES medicamentos(id_medicamento) ON DELETE SET NULL
)";
$conn->query($sql_aplicacoes);

// 3b. Tabela estoque_itens — unifica vacinas e medicamentos com controle de quantidade
$sql_estoque_itens = "CREATE TABLE IF NOT EXISTS estoque_itens (
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
)";
$conn->query($sql_estoque_itens);
echo "Tabela 'estoque_itens' criada ou já existente.<br>";

// 3c. Adicionar colunas extras em aplicacoes (seguro — IF NOT EXISTS via verificação)
$cols = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'id_estoque_item'");
if ($cols->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN id_estoque_item INT NULL AFTER id_medicamento");
    $conn->query("ALTER TABLE aplicacoes ADD CONSTRAINT fk_apl_estoque FOREIGN KEY (id_estoque_item) REFERENCES estoque_itens(id) ON DELETE SET NULL");
}

$cols2 = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'id_usuario'");
if ($cols2->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN id_usuario INT NULL AFTER observacoes");
    $conn->query("ALTER TABLE aplicacoes ADD CONSTRAINT fk_apl_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL");
}

$cols3 = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'quantidade_utilizada'");
if ($cols3->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN quantidade_utilizada INT DEFAULT 1 AFTER id_usuario");
}
echo "Colunas de estoque em 'aplicacoes' configuradas.<br>";

echo "Outras tabelas configuradas com sucesso.<br>";

// 4. Seed users
// Check if admin exists
$result = $conn->query("SELECT id_usuario FROM usuarios WHERE email = 'admin@sistema.com'");
if ($result->num_rows == 0) {
    $nome = "Administrador Geral";
    $email = "admin@sistema.com";
    $senha_hash = password_hash("admin123", PASSWORD_DEFAULT);
    $tipo = "admin";
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo);
    $stmt->execute();
    echo "Usuário Administrador (admin@sistema.com / admin123) semeado.<br>";
} else {
    echo "Usuário Administrador já existe.<br>";
}

// Check if vet exists
$result = $conn->query("SELECT id_usuario FROM usuarios WHERE email = 'vet@sistema.com'");
if ($result->num_rows == 0) {
    $nome = "Dr. Julia Silva (Veterinária)";
    $email = "vet@sistema.com";
    $senha_hash = password_hash("vet123", PASSWORD_DEFAULT);
    $tipo = "veterinario";
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo);
    $stmt->execute();
    echo "Usuário Veterinário (vet@sistema.com / vet123) semeado.<br>";
} else {
    echo "Usuário Veterinário já existe.<br>";
}

$conn->close();
echo "<strong>Inicialização do banco concluída com sucesso!</strong>";
?>
