<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensagem = "Erro ao buscar dados: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    try {
        // Verificar se o email já existe para outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['usuario_id']]);
        if ($stmt->fetch()) {
            $mensagem = "Este email já está em uso por outro usuário.";
            $tipo_mensagem = "danger";
        } else {
            // Se tentou alterar a senha
            if (!empty($senha_atual)) {
                if (password_verify($senha_atual, $usuario['senha'])) {
                    if ($nova_senha === $confirmar_senha && strlen($nova_senha) >= 6) {
                        // Atualizar dados e senha
                        $stmt = $pdo->prepare("
                            UPDATE usuarios 
                            SET nome = ?, email = ?, telefone = ?, senha = ? 
                            WHERE id = ?
                        ");
                        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt->execute([$nome, $email, $telefone, $nova_senha_hash, $_SESSION['usuario_id']]);
                        
                        $mensagem = "Dados e senha atualizados com sucesso!";
                        $tipo_mensagem = "success";
                        
                        // Atualizar dados da sessão
                        $_SESSION['usuario_nome'] = $nome;
                    } else {
                        $mensagem = "As senhas não conferem ou são muito curtas.";
                        $tipo_mensagem = "danger";
                    }
                } else {
                    $mensagem = "Senha atual incorreta.";
                    $tipo_mensagem = "danger";
                }
            } else {
                // Atualizar apenas dados pessoais
                $stmt = $pdo->prepare("
                    UPDATE usuarios 
                    SET nome = ?, email = ?, telefone = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $email, $telefone, $_SESSION['usuario_id']]);
                
                $mensagem = "Dados atualizados com sucesso!";
                $tipo_mensagem = "success";
                
                // Atualizar dados da sessão
                $_SESSION['usuario_nome'] = $nome;
            }
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao atualizar dados: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card mt-5">
                <div class="card-body">
                    <h2 class="text-center mb-4">Meu Perfil</h2>

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
                                   value="<?php echo $usuario['nome']; ?>">
                            <div class="invalid-feedback">
                                Por favor, insira seu nome completo.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   value="<?php echo $usuario['email']; ?>">
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
                                   value="<?php echo $usuario['telefone']; ?>">
                        </div>

                        <hr>

                        <h5 class="mb-3">Alterar Senha</h5>
                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha Atual</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="senha_atual" 
                                   name="senha_atual">
                            <div class="form-text">
                                Preencha apenas se desejar alterar sua senha.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nova_senha" class="form-label">Nova Senha</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="nova_senha" 
                                   name="nova_senha"
                                   minlength="6"
                                   maxlength="20"
                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                                   title="A senha deve conter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números">
                            <div class="invalid-feedback">
                                A senha deve ter no mínimo 6 caracteres, incluindo letras maiúsculas, minúsculas e números.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirmar_senha" 
                                   name="confirmar_senha"
                                   minlength="6"
                                   maxlength="20">
                            <div class="invalid-feedback">
                                As senhas não conferem. Por favor, verifique.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Salvar Alterações
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
    var senha = document.getElementById('nova_senha').value;
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

<?php require_once 'includes/footer.php'; ?> 