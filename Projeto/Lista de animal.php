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
  <!-- TOPO (menu.js will wrap this inside main content) -->
  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Lista de Animais</h2>
      <p class="subtitulo">Gerencie todos os animais cadastrados na fazenda</p>
    </div>
    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
    <a class="btn btn-success rounded-pill px-4 py-2" href="cadastro de animal.php">
      <i class="bi bi-plus-circle me-2"></i> Novo Animal
    </a>
    <?php endif; ?>
  </div>

  <!-- BUSCA -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="input-group input-icon-wrapper">
        <span class="input-group-text bg-transparent border-0 ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="pesquisa" class="form-control form-control-custom" placeholder="Pesquisar animal por nome, número ou raça..." onkeyup="filtrarAnimais()" />
      </div>
    </div>
  </div>

  <!-- CARDS LIST -->
  <div class="row g-4" id="listaAnimais">
    <!-- Rendered dynamically -->
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
            <label class="form-label fw-semibold">Número</label>
            <input type="text" id="editNumero" class="form-control" />
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
            <input type="text" id="editLote" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Sexo</label>
            <select id="editSexo" class="form-select">
              <option>Macho</option>
              <option>Fêmea</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Pai</label>
            <input type="text" id="editPai" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mãe</label>
            <input type="text" id="editMae" class="form-control" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success rounded-pill px-4" onclick="salvarEdicaoAnimal()">Salvar Alterações</button>
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
    function getAnimais() {
      return JSON.parse(localStorage.getItem("animais")) || [];
    }

    function renderizarAnimais(lista = getAnimais()) {
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
                <span class="fs-5">${animal.nome}</span>
                <span class="badge-id">${animal.numero}</span>
              </div>
              <div class="item-card-body">
                <p><strong>Espécie:</strong> ${animal.especie || 'Bovino'}</p>
                <p><strong>Raça:</strong> ${animal.raca || 'Nelore'}</p>
                <p><strong>Sexo:</strong> ${animal.sexo}</p>
                <p><strong>Lote:</strong> <span class="badge bg-light text-dark border">${animal.lote || 'Nenhum'}</span></p>
                <div class="row mt-2 g-1 text-muted small">
                  <div class="col-6"><strong>Pai:</strong> ${animal.pai || '---'}</div>
                  <div class="col-6"><strong>Mãe:</strong> ${animal.mae || '---'}</div>
                </div>
              </div>
              <div class="item-card-actions">
                <a class="btn btn-sm btn-outline-success rounded-pill px-3" href="Ficha de animal.php?id=${animal.id}">
                  <i class="bi bi-file-text"></i> Ficha
                </a>
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="editarAnimal(${animal.id})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="excluirAnimal(${animal.id})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      });
    }

    function excluirAnimal(id) {
      if (confirm("Deseja realmente excluir este animal?")) {
        let animais = getAnimais();
        animais = animais.filter(a => a.id !== id);
        localStorage.setItem("animais", JSON.stringify(animais));
        renderizarAnimais();
      }
    }

    function editarAnimal(id) {
      const animais = getAnimais();
      const animal = animais.find(a => a.id === id);
      if (animal) {
        document.getElementById("editAnimalId").value = animal.id;
        document.getElementById("editNome").value = animal.nome;
        document.getElementById("editNumero").value = animal.numero;
        document.getElementById("editEspecie").value = animal.especie || 'Bovino';
        document.getElementById("editRaca").value = animal.raca || '';
        document.getElementById("editLote").value = animal.lote || '';
        document.getElementById("editSexo").value = animal.sexo;
        document.getElementById("editPai").value = animal.pai || '';
        document.getElementById("editMae").value = animal.mae || '';

        new bootstrap.Modal(document.getElementById("modalEditarAnimal")).show();
      }
    }

    function salvarEdicaoAnimal() {
      const id = parseInt(document.getElementById("editAnimalId").value);
      const animais = getAnimais();
      const index = animais.findIndex(a => a.id === id);

      if (index !== -1) {
        animais[index].nome = document.getElementById("editNome").value;
        animais[index].numero = document.getElementById("editNumero").value;
        animais[index].especie = document.getElementById("editEspecie").value;
        animais[index].raca = document.getElementById("editRaca").value;
        animais[index].lote = document.getElementById("editLote").value;
        animais[index].sexo = document.getElementById("editSexo").value;
        animais[index].pai = document.getElementById("editPai").value;
        animais[index].mae = document.getElementById("editMae").value;

        localStorage.setItem("animais", JSON.stringify(animais));
        
        // Hide modal
        bootstrap.Modal.getInstance(document.getElementById("modalEditarAnimal")).hide();
        renderizarAnimais();
      }
    }

    function filtrarAnimais() {
      const query = document.getElementById("pesquisa").value.toLowerCase();
      const animais = getAnimais();
      const filtrados = animais.filter(animal => 
        animal.nome.toLowerCase().includes(query) ||
        animal.numero.toLowerCase().includes(query) ||
        animal.raca.toLowerCase().includes(query)
      );
      renderizarAnimais(filtrados);
    }

    // Run render on load
    window.addEventListener("load", () => {
      renderizarAnimais();
    });
  </script>
</body>

</html>