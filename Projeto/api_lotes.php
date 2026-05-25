<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json; charset=utf-8');

// -----------------------------------------------
// Helpers
// -----------------------------------------------
function resposta($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

function somenteAdmin() {
    if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
        http_response_code(403);
        resposta(false, 'Acesso restrito a administradores.');
    }
}

// -----------------------------------------------
// GET — Lista lotes com contagem de animais
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "
        SELECT
            l.id_lote,
            l.codigo_lote,
            l.tipo_animal,
            l.quantidade_animais,
            l.criado_em,
            COUNT(al.id_animal) AS total_animais
        FROM lotes l
        LEFT JOIN animal_lote al ON al.id_lote = l.id_lote
        LEFT JOIN animais a      ON a.id_animal = al.id_animal
        GROUP BY l.id_lote
        ORDER BY l.codigo_lote ASC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        resposta(false, 'Erro ao consultar lotes: ' . $conn->error);
    }

    $lotes = [];
    while ($row = $result->fetch_assoc()) {
        // Buscar animais deste lote
        $id_lote = (int) $row['id_lote'];
        $stmt_anim = $conn->prepare("
            SELECT a.id_animal, a.nome_animal, a.numero_brinco
            FROM animais a
            INNER JOIN animal_lote al ON al.id_animal = a.id_animal
            WHERE al.id_lote = ?
            ORDER BY a.nome_animal ASC
        ");
        $stmt_anim->bind_param("i", $id_lote);
        $stmt_anim->execute();
        $res_anim = $stmt_anim->get_result();
        $animais  = [];
        while ($anim = $res_anim->fetch_assoc()) {
            $animais[] = $anim;
        }
        $stmt_anim->close();

        $row['animais'] = $animais;
        $lotes[] = $row;
    }

    resposta(true, '', $lotes);
}

// -----------------------------------------------
// POST — Ações de escrita (apenas admin)
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── CRIAR LOTE ──────────────────────────────
    if ($action === 'criar') {
        somenteAdmin();

        $codigo_lote       = trim($_POST['codigo_lote'] ?? '');
        $tipo_animal       = trim($_POST['tipo_animal'] ?? '');
        $quantidade_animais = (int) ($_POST['quantidade_animais'] ?? 0);

        if (empty($codigo_lote)) {
            resposta(false, 'O código/nome do lote é obrigatório.');
        }

        // Verificar duplicata
        $chk = $conn->prepare("SELECT id_lote FROM lotes WHERE codigo_lote = ?");
        $chk->bind_param("s", $codigo_lote);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            resposta(false, 'Já existe um lote com esse código.');
        }
        $chk->close();

        $stmt = $conn->prepare("INSERT INTO lotes (codigo_lote, tipo_animal, quantidade_animais) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $codigo_lote, $tipo_animal, $quantidade_animais);
        if ($stmt->execute()) {
            $novo_id = $conn->insert_id;
            $stmt->close();
            resposta(true, 'Lote criado com sucesso.', ['id_lote' => $novo_id, 'codigo_lote' => $codigo_lote]);
        } else {
            resposta(false, 'Erro ao criar lote: ' . $conn->error);
        }
    }

    // ── EDITAR LOTE ─────────────────────────────
    if ($action === 'editar') {
        somenteAdmin();

        $id_lote     = (int) ($_POST['id_lote'] ?? 0);
        $codigo_lote = trim($_POST['codigo_lote'] ?? '');
        $tipo_animal = trim($_POST['tipo_animal'] ?? '');
        $quantidade_animais = (int) ($_POST['quantidade_animais'] ?? 0);

        if ($id_lote <= 0 || empty($codigo_lote)) {
            resposta(false, 'Dados inválidos.');
        }

        // Verificar duplicata de codigo_lote em outros lotes
        $chk = $conn->prepare("SELECT id_lote FROM lotes WHERE codigo_lote = ? AND id_lote != ?");
        $chk->bind_param("si", $codigo_lote, $id_lote);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            resposta(false, 'Já existe outro lote com esse código.');
        }
        $chk->close();

        $stmt = $conn->prepare("UPDATE lotes SET codigo_lote = ?, tipo_animal = ?, quantidade_animais = ? WHERE id_lote = ?");
        $stmt->bind_param("ssii", $codigo_lote, $tipo_animal, $quantidade_animais, $id_lote);
        if ($stmt->execute()) {
            $stmt->close();
            resposta(true, 'Lote atualizado com sucesso.');
        } else {
            resposta(false, 'Erro ao atualizar lote: ' . $conn->error);
        }
    }

    // ── EXCLUIR LOTE ────────────────────────────
    if ($action === 'excluir') {
        somenteAdmin();

        $id_lote = (int) ($_POST['id_lote'] ?? 0);
        if ($id_lote <= 0) {
            resposta(false, 'ID do lote inválido.');
        }

        // Verificar se há animais associados
        $chk = $conn->prepare("SELECT COUNT(*) AS total FROM animal_lote WHERE id_lote = ?");
        $chk->bind_param("i", $id_lote);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($row['total'] > 0) {
            resposta(false, 'Não é possível excluir: este lote possui ' . $row['total'] . ' animal(is) vinculado(s). Remova os animais antes.');
        }

        $stmt = $conn->prepare("DELETE FROM lotes WHERE id_lote = ?");
        $stmt->bind_param("i", $id_lote);
        if ($stmt->execute()) {
            $stmt->close();
            resposta(true, 'Lote excluído com sucesso.');
        } else {
            resposta(false, 'Erro ao excluir lote: ' . $conn->error);
        }
    }

    resposta(false, 'Ação inválida.');
}

resposta(false, 'Método não permitido.');
