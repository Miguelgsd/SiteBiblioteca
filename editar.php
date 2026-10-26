<?php
require 'db.php';
 
$id = (int)($_GET['id'] ?? 0);
 
$stmt = $pdo->prepare(
    "SELECT l.id, l.titulo, l.paginas,
            GROUP_CONCAT(a.nome ORDER BY a.nome SEPARATOR ', ') AS autores
     FROM livros l
     LEFT JOIN livros_autores la ON la.livro_id = l.id
     LEFT JOIN autores a ON a.id = la.autor_id
     WHERE l.id = :id
     GROUP BY l.id"
);
$stmt->execute([':id' => $id]);
$livro = $stmt->fetch();
 
if (!$livro) {
    http_response_code(404);
    exit('Livro não encontrado.');
}
 
$erro        = '';
$titulo      = $livro['titulo'];
$paginasTxt  = (string)($livro['paginas'] ?? '');
$autoresTxt  = $livro['autores'] ?? '';
 
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
 
            $pdo->prepare("UPDATE livros SET titulo = :t, paginas = :p WHERE id = :id")
                ->execute([':t' => $titulo, ':p' => $paginas, ':id' => $id]);
 
            $pdo->prepare("DELETE FROM livros_autores WHERE livro_id = :id")
                ->execute([':id' => $id]);
            vincularAutores($pdo, $id, $autores);
 
            $pdo->commit();
            header('Location: index.php?msg=editado');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $erro = 'Não foi possível atualizar o livro.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar livro</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Editar livro</h1>
 
  <?php if ($erro): ?><p class="erro"><?= h($erro) ?></p><?php endif; ?>
 
  <form method="post" action="editar.php?id=<?= $id ?>" class="formulario">
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
 
    <button type="submit" style="width: 200px">Salvar alterações</button>
    <button href="index.php">Cancelar</button>
  </form>
</body>
</html>
