<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

$id = $_SESSION['usuario_id'];
$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

$sql = "SELECT nome, email, foto FROM usuarios WHERE id_usuario = $id";
$res = $conn->query($sql);
$user = $res->fetch_assoc();

if (!$user['foto']) {
    $user['foto'] = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200';
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
      tipo: <?php echo json_encode($_SESSION['usuario_tipo']); ?>,
      foto: <?php echo json_encode($user['foto']); ?>
    };
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configurações - Vacinação Animal</title>
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
      <h2 class="titulo-pagina">Configurações do Sistema</h2>
      <p class="subtitulo">Ajuste as preferências de acesso, alertas e perfil de usuário</p>
    </div>
  </div>

  <div class="row">
    <!-- Profile Card (Left) -->
    <div class="col-12 col-md-6 mb-4">
      <div class="card card-premium shadow-sm h-100">
        <div class="card-header-green">
          <span>Perfil do Usuário</span>
          <i class="bi bi-person-circle"></i>
        </div>
        <div class="card-body text-center py-4">
          <img src="<?= htmlspecialchars($user['foto']) ?>" id="userFoto" class="rounded-circle border mb-3 shadow-sm" width="100" height="100" style="object-fit: cover;">
          <h4 class="fw-bold mb-1" id="userNome"><?= htmlspecialchars($user['nome']) ?></h4>
          <p class="text-muted small mb-4" id="userEmail"><?= htmlspecialchars($user['email']) ?></p>

          <!-- Edit Inputs (Hidden by default) -->
          <div id="perfilEditInputs" class="d-none text-start mb-4">
            <div class="mb-3">
              <label class="form-label small fw-semibold">URL da Foto de Perfil</label>
              <input type="text" id="inputFoto" class="form-control" value="<?= htmlspecialchars($user['foto']) ?>">
            </div>
            
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nome Completo</label>
              <input type="text" id="inputNome" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">E-mail</label>
              <input type="email" id="inputEmail" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
            </div>
            <?php if(!$isAdmin): ?>
              <small class="text-warning"><i class="bi bi-info-circle"></i> Veterinários e Técnicos só podem alterar a foto de perfil.</small>
            <?php endif; ?>
          </div>

          <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-outline-success rounded-pill px-4" id="btnEditarPerfil" onclick="habilitarEdicaoPerfil()">
              Editar Perfil
            </button>
            <button class="btn btn-success rounded-pill px-4 d-none" id="btnSalvarPerfil" onclick="salvarPerfil()">
              Salvar Perfil
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Preferences & Security (Right) -->
    <div class="col-12 col-md-6 mb-4">
      <div class="card card-premium shadow-sm mb-4">
        <div class="card-header-green">
          <span>Segurança</span>
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="card-body">
          <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none text-dark py-2">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-key-fill text-success fs-5"></i>
              <span>Alterar senha de acesso</span>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
        </div>
      </div>

      <div class="card card-premium shadow-sm">
        <div class="card-header-green">
          <span>Preferências de Alertas</span>
          <i class="bi bi-sliders"></i>
        </div>
        <div class="card-body">
          <div class="switch-group">
            <div>
              <h6 class="fw-bold mb-1">Notificações por Email</h6>
              <small class="text-muted">Aviso de vencimentos e vacinas atrasadas</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="swNotificacoes" checked>
            </div>
          </div>
          <div class="switch-group">
            <div>
              <h6 class="fw-bold mb-1">Lembretes Diários</h6>
              <small class="text-muted">Relatórios resumidos sobre o rebanho</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="swLembretes" checked>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    function habilitarEdicaoPerfil() {
      document.getElementById("userNome").classList.add("d-none");
      document.getElementById("userEmail").classList.add("d-none");
      
      document.getElementById("perfilEditInputs").classList.remove("d-none");
      document.getElementById("btnEditarPerfil").classList.add("d-none");
      document.getElementById("btnSalvarPerfil").classList.remove("d-none");
    }

    async function salvarPerfil() {
      const btn = document.getElementById("btnSalvarPerfil");
      btn.disabled = true;
      btn.innerText = "Salvando...";

      const data = {
        foto: document.getElementById("inputFoto").value,
        nome: document.getElementById("inputNome").value,
        email: document.getElementById("inputEmail").value
      };

      try {
        const res = await fetch('perfil_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if(result.success) {
          alert(result.message);
          window.location.reload();
        } else {
          alert("Erro ao atualizar perfil.");
        }
      } catch(e) {
        alert("Erro de conexão.");
      } finally {
        btn.disabled = false;
        btn.innerText = "Salvar Perfil";
      }
    }
  </script>
</body>
</html>
