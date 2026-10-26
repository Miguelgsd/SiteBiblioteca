-- MariaDB/MySQL. Rode uma vez: mysql -u root -p BibliotecaVirtual < banco.sql
DROP TABLE IF EXISTS livros_autores, autores, livros;

CREATE TABLE livros (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  titulo  VARCHAR(255) NOT NULL,
  paginas INT
);

CREATE TABLE autores (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE livros_autores (
  livro_id INT NOT NULL,
  autor_id INT NOT NULL,
  PRIMARY KEY (livro_id, autor_id),
  FOREIGN KEY (livro_id) REFERENCES livros(id)  ON DELETE CASCADE,
  FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE CASCADE
);