<?php
require_once "sessao.php";
checarAcesso("admin"); // Somente admins podem ver e alterar usuários

require_once "../Banco/conexao.php";

$sucesso = "";
$erro = "";

// 1. Cadastrar Usuário
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $telefone = trim($_POST['telefone']);
    $tipo_usuario = $_POST['tipo_usuario'];

    if (empty($nome) || empty($email) || empty($senha) || empty($tipo_usuario)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Verifica se e-mail já existe
        $stmt_check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $erro = "Já existe um usuário cadastrado com este e-mail.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt_ins = $conn->prepare("INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("sssss", $nome, $email, $senha_hash, $telefone, $tipo_usuario);
            if ($stmt_ins->execute()) {
                $sucesso = "Usuário cadastrado com sucesso!";
            } else {
                $erro = "Erro ao cadastrar usuário: " . $conn->error;
            }
            $stmt_ins->close();
        }
        $stmt_check->close();
    }
}

// 2. Editar Usuário
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id_usuario = intval($_POST['id_usuario']);
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $telefone = trim($_POST['telefone']);
    $tipo_usuario = $_POST['tipo_usuario'];

    if (empty($nome) || empty($email) || empty($tipo_usuario)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Verifica se e-mail já existe para outro usuário
        $stmt_check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
        $stmt_check->bind_param("si", $email, $id_usuario);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $erro = "Já existe outro usuário cadastrado com este e-mail.";
        } else {
            if (!empty($senha)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt_upd = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, telefone = ?, tipo_usuario = ? WHERE id_usuario = ?");
                $stmt_upd->bind_param("sssssi", $nome, $email, $senha_hash, $telefone, $tipo_usuario, $id_usuario);
            } else {
                $stmt_upd = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, tipo_usuario = ? WHERE id_usuario = ?");
                $stmt_upd->bind_param("ssssi", $nome, $email, $telefone, $tipo_usuario, $id_usuario);
            }

            if ($stmt_upd->execute()) {
                $sucesso = "Usuário atualizado com sucesso!";
                // Se atualizou o próprio perfil logado, atualiza a sessão
                if ($id_usuario === intval($_SESSION['usuario_id'])) {
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario_email'] = $email;
                    $_SESSION['usuario_tipo'] = $tipo_usuario;
                    $_SESSION['usuario_telefone'] = $telefone;
                }
            } else {
                $erro = "Erro ao atualizar usuário: " . $conn->error;
            }
            $stmt_upd->close();
        }
        $stmt_check->close();
    }
}

// 3. Excluir Usuário
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);

    if ($id_excluir === intval($_SESSION['usuario_id'])) {
        $erro = "Você não pode excluir a sua própria conta ativa.";
    } else {
        $stmt_del = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt_del->bind_param("i", $id_excluir);
        if ($stmt_del->execute()) {
            $sucesso = "Usuário excluído com sucesso!";
        } else {
            $erro = "Erro ao excluir usuário: " . $conn->error;
        }
        $stmt_del->close();
    }
}

