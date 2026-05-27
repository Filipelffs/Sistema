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
      <div class="card card-premium shadow-sm">
        <div class="card-header-green">
          <span>Perfil do Usuário</span>
          <i class="bi bi-person-circle"></i>
        </div>
        <div class="card-body text-center py-4">
          <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200" id="userFoto" class="rounded-circle border mb-3 shadow-sm" width="100" height="100" style="object-fit: cover;">
          <h4 class="fw-bold mb-1" id="userNome">julia Silva</h4>
          <p class="text-muted small mb-4" id="userEmail">juliasilva@gmail.com</p>

          <!-- Edit Inputs (Hidden by default) -->
          <div id="perfilEditInputs" class="d-none text-start mb-4">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nome Completo</label>
              <input type="text" id="inputNome" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">E-mail</label>
              <input type="email" id="inputEmail" class="form-control">
            </div>
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
      <!-- Security Panel -->
      <div class="card card-premium shadow-sm mb-4">
        <div class="card-header-green">
          <span>Segurança</span>
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="card-body">
          <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none text-dark py-2" onclick="alert('Redirecionando para alteração de senha de segurança...')">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-key-fill text-success fs-5"></i>
              <span>Alterar senha de acesso</span>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
        </div>
      </div>

      <!-- Preferences Panel -->
      <div class="card card-premium shadow-sm">
        <div class="card-header-green">
          <span>Preferências de Alertas</span>
          <i class="bi bi-sliders"></i>
        </div>
        <div class="card-body">
          <!-- Notificações -->
          <div class="switch-group">
            <div>
              <h6 class="fw-bold mb-1">Notificações por Email</h6>
              <small class="text-muted">Aviso de vencimentos e vacinas atrasadas</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="swNotificacoes" onchange="salvarPreferencias()">
            </div>
          </div>

          <!-- Lembretes -->
          <div class="switch-group">
            <div>
              <h6 class="fw-bold mb-1">Lembretes Diários</h6>
              <small class="text-muted">Relatórios resumidos sobre o rebanho</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="swLembretes" onchange="salvarPreferencias()">
            </div>
          </div>

          <!-- Validação de Acesso -->
          <div class="switch-group">
            <div>
              <h6 class="fw-bold mb-1">Validação de Acesso</h6>
              <small class="text-muted">Pedir senha para realizar alterações sensíveis</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="swValidacao" onchange="salvarPreferencias()">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Exit Button -->
  <div class="d-grid mb-5">
    <a href="logout.php" class="btn btn-danger btn-lg rounded-pill py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
      <i class="bi bi-box-arrow-right"></i> Sair da Conta
    </a>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const user = JSON.parse(localStorage.getItem("usuario")) || {
        nome: "Julia Silva",
        email: "Juliasilva@gmail.com",
        foto: "https://brasil.elpais.com/brasil/2017/07/30/deportes/1501429062_479994.html",
        notificacoes: true,
        lembretes: true,
        validacao: false
      };

      // Load profile info
      document.getElementById("userNome").innerText = user.nome;
      document.getElementById("userEmail").innerText = user.email;
      document.getElementById("userFoto").src = user.foto;

      // Set switches state
      document.getElementById("swNotificacoes").checked = user.notificacoes;
      document.getElementById("swLembretes").checked = user.lembretes;
      document.getElementById("swValidacao").checked = user.validacao;
    });

    function habilitarEdicaoPerfil() {
      const user = JSON.parse(localStorage.getItem("usuario"));
      
      document.getElementById("inputNome").value = user.nome;
      document.getElementById("inputEmail").value = user.email;

      document.getElementById("userNome").classList.add("d-none");
      document.getElementById("userEmail").classList.add("d-none");
      
      document.getElementById("perfilEditInputs").classList.remove("d-none");
      document.getElementById("btnEditarPerfil").classList.add("d-none");
      document.getElementById("btnSalvarPerfil").classList.remove("d-none");
    }

    function salvarPerfil() {
      const user = JSON.parse(localStorage.getItem("usuario"));
      user.nome = document.getElementById("inputNome").value;
      user.email = document.getElementById("inputEmail").value;

      localStorage.setItem("usuario", JSON.stringify(user));

      // Update UI
      document.getElementById("userNome").innerText = user.nome;
      document.getElementById("userEmail").innerText = user.email;

      document.getElementById("userNome").classList.remove("d-none");
      document.getElementById("userEmail").classList.remove("d-none");
      
      document.getElementById("perfilEditInputs").classList.add("d-none");
      document.getElementById("btnEditarPerfil").classList.remove("d-none");
      document.getElementById("btnSalvarPerfil").classList.add("d-none");

      alert("Perfil atualizado com sucesso!");
    }

    function salvarPreferencias() {
      const user = JSON.parse(localStorage.getItem("usuario"));
      user.notificacoes = document.getElementById("swNotificacoes").checked;
      user.lembretes = document.getElementById("swLembretes").checked;
      user.validacao = document.getElementById("swValidacao").checked;

      localStorage.setItem("usuario", JSON.stringify(user));
    }
  </script>
</body>

</html>
