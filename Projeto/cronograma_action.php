<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'])) {
        $action = $input['action'];
    }
}

if ($action === 'list') {
    // Fetch filter parameters
    $data_inicial = isset($_GET['data_inicial']) ? trim($_GET['data_inicial']) : '';
    $data_final = isset($_GET['data_final']) ? trim($_GET['data_final']) : '';
    $id_lote = isset($_GET['id_lote']) && $_GET['id_lote'] !== '' ? intval($_GET['id_lote']) : 0;
    $especie = isset($_GET['especie']) ? trim($_GET['especie']) : '';
    $id_vacina = isset($_GET['id_vacina']) && $_GET['id_vacina'] !== '' ? intval($_GET['id_vacina']) : 0;
    $vet_resp = isset($_GET['vet_resp']) && $_GET['vet_resp'] !== '' ? intval($_GET['vet_resp']) : 0;
    $situacao = isset($_GET['situacao']) ? trim($_GET['situacao']) : 'Todas';

    // Base query
    $sql = "SELECT cv.*, 
                   a.nome_animal, 
                   a.numero_brinco, 
                   a.especie, 
                   a.id_lote, 
                   l.codigo_lote, 
                   vm.nome AS vacina_nome, 
                   vm.quantidade AS estoque_qtd, 
                   vm.data_vencimento AS estoque_vencimento, 
                   u.nome AS veterinario_nome,
                   CASE
                       WHEN cv.status_cronograma = 'Aplicada' THEN 'Aplicada'
                       WHEN cv.data_prevista < CURDATE() THEN 'Atrasada'
                       WHEN cv.data_prevista = CURDATE() THEN 'Hoje'
                       ELSE 'Pendente'
                   END AS situacao_atual
            FROM cronograma_vacinacao cv
            JOIN animais a ON cv.id_animal = a.id_animal
            LEFT JOIN lotes l ON a.id_lote = l.id_lote
            JOIN vacinas_medicamentos vm ON cv.id_vacina_medicamento = vm.id
            LEFT JOIN usuarios u ON cv.id_usuario_responsavel = u.id_usuario";

    $whereClauses = [];
    $params = [];
    $types = '';

    if (!empty($data_inicial)) {
        $whereClauses[] = "cv.data_prevista >= ?";
        $params[] = $data_inicial;
        $types .= 's';
    }
    if (!empty($data_final)) {
        $whereClauses[] = "cv.data_prevista <= ?";
        $params[] = $data_final;
        $types .= 's';
    }
    if ($id_lote > 0) {
        $whereClauses[] = "a.id_lote = ?";
        $params[] = $id_lote;
        $types .= 'i';
    }
    if (!empty($especie)) {
        $whereClauses[] = "a.especie = ?";
        $params[] = $especie;
        $types .= 's';
    }
    if ($id_vacina > 0) {
        $whereClauses[] = "cv.id_vacina_medicamento = ?";
        $params[] = $id_vacina;
        $types .= 'i';
    }
    if ($vet_resp > 0) {
        $whereClauses[] = "cv.id_usuario_responsavel = ?";
        $params[] = $vet_resp;
        $types .= 'i';
    }

    if (count($whereClauses) > 0) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    if ($situacao !== 'Todas') {
        $sql .= " HAVING situacao_atual = ?";
        $params[] = $situacao;
        $types .= 's';
    }

    $sql .= " ORDER BY cv.data_prevista ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $conn->error]);
    }
    exit;
}

if ($action === 'apply') {
    // Confirm a vaccination from the schedule
    $id_cronograma = isset($input['id_cronograma']) ? intval($input['id_cronograma']) : 0;
    $data_aplicacao = isset($input['data_aplicacao']) ? trim($input['data_aplicacao']) : date('Y-m-d');
    $dose_aplicada = isset($input['dose_aplicada']) ? trim($input['dose_aplicada']) : '';
    $observacoes = isset($input['observacoes']) ? trim($input['observacoes']) : '';
    
    // Booster schedule parameters
    $agendar_reforco = isset($input['agendar_reforco']) ? (bool)$input['agendar_reforco'] : false;
    $data_reforco = isset($input['data_reforco']) ? trim($input['data_reforco']) : '';
    $dose_reforco = isset($input['dose_reforco']) ? trim($input['dose_reforco']) : 'Reforço';

    if ($id_cronograma <= 0 || empty($dose_aplicada)) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos. Preencha todos os campos obrigatórios.']);
        exit;
    }

    // Fetch schedule details
    $stmtC = $conn->prepare("SELECT * FROM cronograma_vacinacao WHERE id_cronograma = ?");
    $stmtC->bind_param("i", $id_cronograma);
    $stmtC->execute();
    $resC = $stmtC->get_result();
    $cronoItem = $resC->fetch_assoc();
    $stmtC->close();

    if (!$cronoItem) {
        echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado.']);
        exit;
    }

    $id_animal = $cronoItem['id_animal'];
    $id_vacina = $cronoItem['id_vacina_medicamento'];
    $id_vet = $cronoItem['id_usuario_responsavel'];

    // Check vaccine stock
    $checkStock = $conn->prepare("SELECT quantidade FROM vacinas_medicamentos WHERE id = ?");
    $checkStock->bind_param("i", $id_vacina);
    $checkStock->execute();
    $resStock = $checkStock->get_result();
    $stockRow = $resStock->fetch_assoc();
    $checkStock->close();

    if (!$stockRow || $stockRow['quantidade'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vacina fora de estoque no inventário.']);
        exit;
    }

    // Begin Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert application history
        $tecnico = $_SESSION['usuario_nome'];
        $status_aplicacao = 'Concluído';
        
        $stmtIns = $conn->prepare("INSERT INTO aplicacoes (id_animal, id_vacina_medicamento, dose, tecnico, status_aplicacao, data_aplicacao, observacoes) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->bind_param("iisssss", $id_animal, $id_vacina, $dose_aplicada, $tecnico, $status_aplicacao, $data_aplicacao, $observacoes);
        if (!$stmtIns->execute()) {
            throw new Exception("Erro ao gravar histórico de aplicação: " . $conn->error);
        }
        $stmtIns->close();

        // 2. Decrement inventory
        $stmtDec = $conn->prepare("UPDATE vacinas_medicamentos SET quantidade = quantidade - 1 WHERE id = ?");
        $stmtDec->bind_param("i", $id_vacina);
        if (!$stmtDec->execute()) {
            throw new Exception("Erro ao atualizar o estoque: " . $conn->error);
        }
        $stmtDec->close();

        // 3. Mark schedule as Applied
        $stmtUpC = $conn->prepare("UPDATE cronograma_vacinacao SET status_cronograma = 'Aplicada' WHERE id_cronograma = ?");
        $stmtUpC->bind_param("i", $id_cronograma);
        if (!$stmtUpC->execute()) {
            throw new Exception("Erro ao atualizar status do cronograma: " . $conn->error);
        }
        $stmtUpC->close();

        // 4. Create booster schedule if checked
        if ($agendar_reforco && !empty($data_reforco)) {
            $status_novo = 'Agendada';
            $stmtRef = $conn->prepare("INSERT INTO cronograma_vacinacao (id_animal, id_vacina_medicamento, data_prevista, dose, id_usuario_responsavel, status_cronograma) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            $stmtRef->bind_param("iisiss", $id_animal, $id_vacina, $data_reforco, $dose_reforco, $id_vet, $status_novo);
            if (!$stmtRef->execute()) {
                throw new Exception("Erro ao agendar o reforço: " . $conn->error);
            }
            $stmtRef->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Vacinação confirmada e registrada com sucesso!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
?>
