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
  <title>Registro de Aplicação - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Registro de Aplicação</h2>
      <p class="subtitulo">Grave a aplicação de uma vacina ou medicamento em um animal ou lote</p>
    </div>
  </div>

  <div class="card card-premium">
    <div class="card-header-green">
      <span>Registro de Aplicação</span>
      <i class="bi bi-clipboard2-check"></i>
    </div>
    <div class="card-body">
      <form id="aplicacaoForm">
        <!-- Animal ou Lote -->
        <div class="form-group-custom mb-3">
          <label>Animal ou Lote</label>
          <select id="aplAnimal" class="form-select form-control-custom-noicon" required>
            <!-- Rendered dynamically -->
          </select>
        </div>

        <!-- Vacina ou Medicamento -->
        <div class="form-group-custom mb-3">
          <label>Vacina ou Medicamento</label>
          <select id="aplItem" class="form-select form-control-custom-noicon" required>
            <!-- Rendered dynamically -->
          </select>
        </div>

        <!-- Tipo -->
        <div class="form-group-custom mb-3">
          <label>Tipo</label>
          <input type="text" id="aplTipo" class="form-control-custom-noicon" placeholder="Ex: 1ª dose, reforço, dose única..." required>
        </div>

        <!-- Data da aplicação -->
        <div class="form-group-custom mb-3">
          <label>Data da aplicação</label>
          <input type="date" id="aplData" class="form-control-custom-noicon" required>
        </div>

        <!-- Observações -->
        <div class="form-group-custom mb-4">
          <label>Observações</label>
          <textarea id="aplObs" class="form-control-custom-noicon" rows="3" placeholder="Insira detalhes adicionais sobre o estado do animal ou reação..."></textarea>
        </div>

        <!-- Botoes Salvar e Cancelar -->
        <div class="d-flex gap-3 mt-4">
          <a href="Lista de Vacinas.php" class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
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
    document.addEventListener("DOMContentLoaded", function() {
      const animais = JSON.parse(localStorage.getItem("animais")) || [];
      const vacinas = JSON.parse(localStorage.getItem("vacinas")) || [];
      const medicamentos = JSON.parse(localStorage.getItem("medicamentos")) || [];

      // Populate animal dropdown
      const animalSelect = document.getElementById("aplAnimal");
      animalSelect.innerHTML = `<option value="">Selecione o animal ou lote...</option>`;
      animais.forEach(a => {
        animalSelect.innerHTML += `<option value="${a.id}">${a.nome} (${a.numero}) - ${a.lote}</option>`;
      });

      // Populate vaccine / medicine dropdown
      const itemSelect = document.getElementById("aplItem");
      itemSelect.innerHTML = `<option value="">Selecione a vacina ou medicamento...</option>`;
      
      itemSelect.innerHTML += `<optgroup label="Vacinas">`;
      vacinas.forEach(v => {
        itemSelect.innerHTML += `<option value="Vacina|${v.nome}|${v.lote}">${v.nome} (Lote: ${v.lote})</option>`;
      });
      itemSelect.innerHTML += `</optgroup>`;

      itemSelect.innerHTML += `<optgroup label="Medicamentos">`;
      medicamentos.forEach(m => {
        itemSelect.innerHTML += `<option value="Medicamento|${m.nome}|${m.lote}">${m.nome} (Lote: ${m.lote})</option>`;
      });
      itemSelect.innerHTML += `</optgroup>`;

      // Submit logic
      document.getElementById("aplicacaoForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const selectedItem = document.getElementById("aplItem").value.split("|");
        const itemType = selectedItem[0];
        const itemName = selectedItem[1];
        const itemLote = selectedItem[2];

        const novaAplicacao = {
          id: Date.now(),
          animalId: parseInt(document.getElementById("aplAnimal").value),
          itemNome: itemName,
          tipo: itemType,
          dose: document.getElementById("aplTipo").value, // We map the "Tipo" field to the dose/type field
          data: document.getElementById("aplData").value,
          status: "Concluído", // Newly registered applications are marked as completed
          tecnico: "Julia Silva",
          lote: itemLote,
          obs: document.getElementById("aplObs").value
        };

        const aplicacoes = JSON.parse(localStorage.getItem("aplicacoes")) || [];
        aplicacoes.push(novaAplicacao);
        localStorage.setItem("aplicacoes", JSON.stringify(aplicacoes));

        alert("Aplicação registrada com sucesso!");
        window.location.href = "Lista de Vacinas.php";
      });
    });
  </script>
</body>

</html>