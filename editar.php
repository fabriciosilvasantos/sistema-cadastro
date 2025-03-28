<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem = '';
$tipo_mensagem = '';

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: lista.php');
        exit;
    }
} catch(PDOException $e) {
    $mensagem = "Erro ao buscar usuário: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erros = validarFormulario($_POST);
    
    if (empty($erros)) {
        try {
            // Verificar se o email já existe para outro usuário
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$_POST['email'], $id]);
            
            if ($stmt->rowCount() > 0) {
                $mensagem = "Este email já está cadastrado para outro usuário";
                $tipo_mensagem = "danger";
            } else {
                // Atualizar usuário
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['email'],
                    $_POST['telefone'],
                    $id
                ]);

                // Se uma nova senha foi fornecida, atualizar
                if (!empty($_POST['senha'])) {
                    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt->execute([$senha_hash, $id]);
                }

                $mensagem = "Usuário atualizado com sucesso!";
                $tipo_mensagem = "success";
                
                // Atualizar dados do usuário na sessão
                $usuario = array_merge($usuario, $_POST);
            }
        } catch(PDOException $e) {
            $mensagem = "Erro ao atualizar usuário: " . $e->getMessage();
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
                <h2 class="text-center mb-4">Editar Usuário</h2>

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
                               value="<?php echo htmlspecialchars($usuario['nome']); ?>">
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
                               value="<?php echo htmlspecialchars($usuario['email']); ?>">
                        <div class="invalid-feedback">
                            Por favor, insira um endereço de email válido.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" 
                               class="form-control" 
                               id="senha" 
                               name="senha" 
                               minlength="6"
                               maxlength="20"
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                               title="A senha deve conter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números">
                        <div class="invalid-feedback">
                            A senha deve ter no mínimo 6 caracteres, incluindo letras maiúsculas, minúsculas e números.
                        </div>
                    </div>

                    <div class="mb-4">
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

                    <div class="mb-4">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" 
                               class="form-control" 
                               id="telefone" 
                               name="telefone" 
                               required 
                               pattern="\(\d{2}\) \d{5}-\d{4}"
                               title="Use o formato: (00) 00000-0000"
                               value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                        <div class="invalid-feedback">
                            Por favor, insira um número de telefone válido no formato (00) 00000-0000.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="lista.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 