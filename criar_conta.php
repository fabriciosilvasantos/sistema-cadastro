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
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    
    try {
        // Verificar se o email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $mensagem = "Este email já está cadastrado.";
            $tipo_mensagem = "danger";
        } else {
            if ($senha === $confirmar_senha && strlen($senha) >= 6) {
                // Inserir novo usuário com nível de acesso "Usuário" (id = 3)
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nome, email, senha, telefone, nivel_acesso_id, status) 
                    VALUES (?, ?, ?, ?, 3, 'ativo')
                ");
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt->execute([$nome, $email, $senha_hash, $telefone]);
                
                $mensagem = "Conta criada com sucesso! Você já pode fazer login.";
                $tipo_mensagem = "success";
                
                // Redirecionar para a página de login após 2 segundos
                header("refresh:2;url=login.php");
            } else {
                $mensagem = "As senhas não conferem ou são muito curtas.";
                $tipo_mensagem = "danger";
            }
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao criar conta: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Sistema de Controle de Usuários</title>
    
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
                        <h2 class="text-center mb-4">Criar Nova Conta</h2>

                        <?php if ($mensagem): ?>
                            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                                <?php echo $mensagem; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       required 
                                       value="<?php echo $_POST['nome'] ?? ''; ?>">
                                <div class="invalid-feedback">
                                    Por favor, insira o nome completo.
                                </div>
                            </div>

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
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefone" 
                                       name="telefone" 
                                       value="<?php echo $_POST['telefone'] ?? ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       required
                                       minlength="6"
                                       maxlength="20"
                                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                                       title="A senha deve conter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números">
                                <div class="invalid-feedback">
                                    A senha deve ter no mínimo 6 caracteres, incluindo letras maiúsculas, minúsculas e números.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirmar_senha" 
                                       name="confirmar_senha" 
                                       required
                                       minlength="6"
                                       maxlength="20">
                                <div class="invalid-feedback">
                                    As senhas não conferem. Por favor, verifique.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Criar Conta
                                </button>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar para Login
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

    // Validação de senha
    document.getElementById('confirmar_senha').addEventListener('input', function() {
        var senha = document.getElementById('senha').value;
        var confirmarSenha = this.value;
        var feedback = this.nextElementSibling;
        
        if (senha !== confirmarSenha) {
            this.setCustomValidity('As senhas não conferem');
            feedback.style.display = 'block';
        } else {
            this.setCustomValidity('');
            feedback.style.display = 'none';
        }
    });
    </script>
</body>
</html> 