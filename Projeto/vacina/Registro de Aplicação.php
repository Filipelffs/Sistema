<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

// Busca animais
$sqlAnimais = "SELECT a.id_animal, a.nome_animal, a.numero_brinco, l.codigo_lote 
               FROM animais a 
               LEFT JOIN lotes l ON a.id_lote = l.id_lote";
$resAnimais = $conn->query($sqlAnimais);
$animais = [];
if ($resAnimais) {
    while($row = $resAnimais->fetch_assoc()) {
        $animais[] = $row;
    }
}

// Busca estoque disponível (qtd > 0)
$sqlProdutos = "SELECT id, nome, tipo, quantidade, data_vencimento 
                FROM vacinas_medicamentos 
                WHERE quantidade > 0 
                ORDER BY data_vencimento ASC";
$resProdutos = $conn->query($sqlProdutos);
$produtos = [];
if ($resProdutos) {
    while($row = $resProdutos->fetch_assoc()) {
        $produtos[] = $row;
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
      <p class="subtitulo">Grave a aplicação de uma vacina ou medicamento em um animal</p>
    </div>
  </div>

  <div class="card card-premium">
    <div class="card-header-green">
      <span>Registro de Aplicação</span>
      <i class="bi bi-clipboard2-check"></i>
    </div>
    <div class="card-body">
      <form id="aplicacaoForm">
        <div id="formAlert" class="alert d-none"></div>

        <!-- Animal -->
        <div class="form-group-custom mb-3">
          <label>Animal</label>
          <select id="aplAnimal" class="form-select form-control-custom-noicon" required>
            <option value="">Selecione o animal...</option>
            <?php foreach($animais as $a): ?>
              <option value="<?= $a['id_animal'] ?>">
                <?= htmlspecialchars($a['nome_animal']) ?> 
                (<?= htmlspecialchars($a['numero_brinco']) ?>) - Lote: <?= htmlspecialchars($a['codigo_lote'] ?? 'Sem lote') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Produto -->
        <div class="form-group-custom mb-3">
          <label>Vacina ou Medicamento (Disponíveis)</label>
          <select id="aplItem" class="form-select form-control-custom-noicon" required>
            <option value="">Selecione o produto...</option>
            <?php 
              // Agrupar por tipo
              $vacinas = array_filter($produtos, fn($p) => $p['tipo'] === 'vacina');
              $medicamentos = array_filter($produtos, fn($p) => $p['tipo'] === 'medicamento');
            ?>
            <optgroup label="Vacinas">
              <?php foreach($vacinas as $v): ?>
                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome']) ?> (Estoque: <?= $v['quantidade'] ?> | Vence: <?= date('d/m/Y', strtotime($v['data_vencimento'])) ?>)</option>
              <?php endforeach; ?>
            </optgroup>
            <optgroup label="Medicamentos">
              <?php foreach($medicamentos as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?> (Estoque: <?= $m['quantidade'] ?> | Vence: <?= date('d/m/Y', strtotime($m['data_vencimento'])) ?>)</option>
              <?php endforeach; ?>
            </optgroup>
          </select>
        </div>

        <!-- Tipo -->
        <div class="form-group-custom mb-3">
          <label>Tipo / Dose</label>
          <input type="text" id="aplTipo" class="form-control-custom-noicon" placeholder="Ex: 1ª dose, reforço, dose única..." required>
        </div>

        <!-- Data da aplicação -->
        <div class="form-group-custom mb-3">
          <label>Data da aplicação</label>
          <input type="date" id="aplData" class="form-control-custom-noicon" required value="<?= date('Y-m-d') ?>">
        </div>

        <!-- Observações -->
        <div class="form-group-custom mb-4">
          <label>Observações</label>
          <textarea id="aplObs" class="form-control-custom-noicon" rows="3" placeholder="Insira detalhes adicionais sobre o estado do animal ou reação..."></textarea>
        </div>

        <!-- Botoes Salvar e Cancelar -->
        <div class="d-flex gap-3 mt-4">
          <a href="historico_de_vacinas.php" class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
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
    document.getElementById("aplicacaoForm").addEventListener("submit", async function(e) {
      e.preventDefault();

      const btn = document.getElementById("btnSalvar");
      const alertDiv = document.getElementById("formAlert");
      btn.disabled = true;
      btn.innerHTML = 'Salvando...';
      alertDiv.classList.add('d-none');

      const data = {
        id_animal: document.getElementById("aplAnimal").value,
        id_produto: document.getElementById("aplItem").value,
        tipo: document.getElementById("aplTipo").value,
        data_aplicacao: document.getElementById("aplData").value,
        observacoes: document.getElementById("aplObs").value
      };

      try {
        const response = await fetch('aplicacao_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
          alert("Aplicação registrada com sucesso! O estoque foi atualizado.");
          window.location.href = "historico_de_vacinas.php";
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = result.message;
          alertDiv.classList.remove('d-none');
          btn.disabled = false;
          btn.innerHTML = 'Salvar';
        }
      } catch (error) {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Erro de conexão com o servidor.';
        alertDiv.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Salvar';
      }
    });
  </script>
</body>
</html>