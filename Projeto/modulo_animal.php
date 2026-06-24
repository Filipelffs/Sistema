<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

// Fetch lotes for filter dropdown
$sqlLotes = "SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC";
$resLotes = $conn->query($sqlLotes);
$lotesFilter = [];
if ($resLotes) {
    while ($r = $resLotes->fetch_assoc()) {
        $lotesFilter[] = $r;
    }
}

// Fetch distinct breeds for filter
$sqlRacas = "SELECT DISTINCT raca FROM animais WHERE raca IS NOT NULL AND raca != '' ORDER BY raca ASC";
$resRacas = $conn->query($sqlRacas);
$racasFilter = [];
if ($resRacas) {
    while ($r = $resRacas->fetch_assoc()) {
        $racasFilter[] = $r['raca'];
    }
}
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
              <?php foreach($lotesFilter as $l): ?>
                <option value="<?= $l['id_lote'] ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label small fw-semibold">Filtrar por Raça</label>
            <select id="filtroRaca" class="form-select" onchange="aplicarFiltros()">
              <option value="">Todas</option>
              <?php foreach($racasFilter as $raca): ?>
                <option value="<?= htmlspecialchars($raca) ?>"><?= htmlspecialchars($raca) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-4 d-flex align-items-end">
            <button class="btn btn-success rounded-pill w-100" onclick="limparFiltros()">Limpar Filtros</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Animal Cards List -->
  <div class="row g-4" id="moduleAnimaisList">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    let animaisCompleta = [];

    async function carregarAnimais() {
      try {
        const response = await fetch('animal_action.php?action=list');
        const result = await response.json();
        if (result.success) {
          animaisCompleta = result.data;
          renderizarModuleAnimais(animaisCompleta);
        } else {
          document.getElementById("moduleAnimaisList").innerHTML = `<div class="col-12 alert alert-danger">${result.message}</div>`;
        }
      } catch (error) {
        document.getElementById("moduleAnimaisList").innerHTML = `<div class="col-12 alert alert-danger">Erro ao carregar animais do banco de dados.</div>`;
      }
    }

    function renderizarModuleAnimais(lista) {
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
        // Choose image based on species
        let imgUrl = "https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=150";
        if (animal.especie && animal.especie.toLowerCase().includes("capri")) {
          imgUrl = "https://images.unsplash.com/photo-1524388680868-377a2e6bbb1c?q=80&w=150";
        }

        container.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
              <!-- Custom Green Banner Header -->
              <div class="bg-success text-white px-3 py-2 fw-semibold d-flex justify-content-between align-items-center">
                <span>${animal.nome_animal}</span>
                <span class="badge bg-white text-success rounded-pill">${animal.numero_brinco || 'S/N'}</span>
              </div>
              <div class="card-body bg-white p-3">
                <div class="d-flex gap-3">
                  <img src="${imgUrl}" class="rounded-circle border" width="55" height="55" style="object-fit: cover; flex-shrink: 0;">
                  <ul class="list-unstyled mb-0 small">
                    <li class="py-1"><strong>Espécie:</strong> ${animal.especie || 'N/A'}</li>
                    <li class="py-1"><strong>Raça:</strong> ${animal.raca || 'Não especificada'}</li>
                    <li class="py-1"><strong>Sexo:</strong> ${animal.sexo}</li>
                    <li class="py-1"><strong>Mãe:</strong> ${animal.mae || 'Não informada'}</li>
                    <li class="py-1"><strong>Pai:</strong> ${animal.pai || 'Não informado'}</li>
                  </ul>
                </div>
              </div>
              <div class="bg-light px-3 py-2 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-tag-fill me-1"></i>${animal.codigo_lote || 'Sem Lote'}</small>
                <a href="ficha_animal.php?id=${animal.id_animal}" class="btn btn-sm btn-success rounded-pill px-3 py-0 fs-7">Ficha</a>
              </div>
            </div>
          </div>
        `;
      });
    }

    function aplicarFiltros() {
      const loteId = document.getElementById("filtroLote").value;
      const raca = document.getElementById("filtroRaca").value;
      let animais = [...animaisCompleta];

      if (loteId) {
        animais = animais.filter(a => String(a.id_lote) === loteId);
      }
      if (raca) {
        animais = animais.filter(a => a.raca && a.raca.includes(raca));
      }

      renderizarModuleAnimais(animais);
    }

    function limparFiltros() {
      document.getElementById("filtroLote").value = "";
      document.getElementById("filtroRaca").value = "";
      renderizarModuleAnimais(animaisCompleta);
    }

    window.addEventListener("load", carregarAnimais);
  </script>
</body>

</html>