<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;

// Verificar token
if (isset($_GET['token'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT rs.*, u.email 
            FROM recuperacao_senha rs
            JOIN usuarios u ON rs.usuario_id = u.id
            WHERE rs.token = ? 
            AND rs.data_expiracao > NOW() 
            AND rs.usado = FALSE
        ");
        $stmt->execute([$_GET['token']]);
        $recuperacao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recuperacao) {
            $token_valido = true;
        } else {
            $mensagem = "Link inválido ou expirado.";
            $tipo_mensagem = "danger";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao verificar token: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
} else {
    $mensagem = "Link inválido.";
    $tipo_mensagem = "danger";
}

// Processar formulário de redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valido) {
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if ($senha === $confirmar_senha && strlen($senha) >= 6) {
        try {
            // Atualizar senha
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt->execute([$senha_hash, $recuperacao['usuario_id']]);
            
            // Marcar token como usado
            $stmt = $pdo->prepare("UPDATE recuperacao_senha SET usado = TRUE WHERE id = ?");
            $stmt->execute([$recuperacao['id']]);
            
            $mensagem = "Senha atualizada com sucesso! Você pode fazer login agora.";
            $tipo_mensagem = "success";
            $token_valido = false;
        } catch(PDOException $e) {
            $mensagem = "Erro ao atualizar senha: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = "As senhas não conferem ou são muito curtas.";
        $tipo_mensagem = "danger";
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card mt-5">
                <div class="card-body">
                    <h2 class="text-center mb-4">Redefinir Senha</h2>

                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($token_valido): ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Nova Senha</label>
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
                                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
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
                                    <i class="bi bi-check-lg"></i> Atualizar Senha
                                </button>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar para o Login
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Voltar para o Login
                            </a>
                        </div>
                    <?php endif; ?>
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

<?php require_once 'includes/footer.php'; ?> 