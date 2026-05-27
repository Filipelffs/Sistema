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
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
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

  <!-- Mobile button -->
  <?php if ($isAdmin): ?>
  <div class="d-md-none mb-3">
    <button class="btn btn-success w-100 rounded-pill" onclick="abrirModalNovoLote()">
      <i class="bi bi-plus-circle me-2"></i> Novo Lote
    </button>
  </div>
  <?php endif; ?>

  <div class="row g-4" id="lotesContainer">
    <!-- Rendered dynamically via AJAX -->
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    let modalLoteInstance;

    document.addEventListener('DOMContentLoaded', () => {
      modalLoteInstance = new bootstrap.Modal(document.getElementById('modalLote'));
      carregarLotes();
    });

    async function carregarLotes() {
      const container = document.getElementById("lotesContainer");
      try {
        const response = await fetch('lote_action.php?action=list');
        const result = await response.json();
        
        if (!result.success) {
          throw new Error(result.message || 'Erro ao carregar lotes');
        }

        const lotes = result.data;
        container.innerHTML = "";

        if (lotes.length === 0) {
          container.innerHTML = `
            <div class="col-12 text-center py-5">
              <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
              <h6 class="text-muted">Nenhum lote de animal encontrado</h6>
            </div>
          `;
          return;
        }

        lotes.forEach(lote => {
          const qtd = parseInt(lote.qtd_real);
          const countText = qtd === 1 ? "1 animal" : `${qtd} animais`;
          
          let tableRows = "";
          if (lote.animais && lote.animais.length > 0) {
            lote.animais.forEach(a => {
              tableRows += `
                <tr>
                  <td>${a.numero_brinco || 'N/A'}</td>
                  <td class="fw-semibold">
                    <a href="Ficha de animal.php?id=${a.id_animal}" class="text-success text-decoration-none">${a.nome_animal}</a>
                  </td>
                </tr>
              `;
            });
          } else {
            tableRows = `<tr><td colspan="2" class="text-muted">Nenhum animal neste lote</td></tr>`;
          }

          let editBtn = '';
          if (isAdmin) {
            editBtn = `
              <div class="text-center mt-3">
                <button class="btn btn-outline-success rounded-pill px-4 btn-sm" onclick="abrirModalEditarLote(${lote.id_lote}, '${lote.codigo_lote}')">
                  <i class="bi bi-pencil-fill me-2"></i> Editar Lote
                </button>
              </div>
            `;
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
                        <tr>
                          <th>Código</th>
                          <th>Animal</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${tableRows}
                      </tbody>
                    </table>
                  </div>
                  ${editBtn}
                </div>
              </div>
            </div>
          `;
        });

      } catch (error) {
        container.innerHTML = `<div class="col-12 text-center text-danger py-5">Erro: ${error.message}</div>`;
      }
    }

    function mostrarAlertaModal(mensagem, tipo = 'danger') {
      const alert = document.getElementById('modalAlert');
      alert.className = `alert alert-${tipo}`;
      alert.textContent = mensagem;
      alert.classList.remove('d-none');
    }

    function esconderAlertaModal() {
      document.getElementById('modalAlert').classList.add('d-none');
    }

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
      const id = document.getElementById("loteId").value;
      const nome = document.getElementById("loteNome").value.trim();
      const action = id ? 'edit' : 'create';

      if (nome === "") {
        mostrarAlertaModal("O nome do lote não pode ser vazio.");
        return;
      }

      const btn = document.getElementById('btnSalvarLote');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';

      try {
        const response = await fetch(`lote_action.php?action=${action}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id_lote: id,
            codigo_lote: nome
          })
        });
        
        const result = await response.json();
        
        if (result.success) {
          modalLoteInstance.hide();
          carregarLotes(); // Recarrega a lista
        } else {
          mostrarAlertaModal(result.message);
        }
      } catch (error) {
        mostrarAlertaModal("Erro na comunicação com o servidor.");
      } finally {
        btn.disabled = false;
        btn.innerText = "Salvar";
      }
    }
  </script>
</body>
</html>