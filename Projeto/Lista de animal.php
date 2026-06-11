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
    <a class="btn btn-success rounded-pill px-4 py-2" href="cadastro de animal.php">
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
            <input type="text" id="editNumero" class="form-control" readonly disabled style="background-color: #e9ecef; color: #6c757d;" />
            <small class="text-muted"><i class="bi bi-info-circle"></i> Gerado automaticamente</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Espécie</label>
            <input type="text" id="editEspecie" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Raça</label>
            <input type="text" id="editRaca" class="form-control" />
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
              <input type="text" id="editPai" class="form-control" />
            </div>
            <div class="col-6 mb-3">
              <label class="form-label fw-semibold">Mãe</label>
              <input type="text" id="editMae" class="form-control" />
            </div>
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
                <a class="btn btn-sm btn-outline-success rounded-pill px-3" href="Ficha de animal.php?id=${animal.id_animal}">
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

    function editarAnimal(animal) {
      document.getElementById("editAnimalId").value = animal.id_animal;
      document.getElementById("editNome").value = animal.nome_animal;
      document.getElementById("editNumero").value = animal.numero_brinco;
      document.getElementById("editEspecie").value = animal.especie;
      document.getElementById("editRaca").value = animal.raca;
      document.getElementById("editLote").value = animal.id_lote || '';
      document.getElementById("editSexo").value = animal.sexo;
      document.getElementById("editPai").value = animal.pai;
      document.getElementById("editMae").value = animal.mae;
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