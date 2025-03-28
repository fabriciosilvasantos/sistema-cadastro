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

// Definir página atual para o menu
$current_page = 'usuarios.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$usuario_id = $_GET['id'];

try {
    // Buscar dados do usuário
    $stmt = $pdo->prepare("
        SELECT u.*, na.nome as nivel_acesso_nome
        FROM usuarios u
        JOIN niveis_acesso na ON u.nivel_acesso_id = na.id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: usuarios.php');
        exit;
    }

    // Buscar níveis de acesso
    $stmt = $pdo->query("SELECT * FROM niveis_acesso ORDER BY nome");
    $niveis_acesso = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $mensagem = "Erro ao carregar dados do usuário: " . $e->getMessage();
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
        $stmt->execute([$email, $usuario_id]);
        
        if ($stmt->fetch()) {
            $mensagem = "Este email já está cadastrado para outro usuário.";
            $tipo_mensagem = "danger";
        } else {
            // Atualizar dados do usuário
            $sql = "UPDATE usuarios SET 
                    nome = ?, 
                    email = ?, 
                    telefone = ?, 
                    nivel_acesso_id = ?,
                    status = ?";
            
            // Se uma nova senha foi fornecida, incluí-la na atualização
            if (!empty($_POST['senha'])) {
                $sql .= ", senha = ?";
                $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $params = [$nome, $email, $telefone, $nivel_acesso_id, $status, $senha_hash];
            } else {
                $params = [$nome, $email, $telefone, $nivel_acesso_id, $status];
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $usuario_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $mensagem = "Usuário atualizado com sucesso!";
            $tipo_mensagem = "success";
            
            // Atualizar dados do usuário na sessão se for o próprio usuário
            if ($usuario_id == $_SESSION['usuario_id']) {
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

                    <?php if (isset($mensagem)): ?>
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
                                   value="<?php echo h($usuario['nome']); ?>">
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
                                   value="<?php echo h($usuario['email']); ?>">
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
                                   value="<?php echo h($usuario['telefone']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="nivel_acesso_id" class="form-label">Nível de Acesso</label>
                            <select class="form-select" id="nivel_acesso_id" name="nivel_acesso_id" required>
                                <?php foreach ($niveis_acesso as $nivel): ?>
                                    <option value="<?php echo $nivel['id']; ?>" 
                                            <?php echo $usuario['nivel_acesso_id'] == $nivel['id'] ? 'selected' : ''; ?>>
                                        <?php echo h($nivel['nome']); ?>
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

                        <div class="mb-3">
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Salvar Alterações
                            </button>
                            <a href="usuarios.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 