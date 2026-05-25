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
  <!-- TOPO (menu.js will wrap this inside main content) -->
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
      <div class="input-group input-icon-wrapper">
        <span class="input-group-text bg-transparent border-0 ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="pesquisaVacinas" class="form-control form-control-custom" placeholder="Pesquisar por vacina, animal ou lote..." onkeyup="filtrarVacinas()" />
      </div>
    </div>
  </div>

  <!-- LIST OF VACCINE CARDS -->
  <div class="row g-4" id="listaVacinasHistorico">
    <!-- Rendered dynamically -->
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
            <input type="text" id="editAplItem" class="form-control" />
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
    function getAplicacoes() {
      return JSON.parse(localStorage.getItem("aplicacoes")) || [];
    }

    function getAnimais() {
      return JSON.parse(localStorage.getItem("animais")) || [];
    }

    function renderizarAplicacoes(lista = getAplicacoes()) {
      const container = document.getElementById("listaVacinasHistorico");
      container.innerHTML = "";
      const animais = getAnimais();

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
        const animal = animais.find(a => a.id === apl.animalId) || { nome: "Sem Animal", numero: "---", lote: "---" };

        let statusClass = "success";
        let statusText = "No prazo";
        if (apl.status === "Atrasada") {
          statusClass = "danger";
          statusText = "Atrasada";
        } else if (apl.status === "Pendente") {
          statusClass = "warning";
          statusText = "Pendente";
        } else if (apl.status === "Concluído") {
          statusClass = "success";
          statusText = "Aplicada";
        }

        let formattedDate = apl.data;
        const parts = apl.data.split("-");
        if (parts.length === 3) {
          formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        container.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4">
            <div class="item-card">
              <div class="item-card-header">
                <span class="fs-5">${apl.itemNome}</span>
                <span class="badge-status ${statusClass} bg-white text-${statusClass === 'warning' ? 'warning-color' : statusClass === 'success' ? 'success' : 'danger'} py-1 px-3 fw-bold">${statusText}</span>
              </div>
              <div class="item-card-body">
                <p><strong>Animal:</strong> ${animal.nome} (${animal.numero})</p>
                <p><strong>Tipo:</strong> ${apl.tipo || 'Vacina'}</p>
                <p><strong>Dose:</strong> ${apl.dose || '1ª dose'}</p>
                <p><strong>Data:</strong> ${formattedDate}</p>
                <p><strong>Técnico:</strong> ${apl.tecnico || 'Julia Silva'}</p>
              </div>
              <div class="item-card-actions">
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="editarAplicacao(${apl.id})">
                  <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="excluirAplicacao(${apl.id})">
                  <i class="bi bi-trash"></i> Excluir
                </button>
              </div>
            </div>
          </div>
        `;
      });
    }

    function excluirAplicacao(id) {
      if (confirm("Deseja realmente excluir este registro de aplicação?")) {
        let aplicacoes = getAplicacoes();
        aplicacoes = aplicacoes.filter(a => a.id !== id);
        localStorage.setItem("aplicacoes", JSON.stringify(aplicacoes));
        renderizarAplicacoes();
      }
    }

    function editarAplicacao(id) {
      const aplicacoes = getAplicacoes();
      const apl = aplicacoes.find(a => a.id === id);
      if (apl) {
        document.getElementById("editAplId").value = apl.id;
        document.getElementById("editAplItem").value = apl.itemNome;
        document.getElementById("editAplDose").value = apl.dose || '';
        document.getElementById("editAplData").value = apl.data;
        document.getElementById("editAplTecnico").value = apl.tecnico || 'Julia Silva';
        document.getElementById("editAplStatus").value = apl.status;

        new bootstrap.Modal(document.getElementById("modalEditarAplicacao")).show();
      }
    }

    function salvarEdicaoAplicacao() {
      const id = parseInt(document.getElementById("editAplId").value);
      const aplicacoes = getAplicacoes();
      const index = aplicacoes.findIndex(a => a.id === id);

      if (index !== -1) {
        aplicacoes[index].itemNome = document.getElementById("editAplItem").value;
        aplicacoes[index].dose = document.getElementById("editAplDose").value;
        aplicacoes[index].data = document.getElementById("editAplData").value;
        aplicacoes[index].tecnico = document.getElementById("editAplTecnico").value;
        aplicacoes[index].status = document.getElementById("editAplStatus").value;

        localStorage.setItem("aplicacoes", JSON.stringify(aplicacoes));
        
        bootstrap.Modal.getInstance(document.getElementById("modalEditarAplicacao")).hide();
        renderizarAplicacoes();
      }
    }

    function filtrarVacinas() {
      const query = document.getElementById("pesquisaVacinas").value.toLowerCase();
      const aplicacoes = getAplicacoes();
      const animais = getAnimais();
      
      const filtrados = aplicacoes.filter(apl => {
        const animal = animais.find(a => a.id === apl.animalId) || { nome: "", numero: "", lote: "" };
        return apl.itemNome.toLowerCase().includes(query) ||
               animal.nome.toLowerCase().includes(query) ||
               animal.numero.toLowerCase().includes(query) ||
               (apl.lote && apl.lote.toLowerCase().includes(query));
      });
      
      renderizarAplicacoes(filtrados);
    }

    window.addEventListener("load", () => {
      renderizarAplicacoes();
    });
  </script>
</body>

</html>