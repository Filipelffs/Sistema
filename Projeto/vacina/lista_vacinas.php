<?php
require_once "../sessao.php";
$isAllowed = in_array($_SESSION['usuario_tipo'], ['admin', 'veterinario']);
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estoque - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="../style.css">
  <style>
    .card-estoque {
      border-radius: 12px;
      margin-bottom: 15px;
      transition: all 0.3s;
    }
    .card-estoque.esgotado {
      opacity: 0.65;
    }
    .status-badge {
      font-size: 0.8rem;
      padding: 0.4em 0.8em;
      border-radius: 20px;
    }
    .nav-pills .nav-link {
      border-radius: 20px;
      margin-right: 5px;
    }
  </style>
</head>
<body>

  <div class="topo-pagina d-none d-md-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="titulo-pagina">Estoque de Produtos</h2>
      <p class="subtitulo">Gerencie vacinas e medicamentos</p>
    </div>
  </div>

  <!-- Mobile header -->
  <div class="d-md-none text-center mb-4 mt-2">
    <h4 class="fw-bold text-success">Estoque</h4>
  </div>

  <!-- Busca e Filtros -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="row align-items-center g-3">
        <div class="col-12 col-md-6">
          <div class="input-icon-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="inputBusca" class="form-control form-control-custom" placeholder="Buscar produto..." onkeyup="filtrarLista()">
          </div>
        </div>
        <div class="col-12 col-md-6 text-md-end">
          <ul class="nav nav-pills justify-content-md-end justify-content-center" id="filtrosEstoque">
            <li class="nav-item">
              <a class="nav-link active" href="#" onclick="mudarAba(event, 'todos')">Todos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="mudarAba(event, 'vacina')">Vacinas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="mudarAba(event, 'medicamento')">Medicamentos</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de Estoque -->
  <div id="listaEstoque" class="row">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>
  </div>

  <!-- Botões Inferiores -->
  <?php if ($isAllowed): ?>
  <div class="row mt-4 g-2">
    <div class="col-6">
      <button class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm" onclick="abrirModalCadastro('vacina')">
        <i class="bi bi-plus-circle"></i> Nova Vacina
      </button>
    </div>
    <div class="col-6">
      <button class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm" onclick="abrirModalCadastro('medicamento')">
        <i class="bi bi-plus-circle"></i> Novo Med.
      </button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modal Cadastro/Edição -->
  <div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title" id="modalProdutoTitle">Cadastrar Produto</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="prodId" value="">
          <div id="modalAlert" class="alert d-none"></div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo</label>
            <select id="prodTipo" class="form-select">
              <option value="vacina">Vacina</option>
              <option value="medicamento">Medicamento</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome</label>
            <input type="text" id="prodNome" class="form-control" placeholder="Nome do produto">
          </div>
          
          <div class="row mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Quantidade</label>
              <input type="number" id="prodQtd" class="form-control" min="0" value="0">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Fabricação</label>
              <input type="date" id="prodFab" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Vencimento</label>
            <input type="date" id="prodVenc" class="form-control">
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Descrição (Opcional)</label>
            <textarea id="prodDesc" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarProduto()" id="btnSalvarProd">Salvar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="../menu.js"></script>

  <script>
    let produtos = [];
    let filtroAtual = 'todos';
    let modalProdutoInstance;

    document.addEventListener('DOMContentLoaded', () => {
      modalProdutoInstance = new bootstrap.Modal(document.getElementById('modalProduto'));
      carregarEstoque();
    });

    async function carregarEstoque() {
      try {
        const response = await fetch('vacina_action.php?action=list');
        const result = await response.json();
        if (result.success) {
          produtos = result.data;
          renderizarLista();
        } else {
          document.getElementById('listaEstoque').innerHTML = `<div class="col-12 alert alert-danger">${result.message}</div>`;
        }
      } catch (e) {
        document.getElementById('listaEstoque').innerHTML = `<div class="col-12 alert alert-danger">Erro de comunicação.</div>`;
      }
    }

    function mudarAba(event, tipo) {
      event.preventDefault();
      document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
      event.target.classList.add('active');
      filtroAtual = tipo;
      renderizarLista();
    }

    function filtrarLista() {
      renderizarLista();
    }

    function formatDate(dateStr) {
      if(!dateStr) return '--';
      const parts = dateStr.split('-');
      if(parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
      return dateStr;
    }

    function renderizarLista() {
      const container = document.getElementById('listaEstoque');
      const termoBusca = document.getElementById('inputBusca').value.toLowerCase();
      
      const filtrados = produtos.filter(p => {
        const matchFiltro = filtroAtual === 'todos' || p.tipo === filtroAtual;
        const matchBusca = p.nome.toLowerCase().includes(termoBusca);
        return matchFiltro && matchBusca;
      });

      if (filtrados.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-box fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhum produto encontrado.</h6>
          </div>`;
        return;
      }

      container.innerHTML = "";
      filtrados.forEach(p => {
        const qtd = parseInt(p.quantidade);
        const isEsgotado = qtd === 0;
        const badgeClass = isEsgotado ? 'bg-danger' : 'bg-success';
        const badgeText = isEsgotado ? 'Não tem no estoque' : 'Tem no estoque';
        const cardClass = isEsgotado ? 'esgotado' : '';
        const icon = p.tipo === 'vacina' ? 'bi-virus' : 'bi-capsule';

        container.innerHTML += `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-estoque shadow-sm p-3 ${cardClass}">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi ${icon} fs-4 text-${isEsgotado ? 'secondary' : 'success'}"></i>
                  <h5 class="mb-0 fw-bold">${p.nome}</h5>
                </div>
                <span class="badge ${badgeClass} status-badge">${badgeText}</span>
              </div>
              
              <div class="small mb-3 text-muted">
                <span class="text-capitalize border border-secondary rounded px-2 py-1 me-2">${p.tipo}</span>
                Disponível: <strong class="fs-6">${qtd}</strong>
              </div>

              <div class="row g-2 small mb-3" style="color:var(--text-muted)">
                <div class="col-6">
                  <i class="bi bi-calendar-check me-1"></i> Fab: ${formatDate(p.data_fabricacao)}
                </div>
                <div class="col-6">
                  <i class="bi bi-calendar-x me-1 text-danger"></i> Venc: <span class="${isEsgotado ? '' : 'fw-bold text-danger'}">${formatDate(p.data_vencimento)}</span>
                </div>
              </div>

              ${<?php echo $isAllowed ? 'true' : 'false'; ?> ? `
              <div class="d-flex gap-2 mt-auto border-top pt-3">
                <button class="btn btn-sm btn-outline-secondary w-100 rounded-pill" onclick='editarProduto(${JSON.stringify(p)})'>
                  <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-circle px-2" onclick="excluirProduto(${p.id})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
              ` : ''}
            </div>
          </div>
        `;
      });
    }

    function mostrarAlertaModal(mensagem, tipo = 'danger') {
      const alert = document.getElementById('modalAlert');
      alert.className = `alert alert-${tipo}`;
      alert.textContent = mensagem;
      alert.classList.remove('d-none');
    }

    function abrirModalCadastro(tipo) {
      document.getElementById('modalProdutoTitle').innerText = tipo === 'vacina' ? 'Nova Vacina' : 'Novo Medicamento';
      document.getElementById('prodId').value = '';
      document.getElementById('prodTipo').value = tipo;
      document.getElementById('prodNome').value = '';
      document.getElementById('prodQtd').value = '0';
      document.getElementById('prodFab').value = '';
      document.getElementById('prodVenc').value = '';
      document.getElementById('prodDesc').value = '';
      document.getElementById('modalAlert').classList.add('d-none');
      modalProdutoInstance.show();
    }

    function editarProduto(p) {
      document.getElementById('modalProdutoTitle').innerText = 'Editar Produto';
      document.getElementById('prodId').value = p.id;
      document.getElementById('prodTipo').value = p.tipo;
      document.getElementById('prodNome').value = p.nome;
      document.getElementById('prodQtd').value = p.quantidade;
      document.getElementById('prodFab').value = p.data_fabricacao;
      document.getElementById('prodVenc').value = p.data_vencimento;
      document.getElementById('prodDesc').value = p.descricao;
      document.getElementById('modalAlert').classList.add('d-none');
      modalProdutoInstance.show();
    }

    async function salvarProduto() {
      const id = document.getElementById('prodId').value;
      const data = {
        id: id,
        tipo: document.getElementById('prodTipo').value,
        nome: document.getElementById('prodNome').value,
        quantidade: document.getElementById('prodQtd').value,
        data_fabricacao: document.getElementById('prodFab').value,
        data_vencimento: document.getElementById('prodVenc').value,
        descricao: document.getElementById('prodDesc').value
      };

      if (!data.nome || !data.quantidade || !data.data_vencimento) {
        mostrarAlertaModal("Preencha os campos obrigatórios (Nome, Quantidade e Vencimento).");
        return;
      }

      const action = id ? 'edit' : 'create';
      const btn = document.getElementById('btnSalvarProd');
      btn.disabled = true;

      try {
        const res = await fetch(`vacina_action.php?action=${action}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
          modalProdutoInstance.hide();
          carregarEstoque();
        } else {
          mostrarAlertaModal(result.message);
        }
      } catch(e) {
        mostrarAlertaModal("Erro de conexão.");
      } finally {
        btn.disabled = false;
      }
    }

    async function excluirProduto(id) {
      if(!confirm("Deseja realmente excluir este produto?")) return;
      try {
        const res = await fetch(`vacina_action.php?action=delete`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({id: id})
        });
        const result = await res.json();
        if (result.success) {
          carregarEstoque();
        } else {
          alert(result.message);
        }
      } catch(e) {
        alert("Erro de conexão.");
      }
    }
  </script>
</body>
</html>
