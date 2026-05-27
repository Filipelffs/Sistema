<?php
require_once "sessao.php";
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
      <h2 class="titulo-pagina">Relatórios Sanitários</h2>
      <p class="subtitulo">Gere e exporte relatórios consolidados sobre a imunização do rebanho</p>
    </div>
  </div>

  <div class="row">
    <!-- Filter Panel (Left) -->
    <div class="col-12 col-lg-5 mb-4">
      <div class="card card-premium shadow-sm">
        <div class="card-header-green">
          <span>Filtrar Relatórios</span>
          <i class="bi bi-funnel-fill"></i>
        </div>
        <div class="card-body">
          <form id="relatorioForm">
            <!-- Lote -->
            <div class="form-group-custom mb-3">
              <label>Lote de Animais</label>
              <select id="repLote" class="form-select form-control-custom-noicon">
                <option value="">Todos os Lotes</option>
                <option value="LOTE 1">LOTE 1</option>
                <option value="LOTE 2">LOTE 2</option>
                <option value="LOTE 3">LOTE 3</option>
              </select>
            </div>

            <!-- Raça -->
            <div class="form-group-custom mb-3">
              <label>Raça do Animal</label>
              <select id="repRaca" class="form-select form-control-custom-noicon">
                <option value="">Todas as Raças</option>
                <option value="Holandesa">Holandesa</option>
                <option value="Gir">Gir</option>
                <option value="Anglonubiana">Anglonubiana</option>
                <option value="Angus">Angus</option>
              </select>
            </div>

            <!-- Vacinação por animal -->
            <div class="mb-3">
              <label class="form-label fw-semibold small">Situação Vacinal</label>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="chkAplicadas" checked>
                <label class="form-check-label text-muted small" for="chkAplicadas">
                  Vacinas Aplicadas (Concluído)
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="chkAtrasadas" checked>
                <label class="form-check-label text-muted small" for="chkAtrasadas">
                  Vacinas Atrasadas
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="chkPendentes" checked>
                <label class="form-check-label text-muted small" for="chkPendentes">
                  Vacinas Pendentes (No prazo)
                </label>
              </div>
            </div>

            <!-- Status do Período -->
            <div class="mb-4">
              <label class="form-label fw-semibold small">Período de Aplicação</label>
              <div class="row g-2">
                <div class="col-6">
                  <input type="date" id="repDataInicio" class="form-control form-control-custom-noicon" placeholder="Início">
                </div>
                <div class="col-6">
                  <input type="date" id="repDataFim" class="form-control form-control-custom-noicon" placeholder="Fim">
                </div>
              </div>
            </div>

            <!-- Botão Aplicar -->
            <button type="button" class="btn-primary-custom py-2" onclick="gerarRelatorio()">
              Aplicar Filtros
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Report Preview and Exports (Right) -->
    <div class="col-12 col-lg-7">
      <div class="card card-premium shadow-sm">
        <div class="card-header-green">
          <span>Pré-visualização do Relatório</span>
          <div>
            <button class="btn btn-sm btn-light rounded-pill me-1" onclick="exportarPDF()">
              <i class="bi bi-file-pdf-fill text-danger"></i> PDF
            </button>
            <button class="btn btn-sm btn-light rounded-pill" onclick="imprimirRelatorio()">
              <i class="bi bi-printer-fill text-primary"></i> Imprimir
            </button>
          </div>
        </div>
        <div class="card-body bg-white">
          <div id="relatorioPreview" class="p-3 border rounded-3" style="min-height: 300px; background-color: #FAFAFA;">
            <div class="text-center py-5 text-muted" id="previewPlaceholder">
              <i class="bi bi-file-earmark-text fs-1 d-block mb-3"></i>
              <p>Configure os filtros ao lado e clique em "Aplicar Filtros" para gerar a pré-visualização.</p>
            </div>
            <!-- Dynamic Preview Content -->
            <div id="previewContent" class="d-none">
              <h5 class="fw-bold text-center border-bottom pb-2 mb-3">Relatório de Vacinação Animal</h5>
              <div class="row mb-3 small">
                <div class="col-6"><strong>Lote:</strong> <span id="lblLote">Todos</span></div>
                <div class="col-6"><strong>Raça:</strong> <span id="lblRaca">Todas</span></div>
              </div>
              <table class="table table-sm table-striped small">
                <thead>
                  <tr>
                    <th>Animal</th>
                    <th>Lote</th>
                    <th>Item</th>
                    <th>Data</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="tblRelatorioContent">
                  <!-- Rows injected here -->
                </tbody>
              </table>
              <div class="text-end fw-bold small mt-3">
                Total de Registros: <span id="lblTotalRegistros">0</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    function gerarRelatorio() {
      const lote = document.getElementById("repLote").value;
      const raca = document.getElementById("repRaca").value;
      const chkAplicadas = document.getElementById("chkAplicadas").checked;
      const chkAtrasadas = document.getElementById("chkAtrasadas").checked;
      const chkPendentes = document.getElementById("chkPendentes").checked;
      const dataInicio = document.getElementById("repDataInicio").value;
      const dataFim = document.getElementById("repDataFim").value;

      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      const aplicacoes = JSON.parse(localStorage.getItem("aplicacoes")) || [];

      // Filter applications
      let filtradas = aplicacoes.filter(apl => {
        const animal = animais.find(a => a.id === apl.animalId);
        if (!animal) return false;

        // Lote Filter
        if (lote && animal.lote !== lote) return false;

        // Raca Filter
        if (raca && !animal.raca.includes(raca)) return false;

        // Status Filter
        if (apl.status === "Concluído" && !chkAplicadas) return false;
        if (apl.status === "Atrasada" && !chkAtrasadas) return false;
        if (apl.status === "Pendente" && !chkPendentes) return false;

        // Date Filter
        if (dataInicio && apl.data < dataInicio) return false;
        if (dataFim && apl.data > dataFim) return false;

        return true;
      });

      // Update UI labels
      document.getElementById("lblLote").innerText = lote ? lote : "Todos";
      document.getElementById("lblRaca").innerText = raca ? raca : "Todas";

      const tableBody = document.getElementById("tblRelatorioContent");
      tableBody.innerHTML = "";

      if (filtradas.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Nenhum registro corresponde aos filtros selecionados.</td></tr>`;
      } else {
        filtradas.forEach(apl => {
          const animal = animais.find(a => a.id === apl.animalId) || { nome: "Paula", numero: "1003", lote: "Lote" };
          let dateStr = apl.data;
          const parts = apl.data.split("-");
          if(parts.length === 3) dateStr = `${parts[2]}/${parts[1]}/${parts[0]}`;

          tableBody.innerHTML += `
            <tr>
              <td>${animal.nome} (${animal.numero})</td>
              <td>${animal.lote || '---'}</td>
              <td>${apl.itemNome}</td>
              <td>${dateStr}</td>
              <td><span class="badge ${apl.status === 'Concluído' ? 'bg-success' : apl.status === 'Pendente' ? 'bg-warning text-dark' : 'bg-danger'}">${apl.status}</span></td>
            </tr>
          `;
        });
      }

      document.getElementById("lblTotalRegistros").innerText = filtradas.length;

      // Show preview
      document.getElementById("previewPlaceholder").classList.add("d-none");
      document.getElementById("previewContent").classList.remove("d-none");
    }

    function exportarPDF() {
      if (document.getElementById("previewContent").classList.contains("d-none")) {
        alert("Gere o relatório antes de exportá-lo.");
        return;
      }
      alert("Seu PDF está sendo gerado e o download iniciará em instantes!");
    }

    function imprimirRelatorio() {
      if (document.getElementById("previewContent").classList.contains("d-none")) {
        alert("Gere o relatório antes de imprimi-lo.");
        return;
      }
      window.print();
    }
  </script>
</body>

</html>
