-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS cadastro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cadastro;

-- Tabela de níveis de acesso
CREATE TABLE niveis_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    nivel_acesso_id INT NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_acesso_id) REFERENCES niveis_acesso(id)
);

-- Tabela de permissões
CREATE TABLE permissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de relacionamento entre níveis de acesso e permissões
CREATE TABLE nivel_permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel_id INT NOT NULL,
    permissao_id INT NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_id) REFERENCES niveis_acesso(id),
    FOREIGN KEY (permissao_id) REFERENCES permissoes(id),
    UNIQUE KEY unique_nivel_permissao (nivel_id, permissao_id)
);

-- Tabela para recuperação de senha
CREATE TABLE recuperacao_senha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    data_expiracao DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir níveis de acesso
INSERT INTO niveis_acesso (nome) VALUES 
('Administrador'),
('Gerente'),
('Usuário');

-- Inserir permissões
INSERT INTO permissoes (nome) VALUES 
('visualizar_dashboard'),
('cadastrar_usuarios'),
('editar_usuarios'),
('excluir_usuarios'),
('gerenciar_niveis'),
('visualizar_relatorios');

-- Associar permissões aos níveis de acesso
-- Administrador (todas as permissões)
INSERT INTO nivel_permissao (nivel_id, permissao_id) 
SELECT 1, id FROM permissoes;

-- Gerente (visualizar dashboard, cadastrar/editar usuários, ver relatórios)
INSERT INTO nivel_permissao (nivel_id, permissao_id) 
SELECT 2, id FROM permissoes 
WHERE nome IN ('visualizar_dashboard', 'cadastrar_usuarios', 'editar_usuarios', 'visualizar_relatorios');

-- Usuário (apenas visualizar dashboard)
INSERT INTO nivel_permissao (nivel_id, permissao_id) 
SELECT 3, id FROM permissoes 
WHERE nome = 'visualizar_dashboard';

-- Inserir usuário administrador padrão
-- Senha: admin123
INSERT INTO usuarios (nome, email, senha, nivel_acesso_id) VALUES 
('Administrador', 'admin@exemplo.com', '$2y$10$8VQmxGYrAXvJqr1VpQYzb.TgpwQlTtQ8CYqZ6RP/HrD7YT3ZxYXGi', 1); 