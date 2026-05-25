<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json; charset=utf-8');

function resposta($success, $message = '', $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function checarVetOuAdmin() {
    $tipo = $_SESSION['usuario_tipo'] ?? '';
    if (!in_array($tipo, ['admin', 'veterinario'])) {
        http_response_code(403);
        resposta(false, 'Acesso não autorizado.');
    }
}

// ══════════════════════════════════════════════════════
// GET — Listar itens do estoque
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tipo_filtro = $_GET['tipo'] ?? '';
    $params = [];
    $types  = '';

    $sql = "
        SELECT
            e.*,
            u.nome AS criado_por_nome
        FROM estoque_itens e
        LEFT JOIN usuarios u ON u.id_usuario = e.criado_por
    ";

    if ($tipo_filtro === 'vacina' || $tipo_filtro === 'medicamento') {
        $sql    .= " WHERE e.tipo = ?";
        $params[] = $tipo_filtro;
        $types    = 's';
    }

    $sql .= " ORDER BY e.data_vencimento ASC";

    if ($types) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    if (!$result) { resposta(false, 'Erro ao consultar estoque: ' . $conn->error); }

    $itens = [];
    while ($row = $result->fetch_assoc()) {
        $itens[] = $row;
    }
    resposta(true, '', $itens);
}

