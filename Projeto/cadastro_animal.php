<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";
checarAcesso("admin");

$sqlLotes = "SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC";
$resLotes = $conn->query($sqlLotes);
$lotes = [];
if ($resLotes) {
    while ($r = $resLotes->fetch_assoc()) $lotes[] = $r;
}

// Buscar todos os animais para seleção de pai/mãe
$sqlAnimais = "SELECT id_animal, nome_animal, numero_brinco, especie, sexo FROM animais ORDER BY nome_animal ASC";
$resAnimais = $conn->query($sqlAnimais);
$todosAnimais = [];
if ($resAnimais) {
    while ($r = $resAnimais->fetch_assoc()) $todosAnimais[] = $r;
}
?>
<!DOCTYPE html>
<html lang="pt-br" <?= $TEMA_ESCURO ? 'data-theme="dark"' : '' ?>>

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .badge-especie {
      font-size: .78rem;
      padding: .35em .7em;
    }
    select option:disabled { color: #aaa; }
  </style>
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

        <!-- Número do brinco (Automático) -->
        <div class="form-group-custom mb-3">
          <label>Número do Brinco</label>
          <input type="text" class="form-control-custom-noicon" value="Gerado automaticamente ao salvar" readonly disabled
                 style="background-color:#dee2e6;color:#6c757d;font-style:italic;">
          <small class="text-muted"><i class="bi bi-info-circle"></i> O número do brinco é gerado automaticamente pelo sistema (ex: BRI-001)</small>
        </div>

        <!-- Nome do Animal -->
        <div class="form-group-custom mb-3">
          <label>Nome do Animal</label>
          <input type="text" id="aniNome" class="form-control-custom-noicon" placeholder="Ex: Mimosa, Paula..." required>
        </div>

        <!-- Espécie (select fixo) -->
        <div class="form-group-custom mb-3">
          <label>Espécie</label>
          <select id="aniEspecie" class="form-select form-control-custom-noicon" required onchange="onEspecieMudou()">
            <option value="" disabled selected>Selecione a espécie</option>
            <option value="Bovino">Bovino</option>
            <option value="Ovino">Ovino</option>
            <option value="Caprino">Caprino</option>
          </select>
        </div>

        <!-- Raça (depende da espécie) -->
        <div class="form-group-custom mb-3">
          <label>Raça <small class="text-muted">(Opcional)</small></label>
          <select id="aniRaca" class="form-select form-control-custom-noicon" disabled>
            <option value="">Selecione a espécie primeiro</option>
          </select>
        </div>

        <!-- Lote -->
        <div class="form-group-custom mb-3">
          <label>Lote</label>
          <select id="aniLote" class="form-select form-control-custom-noicon">
            <option value="">Selecione o Lote (Opcional)</option>
            <?php foreach($lotes as $l): ?>
              <option value="<?= $l['id_lote'] ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Sexo -->
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
              <select id="aniPai" class="form-select form-control-custom-noicon" disabled>
                <option value="">Selecione a espécie primeiro</option>
              </select>
              <small class="text-muted"><i class="bi bi-info-circle"></i> Filtrado por espécie (machos)</small>
            </div>
          </div>
          <!-- Mãe -->
          <div class="col-6 mb-3">
            <div class="form-group-custom">
              <label>Mãe</label>
              <select id="aniMae" class="form-select form-control-custom-noicon" disabled>
                <option value="">Selecione a espécie primeiro</option>
              </select>
              <small class="text-muted"><i class="bi bi-info-circle"></i> Filtrado por espécie (fêmeas)</small>
            </div>
          </div>
        </div>

        <!-- Peso -->
        <div class="form-group-custom mb-3">
          <label>Peso (kg)</label>
          <input type="number" step="0.01" id="aniPeso" class="form-control-custom-noicon" placeholder="Ex: 150.5">
        </div>

        <!-- Botões -->
        <div class="d-flex gap-3 mt-4">
          <a href="lista_animal.php"
             class="btn btn-outline-secondary py-3 fs-5 rounded-pill w-50 d-flex align-items-center justify-content-center text-decoration-none">
            Cancelar
          </a>
          <button type="submit" id="btnSalvar" class="btn-primary-custom py-3 fs-5 w-50 mt-0">
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="menu.js"></script>

  <script>
    // Todos os animais vindos do banco (para filtros de pai/mãe)
    const todosAnimais = <?php echo json_encode($todosAnimais); ?>;

    // Raças por espécie
    const racasPorEspecie = {
      Bovino:  ['Holstein-Frísia','Hereford','Angus','Simental','Limousin','Azul-Belga','Belted Galloway','Braford'],
      Ovino:   ['Dorper','Santa-Inês','Suffolk','Texel','Morada-Nova'],
      Caprino: ['Boer','Pigmeu','Saanen','Moxotó','Savana','Alpina','Canindé']
    };

    function setGender(gender) {
      document.getElementById("aniSexo").value = gender;
      document.getElementById("btnMacho").classList.toggle('active', gender === 'Macho');
      document.getElementById("btnFemea").classList.toggle('active', gender === 'Fêmea');
    }

    function onEspecieMudou() {
      const especie = document.getElementById('aniEspecie').value;
      atualizarRacas(especie);
      atualizarPaiMae(especie);
    }

    function atualizarRacas(especie) {
      const sel = document.getElementById('aniRaca');
      sel.innerHTML = '<option value="">Nenhuma / Não especificada</option>';
      if (especie && racasPorEspecie[especie]) {
        racasPorEspecie[especie].forEach(r => {
          const op = document.createElement('option');
          op.value = r;
          op.textContent = r;
          sel.appendChild(op);
        });
        sel.disabled = false;
      } else {
        sel.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        sel.disabled = true;
      }
    }

    function atualizarPaiMae(especie) {
      const selPai = document.getElementById('aniPai');
      const selMae = document.getElementById('aniMae');

      if (!especie) {
        selPai.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        selPai.disabled = true;
        selMae.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        selMae.disabled = true;
        return;
      }

      // Filtra machos da mesma espécie para o PAI
      const machos = todosAnimais.filter(a =>
        a.especie.toLowerCase() === especie.toLowerCase() && a.sexo === 'Macho'
      );
      selPai.innerHTML = '<option value="">Nenhum / Não registrado no sistema</option>';
      if (machos.length === 0) {
        selPai.innerHTML += `<option value="" disabled>— Nenhum ${especie.toLowerCase()} macho cadastrado —</option>`;
      } else {
        machos.forEach(a => {
          selPai.innerHTML += `<option value="${a.nome_animal}">${a.nome_animal} (${a.numero_brinco || 'sem brinco'})</option>`;
        });
      }
      selPai.disabled = false;

      // Filtra fêmeas da mesma espécie para a MÃE
      const femeas = todosAnimais.filter(a =>
        a.especie.toLowerCase() === especie.toLowerCase() && a.sexo === 'Fêmea'
      );
      selMae.innerHTML = '<option value="">Nenhuma / Não registrada no sistema</option>';
      if (femeas.length === 0) {
        selMae.innerHTML += `<option value="" disabled>— Nenhum ${especie.toLowerCase()} fêmea cadastrado —</option>`;
      } else {
        femeas.forEach(a => {
          selMae.innerHTML += `<option value="${a.nome_animal}">${a.nome_animal} (${a.numero_brinco || 'sem brinco'})</option>`;
        });
      }
      selMae.disabled = false;
    }

    document.getElementById("animalForm").addEventListener("submit", async function(e) {
      e.preventDefault();

      const especie = document.getElementById("aniEspecie").value;
      if (!especie) {
        const alertDiv = document.getElementById("formAlert");
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Selecione uma espécie antes de salvar.';
        alertDiv.classList.remove('d-none');
        return;
      }

      const btn = document.getElementById("btnSalvar");
      const alertDiv = document.getElementById("formAlert");
      btn.disabled = true;
      btn.innerHTML = 'Salvando...';
      alertDiv.classList.add('d-none');

      const paiVal = document.getElementById("aniPai").value;
      const maeVal = document.getElementById("aniMae").value;

      const data = {
        action: 'create',
        nome: document.getElementById("aniNome").value,
        especie: especie,
        raca: document.getElementById("aniRaca").value,
        id_lote: document.getElementById("aniLote").value,
        sexo: document.getElementById("aniSexo").value,
        data_nascimento: document.getElementById("aniDataNasc").value,
        pai: paiVal || 'Não informado',
        mae: maeVal || 'Não informado',
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
          alert(result.message);
          window.location.href = "lista_animal.php";
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
