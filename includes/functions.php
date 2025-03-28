<?php
function validarFormulario($dados) {
    $erros = [];
    
    if (empty($dados['nome']) || strlen($dados['nome']) < 3) {
        $erros[] = "O nome deve ter pelo menos 3 caracteres";
    }
    
    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido";
    }
    
    if (empty($dados['senha']) || strlen($dados['senha']) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres";
    }
    
    if ($dados['senha'] !== $dados['confirmar_senha']) {
        $erros[] = "As senhas não conferem";
    }
    
    if (empty($dados['telefone']) || !preg_match("/^\(\d{2}\) \d{5}-\d{4}$/", $dados['telefone'])) {
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

/**
 * Verifica se o usuário tem uma determinada permissão
 * @param string $permissao Nome da permissão a ser verificada
 * @return bool True se o usuário tem a permissão, false caso contrário
 */
function temPermissao($permissao) {
    global $pdo;
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.nome 
            FROM permissoes p
            JOIN nivel_permissao np ON p.id = np.permissao_id
            JOIN usuarios u ON u.nivel_acesso_id = np.nivel_id
            WHERE u.id = ? AND u.status = 'ativo'
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $permissoes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return in_array($permissao, $permissoes);
    } catch(PDOException $e) {
        error_log("Erro ao verificar permissão: " . $e->getMessage());
        return false;
    }
}

// Função para obter o nível de acesso do usuário
function getNivelAcesso($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT na.nome, na.descricao
            FROM usuarios u
            JOIN niveis_acesso na ON u.nivel_acesso_id = na.id
            WHERE u.id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao obter nível de acesso: " . $e->getMessage());
        return null;
    }
}

// Função para verificar se o usuário está logado
function verificarLogin() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['usuario_id'];
}

// Função para verificar se o usuário tem acesso à página
function verificarAcesso($pdo, $usuario_id, $permissao_necessaria) {
    if (!temPermissao($permissao_necessaria)) {
        header('Location: index.php');
        exit;
    }
}

// Função para obter todas as permissões do usuário
function getPermissoesUsuario($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.nome, p.descricao
            FROM usuarios u
            JOIN nivel_permissao np ON u.nivel_acesso_id = np.nivel_id
            JOIN permissoes p ON np.permissao_id = p.id
            WHERE u.id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao obter permissões: " . $e->getMessage());
        return [];
    }
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y H:i:s', strtotime($data));
}

/**
 * Função para escapar dados HTML
 * @param string $texto Texto a ser escapado
 * @return string Texto escapado
 */
function h($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?> 