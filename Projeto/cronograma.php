  <?php
  require_once "sessao.php";
  require_once "../Banco/conexao.php";

  // Fetch lotes
  $resLotes = $conn->query("SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC");
  $lotes = [];
  if ($resLotes) {
      while($row = $resLotes->fetch_assoc()) { $lotes[] = $row; }
  }

  // Fetch species
  $resEspecies = $conn->query("SELECT DISTINCT especie FROM animais WHERE especie IS NOT NULL AND especie != '' ORDER BY especie ASC");
  $especies = [];
  if ($resEspecies) {
      while($row = $resEspecies->fetch_assoc()) { $especies[] = $row['especie']; }
  }

  // Fetch vaccines
  $resVacinas = $conn->query("SELECT id, nome FROM vacinas_medicamentos WHERE tipo = 'vacina' ORDER BY nome ASC");
  $vacinas = [];
  if ($resVacinas) {
      while($row = $resVacinas->fetch_assoc()) { $vacinas[] = $row; }
  }

  // Fetch veterinarians (all users who can apply)
  $resVets = $conn->query("SELECT id_usuario, nome FROM usuarios ORDER BY nome ASC");
  $vets = [];
  if ($resVets) {
      while($row = $resVets->fetch_assoc()) { $vets[] = $row; }
  }

  // Count summary numbers for Alerts block
  $resAtrasadasAlert = $conn->query("SELECT COUNT(*) as total FROM cronograma_vacinacao WHERE status_cronograma = 'Agendada' AND data_prevista < CURDATE()");
  $qtdAtrasadas = $resAtrasadasAlert ? intval($resAtrasadasAlert->fetch_assoc()['total']) : 0;

  $resHojeAlert = $conn->query("SELECT COUNT(*) as total FROM cronograma_vacinacao WHERE status_cronograma = 'Agendada' AND data_prevista = CURDATE()");
  $qtdHoje = $resHojeAlert ? intval($resHojeAlert->fetch_assoc()['total']) : 0;

  $resReforcosAlert = $conn->query("SELECT COUNT(*) as total FROM cronograma_vacinacao WHERE status_cronograma = 'Agendada' AND data_prevista > CURDATE() AND data_prevista <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
  $qtdReforcos = $resReforcosAlert ? intval($resReforcosAlert->fetch_assoc()['total']) : 0;

  $resAppliedMonth = $conn->query("SELECT COUNT(*) as total FROM aplicacoes WHERE MONTH(data_aplicacao) = MONTH(CURRENT_DATE()) AND YEAR(data_aplicacao) = YEAR(CURRENT_DATE())");
  $appliedMonth = $resAppliedMonth ? intval($resAppliedMonth->fetch_assoc()['total']) : 0;
  $appliedMonthDisplay = $appliedMonth;
  ?>
  <!DOCTYPE html>
<html lang="pt-br" <?= $TEMA_ESCURO ? 'data-theme="dark"' : '' ?>>
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
  <title>Cronograma de Vacinação - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">

  <style>
    .kpi-card-hover {
      transition: all 0.3s ease;
    }
    .kpi-card-hover:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    }
    .print-header {
      display: none;
    }
    @media print {
      body {
        background-color: white !important;
        color: black !important;
      }
      .app-container {
        display: block !important;
      }
      .menu-lateral, .topo-pagina, .card-premium, .btn, .modal, .alert, .mobile-navbar, #filterForm {
        display: none !important;
      }
      .conteudo-principal {
        padding: 0 !important;
        margin: 0 !important;
      }
      .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 30px;
      }
      .row {
        display: flex !important;
        flex-wrap: wrap !important;
      }
      .col-12, .col-md-6, .col-lg-4 {
        width: 50% !important;
        float: left !important;
        padding: 10px !important;
      }
      .item-card {
        border: 1px solid #ddd !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        page-break-inside: avoid !important;
        margin-bottom: 15px !important;
      }
      .secao-title {
        page-break-after: avoid !important;
        margin-top: 20px !important;
        border-bottom: 2px solid #ccc !important;
        padding-bottom: 5px !important;
      }
    }
  </style>
