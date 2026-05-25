<?php
require_once "sessao.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <script>
    window.USER_SESSION = {
      id:    <?php echo json_encode($_SESSION['usuario_id']); ?>,
      nome:  <?php echo json_encode($_SESSION['usuario_nome']); ?>,
      email: <?php echo json_encode($_SESSION['usuario_email']); ?>,
      tipo:  <?php echo json_encode($_SESSION['usuario_tipo']); ?>
    };
  </script> 
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estoque Vacinas/Medicamentos - Vacinação Animal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .filtro-abas { display: flex; gap: 8px; margin-bottom: 20px; }
    .filtro-btn {
      padding: 8px 20px; border-radius: 50px; border: 2px solid var(--primary-green);
      background: transparent; color: var(--primary-green); font-weight: 600;
      cursor: pointer; transition: all 0.25s; font-size: 0.9rem;
    }
    .filtro-btn.active, .filtro-btn:hover {
      background: var(--primary-green); color: white;
    }
    .item-card.sem-estoque {
      opacity: 0.65; filter: grayscale(0.5);
    }
    .item-card.sem-estoque .item-card-header {
      background-color: #9e9e9e;
    }
    .badge-estoque-ok {
      background: rgba(31,175,122,0.15); color: #168B61;
      border-radius: 50px; padding: 4px 12px; font-size: 0.78rem; font-weight: 700;
    }
    .badge-estoque-zero {
      background: rgba(158,158,158,0.2); color: #757575;
      border-radius: 50px; padding: 4px 12px; font-size: 0.78rem; font-weight: 700;
    }
    .fab-container {
      position: fixed; bottom: 24px; right: 20px;
      display: flex; flex-direction: column; align-items: flex-end; gap: 10px; z-index: 900;
    }
    .fab-btn {
      border-radius: 50px; padding: 12px 22px; font-weight: 600;
      box-shadow: 0 4px 15px rgba(31,175,122,0.35); font-size: 0.9rem;
      display: flex; align-items: center; gap: 8px; border: none; cursor: pointer;
      transition: all 0.25s;
    }
    .fab-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(31,175,122,0.45); }
    .fab-vacina { background: var(--primary-green); color: white; }
    .fab-med    { background: white; color: var(--primary-green); border: 2px solid var(--primary-green); }
    .card-fade-out { transition: opacity 0.4s; opacity: 0 !important; }
  </style>
