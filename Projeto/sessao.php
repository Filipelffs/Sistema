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
 * Carrega a preferência de modo escuro do banco e armazena na sessão.
 * Chamado uma vez por requisição para evitar queries repetidas.
 */
if (!isset($_SESSION['tema_escuro'])) {
    // Conexão temporária apenas para buscar a preferência
    $__host = "localhost"; $__user = "root"; $__pass = ""; $__db = "vacinacao_animal";
    $__c = new mysqli($__host, $__user, $__pass, $__db);
    if (!$__c->connect_error) {
        $__uid = (int)$_SESSION['usuario_id'];
        $__r = $__c->query("SELECT tema_escuro FROM configuracoes WHERE id_usuario = $__uid");
        if ($__r && $__r->num_rows > 0) {
            $__row = $__r->fetch_assoc();
            $_SESSION['tema_escuro'] = (bool)$__row['tema_escuro'];
        } else {
            $_SESSION['tema_escuro'] = false;
        }
        $__c->close();
    } else {
        $_SESSION['tema_escuro'] = false;
    }
    unset($__host, $__user, $__pass, $__db, $__c, $__uid, $__r, $__row);
}

$TEMA_ESCURO = $_SESSION['tema_escuro'] ?? false;

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

