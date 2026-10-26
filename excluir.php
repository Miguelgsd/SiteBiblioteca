<?php
require 'db.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}
 
csrf_check();
 
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $pdo->prepare("DELETE FROM livros WHERE id = :id")->execute([':id' => $id]);
}
 
header('Location: index.php?msg=excluido');
exit;
