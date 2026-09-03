# CATÁLOGO — Livros, Filmes e Jogos

Projeto acadêmico simples e funcional de catálogo/comunidade onde o usuário cria conta, faz login e cadastra seus livros, filmes e jogos favoritos.

## Objetivo

Demonstrar na prática:

- Front-end + Back-end + MySQL
- PHP 8.3+ com **POO** e **MVC**
- **PDO** com **prepared statements**
- Autenticação com hash de senha
- Fluxo **POST** (cadastro) e **GET** (listagem/filtros/busca)
- Segurança (XSS, SQL Injection, CSRF, validação de upload)

O catálogo NÃO é uma rede social — são poucas funcionalidades, todas funcionando corretamente.

## Tecnologias

- PHP 8.3+, MySQL, Composer
- HTML5, CSS3, JavaScript (mínimo)
- Bootstrap 5 + CSS próprio (`templates/css/global.css`)
- PDO, sessões, `password_hash` / `password_verify`

## Estrutura (inspirada no FitCalc)

```
Catalogo/
├── Config/configuration.php   # constantes DB_HOST, DB_NAME, etc.
├── Controller/
│   ├── UsuarioController.php
│   └── ItemController.php
├── Model/
│   ├── Conexao.php            # PDO singleton
│   ├── Usuario.php
│   └── Item.php
├── View/
│   ├── login.php
│   ├── register.php
│   ├── catalog.php            # listagem com GET + filtros + busca
│   ├── item.php               # detalhes do item
│   ├── create.php             # cadastro via POST
│   ├── edit.php               # edição (só dono)
│   ├── meus-itens.php         # itens do usuário logado
│   └── logout.php
├── templates/css/global.css
├── storage/uploads/capas/     # capas enviadas (ignorado no git)
├── database/catalogo.sql
├── index.php                  # página inicial (hero + recentes + busca)
├── composer.json
└── README.md
```

## Requisitos Atendidos

- [x] Interface de cadastro via **POST**
- [x] Listagem via **GET** (filtros `?tipo=` e busca `?busca=`)
- [x] Cadastro e login de usuários
- [x] Hash de senha (`PASSWORD_DEFAULT`)
- [x] PDO + prepared statements
- [x] POO + MVC
- [x] MySQL com relacionamento `usuarios 1—N itens`
- [x] CRUD completo com controle de dono (só edita/exclui o que cadastrou)
- [x] Upload de imagem validado (mime, tamanho, getimagesize)
- [x] Proteção XSS (`htmlspecialchars`) e CSRF (token)
- [x] Responsivo (Bootstrap grid)

## Instalação

### 1. Clonar / copiar o projeto

```bash
git clone <url-do-repo>
cd Catalogo
```

### 2. Dependências

```bash
composer install
```

> Gera `vendor/autoload.php` (PSR-4 para `Controller\` e `Model\`).

### 3. Banco de dados

Crie o banco e importe o script:

```bash
mysql -u root -p < database/catalogo.sql
```

Ou no phpMyAdmin: crie o banco `catalogo` e importe `database/catalogo.sql`.

Ajuste as credenciais em `Config/configuration.php` se necessário:

```php
define("DB_HOST", "localhost");
define("DB_PORT", "3306");
define("DB_NAME", "catalogo");
define("DB_USER", "root");
define("DB_PASSWORD", "");
```

### 4. Servidor local

```bash
php -S localhost:8000
```

Acesse `http://localhost:8000/index.php`.

> Se usar XAMPP/WAMP, coloque a pasta `Catalogo` dentro de `htdocs` e acesse `http://localhost/Catalogo/`.

### 5. Permissão de upload

Garanta que `storage/uploads/capas/` seja gravável:

```bash
chmod 755 storage/uploads/capas
```

## Usuários de Teste

Após importar `database/catalogo.sql`:

| Nome        | E-mail            | Senha  |
|-------------|-------------------|--------|
| Ana Silva   | ana@email.com     | 123456 |
| Lucas Souza | lucas@email.com   | 123456 |

Itens de exemplo já vêm cadastrados (livros, filmes e jogos).

## Fluxos Principais

**Cadastro**: `View/create.php` (form POST) → `ItemController::cadastrarItem()` → `Item::cadastrarItem()` (PDO) → MySQL → redirect.

**Listagem**: `catalog.php?tipo=livro&busca=senhor` (GET) → `ItemController::listarItens()` → `Item::listarItens()` (PDO) → View com cards.

## Segurança

- Senhas com `password_hash` / `password_verify`
- Prepared statements em todas as queries
- `htmlspecialchars` em toda saída
- Validação de tipo, ano, nota e imagem
- Token CSRF em create/edit/excluir
- Checagem de dono antes de editar/excluir

## Git

```bash
git init
git add .
git commit -m "feat: catálogo inicial"
git remote add origin <url>
git push -u origin main
```

`.gitignore` já ignora `vendor/`, `storage/uploads/capas/*` e `.env`.

## Licença

Uso acadêmico.

## Banco de Dados 
--  CATÁLOGO DE LIVROS, FILMES E JOGOS
--  Banco: catalogo

CREATE DATABASE IF NOT EXISTS catalogo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE catalogo;

-- Tabela: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120) NOT NULL,
    email      VARCHAR(190) NOT NULL UNIQUE,
    senha      VARCHAR(255) NOT NULL,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: itens (livros, filmes, jogos)
CREATE TABLE IF NOT EXISTS itens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    titulo      VARCHAR(190) NOT NULL,
    tipo        ENUM('livro','filme','jogo') NOT NULL,
    descricao   TEXT NOT NULL,
    ano         SMALLINT UNSIGNED NOT NULL,
    nota        DECIMAL(3,1) NOT NULL,
    imagem      VARCHAR(255) DEFAULT NULL,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_itens_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_tipo (tipo),
    INDEX idx_titulo (titulo),
    INDEX idx_usuario (usuario_id),
    INDEX idx_criado (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados de exemplo (senhas: 123456)
-- hash gerado com password_hash('123456', PASSWORD_DEFAULT)

INSERT INTO usuarios (nome, email, senha) VALUES
('Ana Silva',  'ana@email.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Lucas Souza','lucas@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO itens (usuario_id, titulo, tipo, descricao, ano, nota, imagem) VALUES
(1, 'Dom Casmurro', 'livro', 'Clássico de Machado de Assis sobre ciúme e memória. Leitura obrigatória da literatura brasileira.', 1899, 9.2, NULL),
(1, 'O Senhor dos Anéis: A Sociedade do Anel', 'filme', 'A jornada de Frodo para destruir o Um Anel. Épico de fantasia dirigido por Peter Jackson.', 2001, 9.0, NULL),
(1, 'The Legend of Zelda: Breath of the Wild', 'jogo', 'Aventura em mundo aberto da Nintendo que redefiniu o gênero.', 2017, 9.8, NULL),
(2, '1984', 'livro', 'Distopia de George Orwell sobre totalitarismo e vigilância.', 1949, 9.1, NULL),
(2, 'Interestelar', 'filme', 'Ficção científica de Christopher Nolan sobre viagem espacial e amor além do tempo.', 2014, 8.9, NULL),
(2, 'God of War (2018)', 'jogo', 'Kratos e Atreus em uma jornada emocionante pela mitologia nórdica.', 2018, 9.5, NULL)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);
