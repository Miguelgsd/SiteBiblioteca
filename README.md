# Biblioteca Virtual

Site para cadastro e consulta de livros e autores, feito em PHP puro com MariaDB/MySQL. Implementa um CRUD completo: cadastrar, consultar, atualizar e excluir livros.

## Funcionalidades

- Listagem de livros com título, autores e número de páginas
- Consulta por título ou por autor (busca parcial, sem diferenciar maiúsculas de minúsculas)
- Cadastro de livros com um ou mais autores (separados por vírgula); autores novos são criados automaticamente
- Edição e exclusão de livros
- Relação muitos-para-muitos entre livros e autores

## Tecnologias

- PHP 8 com PDO
- MariaDB ou MySQL
- HTML e CSS (sem frameworks)

## Requisitos

- PHP 8.0 ou superior, com a extensão `pdo_mysql` habilitada
- MariaDB ou MySQL

## Instalação

1. Clone o repositório:

   ```bash
   git clone https://github.com/<seu-usuario>/SiteBiblioteca.git
   cd SiteBiblioteca
   ```

2. Crie o banco de dados e as tabelas:

   ```bash
   sudo mysql -e "CREATE DATABASE BibliotecaVirtual CHARACTER SET utf8mb4;"
   sudo mysql BibliotecaVirtual < banco.sql
   ```

3. Crie um usuário do banco só para a aplicação:

   ```sql
   CREATE USER 'biblioteca'@'localhost' IDENTIFIED BY 'sua_senha';
   GRANT SELECT, INSERT, UPDATE, DELETE ON BibliotecaVirtual.* TO 'biblioteca'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. Crie o arquivo de configuração a partir do modelo e preencha com seus dados:

   ```bash
   cp config.example.php config.php
   ```

   O `config.php` está no `.gitignore` e não deve ser enviado ao repositório.

5. Inicie o servidor embutido do PHP:

   ```bash
   php -S localhost:8000
   ```

6. Abra `http://localhost:8000` no navegador.

## Estrutura

```
├── index.php            Página principal: busca e tabela de livros
├── cadastrar.php        Formulário e lógica de cadastro
├── editar.php           Formulário e lógica de edição
├── excluir.php          Exclusão (apenas via POST)
├── db.php               Conexão PDO e funções auxiliares
├── config.example.php   Modelo de configuração (copie para config.php)
├── style.css            Estilos
└── banco.sql            Criação das tabelas
```

## Banco de dados

Três tabelas:

- `livros`: id, título e número de páginas
- `autores`: id e nome (único)
- `livros_autores`: relaciona livros e autores; ao excluir um livro, os vínculos dele são removidos junto (`ON DELETE CASCADE`)

## Segurança

- Consultas com prepared statements (proteção contra SQL injection)
- Saída escapada com `htmlspecialchars` (proteção contra XSS)
- Token CSRF nos formulários que alteram dados
- Exclusão apenas por requisição POST
- Credenciais fora do código, em arquivo ignorado pelo Git

## Limitações conhecidas

- Não há login: qualquer pessoa com acesso ao site pode cadastrar, editar e excluir. Não publique em um servidor aberto sem adicionar autenticação.
- Autores que ficam sem nenhum livro continuam na tabela `autores`.
