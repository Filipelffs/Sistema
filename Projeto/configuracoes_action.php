<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

$id = $_SESSION['usuario_id'];

// GET: retorna as configurações do usuário logado
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT tema_escuro FROM configuracoes WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo json_encode(['success' => true, 'tema_escuro' => (bool)$row['tema_escuro']]);
    } else {
        // Ainda não tem registro — retorna padrão (claro)
        echo json_encode(['success' => true, 'tema_escuro' => false]);
    }
    $stmt->close();
    exit;
}

// POST: salva/atualiza preferências
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $tema_escuro = isset($input['tema_escuro']) ? (int)(bool)$input['tema_escuro'] : 0;

    // Upsert: insere se não existe, atualiza se já existe
    $stmt = $conn->prepare("
        INSERT INTO configuracoes (id_usuario, tema_escuro)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE tema_escuro = VALUES(tema_escuro)
    ");
    $stmt->bind_param("ii", $id, $tema_escuro);

    if ($stmt->execute()) {
        // Atualiza a sessão imediatamente para refletir em próximas páginas
        $_SESSION['tema_escuro'] = (bool)$tema_escuro;
        echo json_encode(['success' => true, 'message' => 'Preferências salvas!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método inválido.']);
?>
