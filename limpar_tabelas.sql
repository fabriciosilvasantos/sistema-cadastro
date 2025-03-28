-- Desabilitar verificação de chave estrangeira temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpar tabelas
TRUNCATE TABLE nivel_permissao;
TRUNCATE TABLE permissoes;
TRUNCATE TABLE niveis_acesso;

-- Reabilitar verificação de chave estrangeira
SET FOREIGN_KEY_CHECKS = 1; 