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
    foto VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_usuarios) === TRUE) {
    echo "Tabela 'usuarios' criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela 'usuarios': " . $conn->error);
}

// Add 'foto' column if missing (for existing databases)
$checkFoto = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto'");
if ($checkFoto->num_rows == 0) {
    $conn->query("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
    echo "Coluna 'foto' adicionada à tabela usuarios.<br>";
}

// 3. Create lotes table
$sql_lotes = "CREATE TABLE IF NOT EXISTS lotes (
    id_lote INT PRIMARY KEY AUTO_INCREMENT,
    codigo_lote VARCHAR(50) UNIQUE NOT NULL,
    tipo_animal VARCHAR(50),
    quantidade_animais INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_lotes);

// 4. Create animais table with id_lote
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
    id_lote INT,
    status_animal ENUM('Saudavel','Doente','Tratamento') DEFAULT 'Saudavel',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE SET NULL
)";
$conn->query($sql_animais);

// Add id_lote column if missing
$checkLote = $conn->query("SHOW COLUMNS FROM animais LIKE 'id_lote'");
if ($checkLote->num_rows == 0) {
    $conn->query("ALTER TABLE animais ADD COLUMN id_lote INT");
    $conn->query("ALTER TABLE animais ADD FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE SET NULL");
    echo "Coluna 'id_lote' adicionada à tabela animais.<br>";
}

// 5. Create vacinas_medicamentos (unified table)
$sql_vm = "CREATE TABLE IF NOT EXISTS vacinas_medicamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('vacina', 'medicamento') NOT NULL,
    quantidade INT DEFAULT 0,
    data_fabricacao DATE,
    data_vencimento DATE,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_vm);

// 6. Create aplicacoes table
$sql_aplicacoes = "CREATE TABLE IF NOT EXISTS aplicacoes (
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
)";
$conn->query($sql_aplicacoes);

// Add dose column if missing
$checkDose = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'dose'");
if ($checkDose->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN dose VARCHAR(100) DEFAULT NULL");
}
// Add tecnico column if missing
$checkTecnico = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'tecnico'");
if ($checkTecnico->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN tecnico VARCHAR(100) DEFAULT NULL");
}
// Add status_aplicacao column if missing
$checkStatusA = $conn->query("SHOW COLUMNS FROM aplicacoes LIKE 'status_aplicacao'");
if ($checkStatusA->num_rows == 0) {
    $conn->query("ALTER TABLE aplicacoes ADD COLUMN status_aplicacao ENUM('Concluído', 'Pendente', 'Atrasada') DEFAULT 'Concluído'");
}

// 7. Create notificacoes table
$sql_notif = "CREATE TABLE IF NOT EXISTS notificacoes (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150),
    mensagem TEXT,
    status_notificacao ENUM('pendente','lida') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_notif);

echo "Todas as tabelas configuradas com sucesso.<br>";

// 8. Seed users
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
