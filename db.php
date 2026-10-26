<?php

session_start();
$config = require __DIR__ . '/config.php';
 
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
 

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
 

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
 
function csrf_check() {
    $enviado = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(403);
        exit('Requisição inválida.');
    }
}
 

function separarAutores($texto) {
    $lista = array_map('trim', explode(',', $texto));
    $lista = array_filter($lista, fn($n) => $n !== '');
    return array_values(array_unique($lista));
}
 

function vincularAutores(PDO $pdo, int $livroId, array $autores) {
   
    $autor = $pdo->prepare(
        "INSERT INTO autores (nome) VALUES (:n)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
    );
    $vinculo = $pdo->prepare(
        "INSERT INTO livros_autores (livro_id, autor_id) VALUES (:l, :a)
         ON DUPLICATE KEY UPDATE livro_id = livro_id"
    );
    foreach ($autores as $nome) {
        $autor->execute([':n' => $nome]);
        $vinculo->execute([':l' => $livroId, ':a' => (int)$pdo->lastInsertId()]);
    }
}
 
function validarPaginas($texto) {
    $texto = trim($texto);
    if ($texto === '') return null;
    return filter_var($texto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
}
