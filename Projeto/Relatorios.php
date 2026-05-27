<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

// Fetch Lotes
$sqlLotes = "SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC";
$resLotes = $conn->query($sqlLotes);
$lotes = [];
if ($resLotes) {
    while ($r = $resLotes->fetch_assoc()) {
        $lotes[] = $r;
    }
}

// Fetch Aplicacoes with Animal info
$sql = "SELECT ap.id_aplicacao, ap.data_aplicacao, ap.observacoes, a.nome_animal, a.numero_brinco, l.codigo_lote, v.nome as produto, v.tipo
        FROM aplicacoes ap
        JOIN animais a ON ap.id_animal = a.id_animal
        LEFT JOIN lotes l ON a.id_lote = l.id_lote
        JOIN vacinas_medicamentos v ON ap.id_vacina_medicamento = v.id
        ORDER BY ap.data_aplicacao DESC LIMIT 100";
$res = $conn->query($sql);
$aplicacoes = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $aplicacoes[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <script>
    window.USER_SESSION = {
      id: <?php echo json_encode($_SESSION['usuario_id']); ?>,
      nome: <?php echo json_encode($_SESSION['usuario_nome']); ?>,
      email: <?php echo json_encode($_SESSION['usuario_email']); ?>,
      tipo: <?php echo json_encode($_SESSION['usuario_tipo']); ?>
    };
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatórios - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="topo-pagina d-none d-md-flex">
    <div>
      <h2 class="titulo-pagina">Relatórios</h2>
      <p class="subtitulo">Gere e visualize relatórios de aplicações (Dados Reais)</p>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <h5 class="fw-bold text-success mb-3"><i class="bi bi-funnel-fill me-2"></i> Filtros</h5>
      <form class="row g-3" id="formRelatorio">
        <div class="col-md-4">
          <label class="form-label">Data Início</label>
          <input type="date" id="filtroInicio" class="form-control rounded-pill">
        </div>
        <div class="col-md-4">
          <label class="form-label">Data Fim</label>
          <input type="date" id="filtroFim" class="form-control rounded-pill">
        </div>
        <div class="col-md-4">
          <label class="form-label">Lote</label>
          <select id="filtroLote" class="form-select rounded-pill">
            <option value="">Todos os Lotes</option>
            <?php foreach($lotes as $l): ?>
              <option value="<?= htmlspecialchars($l['codigo_lote']) ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 text-end mt-3">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" onclick="limparFiltros()">Limpar</button>
          <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" onclick="filtrarRelatorio()"><i class="bi bi-search"></i> Buscar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabela de Resultados -->
  <div class="card card-premium">
    <div class="card-header-green d-flex justify-content-between align-items-center">
      <span>Resultados</span>
      <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-success" onclick="imprimirRelatorio()">
        <i class="bi bi-printer-fill me-1"></i> Imprimir
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" id="tabelaRelatorio">
          <thead class="table-success">
            <tr>
              <th>Data</th>
              <th>Animal / Brinco</th>
              <th>Lote</th>
              <th>Produto (Tipo)</th>
              <th>Observações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($aplicacoes)): ?>
              <tr><td colspan="5" class="text-center py-4">Nenhuma aplicação encontrada.</td></tr>
            <?php else: ?>
              <?php foreach($aplicacoes as $ap): ?>
                <tr data-lote="<?= htmlspecialchars($ap['codigo_lote'] ?? '') ?>" data-data="<?= $ap['data_aplicacao'] ?>">
                  <td><?= date('d/m/Y', strtotime($ap['data_aplicacao'])) ?></td>
                  <td><strong><?= htmlspecialchars($ap['nome_animal']) ?></strong> <small class="text-muted">(<?= htmlspecialchars($ap['numero_brinco'] ?? '-') ?>)</small></td>
                  <td><span class="badge bg-secondary"><?= htmlspecialchars($ap['codigo_lote'] ?? 'Sem Lote') ?></span></td>
                  <td><?= htmlspecialchars($ap['produto']) ?> <small>(<?= ucfirst($ap['tipo']) ?>)</small></td>
                  <td class="small text-muted"><?= htmlspecialchars($ap['observacoes']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    function filtrarRelatorio() {
      const ini = document.getElementById("filtroInicio").value;
      const fim = document.getElementById("filtroFim").value;
      const lote = document.getElementById("filtroLote").value.toLowerCase();

      const tbody = document.querySelector("#tabelaRelatorio tbody");
      const rows = tbody.querySelectorAll("tr");

      let count = 0;
      rows.forEach(tr => {
        if (!tr.hasAttribute('data-data')) return;

        const rowDataStr = tr.getAttribute('data-data');
        const rowLote = tr.getAttribute('data-lote').toLowerCase();

        let show = true;
        if (lote !== "" && rowLote !== lote) show = false;
        
        if (show && ini !== "") {
          if (new Date(rowDataStr) < new Date(ini)) show = false;
        }
        if (show && fim !== "") {
          if (new Date(rowDataStr) > new Date(fim)) show = false;
        }

        tr.style.display = show ? "" : "none";
        if (show) count++;
      });
      
      // Mudar visual se zero resultados e etc
    }

    function limparFiltros() {
      document.getElementById("formRelatorio").reset();
      const rows = document.querySelectorAll("#tabelaRelatorio tbody tr");
      rows.forEach(tr => {
        tr.style.display = "";
      });
    }

    function imprimirRelatorio() {
      window.print();
    }
  </script>
</body>
</html>
