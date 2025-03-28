<?php
require_once 'config/database.php';

// Remover usuário existente
$pdo->exec("DELETE FROM usuarios WHERE email = 'admin@sistema.com'");

// Criar novo usuário
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO usuarios (nome, email, senha, telefone, nivel_acesso_id, status) 
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    'Administrador',
    'admin@sistema.com',
    $hash,
    '(11) 99999-9999',
    1,
    'ativo'
]);

echo "Usuário administrador criado com sucesso!\n";
echo "Email: admin@sistema.com\n";
echo "Senha: admin123\n";
echo "Hash gerado: " . $hash . "\n"; 