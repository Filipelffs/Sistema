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

  <div class="topo-pagina d-none d-md-flex">
    <div>
      <h2 class="titulo-pagina">Lotes de Animais</h2>
      <p class="subtitulo">Gerenciamento de lotes e agrupamentos do rebanho</p>
    </div>
  </div>

  <div class="row g-4" id="lotesContainer">
    <!-- Rendered dynamically -->
  </div>

  <!-- EDIT LOTE MODAL -->
  <div class="modal fade" id="modalEditarLote" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">Editar Lote</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editLoteNomeOriginal" />
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome do Lote</label>
            <input type="text" id="editLoteNome" class="form-control" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarEdicaoLote()">Salvar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    function renderizarLotes() {
      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      const container = document.getElementById("lotesContainer");
      container.innerHTML = "";

      // Group animals by Lote
      const lotes = {};
      animais.forEach(a => {
        const loteNome = a.lote || "Sem Lote";
        if (!lotes[loteNome]) {
          lotes[loteNome] = [];
        }
        lotes[loteNome].push(a);
      });

      const loteNomes = Object.keys(lotes);

      if (loteNomes.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhum lote de animal encontrado</h6>
          </div>
        `;
        return;
      }

      loteNomes.forEach(loteNome => {
        const loteAnimais = lotes[loteNome];
        const countText = loteAnimais.length === 1 ? "1 animal" : `${loteAnimais.length} animais`;

        let tableRows = "";
        loteAnimais.forEach(a => {
          tableRows += `
            <tr>
              <td>${a.numero}</td>
              <td class="fw-semibold">
                <a href="Ficha de animal.php?id=${a.id}" class="text-success text-decoration-none">${a.nome}</a>
              </td>
            </tr>
          `;
        });

        container.innerHTML += `
          <div class="col-12 col-md-6">
            <div class="card card-premium shadow-sm">
              <div class="card-header-green">
                <span class="fs-5">${loteNome}</span>
                <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold">${countText}</span>
              </div>
              <div class="card-body p-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped text-center mb-3">
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
                <div class="text-center">
                  <button class="btn btn-outline-success rounded-pill px-4 btn-sm" onclick="editarLote('${loteNome}')">
                    <i class="bi bi-pencil-fill me-2"></i> Editar Lote
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      });
    }

    function editarLote(loteNome) {
      document.getElementById("editLoteNomeOriginal").value = loteNome;
      document.getElementById("editLoteNome").value = loteNome;
      new bootstrap.Modal(document.getElementById("modalEditarLote")).show();
    }

    function salvarEdicaoLote() {
      const original = document.getElementById("editLoteNomeOriginal").value;
      const novo = document.getElementById("editLoteNome").value;

      if (novo.trim() === "") {
        alert("O nome do lote não pode ser vazio.");
        return;
      }

      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      animais.forEach(a => {
        if (a.lote === original) {
          a.lote = novo;
        }
      });

      localStorage.setItem("animais", JSON.stringify(animais));
      bootstrap.Modal.getInstance(document.getElementById("modalEditarLote")).hide();
      renderizarLotes();
    }

    window.addEventListener("load", renderizarLotes);
  </script>
</body>

</html>