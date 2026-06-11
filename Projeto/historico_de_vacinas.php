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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Histórico de Vacinas - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <!-- CSS -->
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <!-- TOPO -->
  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Histórico de Aplicações</h2>
      <p class="subtitulo">Registro completo de vacinas e medicamentos aplicados</p>
    </div>
    <a class="btn btn-success rounded-pill px-4 py-2" href="Registro de Aplicação.php">
      <i class="bi bi-plus-circle me-2"></i> Nova Aplicação
    </a>
  </div>

  <!-- BUSCA -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="input-icon-wrapper">
        <i class="bi bi-search"></i>
        <input type="text" id="pesquisaVacinas" class="form-control form-control-custom" placeholder="Pesquisar por vacina, animal ou lote..." onkeyup="filtrarVacinas()" />
      </div>
    </div>
  </div>

  <!-- LIST OF VACCINE CARDS -->
  <div class="row g-4" id="listaVacinasHistorico">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
    </div>
  </div>

  <!-- EDIT APPLICATION MODAL -->
  <div class="modal fade" id="modalEditarAplicacao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">Editar Aplicação</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editAplId" />
          <div class="mb-3">
            <label class="form-label fw-semibold">Vacina / Medicamento</label>
            <input type="text" id="editAplItem" class="form-control" readonly disabled style="background-color: #e9ecef; color: #6c757d;" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Dose / Tipo</label>
            <input type="text" id="editAplDose" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Data</label>
            <input type="date" id="editAplData" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Técnico</label>
            <input type="text" id="editAplTecnico" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select id="editAplStatus" class="form-select">
              <option value="Concluído">Concluído</option>
              <option value="Pendente">Pendente</option>
              <option value="Atrasada">Atrasada</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Observações</label>
            <textarea id="editAplObs" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarEdicaoAplicacao()">Salvar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <!-- JS LOGIC -->
  <script>
    let aplicacoesList = [];

    async function carregarHistorico() {
      try {
        const response = await fetch('aplicacao_action.php?action=list');
        const result = await response.json();
        if (result.success) {
          aplicacoesList = result.data;
          renderizarAplicacoes(aplicacoesList);
        } else {
          document.getElementById('listaVacinasHistorico').innerHTML = `<div class="col-12 alert alert-danger">${result.message}</div>`;
        }
      } catch (error) {
        document.getElementById('listaVacinasHistorico').innerHTML = `<div class="col-12 alert alert-danger">Erro de conexão ao carregar histórico.</div>`;
      }
    }

    function renderizarAplicacoes(lista) {
      const container = document.getElementById("listaVacinasHistorico");
      container.innerHTML = "";

      if (lista.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-virus fs-1 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Nenhum registro de aplicação encontrado</h5>
          </div>
        `;
        return;
      }

      lista.forEach(apl => {
        let statusClass = "success";
        let statusText = "Aplicada";
        if (apl.status_aplicacao === "Atrasada") {
          statusClass = "danger";
          statusText = "Atrasada";
        } else if (apl.status_aplicacao === "Pendente") {
          statusClass = "warning";
          statusText = "Pendente";
        } else if (apl.status_aplicacao === "Concluído") {
          statusClass = "success";
          statusText = "Aplicada";
        }

        let formattedDate = apl.data_aplicacao;
        const parts = apl.data_aplicacao.split("-");
        if (parts.length === 3) {
          formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        container.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4">
            <div class="item-card">
              <div class="item-card-header">
                <span class="fs-5">${apl.produto_nome || 'Produto Removido'}</span>
                <span class="badge-status ${statusClass} bg-white text-${statusClass === 'warning' ? 'warning-color' : statusClass === 'success' ? 'success' : 'danger'} py-1 px-3 fw-bold">${statusText}</span>
              </div>
              <div class="item-card-body">
                <p><strong>Animal:</strong> ${apl.nome_animal || 'Sem Animal'} (${apl.numero_brinco || '---'})</p>
                <p><strong>Lote:</strong> <span class="badge bg-light text-dark border">${apl.codigo_lote || 'Sem Lote'}</span></p>
                <p><strong>Tipo:</strong> ${apl.produto_tipo ? (apl.produto_tipo.charAt(0).toUpperCase() + apl.produto_tipo.slice(1)) : 'Vacina'}</p>
                <p><strong>Dose:</strong> ${apl.dose || '1ª dose'}</p>
                <p><strong>Data:</strong> ${formattedDate}</p>
                <p><strong>Técnico:</strong> ${apl.tecnico || 'Não informado'}</p>
                <p><strong>Obs:</strong> <span class="text-muted">${apl.observacoes || 'Nenhuma'}</span></p>
              </div>
              <div class="item-card-actions">
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick='editarAplicacao(${JSON.stringify(apl).replace(/'/g, "&#39;")})'>
                  <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="excluirAplicacao(${apl.id_aplicacao})">
                  <i class="bi bi-trash"></i> Excluir
                </button>
              </div>
            </div>
          </div>
        `;
      });
    }

    async function excluirAplicacao(id) {
      if (confirm("Deseja realmente excluir este registro de aplicação?")) {
        try {
          const res = await fetch('aplicacao_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id_aplicacao: id })
          });
          const result = await res.json();
          if (result.success) {
            carregarHistorico();
          } else {
            alert(result.message);
          }
        } catch (e) {
          alert("Erro de conexão.");
        }
      }
    }

    function editarAplicacao(apl) {
      document.getElementById("editAplId").value = apl.id_aplicacao;
      document.getElementById("editAplItem").value = apl.produto_nome || '';
      document.getElementById("editAplDose").value = apl.dose || '';
      document.getElementById("editAplData").value = apl.data_aplicacao;
      document.getElementById("editAplTecnico").value = apl.tecnico || '';
      document.getElementById("editAplStatus").value = apl.status_aplicacao;
      document.getElementById("editAplObs").value = apl.observacoes || '';

      new bootstrap.Modal(document.getElementById("modalEditarAplicacao")).show();
    }

    async function salvarEdicaoAplicacao() {
      const data = {
        action: 'edit',
        id_aplicacao: document.getElementById("editAplId").value,
        dose: document.getElementById("editAplDose").value,
        data_aplicacao: document.getElementById("editAplData").value,
        tecnico: document.getElementById("editAplTecnico").value,
        status: document.getElementById("editAplStatus").value,
        observacoes: document.getElementById("editAplObs").value
      };

      try {
        const res = await fetch('aplicacao_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
          bootstrap.Modal.getInstance(document.getElementById("modalEditarAplicacao")).hide();
          carregarHistorico();
        } else {
          alert(result.message);
        }
      } catch (e) {
        alert("Erro de conexão.");
      }
    }

    function filtrarVacinas() {
      const query = document.getElementById("pesquisaVacinas").value.toLowerCase();
      const filtrados = aplicacoesList.filter(apl => 
        (apl.produto_nome && apl.produto_nome.toLowerCase().includes(query)) ||
        (apl.nome_animal && apl.nome_animal.toLowerCase().includes(query)) ||
        (apl.numero_brinco && apl.numero_brinco.toLowerCase().includes(query)) ||
        (apl.codigo_lote && apl.codigo_lote.toLowerCase().includes(query))
      );
      renderizarAplicacoes(filtrados);
    }

    window.addEventListener("load", carregarHistorico);
  </script>
</body>

</html>