// Listagem de usuários
$usuarios = [];
$result = $conn->query("SELECT id_usuario, nome, email, telefone, tipo_usuario, criado_em FROM usuarios ORDER BY nome ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuários - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- Topo -->
  <div class="topo-pagina d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="titulo-pagina">Cadastro de Usuários</h2>
      <p class="subtitulo">Gerencie as contas administrativas e veterinárias de acesso ao sistema</p>
    </div>
    <button class="btn btn-success rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
      <i class="bi bi-person-plus-fill me-2"></i> Novo Usuário
    </button>
  </div>

  <!-- Alertas -->
  <?php if (!empty($sucesso)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: var(--border-radius-md);">
      <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($sucesso); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if (!empty($erro)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: var(--border-radius-md);">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($erro); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Busca e Filtros -->
  <div class="card card-premium mb-4">
    <div class="card-body">
      <div class="input-group input-icon-wrapper">
        <span class="input-group-text bg-transparent border-0 ps-3">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="pesquisaUsuario" class="form-control form-control-custom" placeholder="Pesquisar por nome ou e-mail..." onkeyup="filtrarUsuarios()" />
      </div>
    </div>
  </div>

  <!-- Tabela de Usuários -->
  <div class="card card-premium shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelaUsuarios">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Nome</th>
              <th>E-mail</th>
              <th>Telefone</th>
              <th>Perfil</th>
              <th class="text-end pe-4">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($usuarios) === 0): ?>
              <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                  <i class="bi bi-people-fill fs-1 d-block mb-3"></i>
                  Nenhum usuário cadastrado.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($usuarios as $u): ?>
                <tr class="usuario-linha" data-nome="<?php echo strtolower(htmlspecialchars($u['nome'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($u['email'])); ?>">
                  <td class="ps-4 fw-semibold text-dark">
                    <?php echo htmlspecialchars($u['nome']); ?>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($u['email']); ?>
                  </td>
                  <td>
                    <?php echo !empty($u['telefone']) ? htmlspecialchars($u['telefone']) : '<span class="text-muted small">Não cadastrado</span>'; ?>
                  </td>
                  <td>
                    <?php if ($u['tipo_usuario'] === 'admin'): ?>
                      <span class="badge-status success"><i class="bi bi-shield-fill me-1"></i> Admin</span>
                    <?php else: ?>
                      <span class="badge-status warning"><i class="bi bi-activity me-1"></i> Veterinário</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-4">
                    <button class="btn btn-outline-success btn-sm rounded-pill px-3 me-2" 
                            onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                      <i class="bi bi-pencil-fill me-1"></i> Editar
                    </button>
                    <?php if (intval($u['id_usuario']) !== intval($_SESSION['usuario_id'])): ?>
                      <a href="Usuarios.php?excluir=<?php echo $u['id_usuario']; ?>" 
                         class="btn btn-outline-danger btn-sm rounded-pill px-3"
                         onclick="return confirm('Tem certeza que deseja excluir o usuário <?php echo htmlspecialchars($u['nome']); ?>?')">
                        <i class="bi bi-trash-fill me-1"></i> Excluir
                      </a>
                    <?php else: ?>
                      <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Você não pode excluir sua própria conta">
                        <i class="bi bi-person-fill-lock"></i> Atual
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MODAL NOVO USUÁRIO -->
  <div class="modal fade" id="modalNovoUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <form action="Usuarios.php" method="POST">
          <input type="hidden" name="acao" value="cadastrar">
          <div class="modal-header bg-success text-white rounded-top-4">
            <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Novo Usuário</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nome Completo <span class="text-danger">*</span></label>
              <input type="text" name="nome" class="form-control" placeholder="Ex: Dr. João Pedro" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">E-mail <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="Ex: joao@sistema.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Senha Inicial <span class="text-danger">*</span></label>
              <input type="password" name="senha" class="form-control" placeholder="Senha de acesso" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Telefone</label>
              <input type="text" name="telefone" class="form-control" placeholder="Ex: (81) 98888-8888">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Perfil de Acesso <span class="text-danger">*</span></label>
              <select name="tipo_usuario" class="form-select" required>
                <option value="veterinario" selected>Veterinário (Restrito)</option>
                <option value="admin">Administrador Geral</option>
              </select>
            </div>
          </div>
          <div class="modal-footer p-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success rounded-pill px-4">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL EDITAR USUÁRIO -->
  <div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4">
        <form action="Usuarios.php" method="POST">
          <input type="hidden" name="acao" value="editar">
          <input type="hidden" name="id_usuario" id="editId">
          <div class="modal-header bg-success text-white rounded-top-4">
            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Usuário</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nome Completo <span class="text-danger">*</span></label>
              <input type="text" name="nome" id="editNome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">E-mail <span class="text-danger">*</span></label>
              <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Nova Senha <span class="text-muted small">(deixe em branco para não alterar)</span></label>
              <input type="password" name="senha" class="form-control" placeholder="Nova senha">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Telefone</label>
              <input type="text" name="telefone" id="editTelefone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Perfil de Acesso <span class="text-danger">*</span></label>
              <select name="tipo_usuario" id="editTipo" class="form-select" required>
                <option value="veterinario">Veterinário (Restrito)</option>
                <option value="admin">Administrador Geral</option>
              </select>
            </div>
          </div>
          <div class="modal-footer p-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success rounded-pill px-4">Salvar Alterações</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Script session meta values to feed the client menu -->
  <script>
    window.USER_SESSION = {
      id: <?php echo json_encode($_SESSION['usuario_id']); ?>,
      nome: <?php echo json_encode($_SESSION['usuario_nome']); ?>,
      email: <?php echo json_encode($_SESSION['usuario_email']); ?>,
      tipo: <?php echo json_encode($_SESSION['usuario_tipo']); ?>
    };
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <!-- JS Logic -->
  <script>
    function filtrarUsuarios() {
      const q = document.getElementById("pesquisaUsuario").value.toLowerCase().trim();
      const linhas = document.querySelectorAll(".usuario-linha");
      linhas.forEach(linha => {
        const nome = linha.getAttribute("data-nome");
        const email = linha.getAttribute("data-email");
        if (nome.includes(q) || email.includes(q)) {
          linha.style.display = "";
        } else {
          linha.style.display = "none";
        }
      });
    }

    function abrirModalEditar(usuario) {
      document.getElementById("editId").value = usuario.id_usuario;
      document.getElementById("editNome").value = usuario.nome;
      document.getElementById("editEmail").value = usuario.email;
      document.getElementById("editTelefone").value = usuario.telefone || '';
      document.getElementById("editTipo").value = usuario.tipo_usuario;
      
      const modal = new bootstrap.Modal(document.getElementById("modalEditarUsuario"));
      modal.show();
    }
  </script>
</body>

</html>
