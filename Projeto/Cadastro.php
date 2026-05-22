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
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $telefone = trim($_POST['telefone']);

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Verifica se e-mail já existe
        $stmt_check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $erro = "Já existe uma conta associada a este e-mail.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $tipo_usuario = "veterinario"; // Perfil padrão para novos cadastros autônomos
            
            $stmt_ins = $conn->prepare("INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("sssss", $nome, $email, $senha_hash, $telefone, $tipo_usuario);
            
            if ($stmt_ins->execute()) {
                $sucesso = "Cadastro realizado com sucesso! Faça login para continuar.";
            } else {
                $erro = "Erro ao registrar: " . $conn->error;
            }
            $stmt_ins->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Vacinação Animal</title>
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
            <svg viewBox="0 0 100 100" width="80" height="80" class="mb-2">
                <rect x="38" y="15" width="24" height="70" rx="8" fill="#1FAF7A" />
                <rect x="15" y="38" width="70" height="24" rx="8" fill="#1FAF7A" />
                <circle cx="50" cy="50" r="10" fill="white" />
                <path d="M46,50 L54,50 M50,46 L50,54" stroke="#1FAF7A" stroke-width="3" stroke-linecap="round" />
            </svg>
            <h1 class="m-0">VACINAÇÃO ANIMAL</h1>
            <p class="text-muted small">REGISTRE-SE PARA COMEÇAR</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show text-start" role="alert" style="border-radius: var(--border-radius-md); font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($erro); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success alert-dismissible fade show text-start" role="alert" style="border-radius: var(--border-radius-md); font-size: 0.9rem;">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($sucesso); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="Cadastro.php" method="POST">
            <!-- Nome -->
            <div class="form-group-custom">
                <label>Nome Completo <span class="text-danger">*</span></label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-person-fill"></i>
                    <input type="text" name="nome" class="form-control-custom" placeholder="Digite seu nome completo" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                </div>
            </div>

            <!-- Email -->
            <div class="form-group-custom">
                <label>E-mail <span class="text-danger">*</span></label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="email" class="form-control-custom" placeholder="Digite seu e-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <!-- Telefone -->
            <div class="form-group-custom">
                <label>Telefone</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-telephone-fill"></i>
                    <input type="tel" name="telefone" class="form-control-custom" placeholder="(00) 00000-0000" value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                </div>
            </div>

            <!-- Senha -->
            <div class="form-group-custom">
                <label>Senha <span class="text-danger">*</span></label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="senha" class="form-control-custom" placeholder="Crie uma senha" required>
                </div>
            </div>

            <!-- Termos Checkbox -->
            <div class="mb-4 text-start">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="aceitoTermos" required>
                    <label class="form-check-label text-muted small" for="aceitoTermos">
                        Aceito os termos e a política de privacidade.
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-primary-custom py-3 fs-5">
                Cadastrar-se
            </button>

            <div class="footer-text mt-4">
                Já tem uma conta?
                <a href="Login.php">Fazer Login</a>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
