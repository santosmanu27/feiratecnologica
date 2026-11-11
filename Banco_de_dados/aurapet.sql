CREATE DATABASE IF NOT EXISTS aurapet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aurapet;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    telefone VARCHAR(25) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    bio VARCHAR(500) NOT NULL DEFAULT '',
    foto VARCHAR(100) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cartoes_teste (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    bandeira VARCHAR(20) NOT NULL,
    ultimos4 CHAR(4) NOT NULL,
    validade CHAR(7) NOT NULL,
    INDEX idx_cartoes_usuario (usuario_id),
    CONSTRAINT fk_cartao_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos_simulados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    itens_json LONGTEXT NOT NULL,
    endereco_json TEXT NOT NULL,
    metodo VARCHAR(20) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pedidos_usuario (usuario_id),
    CONSTRAINT fk_simulacao_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
