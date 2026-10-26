<?php
require 'db.php';
 
$busca = trim($_GET['busca'] ?? '');
$tipo  = ($_GET['tipo'] ?? 'titulo') === 'autor' ? 'autor' : 'titulo';
 
$mensagens = [
    'cadastrado' => 'Livro cadastrado.',
    'editado'    => 'Livro atualizado.',
    'excluido'   => 'Livro excluído.',
];
$msg = $mensagens[$_GET['msg'] ?? ''] ?? '';
 
$where  = '';
$params = [];
if ($busca !== '') {
    if ($tipo === 'autor') {

        $where = "WHERE l.id IN (
                    SELECT x.livro_id FROM livros_autores x
                    JOIN autores y ON y.id = x.autor_id
                    WHERE y.nome LIKE :busca)";
    } else {
        $where = "WHERE l.titulo LIKE :busca";
    }
    $params[':busca'] = '%' . addcslashes($busca, '%_\\') . '%';
}
 
$sql = "SELECT l.id, l.titulo, l.paginas,
               GROUP_CONCAT(a.nome ORDER BY a.nome SEPARATOR ', ') AS autores
        FROM livros l
        LEFT JOIN livros_autores la ON la.livro_id = l.id
        LEFT JOIN autores a ON a.id = la.autor_id
        $where
        GROUP BY l.id
        ORDER BY l.titulo";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();
?>
<!DOCTYPE html>
 
<html>
    <head>
        <meta charset="UTF-8">
        <title>Página Inicial</title>
        <style>
            button {
                font-size: 20px;
                border-radius: 5px;
                background-color: rgb(55, 140, 238);
                width: 190px;
                height: 35px;
            }
 
            h1{
                font-size: 60px;
                text-align: center;
                font-family: 'Courier New', Courier, monospace;
                padding-bottom: 150px;
            }
 
            body{
                align-items: center;
                background-color: rgb(184, 188, 205);
            }
 
            #botoes{
                align-items: center;
                text-align: center;
            }
 
            #mainText{
                margin-top: 30px;
            }
 
            table {
                border-collapse: collapse;
                width: 50%;
                margin: 0 auto
            }
 
            th, td {
                border: 1px solid;
                padding: 8px;
                text-align: center;
                font-size: 25px;
                font-family: sans-serif;
            }
 
            th {background-color: #f0f0f0;}

 
            #botoes { margin-top: 10px; }
            #botoes input[type=text] { font-size: 20px; height: 35px; padding: 0 8px; width: 300px; }
            #aviso { text-align: center; font-family: sans-serif; font-size: 20px; margin-top: -150px; margin-bottom: 120px; color: #1b5e20; }
            button.mini { width: auto; height: auto; font-size: 16px; padding: 4px 10px; }
            button.perigo { background-color: rgb(220, 90, 90); }
            form.inline { display: inline; }
        </style>
    </head>
    <body>
 
        <div id="mainText">
            <h1>Biblioteca Virtual</h1>
 
            <?php if ($msg): ?>
                <p id="aviso"><?= h($msg) ?></p>
            <?php endif; ?>
 
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Nº Páginas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros as $l): ?>
                        <tr>
                            <td><?= h($l['titulo']) ?></td>
                            <td><?= h($l['autores'] ?? '') ?></td>
                            <td><?= h($l['paginas'] ?? '') ?></td>
                            <td>
                                <button type="button" class="mini"
                                        onclick="location.href='editar.php?id=<?= (int)$l['id'] ?>'">Atualizar</button>
                                <form method="post" action="excluir.php" class="inline"
                                      onsubmit="return confirm('Excluir este livro?');">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                    <button type="submit" class="mini perigo">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$livros): ?>
                        <tr><td colspan="4">Nenhum livro encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
 
            <div id="botoes">
                <form method="get">
                    <input type="text" name="busca" placeholder="Buscar..." value="<?= h($busca) ?>">
                    <button type="submit" name="tipo" value="titulo">Consultar livro</button>
                    <button type="submit" name="tipo" value="autor">Consultar autor</button>
                    <button type="button" onclick="location.href='cadastrar.php'">Cadastrar livro</button>
                </form>
            </div>
        </div>
    </body>
</html>
