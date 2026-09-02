-- ============================================
--  CATÁLOGO DE LIVROS, FILMES E JOGOS
--  Banco: catalogo
-- ============================================

CREATE DATABASE IF NOT EXISTS catalogo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE catalogo;

-- --------------------------------------------
-- Tabela: usuarios
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120) NOT NULL,
    email      VARCHAR(190) NOT NULL UNIQUE,
    senha      VARCHAR(255) NOT NULL,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabela: itens (livros, filmes, jogos)
-- --------------------------------------------
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

-- --------------------------------------------
-- Dados de exemplo (senhas: 123456)
-- hash gerado com password_hash('123456', PASSWORD_DEFAULT)
-- --------------------------------------------
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
