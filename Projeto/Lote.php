<?php
require_once "sessao.php";
$isAdmin = $_SESSION['usuario_tipo'] === 'admin';
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lotes - Vacinação Animal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- TOPO -->
  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Lotes de Animais</h2>
      <p class="subtitulo">Gerenciamento de lotes e agrupamentos do rebanho</p>
    </div>
    <?php if ($isAdmin): ?>
    <button class="btn btn-success rounded-pill px-4 py-2" onclick="abrirModalCriar()">
      <i class="bi bi-plus-circle me-2"></i> Novo Lote
    </button>
    <?php endif; ?>
  </div>

  <!-- BUSCA -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="input-group input-icon-wrapper">
        <span class="input-group-text bg-transparent border-0 ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="pesquisaLote" class="form-control form-control-custom"
               placeholder="Pesquisar lote pelo código..." onkeyup="filtrarLotes()" />
      </div>
    </div>
  </div>

  <!-- LISTA DE LOTES -->
  <div class="row g-4" id="lotesContainer">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
      <p class="text-muted mt-3">Carregando lotes...</p>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════ -->
  <!-- MODAL — CADASTRAR NOVO LOTE (admin only)   -->
  <!-- ═══════════════════════════════════════════ -->
  <?php if ($isAdmin): ?>
  <div class="modal fade" id="modalCriarLote" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Novo Lote</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Código / Nome do Lote <span class="text-danger">*</span></label>
            <input type="text" id="criarLoteCodigo" class="form-control"
                   placeholder="Ex: LOTE 04C-01" maxlength="50" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo de Animal</label>
            <select id="criarLoteTipo" class="form-select">
              <option value="">Selecione...</option>
              <option value="Bovino">Bovino</option>
              <option value="Caprino">Caprino</option>
              <option value="Equino">Equino</option>
              <option value="Ovino">Ovino</option>
              <option value="Suíno">Suíno</option>
              <option value="Outros">Outros</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Quantidade Prevista de Animais</label>
            <input type="number" id="criarLoteQtd" class="form-control" min="0" value="0" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarNovoLote()">
            <i class="bi bi-floppy me-1"></i> Salvar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════ -->
  <!-- MODAL — EDITAR LOTE (admin only)           -->
  <!-- ═══════════════════════════════════════════ -->
  <div class="modal fade" id="modalEditarLote" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Editar Lote</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editLoteId" />
          <div class="mb-3">
            <label class="form-label fw-semibold">Código / Nome do Lote <span class="text-danger">*</span></label>
            <input type="text" id="editLoteCodigo" class="form-control" maxlength="50" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo de Animal</label>
            <select id="editLoteTipo" class="form-select">
              <option value="">Selecione...</option>
              <option value="Bovino">Bovino</option>
              <option value="Caprino">Caprino</option>
              <option value="Equino">Equino</option>
              <option value="Ovino">Ovino</option>
              <option value="Suíno">Suíno</option>
              <option value="Outros">Outros</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Quantidade Prevista de Animais</label>
            <input type="number" id="editLoteQtd" class="form-control" min="0" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarEdicaoLote()">
            <i class="bi bi-floppy me-1"></i> Salvar Alterações
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- TOAST FEEDBACK -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastFeedback" class="toast align-items-center border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold" id="toastMsg"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Menu JS -->
  <script src="menu.js"></script>

  <script>
    const isAdmin = window.USER_SESSION.tipo === 'admin';
    let todosLotes = [];

    // ─────────────────────────────────────────────
    // Toast helper
    // ─────────────────────────────────────────────
    function mostrarToast(msg, tipo = 'success') {
      const el  = document.getElementById('toastFeedback');
      const msg_el = document.getElementById('toastMsg');
      el.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
      el.classList.add('text-bg-' + tipo);
      msg_el.textContent = msg;
      bootstrap.Toast.getOrCreateInstance(el).show();
    }

    // ─────────────────────────────────────────────
    // Carregar lotes da API
    // ─────────────────────────────────────────────
    async function carregarLotes() {
      try {
        const res  = await fetch('api_lotes.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        todosLotes = json.data || [];
        renderizarLotes(todosLotes);
      } catch (err) {
        document.getElementById('lotesContainer').innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-exclamation-circle fs-1 text-danger d-block mb-3"></i>
            <h6 class="text-muted">Erro ao carregar lotes: ${err.message}</h6>
          </div>`;
      }
    }

    // ─────────────────────────────────────────────
    // Renderizar cards de lote
    // ─────────────────────────────────────────────
    function renderizarLotes(lista) {
      const container = document.getElementById('lotesContainer');
      container.innerHTML = '';

      if (lista.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhum lote encontrado</h6>
            ${isAdmin ? '<p class="text-muted small">Clique em "Novo Lote" para começar.</p>' : ''}
          </div>`;
        return;
      }

      lista.forEach(lote => {
        const countText = lote.animais.length === 1 ? '1 animal' : `${lote.animais.length} animais`;
        const tipoText  = lote.tipo_animal ? `<span class="badge bg-white text-success border border-success ms-2">${lote.tipo_animal}</span>` : '';

        let tableRows = lote.animais.length === 0
          ? '<tr><td colspan="2" class="text-muted text-center small py-2">Nenhum animal neste lote</td></tr>'
          : lote.animais.map(a => `
              <tr>
                <td class="text-muted small">${a.numero_brinco || '---'}</td>
                <td class="fw-semibold">
                  <a href="Ficha_de_animal.php?id=${a.id_animal}" class="text-success text-decoration-none">
                    ${a.nome_animal}
                  </a>
                </td>
              </tr>`).join('');

        const botoesAdmin = isAdmin ? `
          <button class="btn btn-sm btn-outline-warning rounded-pill px-3"
                  onclick="abrirModalEditar(${lote.id_lote}, '${escapeHtml(lote.codigo_lote)}', '${escapeHtml(lote.tipo_animal || '')}', ${lote.quantidade_animais || 0})">
            <i class="bi bi-pencil-fill me-1"></i> Editar
          </button>
          <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                  onclick="excluirLote(${lote.id_lote}, '${escapeHtml(lote.codigo_lote)}')">
            <i class="bi bi-trash3-fill me-1"></i> Excluir
          </button>` : '';

        container.innerHTML += `
          <div class="col-12 col-md-6" id="card-lote-${lote.id_lote}">
            <div class="card card-premium shadow-sm">
              <div class="card-header-green">
                <span class="fs-5">${escapeHtml(lote.codigo_lote)}${tipoText}</span>
                <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold">${countText}</span>
              </div>
              <div class="card-body p-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped text-center mb-3 small">
                    <thead class="table-light">
                      <tr><th>Brinco</th><th>Animal</th></tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                  </table>
                </div>
                ${botoesAdmin ? `<div class="d-flex gap-2 justify-content-end">${botoesAdmin}</div>` : ''}
              </div>
            </div>
          </div>`;
      });
    }

    // ─────────────────────────────────────────────
    // Filtro de busca
    // ─────────────────────────────────────────────
    function filtrarLotes() {
      const q = document.getElementById('pesquisaLote').value.toLowerCase();
      const filtrados = todosLotes.filter(l =>
        l.codigo_lote.toLowerCase().includes(q) ||
        (l.tipo_animal && l.tipo_animal.toLowerCase().includes(q))
      );
      renderizarLotes(filtrados);
    }

    // ─────────────────────────────────────────────
    // Abrir modal CRIAR
    // ─────────────────────────────────────────────
    function abrirModalCriar() {
      document.getElementById('criarLoteCodigo').value = '';
      document.getElementById('criarLoteTipo').value   = '';
      document.getElementById('criarLoteQtd').value    = '0';
      new bootstrap.Modal(document.getElementById('modalCriarLote')).show();
    }

    async function salvarNovoLote() {
      const codigo = document.getElementById('criarLoteCodigo').value.trim();
      const tipo   = document.getElementById('criarLoteTipo').value;
      const qtd    = document.getElementById('criarLoteQtd').value;

      if (!codigo) { mostrarToast('O código do lote é obrigatório.', 'danger'); return; }

      const fd = new FormData();
      fd.append('action', 'criar');
      fd.append('codigo_lote', codigo);
      fd.append('tipo_animal', tipo);
      fd.append('quantidade_animais', qtd);

      const res  = await fetch('api_lotes.php', { method: 'POST', body: fd });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalCriarLote')).hide();
        mostrarToast('Lote criado com sucesso!', 'success');
        await carregarLotes();
      } else {
        mostrarToast(json.message || 'Erro ao criar lote.', 'danger');
      }
    }

    // ─────────────────────────────────────────────
    // Abrir modal EDITAR
    // ─────────────────────────────────────────────
    function abrirModalEditar(id, codigo, tipo, qtd) {
      document.getElementById('editLoteId').value     = id;
      document.getElementById('editLoteCodigo').value = codigo;
      document.getElementById('editLoteTipo').value   = tipo;
      document.getElementById('editLoteQtd').value    = qtd;
      new bootstrap.Modal(document.getElementById('modalEditarLote')).show();
    }

    async function salvarEdicaoLote() {
      const id     = document.getElementById('editLoteId').value;
      const codigo = document.getElementById('editLoteCodigo').value.trim();
      const tipo   = document.getElementById('editLoteTipo').value;
      const qtd    = document.getElementById('editLoteQtd').value;

      if (!codigo) { mostrarToast('O código do lote não pode ser vazio.', 'danger'); return; }

      const fd = new FormData();
      fd.append('action', 'editar');
      fd.append('id_lote', id);
      fd.append('codigo_lote', codigo);
      fd.append('tipo_animal', tipo);
      fd.append('quantidade_animais', qtd);

      const res  = await fetch('api_lotes.php', { method: 'POST', body: fd });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalEditarLote')).hide();
        mostrarToast('Lote atualizado com sucesso!', 'success');
        await carregarLotes();
      } else {
        mostrarToast(json.message || 'Erro ao atualizar lote.', 'danger');
      }
    }

    // ─────────────────────────────────────────────
    // EXCLUIR lote
    // ─────────────────────────────────────────────
    async function excluirLote(id, codigo) {
      if (!confirm(`Deseja realmente excluir o lote "${codigo}"?\nEssa ação não pode ser desfeita.`)) return;

      const fd = new FormData();
      fd.append('action', 'excluir');
      fd.append('id_lote', id);

      const res  = await fetch('api_lotes.php', { method: 'POST', body: fd });
      const json = await res.json();

      if (json.success) {
        const card = document.getElementById('card-lote-' + id);
        if (card) {
          card.style.transition = 'opacity 0.4s';
          card.style.opacity    = '0';
          setTimeout(() => card.remove(), 400);
        }
        mostrarToast('Lote excluído com sucesso!', 'success');
      } else {
        mostrarToast(json.message || 'Erro ao excluir lote.', 'danger');
      }
    }

    // ─────────────────────────────────────────────
    // Escape XSS
    // ─────────────────────────────────────────────
    function escapeHtml(str) {
      return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
        .replace(/'/g,'&#39;');
    }

    window.addEventListener('load', carregarLotes);
  </script>
</body>
</html>