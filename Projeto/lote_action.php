<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    // List all lotes and count of animals
    $sql = "SELECT l.id_lote, l.codigo_lote, l.tipo_animal, l.quantidade_animais, COUNT(a.id_animal) as qtd_real 
            FROM lotes l 
            LEFT JOIN animais a ON a.id_lote = l.id_lote 
            GROUP BY l.id_lote";
    $result = $conn->query($sql);
    $lotes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Also fetch animals for this lote to display in the table
            $sqlAnimais = "SELECT id_animal, numero_brinco, nome_animal FROM animais WHERE id_lote = " . $row['id_lote'];
            $resAnimais = $conn->query($sqlAnimais);
            $animais = [];
            if ($resAnimais) {
                while ($a = $resAnimais->fetch_assoc()) {
                    $animais[] = $a;
                }
            }
            $row['animais'] = $animais;
            $lotes[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $lotes]);
    exit;
}

// Below actions require ADMIN permission
if ($action === 'create' || $action === 'edit') {
    if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Permissão negada. Apenas administradores podem realizar esta ação.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'create') {
        $codigo_lote = $conn->real_escape_string($input['codigo_lote']);
        // Check if exists
        $check = $conn->query("SELECT id_lote FROM lotes WHERE codigo_lote = '$codigo_lote'");
        if ($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Já existe um lote com este nome.']);
            exit;
        }

        $sql = "INSERT INTO lotes (codigo_lote) VALUES ('$codigo_lote')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Lote criado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar lote: ' . $conn->error]);
        }
    }

    if ($action === 'edit') {
        $id_lote = intval($input['id_lote']);
        $codigo_lote = $conn->real_escape_string($input['codigo_lote']);
        
        $check = $conn->query("SELECT id_lote FROM lotes WHERE codigo_lote = '$codigo_lote' AND id_lote != $id_lote");
        if ($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Já existe um lote com este nome.']);
            exit;
        }

        $sql = "UPDATE lotes SET codigo_lote = '$codigo_lote' WHERE id_lote = $id_lote";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Lote atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar lote: ' . $conn->error]);
        }
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
?>
