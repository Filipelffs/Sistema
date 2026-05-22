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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status de Vacinação - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Status de Imunização</h2>
      <p class="subtitulo">Acompanhe as aplicações concluídas, pendentes ou atrasadas de todo o rebanho</p>
    </div>
  </div>

  <!-- Legend Header Panel -->
  <div class="card card-premium mb-4">
    <div class="card-body py-3">
      <div class="row text-center align-items-center g-2">
        <div class="col-4">
          <span class="badge-status success w-100 py-2 fs-7"><i class="bi bi-check-circle-fill"></i> CONCLUÍDO</span>
        </div>
        <div class="col-4">
          <span class="badge-status warning w-100 py-2 fs-7"><i class="bi bi-exclamation-circle-fill"></i> PENDENTE</span>
        </div>
        <div class="col-4">
          <span class="badge-status danger w-100 py-2 fs-7"><i class="bi bi-x-circle-fill"></i> ATRASADA</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Applications list grouped or shown individually with status indicators -->
  <div class="row g-3" id="listaVacinasStatus">
    <!-- Rendered dynamically -->
  </div>

  <!-- Bottom Trigger -->
  <div class="d-grid mt-4">
    <a href="Registro de Aplicação.php" class="btn btn-success btn-lg rounded-pill py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
      <i class="bi bi-plus-circle-fill"></i> Adicionar Vacina / Aplicação
    </a>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const aplicacoes = JSON.parse(localStorage.getItem("aplicacoes")) || [];
      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      const container = document.getElementById("listaVacinasStatus");

      if (aplicacoes.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-clipboard-x fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhuma aplicação registrada</h6>
          </div>
        `;
        return;
      }

      container.innerHTML = "";
      aplicacoes.forEach(a => {
        const animal = animais.find(ani => ani.id === a.animalId) || { nome: "Paula", numero: "1003" };

        let statusClass = "success";
        let badgeText = "Concluído";
        if (a.status === "Atrasada") {
          statusClass = "danger";
          badgeText = "Atrasada";
        } else if (a.status === "Pendente") {
          statusClass = "warning";
          badgeText = "Pendente";
        }

        // Animal image
        let imgUrl = "https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=150";
        if (animal.especie && animal.especie.toLowerCase().includes("capri")) {
          imgUrl = "https://images.unsplash.com/photo-1524388680868-377a2e6bbb1c?q=80&w=150";
        }

        // Date format
        let formattedDate = a.data;
        const parts = a.data.split("-");
        if (parts.length === 3) {
          formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        container.innerHTML += `
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-2">
              <div class="card-body p-3">
                <div class="d-flex align-items-center">
                  <img src="${imgUrl}" class="rounded-circle me-3 border" width="55" height="55" style="object-fit: cover;">
                  <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">${animal.nome} <small class="text-muted">(${animal.numero})</small></h6>
                    <small class="d-block text-muted">Item: <strong class="text-dark">${a.itemNome}</strong> | ${a.dose || '1ª dose'}</small>
                    <small class="text-muted">Data prevista: ${formattedDate}</small>
                  </div>
                  <span class="badge-status ${statusClass} px-3 py-2 fw-bold text-center" style="min-width: 100px;">
                    ${badgeText}
                  </span>
                </div>
              </div>
            </div>
          </div>
        `;
      });
    });
  </script>
</body>

</html>