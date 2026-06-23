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
  <title>Cadastro de Medicamento - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Cadastro de Medicamento</h2>
      <p class="subtitulo">Insira as informações do medicamento para o controle sanitário</p>
    </div>
  </div>

  <div class="card card-premium">
    <div class="card-header-green">
      <span>Cadastro de Medicamento</span>
      <i class="bi bi-capsule"></i>
    </div>
    <div class="card-body">
      <form id="medicamentoForm">
        <!-- Medicamento -->
        <div class="form-group-custom mb-3">
          <label>Medicamento</label>
          <input type="text" id="medNome" class="form-control-custom-noicon" placeholder="Ex: Vermífugo X, Antibiótico..." required>
        </div>

        <!-- Tipo -->
        <div class="form-group-custom mb-3">
          <label>Tipo</label>
          <input type="text" id="medTipo" class="form-control-custom-noicon" placeholder="Ex: Vermífugo, Anti-inflamatório..." required>
        </div>

        <!-- Dose Recomendada -->
        <div class="form-group-custom mb-3">
          <label>Dose Recomendada</label>
          <input type="text" id="medDose" class="form-control-custom-noicon" placeholder="Ex: 5ml, 2 comprimidos..." required>
        </div>

        <!-- Via de Aplicação -->
        <div class="form-group-custom mb-3">
          <label>Via de Aplicação</label>
          <input type="text" id="medVia" class="form-control-custom-noicon" placeholder="Ex: Intramuscular, Oral..." required>
        </div>

        <!-- Intervalo de aplicação -->
        <div class="form-group-custom mb-3">
          <label>Intervalo de aplicação</label>
          <input type="text" id="medIntervalo" class="form-control-custom-noicon" placeholder="Ex: 3 em 3 meses..." required>
        </div>

        <!-- Lote -->
        <div class="form-group-custom mb-4">
          <label>Lote</label>
          <input type="text" id="medLote" class="form-control-custom-noicon" placeholder="Ex: LOTE 28" required>
        </div>

        <!-- Botoes Salvar e Cancelar -->
        <div class="d-flex gap-3 mt-4">
          <a href="Dashboard.php" class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
            Cancelar
          </a>
          <button type="submit" class="btn-primary-custom py-3 fs-5 w-50 mt-0">
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    document.getElementById("medicamentoForm").addEventListener("submit", function (e) {
      e.preventDefault();

      const novoMed = {
        id: Date.now(),
        nome: document.getElementById("medNome").value,
        tipo: document.getElementById("medTipo").value,
        dose: document.getElementById("medDose").value,
        via: document.getElementById("medVia").value,
        intervalo: document.getElementById("medIntervalo").value,
        lote: document.getElementById("medLote").value
      };

      const medicamentos = JSON.parse(localStorage.getItem("medicamentos")) || [];
      medicamentos.push(novoMed);
      localStorage.setItem("medicamentos", JSON.stringify(medicamentos));

      alert("Medicamento cadastrado com sucesso!");
      window.location.href = "Dashboard.php";
    });
  </script>
</body>

</html>