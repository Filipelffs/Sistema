<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

$data = [
    'kpis' => [
        'total_animais' => 0,
        'vacinas_aplicadas' => 0,
        'vacinas_atrasadas' => 0
    ],
    'charts' => [
        'animais_lote' => [],
        'estoque' => []
    ],
    'alertas' => []
];

// KPI 1: Total animais
$resAnimais = $conn->query("SELECT COUNT(*) as total FROM animais");
if ($resAnimais) $data['kpis']['total_animais'] = $resAnimais->fetch_assoc()['total'];

// KPI 2: Vacinas Aplicadas (Total de registros em aplicacoes)
$resAplicadas = $conn->query("SELECT COUNT(*) as total FROM aplicacoes");
if ($resAplicadas) $data['kpis']['vacinas_aplicadas'] = $resAplicadas->fetch_assoc()['total'];

// KPI 3: Vacinas Atrasadas (Vamos considerar animais sem aplicacao nos ultimos 365 dias para simplificar, ou apenas um mock caso a lógica de "atrasada" dependa de outras tabelas complexas)
// Como o banco não tem uma tabela clara de "vacinacao pendente", vou considerar vacinas_atrasadas como 0 ou fixo até definirmos a regra de negócio. Para efeito demonstrativo de DB, buscaremos animais sem nenhuma aplicação:
$resAtrasadas = $conn->query("SELECT COUNT(*) as total FROM animais a LEFT JOIN aplicacoes ap ON a.id_animal = ap.id_animal WHERE ap.id_aplicacao IS NULL");
if ($resAtrasadas) $data['kpis']['vacinas_atrasadas'] = $resAtrasadas->fetch_assoc()['total'];

// Chart 1: Animais por Lote
$resChart1 = $conn->query("SELECT l.codigo_lote, COUNT(a.id_animal) as qtd FROM lotes l LEFT JOIN animais a ON a.id_lote = l.id_lote GROUP BY l.id_lote");
if ($resChart1) {
    while ($row = $resChart1->fetch_assoc()) {
        $data['charts']['animais_lote'][] = [
            'lote' => $row['codigo_lote'],
            'quantidade' => (int)$row['qtd']
        ];
    }
}

// Chart 2: Estoque
$resChart2 = $conn->query("SELECT nome, quantidade FROM vacinas_medicamentos WHERE quantidade > 0 ORDER BY quantidade DESC LIMIT 10");
if ($resChart2) {
    while ($row = $resChart2->fetch_assoc()) {
        $data['charts']['estoque'][] = [
            'nome' => $row['nome'],
            'quantidade' => (int)$row['quantidade']
        ];
    }
}

// Alertas (Animais que nunca foram vacinados, limite de 5)
$resAlertas = $conn->query("SELECT a.id_animal, a.nome_animal, a.numero_brinco FROM animais a LEFT JOIN aplicacoes ap ON a.id_animal = ap.id_animal WHERE ap.id_aplicacao IS NULL LIMIT 5");
if ($resAlertas) {
    while ($row = $resAlertas->fetch_assoc()) {
        $data['alertas'][] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $data]);
?>
