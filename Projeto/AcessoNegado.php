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
  <title>Acesso Negado - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- Topo (menu.js will wrap this inside main content) -->
  <div class="topo-pagina d-none d-md-flex">
    <div>
      <h2 class="titulo-pagina">Acesso Restrito</h2>
      <p class="subtitulo">Você não possui privilégios de administrador</p>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 65vh;">
      <div class="col-12 col-md-8 col-lg-6 text-center">
        <div class="card-premium p-5 shadow-sm text-center">
          <div class="mb-4">
             <i class="bi bi-shield-slash-fill text-danger" style="font-size: 5rem;"></i>
          </div>
          <h2 class="fw-bold text-dark mb-3">Acesso Restrito</h2>
          <p class="text-muted fs-5 mb-4">
              Desculpe, sua conta de **Veterinário(a)** não tem permissão para acessar esta área administrativa.
          </p>
          <hr class="my-4" style="opacity: 0.1;">
          <a href="Dashboard.php" class="btn btn-primary-custom py-3 px-5 rounded-pill fs-6 d-inline-flex align-items-center gap-2">
             <i class="bi bi-house-door-fill"></i> Voltar ao Painel Principal
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>
</body>

</html>
