<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$input = json_decode(file_get_contents('php://input'), true);

if (!$action && isset($input['action'])) {
    $action = $input['action'];
}

$isAdmin = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin');

if ($action === 'list') {
    // Listar todos os animais
    $sql = "SELECT a.*, l.codigo_lote FROM animais a LEFT JOIN lotes l ON a.id_lote = l.id_lote ORDER BY a.id_animal DESC";
    $result = $conn->query($sql);
    $animais = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $animais[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $animais]);
    exit;
}

if ($action === 'get') {
    // Buscar um animal específico por ID
    $id_animal = intval($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id_animal <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit;
    }
    $sql = "SELECT a.*, l.codigo_lote FROM animais a LEFT JOIN lotes l ON a.id_lote = l.id_lote WHERE a.id_animal = $id_animal";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $animal = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $animal]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Animal não encontrado.']);
    }
    exit;
}

if ($action === 'create') {
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permissão negada. Somente administradores podem cadastrar animais.']);
        exit;
    }

    $nome = $conn->real_escape_string($input['nome']);
    $especie = $conn->real_escape_string($input['especie']);
    $raca = $conn->real_escape_string($input['raca'] ?? 'Não especificada');
    $id_lote = !empty($input['id_lote']) ? intval($input['id_lote']) : 'NULL';
    $sexo = $conn->real_escape_string($input['sexo']);
    $data_nascimento = $conn->real_escape_string($input['data_nascimento'] ?? date('Y-m-d'));
    $pai = $conn->real_escape_string($input['pai'] ?? 'Não informado');
    $mae = $conn->real_escape_string($input['mae'] ?? 'Não informado');
    $peso = !empty($input['peso']) ? floatval($input['peso']) : 'NULL';

    // Inserir sem numero_brinco (será gerado após insert)
    $sql = "INSERT INTO animais (nome_animal, especie, raca, id_lote, sexo, data_nascimento, pai, mae, peso) 
            VALUES ('$nome', '$especie', '$raca', $id_lote, '$sexo', '$data_nascimento', '$pai', '$mae', $peso)";
    if ($conn->query($sql)) {
        $newId = $conn->insert_id;
        // Gerar numero_brinco automaticamente: BRI-001, BRI-002, etc.
        $brinco = 'BRI-' . str_pad($newId, 3, '0', STR_PAD_LEFT);
        $conn->query("UPDATE animais SET numero_brinco = '$brinco' WHERE id_animal = $newId");
        echo json_encode(['success' => true, 'message' => "Animal cadastrado com sucesso! Brinco: $brinco", 'id' => $newId, 'brinco' => $brinco]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $conn->error]);
    }
    exit;
}

if ($action === 'edit') {
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permissão negada. Somente administradores podem editar animais.']);
        exit;
    }

    $id_animal = intval($input['id_animal']);
    $nome = $conn->real_escape_string($input['nome']);
    $especie = $conn->real_escape_string($input['especie']);
    $raca = $conn->real_escape_string($input['raca'] ?? 'Não especificada');
    $id_lote = !empty($input['id_lote']) ? intval($input['id_lote']) : 'NULL';
    $sexo = $conn->real_escape_string($input['sexo']);
    $data_nascimento = $conn->real_escape_string($input['data_nascimento'] ?? date('Y-m-d'));
    $pai = $conn->real_escape_string($input['pai'] ?? 'Não informado');
    $mae = $conn->real_escape_string($input['mae'] ?? 'Não informado');
    $peso = !empty($input['peso']) ? floatval($input['peso']) : 'NULL';

    // Não permite alterar numero_brinco (é automático)
    $sql = "UPDATE animais SET 
            nome_animal = '$nome', especie = '$especie', raca = '$raca', 
            id_lote = $id_lote, sexo = '$sexo', data_nascimento = '$data_nascimento', 
            pai = '$pai', mae = '$mae', peso = $peso 
            WHERE id_animal = $id_animal";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Animal atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $conn->error]);
    }
    exit;
}

if ($action === 'delete') {
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Permissão negada. Somente administradores podem excluir animais.']);
        exit;
    }
    
    $id_animal = intval($input['id_animal']);
    
    // Deleta do banco (CASCADE deleta aplicações relacionadas)
    $sql = "DELETE FROM animais WHERE id_animal = $id_animal";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Animal excluído com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
?>
