<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói o cookie de sessão se houver
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para o login
header("Location: Login.php");
exit();
?>
