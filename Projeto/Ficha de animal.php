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
  <title>Ficha do Animal - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Ficha do Animal</h2>
      <p class="subtitulo">Ficha cadastral e prontuário vacinal do animal</p>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Animal Card details -->
    <div class="col-12 col-lg-8">
      <div class="animal-profile-card">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
          <h3 id="profileNome">Mimosa</h3>
          <span class="badge bg-white text-success px-3 py-2 rounded-pill fs-6 fw-bold" id="profileNumero">001</span>
        </div>
        <div class="animal-profile-details">
          <div class="animal-profile-text">
            <p><strong>Espécie:</strong> <span id="profileEspecie">Bovino</span></p>
            <p><strong>Raça:</strong> <span id="profileRaca">Holandesa</span></p>
            <p><strong>Sexo:</strong> <span id="profileSexo">Fêmea</span></p>
            <p><strong>Data de Nascimento:</strong> <span id="profileNascimento">27/01/2026</span></p>
            <p><strong>Lote:</strong> <span id="profileLote">LOTE 04C-01</span></p>
            <p><strong>Pai:</strong> <span id="profilePai">Touro 1234</span></p>
            <p><strong>Mãe:</strong> <span id="profileMae">Vaca 5823</span></p>
          </div>
          <div>
            <img src="https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=250" alt="Animal Photo" class="animal-profile-img shadow-sm" id="profileFoto">
          </div>
        </div>
      </div>

      <!-- Vaccine Application History Table -->
      <div class="card card-premium">
        <div class="card-header-green">
          <span>Histórico de Vacinas Aplicadas</span>
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Vacina/Medicamento</th>
                  <th>Tipo/Dose</th>
                  <th>Data</th>
                  <th>Aplicador</th>
                  <th class="pe-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody id="historicoTabela">
                <!-- Rendered dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Quick Stats, Shortcuts, Status Legends -->
    <div class="col-12 col-lg-4">
      <div class="d-grid gap-3 mb-4">
        <a href="Registro de Aplicação.php" id="btnNovaVacina" class="btn btn-success btn-lg rounded-pill py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
          <i class="bi bi-plus-circle-fill"></i> + VACINA
        </a>
        <a href="Lista de animal.php" class="btn btn-outline-secondary btn-lg rounded-pill py-3 d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-arrow-left"></i> Voltar à Lista
        </a>
      </div>

      <!-- Status Indicators Legend Card -->
      <div class="card card-premium">
        <div class="card-header-green">
          <span>Legenda de Status</span>
          <i class="bi bi-info-circle-fill"></i>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status success"><i class="bi bi-check-circle-fill"></i> CONCLUÍDO</span>
              <small class="text-muted">Vacina já aplicada no animal</small>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status warning"><i class="bi bi-exclamation-circle-fill"></i> PENDENTE</span>
              <small class="text-muted">Ainda dentro do prazo de aplicação</small>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status danger"><i class="bi bi-x-circle-fill"></i> ATRASADA</span>
              <small class="text-muted">Data prevista já expirou</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Get URL query params
      const params = new URLSearchParams(window.location.search);
      const animalId = parseInt(params.get("id")) || 1; // Default to Mimosa (1)

      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      const aplicacoes = JSON.parse(localStorage.getItem("aplicacoes")) || [];

      // Find selected animal
      const animal = animais.find(a => a.id === animalId);

      if (animal) {
        // Update DOM
        document.getElementById("profileNome").innerText = animal.nome;
        document.getElementById("profileNumero").innerText = animal.numero;
        document.getElementById("profileEspecie").innerText = animal.especie || "Bovino";
        document.getElementById("profileRaca").innerText = animal.raca || "Nelore";
        document.getElementById("profileSexo").innerText = animal.sexo;
        
        // Format birth date nicely
        if (animal.dataNascimento) {
          const parts = animal.dataNascimento.split("-");
          if(parts.length === 3) {
            document.getElementById("profileNascimento").innerText = `${parts[2]}/${parts[1]}/${parts[0]}`;
          } else {
            document.getElementById("profileNascimento").innerText = animal.dataNascimento;
          }
        } else {
          document.getElementById("profileNascimento").innerText = "27/01/2026";
        }
        
        document.getElementById("profileLote").innerText = animal.lote || "Sem Lote";
        document.getElementById("profilePai").innerText = animal.pai || "Não informado";
        document.getElementById("profileMae").innerText = animal.mae || "Não informada";

        // Dynamic animal image (different image for goats/cows)
        const photoEl = document.getElementById("profileFoto");
        if (animal.especie && animal.especie.toLowerCase().includes("capri")) {
          photoEl.src = "https://images.unsplash.com/photo-1524388680868-377a2e6bbb1c?q=80&w=250"; // Goat
        } else {
          photoEl.src = "https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=250"; // Cow
        }

        // Setup the direct link on "+ Vacina" button to pre-select this animal
        const btnNovaVacina = document.getElementById("btnNovaVacina");
        btnNovaVacina.href = `Registro de Aplicação.php?preselect=${animal.id}`;
      }

      // Filter applications for this animal
      const animalAplicacoes = aplicacoes.filter(a => a.animalId === animalId);
      const tableBody = document.getElementById("historicoTabela");
      tableBody.innerHTML = "";

      if (animalAplicacoes.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">
              Nenhuma vacina ou medicamento aplicado.
            </td>
          </tr>
        `;
        return;
      }

      animalAplicacoes.forEach(a => {
        let statusBadge = "";
        if (a.status === "Concluído") {
          statusBadge = `<span class="badge-status success"><i class="bi bi-check-circle-fill"></i> Aplicado</span>`;
        } else if (a.status === "Pendente") {
          statusBadge = `<span class="badge-status warning"><i class="bi bi-exclamation-circle-fill"></i> Pendente</span>`;
        } else {
          statusBadge = `<span class="badge-status danger"><i class="bi bi-x-circle-fill"></i> Atrasada</span>`;
        }

        // Format date
        let formattedDate = a.data;
        const parts = a.data.split("-");
        if (parts.length === 3) {
          formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        tableBody.innerHTML += `
          <tr>
            <td class="ps-3 fw-semibold">${a.itemNome} <small class="text-muted d-block">Lote: ${a.lote || '---'}</small></td>
            <td>${a.dose || a.tipo || 'Dose única'}</td>
            <td>${formattedDate}</td>
            <td>${a.tecnico || 'Julia Silva'}</td>
            <td class="pe-3 text-center">${statusBadge}</td>
          </tr>
        `;
      });
    });
  </script>
</body>

</html>