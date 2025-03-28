<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erros = validarFormulario($_POST);
    
    if (empty($erros)) {
        try {
            // Verificar se o email já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            
            if ($stmt->rowCount() > 0) {
                $mensagem = "Este email já está cadastrado";
                $tipo_mensagem = "danger";
            } else {
                // Inserir novo usuário
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, telefone) VALUES (?, ?, ?, ?)");
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['email'],
                    $senha_hash,
                    $_POST['telefone']
                ]);

                $mensagem = "Usuário cadastrado com sucesso!";
                $tipo_mensagem = "success";
                
                // Limpar o formulário
                $_POST = array();
            }
        } catch(PDOException $e) {
            $mensagem = "Erro ao cadastrar usuário: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = implode("<br>", $erros);
        $tipo_mensagem = "danger";
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Novo Cadastro</h2>

                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" 
                               class="form-control" 
                               id="nome" 
                               name="nome" 
                               required 
                               minlength="3"
                               maxlength="100"
                               pattern="[A-Za-zÀ-ÿ\s]+"
                               title="Digite apenas letras e espaços"
                               value="<?php echo $_POST['nome'] ?? ''; ?>">
                        <div class="invalid-feedback">
                            Por favor, insira seu nome completo (apenas letras e espaços).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required 
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                        <div class="invalid-feedback">
                            Por favor, insira um endereço de email válido.
                        </div>
                    </div>

                    <div class="mb-4">
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

                    <div class="mb-4">
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

                    <div class="mb-4">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" 
                               class="form-control" 
                               id="telefone" 
                               name="telefone" 
                               required 
                               pattern="\(\d{2}\) \d{5}-\d{4}"
                               title="Use o formato: (00) 00000-0000"
                               value="<?php echo $_POST['telefone'] ?? ''; ?>">
                        <div class="invalid-feedback">
                            Por favor, insira um número de telefone válido no formato (00) 00000-0000.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Cadastrar
                        </button>
                    </div>
                </form>
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