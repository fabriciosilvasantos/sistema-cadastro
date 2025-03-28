<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se tem permissão para editar usuários
if (!temPermissao('editar_usuarios')) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Buscar dados do usuário
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            $mensagem = "Usuário não encontrado.";
            $tipo_mensagem = "danger";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao buscar dados: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
} else {
    header('Location: usuarios.php');
    exit;
}

// Buscar níveis de acesso
try {
    $stmt = $pdo->query("SELECT * FROM niveis_acesso ORDER BY nome");
    $niveis_acesso = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensagem = "Erro ao buscar níveis de acesso: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $nivel_acesso_id = $_POST['nivel_acesso_id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        // Verificar se o email já existe para outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_GET['id']]);
        if ($stmt->fetch()) {
            $mensagem = "Este email já está em uso por outro usuário.";
            $tipo_mensagem = "danger";
        } else {
            // Atualizar dados do usuário
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nome = ?, email = ?, telefone = ?, nivel_acesso_id = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nome, $email, $telefone, $nivel_acesso_id, $status, $_GET['id']]);
            
            $mensagem = "Usuário atualizado com sucesso!";
            $tipo_mensagem = "success";
            
            // Atualizar dados na sessão se for o usuário atual
            if ($_GET['id'] == $_SESSION['usuario_id']) {
                $_SESSION['usuario_nome'] = $nome;
            }
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao atualizar usuário: " . $e->getMessage();
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
                    <h2 class="text-center mb-4">Editar Usuário</h2>

                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($usuario): ?>
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

                            <div class="mb-3">
                                <label for="nivel_acesso_id" class="form-label">Nível de Acesso</label>
                                <select class="form-select" id="nivel_acesso_id" name="nivel_acesso_id" required>
                                    <option value="">Selecione um nível de acesso</option>
                                    <?php foreach ($niveis_acesso as $nivel): ?>
                                        <option value="<?php echo $nivel['id']; ?>" 
                                                <?php echo $nivel['id'] == $usuario['nivel_acesso_id'] ? 'selected' : ''; ?>>
                                            <?php echo $nivel['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um nível de acesso.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="ativo" <?php echo $usuario['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo $usuario['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um status.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Salvar Alterações
                                </button>
                                <a href="usuarios.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar
                                </a>
                            </div>
                        </form>
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
</script>

<?php require_once 'includes/footer.php'; ?> 