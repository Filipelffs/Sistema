<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

// Fetch Lotes for the edit dropdown
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de Animais - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <!-- CSS -->
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <!-- TOPO -->
  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Lista de Animais</h2>
      <p class="subtitulo">Gerencie todos os animais cadastrados na fazenda</p>
    </div>
    <?php if ($isAdmin): ?>
    <a class="btn btn-success rounded-pill px-4 py-2" href="cadastro_animal.php">
      <i class="bi bi-plus-circle me-2"></i> Novo Animal
    </a>
    <?php endif; ?>
  </div>

  <!-- BUSCA -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="input-icon-wrapper">
        <i class="bi bi-search"></i>
        <input type="text" id="pesquisa" class="form-control form-control-custom" placeholder="Pesquisar animal por nome, número ou raça..." onkeyup="filtrarAnimais()" />
      </div>
    </div>
  </div>

  <!-- CARDS LIST -->
  <div class="row g-4" id="listaAnimais">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div class="modal fade" id="modalEditarAnimal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">Editar Animal</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editAnimalId" />
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome</label>
            <input type="text" id="editNome" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Número do Brinco</label>
            <input type="text" id="editNumero" class="form-control" readonly disabled />
            <small class="text-muted"><i class="bi bi-info-circle"></i> Gerado automaticamente</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Espécie</label>
            <select id="editEspecie" class="form-select" onchange="onEspecieMudouEditar()">
              <option value="" disabled>Selecione a espécie</option>
              <option value="Bovino">Bovino</option>
              <option value="Ovino">Ovino</option>
              <option value="Caprino">Caprino</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Raça</label>
            <select id="editRaca" class="form-select" disabled>
              <option value="">Selecione a espécie primeiro</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Lote</label>
            <select id="editLote" class="form-select">
              <option value="">Nenhum Lote</option>
              <?php foreach($lotes as $l): ?>
                <option value="<?= $l['id_lote'] ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Sexo</label>
            <select id="editSexo" class="form-select">
              <option value="Macho">Macho</option>
              <option value="Fêmea">Fêmea</option>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label fw-semibold">Pai</label>
              <select id="editPai" class="form-select" disabled>
                <option value="">Selecione a espécie primeiro</option>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label fw-semibold">Mãe</label>
              <select id="editMae" class="form-select" disabled>
                <option value="">Selecione a espécie primeiro</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Peso (kg)</label>
            <input type="number" step="0.01" id="editPeso" class="form-control" placeholder="Ex: 150.5" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Data de Nascimento</label>
            <input type="date" id="editDataNasc" class="form-control" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" id="btnSalvarEdicao" onclick="salvarEdicaoAnimal()">Salvar Alterações</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <!-- JS LOGIC -->
  <script>
    let animaisList = [];
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

    async function carregarAnimais() {
      try {
        const response = await fetch('animal_action.php?action=list');
        const result = await response.json();
        if (result.success) {
          animaisList = result.data;
          renderizarAnimais(animaisList);
        } else {
          document.getElementById('listaAnimais').innerHTML = `<div class="col-12 alert alert-danger">${result.message}</div>`;
        }
      } catch (error) {
        document.getElementById('listaAnimais').innerHTML = `<div class="col-12 alert alert-danger">Erro ao carregar animais.</div>`;
      }
    }

    function renderizarAnimais(lista) {
      const container = document.getElementById("listaAnimais");
      container.innerHTML = "";

      if (lista.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
            <h5 class="text-muted">Nenhum animal encontrado</h5>
          </div>
        `;
        return;
      }

      lista.forEach(animal => {
        container.innerHTML += `
          <div class="col-12 col-md-6 col-xl-4">
            <div class="item-card">
              <div class="item-card-header">
                <span class="fs-5">${animal.nome_animal}</span>
                <span class="badge-id">${animal.numero_brinco || 'S/N'}</span>
              </div>
              <div class="item-card-body">
                <p><strong>Espécie:</strong> ${animal.especie}</p>
                <p><strong>Raça:</strong> ${animal.raca || 'Não especificada'}</p>
                <p><strong>Sexo:</strong> ${animal.sexo}</p>
                <p><strong>Lote:</strong> <span class="badge bg-light text-dark border">${animal.codigo_lote || 'Nenhum'}</span></p>
                <div class="row mt-2 g-1 text-muted small">
                  <div class="col-6"><strong>Pai:</strong> ${animal.pai || '---'}</div>
                  <div class="col-6"><strong>Mãe:</strong> ${animal.mae || '---'}</div>
                </div>
              </div>
              <div class="item-card-actions">
                <a class="btn btn-sm btn-outline-success rounded-pill px-3" href="ficha_animal.php?id=${animal.id_animal}">
                  <i class="bi bi-file-text"></i> Ficha
                </a>
                ${isAdmin ? `
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick='editarAnimal(${JSON.stringify(animal).replace(/'/g, "&#39;")})'>
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="excluirAnimal(${animal.id_animal})">
                  <i class="bi bi-trash"></i>
                </button>
                ` : ''}
              </div>
            </div>
          </div>
        `;
      });
    }

    async function excluirAnimal(id) {
      if (confirm("Deseja realmente excluir este animal?")) {
        try {
          const res = await fetch('animal_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id_animal: id })
          });
          const result = await res.json();
          if (result.success) {
            carregarAnimais();
          } else {
            alert(result.message);
          }
        } catch (e) {
          alert("Erro de conexão.");
        }
      }
    }

    // Raças por espécie (de acordo com o cadastro)
    const racasPorEspecie = {
      Bovino:  ['Holstein-Frísia','Hereford','Angus','Simental','Limousin','Azul-Belga','Belted Galloway','Braford'],
      Ovino:   ['Dorper','Santa-Inês','Suffolk','Texel','Morada-Nova'],
      Caprino: ['Boer','Pigmeu','Saanen','Moxotó','Savana','Alpina','Canindé']
    };

    function onEspecieMudouEditar() {
      const especie = document.getElementById('editEspecie').value;
      const animalId = parseInt(document.getElementById("editAnimalId").value) || 0;
      atualizarRacasEditar(especie);
      atualizarPaiMaeEditar(especie, 'Não informado', 'Não informado', animalId);
    }

    function atualizarRacasEditar(especie, valorSelecionado = '') {
      const sel = document.getElementById('editRaca');
      sel.innerHTML = '<option value="">Nenhuma / Não especificada</option>';
      if (especie && racasPorEspecie[especie]) {
        racasPorEspecie[especie].forEach(r => {
          const op = document.createElement('option');
          op.value = r;
          op.textContent = r;
          if (r === valorSelecionado) {
            op.selected = true;
          }
          sel.appendChild(op);
        });
        sel.disabled = false;
      } else {
        sel.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        sel.disabled = true;
      }
    }

    function atualizarPaiMaeEditar(especie, paiSelecionado = '', maeSelecionado = '', idAnimalAtual = 0) {
      const selPai = document.getElementById('editPai');
      const selMae = document.getElementById('editMae');

      if (!especie) {
        selPai.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        selPai.disabled = true;
        selMae.innerHTML = '<option value="">Selecione a espécie primeiro</option>';
        selMae.disabled = true;
        return;
      }

      // Normaliza valores nulos/vazios para "Não informado"
      if (!paiSelecionado) paiSelecionado = 'Não informado';
      if (!maeSelecionado) maeSelecionado = 'Não informado';

      // Filtra machos da mesma espécie para o PAI (excluindo o próprio animal sendo editado)
      const machos = animaisList.filter(a =>
        a.especie.toLowerCase() === especie.toLowerCase() && 
        a.sexo === 'Macho' && 
        parseInt(a.id_animal) !== parseInt(idAnimalAtual)
      );
      selPai.innerHTML = '<option value="Não informado">Nenhum / Não registrado no sistema</option>';
      machos.forEach(a => {
        const op = document.createElement('option');
        op.value = a.nome_animal;
        op.textContent = `${a.nome_animal} (${a.numero_brinco || 'sem brinco'})`;
        if (a.nome_animal === paiSelecionado) {
          op.selected = true;
        }
        selPai.appendChild(op);
      });
      // Caso o pai atual seja informado mas não esteja na lista de machos (ex: deletado ou inconsistência), manter selecionado
      if (paiSelecionado !== 'Não informado' && !machos.some(a => a.nome_animal === paiSelecionado)) {
        const op = document.createElement('option');
        op.value = paiSelecionado;
        op.textContent = `${paiSelecionado} (atual)`;
        op.selected = true;
        selPai.appendChild(op);
      }
      selPai.disabled = false;

      // Filtra fêmeas da mesma espécie para a MÃE (excluindo o próprio animal sendo editado)
      const femeas = animaisList.filter(a =>
        a.especie.toLowerCase() === especie.toLowerCase() && 
        a.sexo === 'Fêmea' && 
        parseInt(a.id_animal) !== parseInt(idAnimalAtual)
      );
      selMae.innerHTML = '<option value="Não informado">Nenhuma / Não registrada no sistema</option>';
      femeas.forEach(a => {
        const op = document.createElement('option');
        op.value = a.nome_animal;
        op.textContent = `${a.nome_animal} (${a.numero_brinco || 'sem brinco'})`;
        if (a.nome_animal === maeSelecionado) {
          op.selected = true;
        }
        selMae.appendChild(op);
      });
      // Caso a mãe atual seja informada mas não esteja na lista de fêmeas, manter selecionada
      if (maeSelecionado !== 'Não informado' && !femeas.some(a => a.nome_animal === maeSelecionado)) {
        const op = document.createElement('option');
        op.value = maeSelecionado;
        op.textContent = `${maeSelecionado} (atual)`;
        op.selected = true;
        selMae.appendChild(op);
      }
      selMae.disabled = false;
    }

    function editarAnimal(animal) {
      const idAnimalAtual = parseInt(animal.id_animal);
      document.getElementById("editAnimalId").value = animal.id_animal;
      document.getElementById("editNome").value = animal.nome_animal;
      document.getElementById("editNumero").value = animal.numero_brinco;
      
      // Define a espécie
      document.getElementById("editEspecie").value = animal.especie;
      
      // Carrega raças correspondentes
      atualizarRacasEditar(animal.especie, animal.raca);
      
      document.getElementById("editLote").value = animal.id_lote || '';
      document.getElementById("editSexo").value = animal.sexo;
      
      // Carrega pais correspondentes da mesma espécie
      atualizarPaiMaeEditar(animal.especie, animal.pai, animal.mae, idAnimalAtual);

      document.getElementById("editPeso").value = animal.peso || '';
      document.getElementById("editDataNasc").value = animal.data_nascimento;

      new bootstrap.Modal(document.getElementById("modalEditarAnimal")).show();
    }

    async function salvarEdicaoAnimal() {
      const btn = document.getElementById("btnSalvarEdicao");
      btn.disabled = true;

      const data = {
        action: 'edit',
        id_animal: document.getElementById("editAnimalId").value,
        nome: document.getElementById("editNome").value,
        especie: document.getElementById("editEspecie").value,
        raca: document.getElementById("editRaca").value,
        id_lote: document.getElementById("editLote").value,
        sexo: document.getElementById("editSexo").value,
        pai: document.getElementById("editPai").value,
        mae: document.getElementById("editMae").value,
        peso: document.getElementById("editPeso").value,
        data_nascimento: document.getElementById("editDataNasc").value
      };

      try {
        const res = await fetch('animal_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
          bootstrap.Modal.getInstance(document.getElementById("modalEditarAnimal")).hide();
          carregarAnimais();
        } else {
          alert(result.message);
        }
      } catch (e) {
        alert("Erro de conexão.");
      } finally {
        btn.disabled = false;
      }
    }

    function filtrarAnimais() {
      const query = document.getElementById("pesquisa").value.toLowerCase();
      const filtrados = animaisList.filter(a => 
        (a.nome_animal && a.nome_animal.toLowerCase().includes(query)) ||
        (a.numero_brinco && a.numero_brinco.toLowerCase().includes(query)) ||
        (a.raca && a.raca.toLowerCase().includes(query))
      );
      renderizarAnimais(filtrados);
    }

    window.addEventListener("load", carregarAnimais);
  </script>
</body>
</html>