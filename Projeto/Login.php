<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona para a dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: Dashboard.php");
    exit();
}

require_once "../Banco/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT id_usuario, nome, email, senha, telefone, tipo_usuario, foto FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($senha, $user['senha'])) {
                // Iniciar sessão com sucesso
                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_tipo'] = $user['tipo_usuario'];
                $_SESSION['usuario_telefone'] = $user['telefone'];
                $_SESSION['usuario_foto'] = $user['foto'];

                // Set cookies for frontend JS (menu.js)
                setcookie('usuario_nome', $user['nome'], time() + (86400 * 30), "/");
                setcookie('usuario_tipo', $user['tipo_usuario'], time() + (86400 * 30), "/");
                setcookie('usuario_foto', $user['foto'] ?? '', time() + (86400 * 30), "/");

                header("Location: Dashboard.php");
                exit();
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vacinação Animal</title>
    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="style.css" />
</head>

<body class="login-body">
    <div class="login-container">
        <!-- LOGO -->
        <div class="logo mb-4">
            <img src="img/logo.png" width="200px" height="200px" class="mb-2">

            <h1 class="m-0">VACINAÇÃO ANIMAL</h1>
            <p class="text-muted small">CONECTANDO A SAÚDE DO SEU ANIMAL</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show text-start" role="alert" style="border-radius: var(--border-radius-md); font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($erro); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="Login.php" method="POST">
            <!-- Email -->
            <div class="form-group-custom">
                <label>E-mail do Usuário</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-person-fill"></i>
                    <input type="email" name="email" class="form-control-custom" placeholder="Digite seu email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <!-- Password -->
            <div class="form-group-custom">
                <label>Senha</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="senha" class="form-control-custom" placeholder="Digite sua senha" required>
                </div>
            </div>

            <div class="links-login">
                <a href="#">Esqueci minha senha</a>
            </div>

            <button type="submit" class="btn-primary-custom py-3 fs-5">
                Entrar
            </button>

            <!-- Social Login -->
            <div class="social-login">
                <div class="social-title">ou continue com</div>
                <div class="social-icons">
                    <button type="button" class="social-btn"><i class="bi bi-google text-danger"></i></button>
                    <button type="button" class="social-btn"><i class="bi bi-facebook text-primary"></i></button>
                    <button type="button" class="social-btn"><i class="bi bi-apple text-dark"></i></button>
                </div>
            </div>

            <div class="footer-text">
                Não tem uma conta?
                <a href="Cadastro.php">Cadastre-se</a>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
