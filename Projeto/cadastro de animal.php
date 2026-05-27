<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";
checarAcesso("admin");

// Fetch Lotes for the dropdown
$sqlLotes = "SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC";
$resLotes = $conn->query($sqlLotes);
$lotes = [];
if ($resLotes) {
    while ($r = $resLotes->fetch_assoc()) {
        $lotes[] = $r;
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
  <title>Cadastro de Animal - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Cadastro de Animal</h2>
      <p class="subtitulo">Insira as informações cadastrais do novo animal</p>
    </div>
  </div>

  <div class="card card-premium">
    <div class="card-header-green">
      <span>Cadastro de animal</span>
      <i class="bi bi-plus-circle"></i>
    </div>
    <div class="card-body">
      <form id="animalForm">
        <div id="formAlert" class="alert d-none"></div>

        <!-- Nome do Animal -->
        <div class="form-group-custom mb-3">
          <label>Nome do Animal</label>
          <input type="text" id="aniNome" class="form-control-custom-noicon" placeholder="Ex: Mimosa, Paula..." required>
        </div>

        <!-- Número do brinco -->
        <div class="form-group-custom mb-3">
          <label>Número do brinco</label>
          <input type="text" id="aniNumero" class="form-control-custom-noicon" placeholder="Ex: 001, 1003..." required>
        </div>

        <!-- Espécie -->
        <div class="form-group-custom mb-3">
          <label>Espécie</label>
          <input type="text" id="aniEspecie" class="form-control-custom-noicon" placeholder="Ex: Bovino, Caprino..." required>
        </div>

        <!-- Raça (Opcional) -->
        <div class="form-group-custom mb-3">
          <label>Raça (Opcional)</label>
          <input type="text" id="aniRaca" class="form-control-custom-noicon" placeholder="Ex: Nelore, Gir, Holandesa...">
        </div>

        <!-- Lote (Vindo do BD) -->
        <div class="form-group-custom mb-3">
          <label>Lote</label>
          <select id="aniLote" class="form-select form-control-custom-noicon">
            <option value="">Selecione o Lote (Opcional)</option>
            <?php foreach($lotes as $l): ?>
              <option value="<?= $l['id_lote'] ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Sexo (Macho / Fêmea buttons) -->
        <div class="form-group-custom mb-3">
          <label>Sexo</label>
          <div class="gender-selector">
            <button type="button" class="gender-btn active" id="btnMacho" onclick="setGender('Macho')">Macho</button>
            <button type="button" class="gender-btn" id="btnFemea" onclick="setGender('Fêmea')">Fêmea</button>
          </div>
          <input type="hidden" id="aniSexo" value="Macho">
        </div>

        <!-- Data de Nascimento -->
        <div class="form-group-custom mb-3">
          <label>Data de Nascimento</label>
          <input type="date" id="aniDataNasc" class="form-control-custom-noicon" required>
        </div>

        <div class="row">
          <!-- Pai -->
          <div class="col-6 mb-3">
            <div class="form-group-custom">
              <label>Pai</label>
              <input type="text" id="aniPai" class="form-control-custom-noicon" placeholder="Ex: Touro Nelore">
            </div>
          </div>
          <!-- Mãe -->
          <div class="col-6 mb-3">
            <div class="form-group-custom">
              <label>Mãe</label>
              <input type="text" id="aniMae" class="form-control-custom-noicon" placeholder="Ex: Vaca Gir">
            </div>
          </div>
        </div>

        <!-- Peso -->
        <div class="form-group-custom mb-3">
          <label>Peso (kg)</label>
          <input type="number" step="0.01" id="aniPeso" class="form-control-custom-noicon" placeholder="Ex: 150.5">
        </div>

        <!-- Botoes Salvar e Cancelar -->
        <div class="d-flex gap-3 mt-4">
          <a href="Lista de animal.php" class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
            Cancelar
          </a>
          <button type="submit" id="btnSalvar" class="btn-primary-custom py-3 fs-5 w-50 mt-0">
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
    function setGender(gender) {
      document.getElementById("aniSexo").value = gender;
      
      const btnMacho = document.getElementById("btnMacho");
      const btnFemea = document.getElementById("btnFemea");

      if (gender === 'Macho') {
        btnMacho.classList.add('active');
        btnFemea.classList.remove('active');
      } else {
        btnFemea.classList.add('active');
        btnMacho.classList.remove('active');
      }
    }

    document.getElementById("animalForm").addEventListener("submit", async function(e) {
      e.preventDefault();

      const btn = document.getElementById("btnSalvar");
      const alertDiv = document.getElementById("formAlert");
      btn.disabled = true;
      btn.innerHTML = 'Salvando...';
      alertDiv.classList.add('d-none');

      const data = {
        action: 'create',
        nome: document.getElementById("aniNome").value,
        numero: document.getElementById("aniNumero").value,
        especie: document.getElementById("aniEspecie").value,
        raca: document.getElementById("aniRaca").value,
        id_lote: document.getElementById("aniLote").value, // Vindo do Select
        sexo: document.getElementById("aniSexo").value,
        data_nascimento: document.getElementById("aniDataNasc").value,
        pai: document.getElementById("aniPai").value,
        mae: document.getElementById("aniMae").value,
        peso: document.getElementById("aniPeso").value
      };

      try {
        const response = await fetch('animal_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
          alert("Animal cadastrado com sucesso!");
          window.location.href = "Lista de animal.php";
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = result.message;
          alertDiv.classList.remove('d-none');
          btn.disabled = false;
          btn.innerHTML = 'Salvar';
        }
      } catch (error) {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Erro de comunicação com o servidor.';
        alertDiv.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Salvar';
      }
    });
  </script>
</body>
</html>