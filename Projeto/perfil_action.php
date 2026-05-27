<?php
require_once "../Banco/conexao.php";
require_once "sessao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $_SESSION['usuario_id'];
$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

if (isset($input['foto'])) {
    $foto = $conn->real_escape_string($input['foto']);
    $sql = "UPDATE usuarios SET foto = '$foto' WHERE id_usuario = $id";
    $conn->query($sql);
    $_SESSION['usuario_foto'] = $foto;
}

if ($isAdmin && isset($input['nome']) && isset($input['email'])) {
    $nome = $conn->real_escape_string($input['nome']);
    $email = $conn->real_escape_string($input['email']);
    $sql = "UPDATE usuarios SET nome = '$nome', email = '$email' WHERE id_usuario = $id";
    if ($conn->query($sql)) {
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_email'] = $email;
    }
}

echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
?>
