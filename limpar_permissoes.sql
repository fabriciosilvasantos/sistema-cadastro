-- Limpar tabelas
DELETE FROM nivel_permissao;
DELETE FROM permissoes WHERE id > 6;
DELETE FROM niveis_acesso WHERE id > 3;

-- Recriar permissões
INSERT INTO permissoes (id, nome, descricao) VALUES
(1, 'visualizar_dashboard', 'Visualizar o dashboard'),
(2, 'cadastrar_usuarios', 'Cadastrar novos usuários'),
(3, 'editar_usuarios', 'Editar usuários existentes'),
(4, 'excluir_usuarios', 'Excluir usuários'),
(5, 'gerenciar_niveis', 'Gerenciar níveis de acesso'),
(6, 'visualizar_relatorios', 'Visualizar relatórios');

-- Recriar níveis de acesso
INSERT INTO niveis_acesso (id, nome, descricao) VALUES
(1, 'Administrador', 'Acesso total ao sistema'),
(2, 'Gerente', 'Acesso a relatórios e gerenciamento de usuários'),
(3, 'Usuário', 'Acesso básico ao sistema');

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