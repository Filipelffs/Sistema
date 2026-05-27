<?php
require_once "../../Banco/conexao.php";
require_once "../sessao.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$isAllowed = isset($_SESSION['usuario_tipo']) && in_array($_SESSION['usuario_tipo'], ['admin', 'veterinario']);

if ($action === 'list') {
    $sql = "SELECT * FROM vacinas_medicamentos ORDER BY data_vencimento ASC";
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if (!$isAllowed) {
    echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
    exit;
}

if ($action === 'create' || $action === 'edit') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $nome = $conn->real_escape_string($input['nome']);
    $tipo = $conn->real_escape_string($input['tipo']); // 'vacina' ou 'medicamento'
    $quantidade = intval($input['quantidade']);
    $data_fabricacao = $conn->real_escape_string($input['data_fabricacao']);
    $data_vencimento = $conn->real_escape_string($input['data_vencimento']);
    $descricao = isset($input['descricao']) ? $conn->real_escape_string($input['descricao']) : '';

    if ($action === 'create') {
        $sql = "INSERT INTO vacinas_medicamentos (nome, tipo, quantidade, data_fabricacao, data_vencimento, descricao)
                VALUES ('$nome', '$tipo', $quantidade, '$data_fabricacao', '$data_vencimento', '$descricao')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Produto cadastrado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $conn->error]);
        }
    } else {
        $sql = "UPDATE vacinas_medicamentos SET 
                nome = '$nome', tipo = '$tipo', quantidade = $quantidade, 
                data_fabricacao = '$data_fabricacao', data_vencimento = '$data_vencimento', descricao = '$descricao' 
                WHERE id = $id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Produto atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $conn->error]);
        }
    }
    exit;
}

if ($action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id']);

    // Verifica se já foi aplicado (não pode deletar se tem histórico)
    $check = $conn->query("SELECT id_aplicacao FROM aplicacoes WHERE id_vacina_medicamento = $id");
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir pois já existem aplicações registradas com este produto.']);
        exit;
    }

    $sql = "DELETE FROM vacinas_medicamentos WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Produto excluído com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
?>