</head>
<body>

  <!-- TOPO -->
  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Lista de Vacinas/Medicamentos</h2>
      <p class="subtitulo">Controle de estoque e aplicações no rebanho</p>
    </div>
  </div>

  <!-- BUSCA -->
  <div class="card card-premium mb-3">
    <div class="card-body py-3">
      <div class="input-group input-icon-wrapper">
        <span class="input-group-text bg-transparent border-0 ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="pesquisaEstoque" class="form-control form-control-custom"
               placeholder="Pesquisar vacina ou medicamento..." oninput="renderFiltrado()" />
      </div>
    </div>
  </div>

  <!-- FILTRO DE ABAS -->
  <div class="filtro-abas">
    <button class="filtro-btn active" id="btn-todos"        onclick="setFiltro('todos')">Todos</button>
    <button class="filtro-btn"        id="btn-vacina"       onclick="setFiltro('vacina')">Vacinas</button>
    <button class="filtro-btn"        id="btn-medicamento"  onclick="setFiltro('medicamento')">Medicamentos</button>
  </div>

  <!-- CARDS -->
  <div class="row g-3" id="estoqueContainer">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
      <p class="text-muted mt-3">Carregando estoque...</p>
    </div>
  </div>

  <!-- FAB BUTTONS -->
  <div class="fab-container">
    <button class="fab-btn fab-med"    onclick="abrirModalNovo('medicamento')">
      <i class="bi bi-capsule"></i> Novo Medicamento
    </button>
    <button class="fab-btn fab-vacina" onclick="abrirModalNovo('vacina')">
      <i class="bi bi-virus"></i> Nova Vacina
    </button>
  </div>

  <!-- ══════ MODAL CADASTRO / EDIÇÃO ══════ -->
  <div class="modal fade" id="modalItem" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title" id="modalItemTitulo">Novo Item</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="itemId" />
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
            <input type="text" id="itemNome" class="form-control" placeholder="Ex: Raiva, Ivermectina..." />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
            <select id="itemTipo" class="form-select">
              <option value="vacina">Vacina</option>
              <option value="medicamento">Medicamento</option>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Data de Fabricação</label>
              <input type="date" id="itemFabricacao" class="form-control" />
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Data de Vencimento <span class="text-danger">*</span></label>
              <input type="date" id="itemVencimento" class="form-control" />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Quantidade <span class="text-danger">*</span></label>
            <input type="number" id="itemQuantidade" class="form-control" min="0" value="1" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Descrição <span class="text-muted small">(opcional)</span></label>
            <textarea id="itemDescricao" class="form-control" rows="2"
                      placeholder="Ex: Dose única, conservar sob refrigeração..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarItem()">
            <i class="bi bi-floppy me-1"></i> Salvar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════ MODAL APLICAÇÃO ══════ -->
  <div class="modal fade" id="modalAplicar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-clipboard2-pulse me-2"></i>Registrar Aplicação</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="aplItemId" />
          <div class="alert alert-info py-2 small mb-3" id="aplItemInfo"></div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Lote do Animal <span class="text-danger">*</span></label>
            <select id="aplLote" class="form-select" onchange="carregarAnimaisDoLote()">
              <option value="">Carregando lotes...</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Animal <span class="text-danger">*</span></label>
            <select id="aplAnimal" class="form-select">
              <option value="">Selecione um lote primeiro...</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Quantidade Utilizada</label>
            <input type="number" id="aplQtd" class="form-control" min="1" value="1" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Data da Aplicação</label>
            <input type="date" id="aplData" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Observações</label>
            <textarea id="aplObs" class="form-control" rows="2" placeholder="Reação, dose, responsável..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarAplicacao()">
            <i class="bi bi-check2-circle me-1"></i> Confirmar Aplicação
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastFeedback" class="toast align-items-center border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold" id="toastMsg"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="menu.js"></script>
  <script>
    let todosItens   = [];
    let filtroAtivo  = 'todos';

    // ── Toast ──────────────────────────────────────────
    function mostrarToast(msg, tipo = 'success') {
      const el = document.getElementById('toastFeedback');
      el.classList.remove('text-bg-success','text-bg-danger','text-bg-warning');
      el.classList.add('text-bg-' + tipo);
      document.getElementById('toastMsg').textContent = msg;
      bootstrap.Toast.getOrCreateInstance(el).show();
    }

    // ── Escape XSS ─────────────────────────────────────
    function esc(s) {
      return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                          .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    // ── Formatar data ──────────────────────────────────
    function fmtData(d) {
      if (!d) return '—';
      const p = d.split('-');
      return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d;
    }

    // ── Carregar estoque ───────────────────────────────
    async function carregarEstoque() {
      try {
        const res  = await fetch('api_estoque.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        todosItens = json.data || [];
        renderFiltrado();
      } catch (err) {
        document.getElementById('estoqueContainer').innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-exclamation-circle fs-1 text-danger d-block mb-3"></i>
            <p class="text-muted">Erro ao carregar: ${esc(err.message)}</p>
          </div>`;
      }
    }

    // ── Filtro de abas ─────────────────────────────────
    function setFiltro(f) {
      filtroAtivo = f;
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
      document.getElementById('btn-' + f).classList.add('active');
      renderFiltrado();
    }

    function renderFiltrado() {
      const q = (document.getElementById('pesquisaEstoque').value || '').toLowerCase();
      let lista = todosItens;
      if (filtroAtivo !== 'todos') lista = lista.filter(i => i.tipo === filtroAtivo);
      if (q) lista = lista.filter(i => i.nome.toLowerCase().includes(q));
      renderCards(lista);
    }

    // ── Renderizar cards ───────────────────────────────
    function renderCards(lista) {
      const c = document.getElementById('estoqueContainer');
      c.innerHTML = '';

      if (lista.length === 0) {
        c.innerHTML = `<div class="col-12 text-center py-5">
          <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
          <h6 class="text-muted">Nenhum item encontrado</h6></div>`;
        return;
      }

      lista.forEach(item => {
        const temEstoque  = parseInt(item.quantidade_atual) > 0;
        const icone       = item.tipo === 'vacina' ? 'bi-virus' : 'bi-capsule';
        const badgeHtml   = temEstoque
          ? `<span class="badge-estoque-ok"><i class="bi bi-check-circle me-1"></i>Tem no estoque</span>`
          : `<span class="badge-estoque-zero"><i class="bi bi-x-circle me-1"></i>Não tem no estoque</span>`;

        const btnAplicar = temEstoque
          ? `<button class="btn btn-sm btn-success rounded-pill px-3"
                     onclick="abrirModalAplicar(${item.id},'${esc(item.nome)}',${item.quantidade_atual})">
               <i class="bi bi-clipboard2-pulse"></i> Aplicar
             </button>`
          : `<button class="btn btn-sm btn-secondary rounded-pill px-3" disabled>
               <i class="bi bi-clipboard2-pulse"></i> Sem estoque
             </button>`;

        c.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4" id="card-item-${item.id}">
            <div class="item-card ${temEstoque ? '' : 'sem-estoque'}">
              <div class="item-card-header">
                <span><i class="bi ${icone} me-2"></i>${esc(item.nome)}</span>
                ${badgeHtml}
              </div>
              <div class="item-card-body">
                <p><strong>Tipo:</strong> ${item.tipo === 'vacina' ? 'Vacina' : 'Medicamento'}</p>
                <p><strong>Data de fabricação:</strong> ${fmtData(item.data_fabricacao)}</p>
                <p><strong>Validade:</strong> ${fmtData(item.data_vencimento)}</p>
                <p><strong>Quant. p/ validade:</strong> <span class="fw-bold ${temEstoque ? 'text-success' : 'text-muted'}">${item.quantidade_atual}</span></p>
                ${item.descricao ? `<p class="text-muted small">${esc(item.descricao)}</p>` : ''}
              </div>
              <div class="item-card-actions">
                ${btnAplicar}
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3"
                        onclick="abrirModalEditar(${item.id})">
                  <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-2"
                        onclick="excluirItem(${item.id},'${esc(item.nome)}')">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          </div>`;
      });
    }

    // ── Modal Cadastro / Edição ────────────────────────
    function abrirModalNovo(tipo) {
      document.getElementById('itemId').value          = '';
      document.getElementById('itemNome').value        = '';
      document.getElementById('itemTipo').value        = tipo;
      document.getElementById('itemFabricacao').value  = '';
      document.getElementById('itemVencimento').value  = '';
      document.getElementById('itemQuantidade').value  = '1';
      document.getElementById('itemDescricao').value   = '';
      document.getElementById('modalItemTitulo').textContent =
        tipo === 'vacina' ? '🦠 Nova Vacina' : '💊 Novo Medicamento';
      new bootstrap.Modal(document.getElementById('modalItem')).show();
    }

    function abrirModalEditar(id) {
      const item = todosItens.find(i => i.id == id);
      if (!item) return;
      document.getElementById('itemId').value          = item.id;
      document.getElementById('itemNome').value        = item.nome;
      document.getElementById('itemTipo').value        = item.tipo;
      document.getElementById('itemFabricacao').value  = item.data_fabricacao || '';
      document.getElementById('itemVencimento').value  = item.data_vencimento;
      document.getElementById('itemQuantidade').value  = item.quantidade_atual;
      document.getElementById('itemDescricao').value   = item.descricao || '';
      document.getElementById('modalItemTitulo').textContent = '✏️ Editar Item';
      new bootstrap.Modal(document.getElementById('modalItem')).show();
    }

    async function salvarItem() {
      const id       = document.getElementById('itemId').value;
      const nome     = document.getElementById('itemNome').value.trim();
      const tipo     = document.getElementById('itemTipo').value;
      const fab      = document.getElementById('itemFabricacao').value;
      const venc     = document.getElementById('itemVencimento').value;
      const qtd      = document.getElementById('itemQuantidade').value;
      const desc     = document.getElementById('itemDescricao').value.trim();

      if (!nome)  { mostrarToast('O nome é obrigatório.', 'danger'); return; }
      if (!venc)  { mostrarToast('A data de vencimento é obrigatória.', 'danger'); return; }
      if (qtd < 0){ mostrarToast('Quantidade não pode ser negativa.', 'danger'); return; }

      const fd = new FormData();
      fd.append('action',          id ? 'editar' : 'criar');
      if (id) fd.append('id',      id);
      fd.append('nome',            nome);
      fd.append('tipo',            tipo);
      fd.append('data_fabricacao', fab);
      fd.append('data_vencimento', venc);
      fd.append('quantidade',      qtd);
      fd.append('descricao',       desc);

      const res  = await fetch('api_estoque.php', { method: 'POST', body: fd });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
        mostrarToast(json.message, 'success');
        await carregarEstoque();
      } else {
        mostrarToast(json.message || 'Erro ao salvar.', 'danger');
      }
    }

    async function excluirItem(id, nome) {
      if (!confirm(`Excluir "${nome}" do estoque?\nEsta ação não pode ser desfeita.`)) return;
      const fd = new FormData();
      fd.append('action', 'excluir');
      fd.append('id', id);
      const res  = await fetch('api_estoque.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.success) {
        const card = document.getElementById('card-item-' + id);
        if (card) { card.classList.add('card-fade-out'); setTimeout(() => card.remove(), 400); }
        mostrarToast('Item excluído com sucesso!', 'success');
        todosItens = todosItens.filter(i => i.id != id);
      } else {
        mostrarToast(json.message || 'Erro ao excluir.', 'danger');
      }
    }

    // ── Modal Aplicação ────────────────────────────────
    let lotesCache = [];

    async function abrirModalAplicar(id, nome, qtd) {
      document.getElementById('aplItemId').value = id;
      document.getElementById('aplItemInfo').innerHTML =
        `<strong>${esc(nome)}</strong> — Estoque disponível: <strong class="text-success">${qtd}</strong> unidade(s)`;
      document.getElementById('aplQtd').value  = '1';
      document.getElementById('aplData').value = new Date().toISOString().split('T')[0];
      document.getElementById('aplObs').value  = '';

      // Carregar lotes
      if (lotesCache.length === 0) {
        const res  = await fetch('api_lotes.php');
        const json = await res.json();
        lotesCache = json.data || [];
      }
      const sel = document.getElementById('aplLote');
      sel.innerHTML = '<option value="">Selecione o lote...</option>';
      lotesCache.forEach(l => {
        sel.innerHTML += `<option value="${l.id_lote}">${esc(l.codigo_lote)}</option>`;
      });
      document.getElementById('aplAnimal').innerHTML = '<option value="">Selecione um lote primeiro...</option>';

      new bootstrap.Modal(document.getElementById('modalAplicar')).show();
    }

    async function carregarAnimaisDoLote() {
      const id_lote = document.getElementById('aplLote').value;
      const sel     = document.getElementById('aplAnimal');
      sel.innerHTML = '<option value="">Carregando...</option>';
      if (!id_lote) { sel.innerHTML = '<option value="">Selecione um lote primeiro...</option>'; return; }
      const res  = await fetch(`api_animais.php?id_lote=${id_lote}`);
      const json = await res.json();
      sel.innerHTML = '<option value="">Selecione o animal...</option>';
      (json.data || []).forEach(a => {
        sel.innerHTML += `<option value="${a.id_animal}">${esc(a.nome_animal)} (${esc(a.numero_brinco||'s/n')})</option>`;
      });
      if (!json.data || json.data.length === 0) {
        sel.innerHTML = '<option value="">Nenhum animal neste lote</option>';
      }
    }

    async function salvarAplicacao() {
      const id_item  = document.getElementById('aplItemId').value;
      const id_anim  = document.getElementById('aplAnimal').value;
      const qtd      = document.getElementById('aplQtd').value;
      const data     = document.getElementById('aplData').value;
      const obs      = document.getElementById('aplObs').value;

      if (!id_anim)  { mostrarToast('Selecione um animal.', 'danger'); return; }
      if (!data)     { mostrarToast('Informe a data da aplicação.', 'danger'); return; }

      const fd = new FormData();
      fd.append('action',            'aplicar');
      fd.append('id_estoque_item',   id_item);
      fd.append('id_animal',         id_anim);
      fd.append('quantidade_utilizada', qtd);
      fd.append('data_aplicacao',    data);
      fd.append('observacoes',       obs);

      const res  = await fetch('api_estoque.php', { method: 'POST', body: fd });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalAplicar')).hide();
        mostrarToast(json.message, 'success');
        await carregarEstoque();
      } else {
        mostrarToast(json.message || 'Erro ao registrar aplicação.', 'danger');
      }
    }

    window.addEventListener('load', carregarEstoque);
  </script>
</body>
</html>