// ══════════════════════════════════════════════════════
// POST — Ações de escrita
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checarVetOuAdmin();
    $action = $_POST['action'] ?? '';

    // ── CRIAR ────────────────────────────────────────
    if ($action === 'criar') {
        $nome            = trim($_POST['nome'] ?? '');
        $tipo            = $_POST['tipo'] ?? '';
        $descricao       = trim($_POST['descricao'] ?? '');
        $data_fabricacao = $_POST['data_fabricacao'] ?? null;
        $data_vencimento = $_POST['data_vencimento'] ?? '';
        $quantidade      = (int) ($_POST['quantidade'] ?? 0);
        $criado_por      = (int) $_SESSION['usuario_id'];

        if (empty($nome))            { resposta(false, 'O nome é obrigatório.'); }
        if (!in_array($tipo, ['vacina','medicamento'])) { resposta(false, 'Tipo inválido.'); }
        if (empty($data_vencimento)) { resposta(false, 'A data de vencimento é obrigatória.'); }
        if ($quantidade < 0)         { resposta(false, 'Quantidade não pode ser negativa.'); }

        $data_fab = (!empty($data_fabricacao)) ? $data_fabricacao : null;

        $stmt = $conn->prepare("
            INSERT INTO estoque_itens
                (nome, tipo, descricao, data_fabricacao, data_vencimento, quantidade_atual, quantidade_inicial, criado_por)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssssssii',
            $nome, $tipo, $descricao, $data_fab,
            $data_vencimento, $quantidade, $quantidade, $criado_por
        );
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $stmt->close();
            resposta(true, 'Item cadastrado com sucesso.', ['id' => $id]);
        } else {
            resposta(false, 'Erro ao cadastrar item: ' . $conn->error);
        }
    }

    // ── EDITAR ───────────────────────────────────────
    if ($action === 'editar') {
        $id              = (int) ($_POST['id'] ?? 0);
        $nome            = trim($_POST['nome'] ?? '');
        $tipo            = $_POST['tipo'] ?? '';
        $descricao       = trim($_POST['descricao'] ?? '');
        $data_fabricacao = $_POST['data_fabricacao'] ?? null;
        $data_vencimento = $_POST['data_vencimento'] ?? '';
        $quantidade      = (int) ($_POST['quantidade'] ?? 0);

        if ($id <= 0)                { resposta(false, 'ID inválido.'); }
        if (empty($nome))            { resposta(false, 'O nome é obrigatório.'); }
        if (!in_array($tipo, ['vacina','medicamento'])) { resposta(false, 'Tipo inválido.'); }
        if (empty($data_vencimento)) { resposta(false, 'A data de vencimento é obrigatória.'); }

        $data_fab = (!empty($data_fabricacao)) ? $data_fabricacao : null;

        $stmt = $conn->prepare("
            UPDATE estoque_itens
            SET nome = ?, tipo = ?, descricao = ?, data_fabricacao = ?,
                data_vencimento = ?, quantidade_atual = ?, quantidade_inicial = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssssiii', $nome, $tipo, $descricao, $data_fab, $data_vencimento, $quantidade, $quantidade, $id);
        if ($stmt->execute()) {
            $stmt->close();
            resposta(true, 'Item atualizado com sucesso.');
        } else {
            resposta(false, 'Erro ao atualizar item: ' . $conn->error);
        }
    }

    // ── EXCLUIR ──────────────────────────────────────
    if ($action === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) { resposta(false, 'ID inválido.'); }

        // Verificar se há aplicações vinculadas
        $chk = $conn->prepare("SELECT COUNT(*) AS total FROM aplicacoes WHERE id_estoque_item = ?");
        $chk->bind_param("i", $id);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($row['total'] > 0) {
            resposta(false, "Não é possível excluir: existem {$row['total']} aplicação(ões) usando este item.");
        }

        $stmt = $conn->prepare("DELETE FROM estoque_itens WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            resposta(true, 'Item excluído com sucesso.');
        } else {
            resposta(false, 'Erro ao excluir: ' . $conn->error);
        }
    }

    // ── APLICAR ──────────────────────────────────────
    // Decrementa estoque e registra aplicação no animal
    if ($action === 'aplicar') {
        $id_estoque_item   = (int) ($_POST['id_estoque_item'] ?? 0);
        $id_animal         = (int) ($_POST['id_animal'] ?? 0);
        $data_aplicacao    = $_POST['data_aplicacao'] ?? date('Y-m-d');
        $observacoes       = trim($_POST['observacoes'] ?? '');
        $qtd_utilizada     = max(1, (int) ($_POST['quantidade_utilizada'] ?? 1));
        $id_usuario        = (int) $_SESSION['usuario_id'];

        if ($id_estoque_item <= 0) { resposta(false, 'Selecione um item de estoque válido.'); }
        if ($id_animal <= 0)       { resposta(false, 'Selecione um animal válido.'); }

        // Verificar estoque disponível — pegar item com vencimento mais próximo e qtd > 0
        $stmt_estq = $conn->prepare("
            SELECT id, nome, tipo, quantidade_atual
            FROM estoque_itens
            WHERE id = ? AND quantidade_atual > 0
        ");
        $stmt_estq->bind_param("i", $id_estoque_item);
        $stmt_estq->execute();
        $item = $stmt_estq->get_result()->fetch_assoc();
        $stmt_estq->close();

        if (!$item) {
            resposta(false, 'Este item não possui estoque disponível para aplicação.');
        }

        if ($item['quantidade_atual'] < $qtd_utilizada) {
            resposta(false, "Estoque insuficiente. Disponível: {$item['quantidade_atual']} unidade(s).");
        }

        // Iniciar transação
        $conn->begin_transaction();
        try {
            // 1. Decrementar estoque
            $upd = $conn->prepare("UPDATE estoque_itens SET quantidade_atual = quantidade_atual - ? WHERE id = ?");
            $upd->bind_param("ii", $qtd_utilizada, $id_estoque_item);
            $upd->execute();
            $upd->close();

            // 2. Determinar id_vacina ou id_medicamento (nullable) com base no tipo
            $id_vacina      = null;
            $id_medicamento = null;
            // Mantemos id_estoque_item como FK principal

            // 3. Inserir aplicação
            $ins = $conn->prepare("
                INSERT INTO aplicacoes
                    (id_animal, id_vacina, id_medicamento, id_estoque_item,
                     data_aplicacao, observacoes, id_usuario, quantidade_utilizada)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->bind_param(
                "iiiissii",
                $id_animal, $id_vacina, $id_medicamento, $id_estoque_item,
                $data_aplicacao, $observacoes, $id_usuario, $qtd_utilizada
            );
            $ins->execute();
            $ins->close();

            $conn->commit();

            // Buscar nova quantidade para retornar
            $saldo = $conn->query("SELECT quantidade_atual FROM estoque_itens WHERE id = $id_estoque_item");
            $nova_qtd = $saldo->fetch_assoc()['quantidade_atual'];

            resposta(true, 'Aplicação registrada com sucesso. Estoque atualizado.', [
                'nova_quantidade' => $nova_qtd,
                'item'            => $item['nome'],
                'tipo'            => $item['tipo']
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            resposta(false, 'Erro ao registrar aplicação: ' . $e->getMessage());
        }
    }

    resposta(false, 'Ação inválida.');
}

resposta(false, 'Método não permitido.');
