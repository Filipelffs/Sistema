<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id_animal = intval($input['id_animal']);
$id_produto = intval($input['id_produto']);
$tipo_aplicacao = $conn->real_escape_string($input['tipo']); // Dose / Tipo
$data_aplicacao = $conn->real_escape_string($input['data_aplicacao']);
$observacoes = $conn->real_escape_string($input['observacoes']);

// Check stock
$checkStock = $conn->query("SELECT quantidade FROM vacinas_medicamentos WHERE id = $id_produto");
if ($checkStock && $checkStock->num_rows > 0) {
    $row = $checkStock->fetch_assoc();
    if ($row['quantidade'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Produto fora de estoque!']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
    exit;
}

$conn->begin_transaction();

try {
    // Registra aplicacao
    $sql = "INSERT INTO aplicacoes (id_animal, id_vacina_medicamento, data_aplicacao, observacoes) 
            VALUES ($id_animal, $id_produto, '$data_aplicacao', '$tipo_aplicacao - $observacoes')";
    if (!$conn->query($sql)) {
        throw new Exception("Erro ao registrar aplicação: " . $conn->error);
    }

    // Desconta do estoque
    $sqlStock = "UPDATE vacinas_medicamentos SET quantidade = quantidade - 1 WHERE id = $id_produto";
    if (!$conn->query($sqlStock)) {
        throw new Exception("Erro ao atualizar estoque: " . $conn->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Aplicação registrada e estoque atualizado!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
