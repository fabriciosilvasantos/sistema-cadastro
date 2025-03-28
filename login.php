<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, na.nome as nivel_acesso_nome 
            FROM usuarios u 
            JOIN niveis_acesso na ON u.nivel_acesso_id = na.id 
            WHERE u.email = ? AND u.status = 'ativo'
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso_nome'];
            
            header('Location: index.php');
            exit;
        } else {
            $mensagem = "Email ou senha inválidos.";
            $tipo_mensagem = "danger";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao fazer login: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Controle de Usuários</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Login</h2>

                        <?php if ($mensagem): ?>
                            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                                <?php echo $mensagem; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       value="<?php echo $_POST['email'] ?? ''; ?>">
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor, insira sua senha.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                                </button>
                                <a href="criar_conta.php" class="btn btn-success">
                                    <i class="bi bi-person-plus"></i> Criar Nova Conta
                                </a>
                                <a href="recuperar_senha.php" class="btn btn-link">
                                    <i class="bi bi-key"></i> Esqueceu sua senha?
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validação do formulário
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html> 