</head>
<body>

  <!-- PRINT ONLY HEADER -->
  <div class="print-header">
    <h2>🏫 Escola Fazenda - Cronograma de Vacinação Animal</h2>
    <p>Relatório gerado em: <?php echo date('d/m/Y H:i'); ?></p>
    <hr>
  </div>

  <!-- HEADER -->
  <div class="topo-pagina d-none d-md-flex">
    <div>
      <h2 class="titulo-pagina">📅 Cronograma de Vacinação</h2>
      <p class="subtitulo">Gerencie aplicações, reforços e alertas sanitários do rebanho.</p>
    </div>
  </div>

  <!-- ALERTS NOTIFICATION BLOCK -->
  <div class="row mb-3">
    <div class="col-12">
      <?php if($qtdAtrasadas > 0): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-2 rounded-4 py-2 px-3 shadow-sm border-0 bg-danger bg-opacity-10 text-danger">
          <i class="bi bi-x-circle-fill"></i>
          <span>🔴 <strong><?= $qtdAtrasadas ?></strong> vacinas atrasadas</span>
        </div>
      <?php endif; ?>
      <?php if($qtdHoje > 0): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-2 rounded-4 py-2 px-3 shadow-sm border-0 bg-warning bg-opacity-15 text-warning-color">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>🟡 <strong><?= $qtdHoje ?></strong> vacinas para hoje</span>
        </div>
      <?php endif; ?>
      <?php if($qtdReforcos > 0): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-2 rounded-4 py-2 px-3 shadow-sm border-0 bg-success bg-opacity-10 text-success">
          <i class="bi bi-check-circle-fill"></i>
          <span>🟢 <strong><?= $qtdReforcos ?></strong> reforços previstos para os próximos 7 dias</span>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <!-- Card 1: Late -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card-hover" style="border-left: 5px solid #dc3545 !important;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-bold">🔴 Vacinas Atrasadas</span>
            <h3 class="fw-bold mb-0 text-danger" id="kpiAtrasadas"><?= $qtdAtrasadas ?></h3>
          </div>
          <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="bi bi-x-circle-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>
    <!-- Card 2: Today -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card-hover" style="border-left: 5px solid #ffc107 !important;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-bold">🟡 Vacinas para Hoje</span>
            <h3 class="fw-bold mb-0 text-warning" id="kpiHoje"><?= $qtdHoje ?></h3>
          </div>
          <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>
    <!-- Card 3: Booster -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card-hover" style="border-left: 5px solid #198754 !important;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-bold">🟢 Próximos Reforços</span>
            <h3 class="fw-bold mb-0 text-success" id="kpiReforcos"><?= $qtdReforcos ?></h3>
          </div>
          <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="bi bi-calendar-check-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>
    <!-- Card 4: Applied -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card-hover" style="border-left: 5px solid #0dcaf0 !important;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-bold">✅ Aplicadas no Mês</span>
            <h3 class="fw-bold mb-0 text-info" id="kpiAplicadas"><?= $appliedMonthDisplay ?></h3>
          </div>
          <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="bi bi-check-circle-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FILTERS PANEL -->
  <div class="card card-premium mb-4 shadow-sm border-0">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="bi bi-funnel-fill text-success me-2"></i>Área de Filtros</h5>
      <form id="filterForm" class="row g-3">
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Data Inicial</label>
          <input type="date" id="filtroDataInicial" class="form-control" onchange="carregarCronograma()">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Data Final</label>
          <input type="date" id="filtroDataFinal" class="form-control" onchange="carregarCronograma()">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small fw-semibold">Lote</label>
          <select id="filtroLote" class="form-select" onchange="carregarCronograma()">
            <option value="">Todos</option>
            <?php foreach($lotes as $l): ?>
              <option value="<?= $l['id_lote'] ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small fw-semibold">Espécie</label>
          <select id="filtroEspecie" class="form-select" onchange="carregarCronograma()">
            <option value="">Todas</option>
            <?php foreach($especies as $esp): ?>
              <option value="<?= htmlspecialchars($esp) ?>"><?= htmlspecialchars($esp) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small fw-semibold">Vacina</label>
          <select id="filtroVacina" class="form-select" onchange="carregarCronograma()">
            <option value="">Todas</option>
            <?php foreach($vacinas as $v): ?>
              <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Veterinário Responsável</label>
          <select id="filtroVet" class="form-select" onchange="carregarCronograma()">
            <option value="">Todos</option>
            <?php foreach($vets as $vet): ?>
              <option value="<?= $vet['id_usuario'] ?>"><?= htmlspecialchars($vet['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Situação</label>
          <select id="filtroSituacao" class="form-select" onchange="carregarCronograma()">
            <option value="Todas">Todas</option>
            <option value="Agendada">Agendada</option>
            <option value="Hoje">Hoje</option>
            <option value="Aplicada">Aplicada</option>
            <option value="Atrasada">Atrasada</option>
            <option value="Pendente">Pendente (Reforços)</option>
          </select>
        </div>
        <div class="col-12 col-md-6 d-flex align-items-end justify-content-end gap-2">
          <button type="button" class="btn btn-success rounded-pill px-4 text-white fw-bold" onclick="carregarCronograma()">
            <i class="bi bi-funnel-fill me-1"></i> Filtrar
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="limparFiltros()">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Limpar
          </button>
          <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="exportarPDF()">
            <i class="bi bi-file-pdf-fill me-1"></i> Exportar PDF
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- SCHEDULE LISTINGS -->
  
  <!-- Section: Late Vaccines -->
  <div id="secaoAtrasadas" class="mb-5">
    <h4 class="fw-bold text-danger mb-3 d-flex align-items-center gap-2 secao-title">
      <i class="bi bi-x-circle-fill"></i> Sessão: Vacinas Atrasadas
    </h4>
    <div class="row g-3" id="listaAtrasadas">
      <div class="col-12 text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-danger" role="status"></div> Carregando...
      </div>
    </div>
  </div>

  <!-- Section: Today Vaccines -->
  <div id="secaoHoje" class="mb-5">
    <h4 class="fw-bold text-warning mb-3 d-flex align-items-center gap-2 secao-title" style="color: #d35400 !important;">
      <i class="bi bi-exclamation-triangle-fill"></i> Sessão: Vacinas para Hoje
    </h4>
    <div class="row g-3" id="listaHoje">
      <div class="col-12 text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-warning" role="status"></div> Carregando...
      </div>
    </div>
  </div>

  <!-- Section: Next Boosters -->
  <div id="secaoReforcos" class="mb-5">
    <h4 class="fw-bold text-success mb-3 d-flex align-items-center gap-2 secao-title">
      <i class="bi bi-calendar-check-fill"></i> Sessão: Próximos Reforços
    </h4>
    <div class="row g-3" id="listaReforcos">
      <div class="col-12 text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-success" role="status"></div> Carregando...
      </div>
    </div>
  </div>

  <!-- Section: Applied Vaccines -->
  <div id="secaoAplicadas" class="mb-5 d-none">
    <h4 class="fw-bold text-info mb-3 d-flex align-items-center gap-2 secao-title">
      <i class="bi bi-check-circle-fill"></i> Vacinas Aplicadas
    </h4>
    <div class="row g-3" id="listaAplicadas">
      <div class="col-12 text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-info" role="status"></div> Carregando...
      </div>
    </div>
  </div>


  <!-- MODAL APLICAR VACINA -->
  <div class="modal fade" id="modalAplicarVacina" tabindex="-1" aria-labelledby="modalAplicarVacinaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title" id="modalAplicarVacinaLabel"><i class="bi bi-virus me-2"></i>Aplicar Vacina</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="aplicarForm">
            <input type="hidden" id="cronoId" name="id_cronograma">
            <input type="hidden" id="cronoVacId" name="id_vacina_medicamento">
            
            <div id="aplicarAlert" class="alert d-none"></div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small fw-semibold">Animal</label>
                <input type="text" id="cronoAnimalNome" class="form-control" readonly disabled>
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold">Brinco</label>
                <input type="text" id="cronoBrinco" class="form-control" readonly disabled>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Vacina</label>
              <input type="text" id="cronoVacinaNome" class="form-control" readonly disabled>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small fw-semibold">Lote da Vacina</label>
                <input type="text" id="cronoLoteEstoque" class="form-control" readonly disabled>
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold">Validade da Vacina</label>
                <input type="text" id="cronoValidadeEstoque" class="form-control" readonly disabled>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Data da Aplicação</label>
              <input type="date" id="cronoDataAplicacao" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Dose Aplicada</label>
              <select id="cronoDoseAplicada" class="form-select" required>
                <option value="1ª dose">1ª dose</option>
                <option value="2ª dose (reforço)">2ª dose (reforço)</option>
                <option value="3ª dose">3ª dose</option>
                <option value="Dose de reforço anual">Dose de reforço anual</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Observações</label>
              <textarea id="cronoObs" class="form-control" rows="2" placeholder="Insira detalhes adicionais sobre o estado do animal ou reação..."></textarea>
            </div>

            <!-- Agendamento de reforço -->
            <div class="card border-0 p-3 rounded-3 mb-3" style="background-color: var(--bg-input);">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="cronoAgendarReforco" onchange="toggleReforcoFields()">
                <label class="form-check-label fw-bold small" for="cronoAgendarReforco">Agendar próximo reforço automaticamente?</label>
              </div>
              <div id="cronoReforcoFields" class="d-none">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small fw-semibold">Data do Reforço</label>
                    <input type="date" id="cronoDataReforco" class="form-control">
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-semibold">Dose do Reforço</label>
                    <select id="cronoDoseReforco" class="form-select">
                      <option value="2ª dose (reforço)">2ª dose (reforço)</option>
                      <option value="3ª dose">3ª dose</option>
                      <option value="Dose de reforço anual">Dose de reforço anual</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success rounded-pill px-4" id="btnConfirmarAplicar" onclick="confirmarAplicacao()">Confirmar Aplicação</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    let cronogramaList = [];

    async function carregarCronograma() {
      const data_inicial = document.getElementById("filtroDataInicial").value;
      const data_final = document.getElementById("filtroDataFinal").value;
      const id_lote = document.getElementById("filtroLote").value;
      const especie = document.getElementById("filtroEspecie").value;
      const id_vacina = document.getElementById("filtroVacina").value;
      const vet_resp = document.getElementById("filtroVet").value;
      const situacao = document.getElementById("filtroSituacao").value;

      const url = `cronograma_action.php?action=list&data_inicial=${data_inicial}&data_final=${data_final}&id_lote=${id_lote}&especie=${encodeURIComponent(especie)}&id_vacina=${id_vacina}&vet_resp=${vet_resp}&situacao=${situacao}`;

      try {
        const res = await fetch(url);
        const result = await res.json();
        if (result.success) {
          cronogramaList = result.data;
          renderizarCronograma(cronogramaList, situacao);
          atualizarKPIs(cronogramaList);
        } else {
          console.error(result.message);
        }
      } catch(e) {
        console.error("Erro de conexão ao carregar cronograma.");
      }
    }

    function renderizarCronograma(lista, situacaoFiltrada) {
      const containerAtrasadas = document.getElementById("listaAtrasadas");
      const containerHoje = document.getElementById("listaHoje");
      const containerReforcos = document.getElementById("listaReforcos");
      const containerAplicadas = document.getElementById("listaAplicadas");

      containerAtrasadas.innerHTML = "";
      containerHoje.innerHTML = "";
      containerReforcos.innerHTML = "";
      containerAplicadas.innerHTML = "";

      let countAtrasadas = 0;
      let countHoje = 0;
      let countReforcos = 0;
      let countAplicadas = 0;

      lista.forEach(item => {
        const parts = item.data_prevista.split("-");
        const dataFormatada = (parts.length === 3) ? `${parts[2]}/${parts[1]}/${parts[0]}` : item.data_prevista;
        
        // Calculate days of delay
        const dataPrevista = new Date(item.data_prevista + 'T00:00:00');
        const hojeDate = new Date();
        hojeDate.setHours(0,0,0,0);
        dataPrevista.setHours(0,0,0,0);
        const diffTime = hojeDate - dataPrevista;
        const diasAtraso = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        const htmlCard = `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 item-card mb-0">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h5 class="fw-bold mb-0 text-success">${item.nome_animal}</h5>
                  <span class="badge bg-light text-dark border rounded-pill">Brinco: ${item.numero_brinco || 'N/A'}</span>
                </div>
                <ul class="list-unstyled mb-3 small">
                  <li class="py-1"><strong>Espécie:</strong> ${item.especie || 'N/A'}</li>
                  <li class="py-1"><strong>Lote:</strong> ${item.codigo_lote || 'Sem lote'}</li>
                  <li class="py-1"><strong>Vacina:</strong> <span class="fw-bold text-success">${item.vacina_nome}</span></li>
                  <li class="py-1"><strong>Responsável:</strong> ${item.veterinario_nome || 'Não definido'}</li>
                  <li class="py-1"><strong>Data Prevista:</strong> ${dataFormatada}</li>
                  ${item.situacao_atual === 'Atrasada' ? `<li class="py-1 text-danger"><strong>Dias de Atraso:</strong> <span class="badge bg-danger text-white rounded-pill">${diasAtraso} dias</span></li>` : ''}
                </ul>
                <div class="d-flex gap-2 justify-content-end">
                  ${item.situacao_atual !== 'Aplicada' ? `
                    <button class="btn btn-sm btn-success rounded-pill px-3 py-1 text-white fw-bold d-flex align-items-center gap-1 shadow-xs" onclick='abrirModalAplicar(${JSON.stringify(item).replace(/'/g, "&#39;")})'>
                      <i class="bi bi-check-circle"></i> Aplicar Vacina
                    </button>
                  ` : `
                    <span class="badge bg-success text-white py-1 px-3 rounded-pill fw-semibold"><i class="bi bi-check-all"></i> Aplicada</span>
                  `}
                </div>
              </div>
            </div>
          </div>
        `;

        const htmlHojeCard = `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 item-card mb-0" style="border-top: 4px solid #ffc107 !important;">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h5 class="fw-bold mb-0 text-success">${item.nome_animal}</h5>
                  <span class="badge bg-light text-dark border rounded-pill">Brinco: ${item.numero_brinco || 'N/A'}</span>
                </div>
                <ul class="list-unstyled mb-3 small">
                  <li class="py-1"><strong>Espécie:</strong> ${item.especie || 'N/A'}</li>
                  <li class="py-1"><strong>Lote:</strong> ${item.codigo_lote || 'Sem lote'}</li>
                  <li class="py-1"><strong>Vacina:</strong> <span class="fw-bold text-success">${item.vacina_nome}</span></li>
                  <li class="py-1"><strong>Data Agendada:</strong> ${dataFormatada}</li>
                  <li class="py-1"><strong>Responsável:</strong> ${item.veterinario_nome || 'Não definido'}</li>
                </ul>
                <div class="d-flex gap-2 justify-content-end">
                  <button class="btn btn-sm btn-success rounded-pill px-3 py-1 text-white fw-bold d-flex align-items-center gap-1 shadow-xs" onclick='abrirModalAplicar(${JSON.stringify(item).replace(/'/g, "&#39;")})'>
                    <i class="bi bi-check-circle"></i> Aplicar Vacina
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;

        const htmlReforcoCard = `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 item-card mb-0" style="border-top: 4px solid #198754 !important;">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h5 class="fw-bold mb-0 text-success">${item.nome_animal}</h5>
                  <span class="badge bg-light text-dark border rounded-pill">Brinco: ${item.numero_brinco || 'N/A'}</span>
                </div>
                <ul class="list-unstyled mb-3 small">
                  <li class="py-1"><strong>Vacina:</strong> <span class="fw-bold text-success">${item.vacina_nome}</span></li>
                  <li class="py-1"><strong>Data do Reforço:</strong> ${dataFormatada}</li>
                  <li class="py-1"><strong>Responsável:</strong> ${item.veterinario_nome || 'Não definido'}</li>
                </ul>
                <div class="d-flex gap-2 justify-content-end">
                  <a href="ficha_animal.php?id=${item.id_animal}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1">
                    <i class="bi bi-eye"></i> Visualizar
                  </a>
                </div>
              </div>
            </div>
          </div>
        `;

        if (item.situacao_atual === 'Atrasada') {
          containerAtrasadas.innerHTML += htmlCard;
          countAtrasadas++;
        } else if (item.situacao_atual === 'Hoje') {
          containerHoje.innerHTML += htmlHojeCard;
          countHoje++;
        } else if (item.situacao_atual === 'Pendente') {
          containerReforcos.innerHTML += htmlReforcoCard;
          countReforcos++;
        } else if (item.situacao_atual === 'Aplicada') {
          containerAplicadas.innerHTML += htmlCard;
          countAplicadas++;
        }
      });

      // Display empty messages
      if (countAtrasadas === 0) {
        containerAtrasadas.innerHTML = '<div class="col-12 text-muted py-2"><i class="bi bi-check-circle text-success me-1"></i> Nenhuma vacina atrasada sob estes filtros.</div>';
      }
      if (countHoje === 0) {
        containerHoje.innerHTML = '<div class="col-12 text-muted py-2"><i class="bi bi-info-circle text-muted me-1"></i> Nenhuma vacina agendada para hoje sob estes filtros.</div>';
      }
      if (countReforcos === 0) {
        containerReforcos.innerHTML = '<div class="col-12 text-muted py-2"><i class="bi bi-info-circle text-muted me-1"></i> Nenhum reforço pendente sob estes filtros.</div>';
      }
      if (countAplicadas === 0) {
        containerAplicadas.innerHTML = '<div class="col-12 text-muted py-2"><i class="bi bi-info-circle text-muted me-1"></i> Nenhuma vacina aplicada sob este filtro.</div>';
      }

      // Adjust visibility of sections according to situation filter
      const secaoAtrasadas = document.getElementById("secaoAtrasadas");
      const secaoHoje = document.getElementById("secaoHoje");
      const secaoReforcos = document.getElementById("secaoReforcos");
      const secaoAplicadas = document.getElementById("secaoAplicadas");

      if (situacaoFiltrada === 'Todas') {
        secaoAtrasadas.classList.remove("d-none");
        secaoHoje.classList.remove("d-none");
        secaoReforcos.classList.remove("d-none");
        secaoAplicadas.classList.add("d-none"); // Keep applied hidden by default on 'Todas' to avoid cluttering, or show it if needed
      } else {
        secaoAtrasadas.classList.add("d-none");
        secaoHoje.classList.add("d-none");
        secaoReforcos.classList.add("d-none");
        secaoAplicadas.classList.add("d-none");

        if (situacaoFiltrada === 'Atrasada') secaoAtrasadas.classList.remove("d-none");
        if (situacaoFiltrada === 'Hoje') secaoHoje.classList.remove("d-none");
        if (situacaoFiltrada === 'Pendente') secaoReforcos.classList.remove("d-none");
        if (situacaoFiltrada === 'Aplicada') {
          secaoAplicadas.classList.remove("d-none");
        }
        if (situacaoFiltrada === 'Agendada') {
          secaoAtrasadas.classList.remove("d-none");
          secaoHoje.classList.remove("d-none");
          secaoReforcos.classList.remove("d-none");
        }
      }
    }

    function atualizarKPIs(lista) {
      let atrasadas = 0;
      let hoje = 0;
      let reforcos = 0;
      
      lista.forEach(item => {
        if (item.situacao_atual === 'Atrasada') atrasadas++;
        else if (item.situacao_atual === 'Hoje') hoje++;
        else if (item.situacao_atual === 'Pendente') reforcos++;
      });

      document.getElementById("kpiAtrasadas").textContent = atrasadas;
      document.getElementById("kpiHoje").textContent = hoje;
      document.getElementById("kpiReforcos").textContent = reforcos;
    }

    function abrirModalAplicar(item) {
      document.getElementById("cronoId").value = item.id_cronograma;
      document.getElementById("cronoVacId").value = item.id_vacina_medicamento;
      document.getElementById("cronoAnimalNome").value = item.nome_animal;
      document.getElementById("cronoBrinco").value = item.numero_brinco || 'S/N';
      document.getElementById("cronoVacinaNome").value = item.vacina_nome;
      document.getElementById("cronoLoteEstoque").value = item.codigo_lote || 'Geral';
      
      const vDate = item.estoque_vencimento;
      const parts = vDate ? vDate.split("-") : [];
      const formattedVDate = (parts.length === 3) ? `${parts[2]}/${parts[1]}/${parts[0]}` : 'N/A';
      document.getElementById("cronoValidadeEstoque").value = formattedVDate;

      // Reset reinforcement fields
      document.getElementById("cronoAgendarReforco").checked = false;
      document.getElementById("cronoReforcoFields").classList.add("d-none");
      document.getElementById("cronoDataReforco").value = "";
      
      document.getElementById("aplicarAlert").classList.add("d-none");
      
      new bootstrap.Modal(document.getElementById("modalAplicarVacina")).show();
    }

    function toggleReforcoFields() {
      const checkbox = document.getElementById("cronoAgendarReforco");
      const fields = document.getElementById("cronoReforcoFields");
      if (checkbox.checked) {
        fields.classList.remove("d-none");
        const dateInput = document.getElementById("cronoDataReforco");
        if (!dateInput.value) {
          const today = new Date();
          today.setDate(today.getDate() + 30);
          dateInput.value = today.toISOString().substring(0, 10);
        }
      } else {
        fields.classList.add("d-none");
      }
    }

    async function confirmarAplicacao() {
      const btn = document.getElementById("btnConfirmarAplicar");
      const alertDiv = document.getElementById("aplicarAlert");
      
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Confirmando...';
      alertDiv.classList.add("d-none");

      const data = {
        action: 'apply',
        id_cronograma: document.getElementById("cronoId").value,
        data_aplicacao: document.getElementById("cronoDataAplicacao").value,
        dose_aplicada: document.getElementById("cronoDoseAplicada").value,
        observacoes: document.getElementById("cronoObs").value,
        agendar_reforco: document.getElementById("cronoAgendarReforco").checked,
        data_reforco: document.getElementById("cronoDataReforco").value,
        dose_reforco: document.getElementById("cronoDoseReforco").value
      };

      try {
        const response = await fetch('cronograma_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
          alert(result.message);
          bootstrap.Modal.getInstance(document.getElementById("modalAplicarVacina")).hide();
          window.location.reload(); // Reload to refresh both KPIs and lists
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = result.message;
          alertDiv.classList.remove('d-none');
          btn.disabled = false;
          btn.innerHTML = 'Confirmar Aplicação';
        }
      } catch (error) {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Erro de conexão com o servidor.';
        alertDiv.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Confirmar Aplicação';
      }
    }

    function limparFiltros() {
      document.getElementById("filtroDataInicial").value = "";
      document.getElementById("filtroDataFinal").value = "";
      document.getElementById("filtroLote").value = "";
      document.getElementById("filtroEspecie").value = "";
      document.getElementById("filtroVacina").value = "";
      document.getElementById("filtroVet").value = "";
      document.getElementById("filtroSituacao").value = "Todas";
      carregarCronograma();
    }

    function exportarPDF() {
      window.print();
    }

    window.addEventListener("load", carregarCronograma);
  </script>
</body>
</html>
