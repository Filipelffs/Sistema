<?php
require_once "conexao.php";

$queries = [
    // Create combined table
    "CREATE TABLE IF NOT EXISTS vacinas_medicamentos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        tipo ENUM('vacina', 'medicamento') NOT NULL,
        quantidade INT DEFAULT 0,
        data_fabricacao DATE,
        data_vencimento DATE,
        descricao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Adjust aplicacoes
    "ALTER TABLE aplicacoes DROP FOREIGN KEY aplicacoes_ibfk_2",
    "ALTER TABLE aplicacoes DROP FOREIGN KEY aplicacoes_ibfk_3",
    "ALTER TABLE aplicacoes DROP COLUMN id_vacina",
    "ALTER TABLE aplicacoes DROP COLUMN id_medicamento",
    "ALTER TABLE aplicacoes ADD COLUMN id_vacina_medicamento INT",
    "ALTER TABLE aplicacoes ADD COLUMN quantidade_aplicada INT DEFAULT 1",
    "ALTER TABLE aplicacoes ADD FOREIGN KEY (id_vacina_medicamento) REFERENCES vacinas_medicamentos(id)",

    // Adjust animais to have id_lote directly
    "ALTER TABLE animais ADD COLUMN id_lote INT",
    "ALTER TABLE animais ADD FOREIGN KEY (id_lote) REFERENCES lotes(id_lote)",
    
    // Drop old animal_lote
    "DROP TABLE IF EXISTS animal_lote",

    // Drop old vacinas and medicamentos
    "DROP TABLE IF EXISTS vacinas",
    "DROP TABLE IF EXISTS medicamentos"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Sucesso: $query\n";
    } else {
        echo "Erro ao executar ($query): " . $conn->error . "\n";
    }
}
?>
