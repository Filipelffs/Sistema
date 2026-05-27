<?php
require 'conexao.php';
$conn->query("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200'");
echo 'OK';
?>
