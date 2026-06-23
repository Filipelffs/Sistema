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
  <title>Cadastro de Vacina - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Cadastro de Vacina</h2>
      <p class="subtitulo">Insira as informações da vacina para o controle sanitário</p>
    </div>
  </div>

  <div class="card card-premium">
    <div class="card-header-green">
      <span>Cadastro de Vacinas</span>
      <i class="bi bi-virus"></i>
    </div>
    <div class="card-body">
      <form id="vacinaForm">
        <!-- Identificador do Lote -->
        <div class="form-group-custom mb-3">
          <label>Identificador do Lote</label>
          <input type="text" id="vacLote" class="form-control-custom-noicon" placeholder="Ex: V-2026A" required>
        </div>

        <!-- Nome da Vacina -->
        <div class="form-group-custom mb-3">
          <label>Nome da Vacina</label>
          <input type="text" id="vacNome" class="form-control-custom-noicon" placeholder="Ex: Febre Aftosa, Raiva..." required>
        </div>

        <!-- Tipo de vacina ou medicamento -->
        <div class="form-group-custom mb-3">
          <label>Tipo de vacina ou medicamento</label>
          <input type="text" id="vacTipo" class="form-control-custom-noicon" placeholder="Ex: Vacina viral atenuada..." required>
        </div>

        <!-- Intervalo entre doses -->
        <div class="form-group-custom mb-3">
          <label>Intervalo entre doses</label>
          <input type="text" id="vacIntervalo" class="form-control-custom-noicon" placeholder="Ex: 6 meses, dose única..." required>
        </div>

        <!-- Observações -->
        <div class="form-group-custom mb-4">
          <label>Observações</label>
          <textarea id="vacObs" class="form-control-custom-noicon" rows="3" placeholder="Insira detalhes adicionais sobre contraindicações ou armazenamento..."></textarea>
        </div>

        <!-- Botoes Salvar e Cancelar -->
        <div class="d-flex gap-3 mt-4">
          <a href="vacina/lista_vacinas.php" class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
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
    document.getElementById("vacinaForm").addEventListener("submit", function(e) {
      e.preventDefault();
      
      const novaVacina = {
        id: Date.now(),
        lote: document.getElementById("vacLote").value,
        nome: document.getElementById("vacNome").value,
        tipo: document.getElementById("vacTipo").value,
        intervalo: document.getElementById("vacIntervalo").value,
        obs: document.getElementById("vacObs").value
      };

      const vacinas = JSON.parse(localStorage.getItem("vacinas")) || [];
      vacinas.push(novaVacina);
      localStorage.setItem("vacinas", JSON.stringify(vacinas));

      alert("Vacina cadastrada com sucesso!");
      window.location.href = "vacina/lista_vacinas.php";
    });
  </script>
</body>

</html>
