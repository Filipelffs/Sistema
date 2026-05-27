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
  <title>Módulo Animal - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Módulo Animal</h2>
      <p class="subtitulo">Visualização detalhada dos animais em formato de lista e fichas rápidas</p>
    </div>
  </div>

  <!-- Action Row -->
  <div class="row mb-4 align-items-center g-3">
    <div class="col-12 col-md-6">
      <a href="Lote.php" class="btn btn-success rounded-pill px-4 py-2 w-100 d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-tag-fill"></i> Cadastrar Lote
      </a>
    </div>
    <div class="col-12 col-md-6">
      <button class="btn btn-outline-success rounded-pill px-4 py-2 w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="collapse" data-bs-target="#collapseFiltros">
        <i class="bi bi-funnel-fill"></i> Filtros de Animais
      </button>
    </div>
  </div>

  <!-- Collapsible Filters -->
  <div class="collapse mb-4" id="collapseFiltros">
    <div class="card card-premium">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Filtrar Animais</h5>
        <div class="row g-3">
          <div class="col-6 col-md-4">
            <label class="form-label small fw-semibold">Filtrar por Lote</label>
            <select id="filtroLote" class="form-select" onchange="aplicarFiltros()">
              <option value="">Todos</option>
              <option value="LOTE 1">LOTE 1</option>
              <option value="LOTE 2">LOTE 2</option>
              <option value="LOTE 3">LOTE 3</option>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label small fw-semibold">Filtrar por Raça</label>
            <select id="filtroRaca" class="form-select" onchange="aplicarFiltros()">
              <option value="">Todas</option>
              <option value="Holandesa">Holandesa</option>
              <option value="Gir">Gir</option>
              <option value="Anglonubiana">Anglonubiana</option>
              <option value="Angus">Angus</option>
            </select>
          </div>
          <div class="col-12 col-md-4 d-flex align-items-end">
            <button class="btn btn-success rounded-pill w-100" onclick="limparFiltros()">Limpar Filtros</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Animal Cards List (Mockup Banner Style) -->
  <div class="row g-4" id="moduleAnimaisList">
    <!-- Rendered dynamically -->
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    function getAnimais() {
      return JSON.parse(localStorage.getItem("animais")) || [];
    }

    function renderizarModuleAnimais(lista = getAnimais()) {
      const container = document.getElementById("moduleAnimaisList");
      container.innerHTML = "";

      if (lista.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhum animal cadastrado no módulo</h6>
          </div>
        `;
        return;
      }

      lista.forEach(animal => {
        container.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
              <!-- Custom Green Banner Header -->
              <div class="bg-success text-white px-3 py-2 fw-semibold d-flex justify-content-between align-items-center">
                <span>${animal.nome}</span>
                <span class="badge bg-white text-success rounded-pill">${animal.numero}</span>
              </div>
              <div class="card-body bg-white p-3">
                <ul class="list-unstyled mb-0 small">
                  <li class="py-1"><strong>Raça:</strong> ${animal.raca || 'Holandesa'}</li>
                  <li class="py-1"><strong>Sexo:</strong> ${animal.sexo}</li>
                  <li class="py-1"><strong>Mãe:</strong> ${animal.mae || 'Vaca 5823'}</li>
                  <li class="py-1"><strong>Pai:</strong> ${animal.pai || 'Touro 1234'}</li>
                </ul>
              </div>
              <div class="bg-light px-3 py-2 border-top d-flex justify-content-between">
                <small class="text-muted">${animal.lote || 'LOTE 1'}</small>
                <a href="Ficha de animal.php?id=${animal.id}" class="btn btn-sm btn-success rounded-pill px-3 py-0 fs-7">Ficha</a>
              </div>
            </div>
          </div>
        `;
      });
    }

    function aplicarFiltros() {
      const lote = document.getElementById("filtroLote").value;
      const raca = document.getElementById("filtroRaca").value;
      let animais = getAnimais();

      if (lote) {
        animais = animais.filter(a => a.lote === lote);
      }
      if (raca) {
        animais = animais.filter(a => a.raca.includes(raca));
      }

      renderizarModuleAnimais(animais);
    }

    function limparFiltros() {
      document.getElementById("filtroLote").value = "";
      document.getElementById("filtroRaca").value = "";
      renderizarModuleAnimais();
    }

    window.addEventListener("load", () => {
      renderizarModuleAnimais();
    });
  </script>
</body>

</html>