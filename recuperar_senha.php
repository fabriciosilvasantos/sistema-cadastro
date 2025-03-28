<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    
    try {
        // Verificar se o email existe
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND status = 'ativo'");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // Gerar token único
            $token = bin2hex(random_bytes(32));
            $data_expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Inserir token no banco
            $stmt = $pdo->prepare("INSERT INTO recuperacao_senha (usuario_id, token, data_expiracao) VALUES (?, ?, ?)");
            $stmt->execute([$usuario['id'], $token, $data_expiracao]);
            
            // Aqui você implementaria o envio do email com o link de recuperação
            // Por enquanto, vamos apenas mostrar o token na tela
            $link = "http://localhost/cadastro/redefinir_senha.php?token=" . $token;
            
            $mensagem = "Link de recuperação de senha gerado com sucesso!<br>Link: " . $link;
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Email não encontrado ou conta inativa.";
            $tipo_mensagem = "danger";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao processar a recuperação de senha: " . $e->getMessage();
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
                    <h2 class="text-center mb-4">Recuperar Senha</h2>

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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope"></i> Enviar Link de Recuperação
                            </button>
                            <a href="login.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar para o Login
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
</script>

<?php require_once 'includes/footer.php'; ?> 