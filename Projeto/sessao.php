<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não estiver logado, redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

/**
 * Verifica se o usuário logado possui a permissão requerida.
 * @param string|array $perfisPermitidos Perfil ou lista de perfis permitidos (ex: 'admin' ou ['admin', 'veterinario'])
 */
function checarAcesso($perfisPermitidos) {
    if (!isset($_SESSION['usuario_tipo'])) {
        header("Location: Login.php");
        exit();
    }

    $tipo = $_SESSION['usuario_tipo'];
    
    if (is_array($perfisPermitidos)) {
        if (!in_array($tipo, $perfisPermitidos)) {
            http_response_code(403);
            include_once "AcessoNegado.php";
            exit();
        }
    } else {
        if ($tipo !== $perfisPermitidos) {
            http_response_code(403);
            include_once "AcessoNegado.php";
            exit();
        }
    }
}
?>
