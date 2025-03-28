-- Remover usuário administrador existente
DELETE FROM usuarios WHERE email = 'admin@sistema.com';

-- Inserir novo usuário administrador
INSERT INTO usuarios (nome, email, senha, telefone, nivel_acesso_id, status) 
VALUES (
    'Administrador',
    'admin@sistema.com',
    '$2y$10$YourNewHashHere', -- senha: admin123
    '(11) 99999-9999',
    1, -- ID do nível Administrador
    'ativo'
); 