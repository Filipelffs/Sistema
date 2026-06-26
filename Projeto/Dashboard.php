<?php
require_once "sessao.php";
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
  <title>Dashboard - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

  <div class="topo-pagina d-none d-md-flex">
    <div>
      <h2 class="titulo-pagina">DASHBOARD</h2>
      <p class="subtitulo">Visão geral do sistema de imunização animal (Dados em Tempo Real)</p>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="kpi-row" id="kpiContainer">
    <div class="col-12 text-center py-4" id="kpiLoading">
      <div class="spinner-border text-success" role="status"></div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="row mt-4 mb-4">
    <div class="col-12 col-lg-6 mb-4">
      <div class="card card-premium shadow-sm h-100">
        <div class="card-header-green">
          <span class="fs-6">Animais por Lote</span>
        </div>
        <div class="card-body">
          <canvas id="chartAnimaisLote"></canvas>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-lg-6 mb-4">
      <div class="card card-premium shadow-sm h-100">
        <div class="card-header-green">
          <span class="fs-6">Top Estoque (Qtd > 0)</span>
        </div>
        <div class="card-body">
          <canvas id="chartEstoque"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Alerts Section -->
  <div class="row">
    <div class="col-12 mb-4">
      <h5 class="fw-bold mb-3 text-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i> ALERTA DE ANIMAIS SEM VACINAÇÃO REGISTRADA
      </h5>
      <div id="alertsAtrasadas">
        <!-- Rendered dynamically -->
      </div>
    </div>
  </div>

  <!-- Bottom Actions -->
  <div class="row mt-4 g-3">
    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
    <div class="col-12 col-md-6">
      <a href="cadastro_animal.php" class="btn btn-success btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
        <i class="bi bi-plus-circle"></i> CADASTRAR ANIMAL
      </a>
    </div>
    <div class="col-12 col-md-6">
      <a href="registro_aplicacao.php" class="btn btn-outline-success btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
        <i class="bi bi-virus"></i> NOVA APLICAÇÃO
      </a>
    </div>
    <?php else: ?>
    <div class="col-12">
      <a href="registro_aplicacao.php" class="btn btn-success btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
        <i class="bi bi-virus"></i> NOVA APLICAÇÃO
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <!-- Script to update KPIs and Alerts dynamically -->
  <script>
    document.addEventListener("DOMContentLoaded", async function () {
      if (document.documentElement.getAttribute('data-theme') === 'dark') {
        Chart.defaults.color = '#ffffff';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.15)';
      }
      try {
        const response = await fetch('dashboard_data.php');
        const result = await response.json();
        
        if(result.success) {
          const data = result.data;
          
          // Render KPIs
          const kpiContainer = document.getElementById('kpiContainer');
          kpiContainer.innerHTML = `
            <div class="kpi-card green">
              <div class="kpi-title">Total de animais</div>
              <div class="kpi-value">${data.kpis.total_animais}</div>
            </div>
            <div class="kpi-card yellow">
              <div class="kpi-title">Aplicações Realizadas</div>
              <div class="kpi-value">${data.kpis.vacinas_aplicadas}</div>
            </div>
            <div class="kpi-card red">
              <div class="kpi-title">Sem Aplicação (Alerta)</div>
              <div class="kpi-value">${data.kpis.vacinas_atrasadas}</div>
            </div>
          `;

          // Render Alertas
          const alertasContainer = document.getElementById('alertsAtrasadas');
          if (data.alertas.length > 0) {
            data.alertas.forEach(a => {
              alertasContainer.innerHTML += `
                <div class="alert-box danger mb-3">
                  <div class="alert-content">
                    <h6>${a.nome_animal} (${a.numero_brinco || 'N/A'})</h6>
                    <p>Este animal não possui nenhum registro de aplicação no sistema.</p>
                  </div>
                  <a href="ficha_animal.php?id=${a.id_animal}" class="btn btn-sm btn-danger rounded-pill px-3">Ver Ficha</a>
                </div>
              `;
            });
          } else {
            alertasContainer.innerHTML = `<div class="alert alert-success rounded-pill shadow-sm"><i class="bi bi-check-circle me-2"></i> Todos os animais possuem pelo menos uma aplicação registrada.</div>`;
          }

          // Render Charts
          renderChartAnimaisLote(data.charts.animais_lote);
          renderChartEstoque(data.charts.estoque);

        }
      } catch (error) {
        console.error("Erro ao carregar dashboard:", error);
      }
    });

    function renderChartAnimaisLote(dados) {
      const ctx = document.getElementById('chartAnimaisLote').getContext('2d');
      const labels = dados.map(d => d.lote || 'Sem Lote');
      const valores = dados.map(d => d.quantidade);

      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: valores,
            backgroundColor: ['#198754', '#20c997', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    function renderChartEstoque(dados) {
      const ctx = document.getElementById('chartEstoque').getContext('2d');
      const labels = dados.map(d => d.nome);
      const valores = dados.map(d => d.quantidade);

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Quantidade em Estoque',
            data: valores,
            backgroundColor: '#198754',
            borderRadius: 5
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }
  </script>
</body>
</html>
