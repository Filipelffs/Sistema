<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json; charset=utf-8');

function resposta($success, $message = '', $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_lote = isset($_GET['id_lote']) ? (int) $_GET['id_lote'] : 0;

    if ($id_lote > 0) {
        // Animais de um lote específico
        $stmt = $conn->prepare("
            SELECT a.id_animal, a.nome_animal, a.numero_brinco, a.especie, a.raca, a.sexo, a.status_animal
            FROM animais a
            INNER JOIN animal_lote al ON al.id_animal = a.id_animal
            WHERE al.id_lote = ?
            ORDER BY a.nome_animal ASC
        ");
        $stmt->bind_param("i", $id_lote);
    } else {
        // Todos os animais com seu(s) lote(s)
        $stmt = $conn->prepare("
            SELECT
                a.id_animal, a.nome_animal, a.numero_brinco, a.especie, a.raca, a.sexo, a.status_animal,
                GROUP_CONCAT(l.codigo_lote ORDER BY l.codigo_lote SEPARATOR ', ') AS lotes,
                MIN(al.id_lote) AS id_lote_principal
            FROM animais a
            LEFT JOIN animal_lote al ON al.id_animal = a.id_animal
            LEFT JOIN lotes l        ON l.id_lote = al.id_lote
            GROUP BY a.id_animal
            ORDER BY a.nome_animal ASC
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $animais = [];
    while ($row = $result->fetch_assoc()) {
        $animais[] = $row;
    }
    $stmt->close();
    resposta(true, '', $animais);
}

// Também lista lotes para o select do modal de aplicação
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['lotes'])) {
    $result = $conn->query("SELECT id_lote, codigo_lote, tipo_animal FROM lotes ORDER BY codigo_lote ASC");
    $lotes = [];
    while ($row = $result->fetch_assoc()) {
        $lotes[] = $row;
    }
    resposta(true, '', $lotes);
}

resposta(false, 'Método não permitido.');
