-- Remover a tabela usuarios se existir
DROP TABLE IF EXISTS usuarios;

-- Criar a tabela usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    nivel_acesso_id INT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_acesso_id) REFERENCES niveis_acesso(id)
);

-- Inserir usuário administrador
INSERT INTO usuarios (nome, email, senha, telefone, nivel_acesso_id, status) 
VALUES (
    'Administrador',
    'admin@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '(11) 99999-9999',
    1,
    'ativo'
); 