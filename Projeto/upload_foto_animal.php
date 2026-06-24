<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

$id_animal = isset($_POST['id_animal']) ? intval($_POST['id_animal']) : 0;
if ($id_animal <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do animal inválido.']);
    exit;
}

$foto_url = null;

// 1. Check if file is uploaded
if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['foto_animal'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo. Código: ' . $file['error']]);
        exit;
    }
    
    // Validate size (2MB limit)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'O arquivo é muito grande. Limite máximo: 2MB.']);
        exit;
    }
    
    // Validate extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileInfo = pathinfo($file['name']);
    $extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';
    
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Extensão de arquivo não permitida. Apenas JPG, PNG, GIF e WEBP.']);
        exit;
    }
    
    // Validate MIME type (magic bytes)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo inválido. O arquivo deve ser uma imagem válida.']);
        exit;
    }
    
    // Generate secure filename
    $uniqueName = md5(uniqid(rand(), true)) . '.' . $extension;
    $uploadDir = 'uploads/animais/';
    
    // Ensure upload dir exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $destPath = $uploadDir . $uniqueName;
    
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $foto_url = $destPath;
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao mover arquivo para o diretório de destino.']);
        exit;
    }
} 
// 2. Check if URL is provided
elseif (isset($_POST['foto_url']) && !empty(trim($_POST['foto_url']))) {
    $url = trim($_POST['foto_url']);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'URL de imagem inválida.']);
        exit;
    }
    
    $foto_url = $url;
}

if ($foto_url !== null) {
    // Save to database using prepared statements
    $stmt = $conn->prepare("UPDATE animais SET foto_animal = ? WHERE id_animal = ?");
    $stmt->bind_param("si", $foto_url, $id_animal);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Foto do animal atualizada com sucesso!', 'foto_url' => $foto_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar caminho no banco de dados.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo ou URL fornecido.']);
}
?>
