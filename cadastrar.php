<?php
require 'db.php';
 
$erro = '';
$titulo = $paginasTxt = $autoresTxt = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
 
    $titulo      = trim($_POST['titulo'] ?? '');
    $paginasTxt  = trim($_POST['paginas'] ?? '');
    $autoresTxt  = trim($_POST['autores'] ?? '');
    $autores     = separarAutores($autoresTxt);
    $paginas     = validarPaginas($paginasTxt);
 
    if ($titulo === '') {
        $erro = 'Informe o título.';
    } elseif ($paginas === false) {
        $erro = 'Número de páginas inválido.';
    } elseif (!$autores) {
        $erro = 'Informe ao menos um autor.';
    } else {
        try {
            $pdo->beginTransaction();
 
            $pdo->prepare("INSERT INTO livros (titulo, paginas) VALUES (:t, :p)")
                ->execute([':t' => $titulo, ':p' => $paginas]);
            $livroId = (int)$pdo->lastInsertId();
 
            vincularAutores($pdo, $livroId, $autores);
 
            $pdo->commit();
            header('Location: index.php?msg=cadastrado');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $erro = 'Não foi possível salvar o livro.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastrar livro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Cadastrar livro</h1>
 
  <?php if ($erro): ?><p class="erro"><?= h($erro) ?></p><?php endif; ?>
 
  <form method="post" class="formulario">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
 
    <label>Título
      <input type="text" name="titulo" value="<?= h($titulo) ?>" required>
    </label><br>
 
    <label>Autor(es) <small>(separe por vírgula)</small>
      <input type="text" name="autores" value="<?= h($autoresTxt) ?>" required>
    </label><br>
 
    <label>Nº de páginas
      <input type="number" name="paginas" min="1" max="100000" value="<?= h($paginasTxt) ?>">
    </label><br>
 
    <button type="submit">Salvar</button>
    <button href="index.php">Cancelar</button>
  </form>
</body>
</html>
