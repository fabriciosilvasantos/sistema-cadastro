-- Inserir níveis de acesso
INSERT INTO niveis_acesso (nome, descricao) VALUES
('Administrador', 'Acesso total ao sistema'),
('Gerente', 'Acesso a relatórios e gerenciamento de usuários'),
('Usuário', 'Acesso básico ao sistema');

-- Inserir permissões
INSERT INTO permissoes (nome, descricao) VALUES
('visualizar_dashboard', 'Visualizar o dashboard'),
('cadastrar_usuarios', 'Cadastrar novos usuários'),
('editar_usuarios', 'Editar usuários existentes'),
('excluir_usuarios', 'Excluir usuários'),
('gerenciar_niveis', 'Gerenciar níveis de acesso'),
('visualizar_relatorios', 'Visualizar relatórios');

-- Associar permissões aos níveis de acesso
-- Administrador (todas as permissões)
INSERT INTO nivel_permissao (nivel_id, permissao_id) VALUES
(1, 1), -- visualizar_dashboard
(1, 2), -- cadastrar_usuarios
(1, 3), -- editar_usuarios
(1, 4), -- excluir_usuarios
(1, 5), -- gerenciar_niveis
(1, 6); -- visualizar_relatorios

-- Gerente (dashboard, relatórios e gerenciamento básico de usuários)
INSERT INTO nivel_permissao (nivel_id, permissao_id) VALUES
(2, 1), -- visualizar_dashboard
(2, 2), -- cadastrar_usuarios
(2, 3), -- editar_usuarios
(2, 6); -- visualizar_relatorios

-- Usuário (apenas dashboard)
INSERT INTO nivel_permissao (nivel_id, permissao_id) VALUES
(3, 1); -- visualizar_dashboard 