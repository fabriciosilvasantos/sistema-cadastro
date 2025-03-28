<?php
function validarFormulario($dados) {
    $erros = [];
    
    if (empty($dados['nome'])) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido";
    }
    
    if (strlen($dados['senha']) < 6) {
        $erros[] = "A senha deve ter no mínimo 6 caracteres";
    }
    
    if ($dados['senha'] !== $dados['confirmar_senha']) {
        $erros[] = "As senhas não conferem";
    }
    
    if (!preg_match("/^\(\d{2}\) \d{5}-\d{4}$/", $dados['telefone'])) {
        $erros[] = "Telefone inválido. Use o formato: (00) 00000-0000";
    }

    return $erros;
}

function processarFormulario($pdo, $dados) {
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $senha = $dados['senha'];
    $telefone = trim($dados['telefone']);

    try {
        // Verificar se o email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'sucesso' => false,
                'mensagem' => "Este email já está cadastrado"
            ];
        }

        // Inserir novo usuário
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, telefone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha_hash, $telefone]);
        
        return [
            'sucesso' => true,
            'mensagem' => "Usuário cadastrado com sucesso!"
        ];
    } catch(PDOException $e) {
        return [
            'sucesso' => false,
            'mensagem' => "Erro ao cadastrar: " . $e->getMessage()
        ];
    }
}
?> 