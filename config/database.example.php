<?php
// Configurações de conexão com o banco de dados
$host = 'localhost';      // Host do banco de dados
$dbname = 'cadastro';     // Nome do banco de dados
$username = 'root';       // Usuário do banco de dados
$password = '';          // Senha do banco de dados

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]
    );
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
} 