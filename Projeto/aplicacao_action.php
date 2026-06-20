<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// If action is not in GET, check POST input
if (empty($action)) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'])) {
        $action = $input['action'];
    }
}

// 1. LIST APPLICATIONS
if ($action === 'list') {
    $sql = "SELECT ap.*, 
                   a.nome_animal, 
                   a.numero_brinco, 
                   l.codigo_lote, 
                   vm.nome AS produto_nome, 
                   vm.tipo AS produto_tipo 
            FROM aplicacoes ap
            LEFT JOIN animais a ON ap.id_animal = a.id_animal
            LEFT JOIN lotes l ON a.id_lote = l.id_lote
            LEFT JOIN vacinas_medicamentos vm ON ap.id_vacina_medicamento = vm.id
            ORDER BY ap.data_aplicacao DESC";
            
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar histórico: ' . $conn->error]);
    }
    exit;
}

// 2. DELETE APPLICATION
if ($action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_aplicacao = isset($input['id_aplicacao']) ? intval($input['id_aplicacao']) : 0;

    if ($id_aplicacao <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido para exclusão.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM aplicacoes WHERE id_aplicacao = ?");
    $stmt->bind_param("i", $id_aplicacao);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aplicação excluída com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir aplicação: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// 3. EDIT APPLICATION
if ($action === 'edit') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_aplicacao = isset($input['id_aplicacao']) ? intval($input['id_aplicacao']) : 0;
    $dose = isset($input['dose']) ? trim($input['dose']) : '';
    $tecnico = isset($input['tecnico']) ? trim($input['tecnico']) : '';
    $status_aplicacao = isset($input['status']) ? trim($input['status']) : 'Concluído';
    $data_aplicacao = isset($input['data_aplicacao']) ? trim($input['data_aplicacao']) : '';
    $observacoes = isset($input['observacoes']) ? trim($input['observacoes']) : '';

    if ($id_aplicacao <= 0 || empty($data_aplicacao)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE aplicacoes SET dose = ?, tecnico = ?, status_aplicacao = ?, data_aplicacao = ?, observacoes = ? WHERE id_aplicacao = ?");
    $stmt->bind_param("sssssi", $dose, $tecnico, $status_aplicacao, $data_aplicacao, $observacoes, $id_aplicacao);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aplicação atualizada com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar aplicação: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// 4. ADD APPLICATION (POST default method without explicit action check)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id_animal = intval($input['id_animal']);
    $id_produto = intval($input['id_produto']);
    $dose = isset($input['tipo']) ? trim($input['tipo']) : 'Dose Única';
    $data_aplicacao = isset($input['data_aplicacao']) ? trim($input['data_aplicacao']) : date('Y-m-d');
    $observacoes = isset($input['observacoes']) ? trim($input['observacoes']) : '';
    
    // Default values if not passed
    $tecnico = isset($input['tecnico']) ? trim($input['tecnico']) : $_SESSION['usuario_nome'];
    $status_aplicacao = isset($input['status_aplicacao']) ? trim($input['status_aplicacao']) : 'Concluído';

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
        $stmt = $conn->prepare("INSERT INTO aplicacoes (id_animal, id_vacina_medicamento, dose, tecnico, status_aplicacao, data_aplicacao, observacoes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $id_animal, $id_produto, $dose, $tecnico, $status_aplicacao, $data_aplicacao, $observacoes);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao registrar aplicação: " . $conn->error);
        }
        $stmt->close();

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
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação não suportada ou método incorreto.']);
?>
