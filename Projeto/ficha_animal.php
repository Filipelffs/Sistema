<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

$animalId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch animal details
$animal = null;
if ($animalId > 0) {
    $sql = "SELECT a.*, l.codigo_lote 
            FROM animais a 
            LEFT JOIN lotes l ON a.id_lote = l.id_lote 
            WHERE a.id_animal = $animalId";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $animal = $res->fetch_assoc();
    }
}

// Fetch vaccine history
$aplicacoes = [];
if ($animalId > 0) {
    $sqlApls = "SELECT ap.*, vm.nome AS produto_nome, vm.tipo AS produto_tipo 
                FROM aplicacoes ap
                LEFT JOIN vacinas_medicamentos vm ON ap.id_vacina_medicamento = vm.id
                WHERE ap.id_animal = $animalId
                ORDER BY ap.data_aplicacao DESC";
    $resApls = $conn->query($sqlApls);
    if ($resApls) {
        while ($r = $resApls->fetch_assoc()) {
            $aplicacoes[] = $r;
        }
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ficha do Animal - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Ficha do Animal</h2>
      <p class="subtitulo">Ficha cadastral e prontuário vacinal do animal</p>
    </div>
  </div>

  <?php if (!$animal): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> Animal não encontrado ou ID inválido.
      <br><br>
      <a href="lista_animal.php" class="btn btn-primary rounded-pill px-4">Voltar à Lista</a>
    </div>
  <?php else: ?>
  <div class="row">
    <!-- Left Column: Animal Card details -->
    <div class="col-12 col-lg-8">
      <div class="animal-profile-card">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
          <h3 id="profileNome"><?= htmlspecialchars($animal['nome_animal']) ?></h3>
          <span class="badge bg-white text-success px-3 py-2 rounded-pill fs-6 fw-bold" id="profileNumero"><?= htmlspecialchars($animal['numero_brinco'] ?? 'S/N') ?></span>
        </div>
        <div class="animal-profile-details">
          <div class="animal-profile-text">
            <p><strong>Espécie:</strong> <span><?= htmlspecialchars($animal['especie']) ?></span></p>
            <p><strong>Raça:</strong> <span><?= htmlspecialchars($animal['raca'] ?? 'Não especificada') ?></span></p>
            <p><strong>Sexo:</strong> <span><?= htmlspecialchars($animal['sexo']) ?></span></p>
            <p><strong>Data de Nascimento:</strong> <span>
              <?= $animal['data_nascimento'] ? date('d/m/Y', strtotime($animal['data_nascimento'])) : 'Não cadastrada' ?>
            </span></p>
            <p><strong>Lote:</strong> <span><?= htmlspecialchars($animal['codigo_lote'] ?? 'Sem Lote') ?></span></p>
            <p><strong>Pai:</strong> <span><?= htmlspecialchars($animal['pai'] ?? 'Não informado') ?></span></p>
            <p><strong>Mãe:</strong> <span><?= htmlspecialchars($animal['mae'] ?? 'Não informada') ?></span></p>
          </div>
          <div>
            <?php
              $imgUrl = "https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=250"; // Bovino default
              if (!empty($animal['foto_animal'])) {
                  $imgUrl = htmlspecialchars($animal['foto_animal']);
              } elseif (stripos($animal['especie'], 'capri') !== false || stripos($animal['especie'], 'bode') !== false || stripos($animal['especie'], 'cabra') !== false) {
                  $imgUrl = "https://images.unsplash.com/photo-1524388680868-377a2e6bbb1c?q=80&w=250"; // Capri default
              }
            ?>
            <div class="position-relative d-inline-block">
              <img src="<?= $imgUrl ?>" alt="Animal Photo" class="animal-profile-img shadow-sm" id="profileFoto" style="width: 150px; height: 150px; object-fit: cover; border-radius: 20px;">
              <button class="btn btn-sm btn-success rounded-circle position-absolute bottom-0 end-0 m-1 shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#modalFotoAnimal" title="Alterar Foto">
                <i class="bi bi-camera-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Vaccine Application History Table -->
      <div class="card card-premium">
        <div class="card-header-green">
          <span>Histórico de Vacinas Aplicadas</span>
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Vacina/Medicamento</th>
                  <th>Tipo/Dose</th>
                  <th>Data</th>
                  <th>Aplicador</th>
                  <th class="pe-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody id="historicoTabela">
                <?php if (empty($aplicacoes)): ?>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                      Nenhuma vacina ou medicamento aplicado.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($aplicacoes as $apl): ?>
                    <?php
                      $statusClass = "success";
                      $statusText = "Aplicado";
                      if ($apl['status_aplicacao'] === 'Atrasada') {
                          $statusClass = "danger";
                          $statusText = "Atrasada";
                      } else if ($apl['status_aplicacao'] === 'Pendente') {
                          $statusClass = "warning";
                          $statusText = "Pendente";
                      }
                    ?>
                    <tr>
                      <td class="ps-3 fw-semibold">
                        <?= htmlspecialchars($apl['produto_nome'] ?? 'Produto Removido') ?>
                      </td>
                      <td><?= htmlspecialchars($apl['dose'] ?? 'Dose única') ?></td>
                      <td><?= date('d/m/Y', strtotime($apl['data_aplicacao'])) ?></td>
                      <td><?= htmlspecialchars($apl['tecnico'] ?? 'Não informado') ?></td>
                      <td class="pe-3 text-center">
                        <span class="badge-status <?= $statusClass ?>">
                          <i class="bi <?= $statusClass === 'success' ? 'bi-check-circle-fill' : ($statusClass === 'warning' ? 'bi-exclamation-circle-fill' : 'bi-x-circle-fill') ?> me-1"></i>
                          <?= $statusText ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Quick Stats, Shortcuts, Status Legends -->
    <div class="col-12 col-lg-4">
      <div class="d-grid gap-3 mb-4">
        <a href="vacina/lista_vacinas.php" class="btn btn-success btn-lg rounded-pill py-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
          <i class="bi bi-plus-circle-fill"></i> + VACINA
        </a>
        <a href="lista_animal.php" class="btn btn-outline-secondary btn-lg rounded-pill py-3 d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-arrow-left"></i> Voltar à Lista
        </a>
      </div>

      <!-- Status Indicators Legend Card -->
      <div class="card card-premium">
        <div class="card-header-green">
          <span>Legenda de Status</span>
          <i class="bi bi-info-circle-fill"></i>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status success"><i class="bi bi-check-circle-fill"></i> CONCLUÍDO</span>
              <small class="text-muted">Vacina já aplicada no animal</small>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status warning"><i class="bi bi-exclamation-circle-fill"></i> PENDENTE</span>
              <small class="text-muted">Ainda dentro do prazo de aplicação</small>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge-status danger"><i class="bi bi-x-circle-fill"></i> ATRASADA</span>
              <small class="text-muted">Data prevista já expirou</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- MODAL ALTERAR FOTO ANIMAL -->
  <div class="modal fade" id="modalFotoAnimal" tabindex="-1" aria-labelledby="modalFotoAnimalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title" id="modalFotoAnimalLabel"><i class="bi bi-camera me-2"></i>Alterar Foto do Animal</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="fotoAnimalForm" enctype="multipart/form-data">
            <input type="hidden" name="id_animal" value="<?= $animal['id_animal'] ?>">
            
            <div id="uploadAlert" class="alert d-none"></div>

            <!-- Upload File -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Enviar arquivo do computador</label>
              <input type="file" name="foto_animal" id="fileFotoAnimal" class="form-control" accept="image/*">
              <div class="form-text">Imagens JPG, PNG, GIF ou WEBP (máx. 2MB).</div>
            </div>

            <div class="text-center my-3 text-muted fw-bold">--- OU ---</div>

            <!-- Image URL -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Link (URL) da imagem na internet</label>
              <input type="url" name="foto_url" id="urlFotoAnimal" class="form-control" placeholder="https://exemplo.com/foto.jpg">
            </div>
          </form>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success rounded-pill px-4" id="btnSalvarFoto" onclick="enviarFotoAnimal()">Salvar Foto</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    async function enviarFotoAnimal() {
      const btn = document.getElementById("btnSalvarFoto");
      const alertDiv = document.getElementById("uploadAlert");
      
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
      alertDiv.classList.add("d-none");
      
      const formData = new FormData(document.getElementById("fotoAnimalForm"));
      
      try {
        const response = await fetch('upload_foto_animal.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          alert("Foto do animal atualizada com sucesso!");
          window.location.reload();
        } else {
          alertDiv.className = "alert alert-danger";
          alertDiv.textContent = result.message;
          alertDiv.classList.remove("d-none");
          btn.disabled = false;
          btn.innerHTML = 'Salvar Foto';
        }
      } catch (error) {
        alertDiv.className = "alert alert-danger";
        alertDiv.textContent = "Erro de conexão com o servidor.";
        alertDiv.classList.remove("d-none");
        btn.disabled = false;
        btn.innerHTML = 'Salvar Foto';
      }
    }
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>
</body>

</html>