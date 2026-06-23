<?php
require_once "sessao.php";
$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lotes - Vacinação Animal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .btn-excluir-opcao {
      text-align: left;
      white-space: normal;
    }
    .opcao-excluir-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .animal-select-item {
      cursor: pointer;
      transition: background .15s;
    }
    .animal-select-item:hover {
      background: #f0faf0;
    }
    .animal-select-item.selected {
      background: #d4edda;
    }
  </style>
</head>

<body>

  <div class="topo-pagina d-none d-md-flex justify-content-between align-items-center">
    <div>
      <h2 class="titulo-pagina">Lotes de Animais</h2>
      <p class="subtitulo">Gerenciamento de lotes e agrupamentos do rebanho</p>
    </div>
    <?php if ($isAdmin): ?>
    <div>
      <button class="btn btn-success rounded-pill px-4" onclick="abrirModalNovoLote()">
        <i class="bi bi-plus-circle me-2"></i> Novo Lote
      </button>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($isAdmin): ?>
  <div class="d-md-none mb-3">
    <button class="btn btn-success w-100 rounded-pill" onclick="abrirModalNovoLote()">
      <i class="bi bi-plus-circle me-2"></i> Novo Lote
    </button>
  </div>
  <?php endif; ?>

  <div class="row g-4" id="lotesContainer">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>
  </div>

  <!-- MODAL NOVO/EDITAR LOTE -->
  <div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title" id="modalLoteTitle">Lote</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="loteId" value="" />
          <div id="modalAlert" class="alert d-none"></div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome do Lote</label>
            <input type="text" id="loteNome" class="form-control" placeholder="Ex: Lote 1 - Bezerros" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarLote()" id="btnSalvarLote">Salvar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL OPÇÕES DE EXCLUSÃO -->
  <div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-danger text-white rounded-top-4">
          <h5 class="modal-title">
            <i class="bi bi-trash3-fill me-2"></i>Opções de Exclusão
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pb-1">
          <p class="text-muted mb-3">Lote: <strong id="excluirLoteNome"></strong></p>
          <div class="d-grid gap-2">

            <!-- Opção 1: Animal específico -->
            <button class="btn btn-outline-warning btn-excluir-opcao py-3 px-3 d-flex align-items-center gap-3 rounded-3"
                    onclick="abrirEscolhaAnimal()">
              <span class="opcao-excluir-icon bg-warning bg-opacity-15 text-warning">
                <i class="bi bi-person-dash-fill fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">Remover um animal específico</div>
                <small class="text-muted">Escolha um animal para retirar do lote. Ele ficará sem lote.</small>
              </div>
            </button>

            <!-- Opção 2: Todos os animais do lote -->
            <button class="btn btn-outline-orange btn-excluir-opcao py-3 px-3 d-flex align-items-center gap-3 rounded-3"
                    style="border-color:#fd7e14; color:#fd7e14;"
                    onclick="confirmarRemoverTodos()">
              <span class="opcao-excluir-icon text-white" style="background:#fd7e14;">
                <i class="bi bi-people-fill fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">Remover todos os animais do lote</div>
                <small class="text-muted">Os animais ficam dispersos. O lote continua existindo (vazio).</small>
              </div>
            </button>

            <!-- Opção 3: Excluir o lote em si -->
            <button class="btn btn-outline-danger btn-excluir-opcao py-3 px-3 d-flex align-items-center gap-3 rounded-3"
                    onclick="confirmarExcluirLote()">
              <span class="opcao-excluir-icon bg-danger bg-opacity-15 text-danger">
                <i class="bi bi-trash-fill fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">Excluir o lote</div>
                <small class="text-muted">O lote é deletado. Os animais ficam dispersos para serem redirecionados.</small>
              </div>
            </button>

          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL ESCOLHER ANIMAL ESPECÍFICO -->
  <div class="modal fade" id="modalEscolherAnimal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-warning text-dark rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-person-dash-fill me-2"></i>Selecionar Animal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-2">
          <p class="text-muted small px-2 mb-2">Clique no animal que deseja remover do lote:</p>
          <div id="listaAnimaisEscolha" style="max-height:280px; overflow-y:auto;">
            <!-- preenchido dinamicamente -->
          </div>
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-warning rounded-pill px-3 btn-sm text-dark fw-semibold" onclick="confirmarRemoverAnimal()" id="btnConfirmarRemoverAnimal" disabled>
            Remover
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="menu.js"></script>

  <script>
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    let modalLoteInstance, modalExcluirInstance, modalEscolherAnimalInstance;
    let _excluirLoteId = null, _excluirLoteNome = null, _excluirAnimais = [];
    let _animalSelecionadoId = null;

    document.addEventListener('DOMContentLoaded', () => {
      modalLoteInstance         = new bootstrap.Modal(document.getElementById('modalLote'));
      modalExcluirInstance      = new bootstrap.Modal(document.getElementById('modalExcluir'));
      modalEscolherAnimalInstance = new bootstrap.Modal(document.getElementById('modalEscolherAnimal'));
      carregarLotes();
    });

    // Mapa global para guardar os dados de cada lote (evita JSON inline no onclick)
    const _lotesMap = {};

    async function carregarLotes() {
      const container = document.getElementById("lotesContainer");
      try {
        const response = await fetch('lote_action.php?action=list');
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erro ao carregar lotes');

        const lotes = result.data;
        container.innerHTML = "";

        if (lotes.length === 0) {
          container.innerHTML = `
            <div class="col-12 text-center py-5">
              <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
              <h6 class="text-muted">Nenhum lote de animal encontrado</h6>
            </div>`;
          return;
        }

        lotes.forEach(lote => {
          // Guardar dados do lote no map para uso seguro no modal (evita problemas com caracteres especiais no onclick)
          _lotesMap[lote.id_lote] = { nome: lote.codigo_lote, animais: lote.animais || [] };

          const qtd = parseInt(lote.qtd_real);
          const countText = qtd === 1 ? "1 animal" : `${qtd} animais`;

          let tableRows = "";
          if (lote.animais && lote.animais.length > 0) {
            lote.animais.forEach(a => {
              tableRows += `
                <tr>
                  <td>${a.numero_brinco || 'N/A'}</td>
                  <td class="fw-semibold">
                    <a href="ficha_animal.php?id=${a.id_animal}" class="text-success text-decoration-none">${a.nome_animal}</a>
                  </td>
                </tr>`;
            });
          } else {
            tableRows = `<tr><td colspan="2" class="text-muted">Nenhum animal neste lote</td></tr>`;
          }

          let editBtn = '';
          if (isAdmin) {
            editBtn = `
              <div class="d-flex justify-content-center gap-2 mt-3">
                <button class="btn btn-outline-success rounded-pill px-3 btn-sm"
                        onclick="abrirModalEditarLote(${lote.id_lote}, '${lote.codigo_lote.replace(/'/g,"\\'")}')">
                  <i class="bi bi-pencil-fill me-1"></i> Editar
                </button>
                <button class="btn btn-outline-danger rounded-pill px-3 btn-sm"
                        onclick="abrirModalExcluir(${lote.id_lote})">
                  <i class="bi bi-trash-fill me-1"></i> Excluir
                </button>
              </div>`;
          }

          container.innerHTML += `
            <div class="col-12 col-md-6">
              <div class="card card-premium shadow-sm h-100">
                <div class="card-header-green d-flex justify-content-between align-items-center">
                  <span class="fs-5">${lote.codigo_lote}</span>
                  <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold">${countText}</span>
                </div>
                <div class="card-body p-3 d-flex flex-column">
                  <div class="table-responsive flex-grow-1">
                    <table class="table table-bordered table-striped text-center mb-0">
                      <thead class="table-light">
                        <tr><th>Código</th><th>Animal</th></tr>
                      </thead>
                      <tbody>${tableRows}</tbody>
                    </table>
                  </div>
                  ${editBtn}
                </div>
              </div>
            </div>`;
        });
      } catch (error) {
        container.innerHTML = `<div class="col-12 text-center text-danger py-5">Erro: ${error.message}</div>`;
      }
    }

    /* ---- Modal novo/editar lote ---- */
    function mostrarAlertaModal(msg, tipo = 'danger') {
      const a = document.getElementById('modalAlert');
      a.className = `alert alert-${tipo}`;
      a.textContent = msg;
      a.classList.remove('d-none');
    }
    function esconderAlertaModal() { document.getElementById('modalAlert').classList.add('d-none'); }

    function abrirModalNovoLote() {
      document.getElementById("modalLoteTitle").innerText = "Novo Lote";
      document.getElementById("loteId").value = "";
      document.getElementById("loteNome").value = "";
      esconderAlertaModal();
      modalLoteInstance.show();
    }
    function abrirModalEditarLote(id, nome) {
      document.getElementById("modalLoteTitle").innerText = "Editar Lote";
      document.getElementById("loteId").value = id;
      document.getElementById("loteNome").value = nome;
      esconderAlertaModal();
      modalLoteInstance.show();
    }
    async function salvarLote() {
      const id   = document.getElementById("loteId").value;
      const nome = document.getElementById("loteNome").value.trim();
      if (!nome) { mostrarAlertaModal("O nome do lote não pode ser vazio."); return; }

      const btn = document.getElementById('btnSalvarLote');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';

      try {
        const res  = await fetch(`lote_action.php?action=${id ? 'edit' : 'create'}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_lote: id, codigo_lote: nome })
        });
        const result = await res.json();
        if (result.success) { modalLoteInstance.hide(); carregarLotes(); }
        else mostrarAlertaModal(result.message);
      } catch { mostrarAlertaModal("Erro na comunicação com o servidor."); }
      finally { btn.disabled = false; btn.innerText = "Salvar"; }
    }

    /* ---- Modal 3 opções de exclusão ---- */
    function abrirModalExcluir(id) {
      const dados = _lotesMap[id];
      if (!dados) { alert('Dados do lote não encontrados. Recarregue a página.'); return; }
      _excluirLoteId   = id;
      _excluirLoteNome = dados.nome;
      _excluirAnimais  = dados.animais;
      document.getElementById('excluirLoteNome').textContent = dados.nome;
      modalExcluirInstance.show();
    }

    /* Opção 1 – escolher animal específico */
    function abrirEscolhaAnimal() {
      if (_excluirAnimais.length === 0) {
        alert('Este lote não possui animais para remover.');
        return;
      }
      _animalSelecionadoId = null;
      document.getElementById('btnConfirmarRemoverAnimal').disabled = true;

      const lista = document.getElementById('listaAnimaisEscolha');
      lista.innerHTML = _excluirAnimais.map(a => `
        <div class="animal-select-item d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1"
             id="ai-${a.id_animal}"
             onclick="selecionarAnimal(${a.id_animal})">
          <i class="bi bi-tag-fill text-success"></i>
          <span class="fw-semibold">${a.nome_animal}</span>
          <small class="text-muted ms-auto">${a.numero_brinco || ''}</small>
        </div>`).join('');

      modalExcluirInstance.hide();
      modalEscolherAnimalInstance.show();
    }
    function selecionarAnimal(id) {
      _animalSelecionadoId = id;
      document.querySelectorAll('.animal-select-item').forEach(el => el.classList.remove('selected'));
      document.getElementById('ai-' + id)?.classList.add('selected');
      document.getElementById('btnConfirmarRemoverAnimal').disabled = false;
    }
    async function confirmarRemoverAnimal() {
      if (!_animalSelecionadoId) return;
      try {
        const res = await fetch('lote_action.php?action=delete_animal', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_animal: _animalSelecionadoId, id_lote: _excluirLoteId })
        });
        const result = await res.json();
        if (result.success) {
          modalEscolherAnimalInstance.hide();
          carregarLotes();
        } else {
          alert(result.message);
        }
      } catch { alert("Erro na comunicação com o servidor."); }
    }

    /* Opção 2 – remover todos os animais */
    async function confirmarRemoverTodos() {
      if (!confirm(`Remover TODOS os animais do lote "${_excluirLoteNome}"?\nEles ficarão dispersos (sem lote), mas o lote continuará existindo.`)) return;
      try {
        const res = await fetch('lote_action.php?action=remove_all_animals', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_lote: _excluirLoteId })
        });
        const result = await res.json();
        if (result.success) { modalExcluirInstance.hide(); carregarLotes(); }
        else alert(result.message);
      } catch { alert("Erro na comunicação com o servidor."); }
    }

    /* Opção 3 – excluir o próprio lote */
    async function confirmarExcluirLote() {
      if (!confirm(`Excluir o lote "${_excluirLoteNome}"?\nOs animais ficarão dispersos e poderão ser redirecionados para outro lote.`)) return;
      try {
        const res = await fetch('lote_action.php?action=delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_lote: _excluirLoteId })
        });
        const result = await res.json();
        if (result.success) { modalExcluirInstance.hide(); carregarLotes(); }
        else alert(result.message);
      } catch { alert("Erro na comunicação com o servidor."); }
    }
  </script>
</body>
</html>
