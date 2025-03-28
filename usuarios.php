<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se tem permissão para visualizar usuários
if (!temPermissao('visualizar_usuarios')) {
    header('Location: index.php');
    exit;
}

// Definir página atual para o menu
$current_page = 'usuarios.php';

// Processar exclusão de usuário
if (isset($_POST['excluir']) && temPermissao('excluir_usuarios')) {
    $usuario_id = $_POST['usuario_id'];
    
    try {
        // Não permitir excluir o próprio usuário
        if ($usuario_id != $_SESSION['usuario_id']) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $mensagem = "Usuário excluído com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Você não pode excluir seu próprio usuário.";
            $tipo_mensagem = "warning";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao excluir usuário: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Processar alteração de status
if (isset($_POST['alterar_status']) && temPermissao('editar_usuarios')) {
    $usuario_id = $_POST['usuario_id'];
    $novo_status = $_POST['novo_status'];
    
    try {
        // Não permitir alterar o status do próprio usuário
        if ($usuario_id != $_SESSION['usuario_id']) {
            $stmt = $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $usuario_id]);
            $mensagem = "Status do usuário alterado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Você não pode alterar seu próprio status.";
            $tipo_mensagem = "warning";
        }
    } catch(PDOException $e) {
        $mensagem = "Erro ao alterar status: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Buscar usuários com filtros
$where = [];
$params = [];

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $where[] = "(nome LIKE ? OR email LIKE ?)";
    $params[] = "%{$_GET['busca']}%";
    $params[] = "%{$_GET['busca']}%";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['nivel']) && !empty($_GET['nivel'])) {
    $where[] = "nivel_acesso_id = ?";
    $params[] = $_GET['nivel'];
}

$sql = "
    SELECT 
        u.*,
        na.nome as nivel_acesso_nome
    FROM usuarios u
    JOIN niveis_acesso na ON u.nivel_acesso_id = na.id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.nome";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar níveis de acesso para o filtro
    $stmt = $pdo->query("SELECT * FROM niveis_acesso ORDER BY nome");
    $niveis_acesso = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $mensagem = "Erro ao carregar usuários: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Gerenciar Usuários</h2>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="busca" class="form-label">Buscar</label>
                    <input type="text" 
                           class="form-control" 
                           id="busca" 
                           name="busca" 
                           value="<?php echo $_GET['busca'] ?? ''; ?>"
                           placeholder="Nome ou email">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?php echo isset($_GET['status']) && $_GET['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo isset($_GET['status']) && $_GET['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="nivel" class="form-label">Nível de Acesso</label>
                    <select class="form-select" id="nivel" name="nivel">
                        <option value="">Todos</option>
                        <?php foreach ($niveis_acesso as $nivel): ?>
                            <option value="<?php echo $nivel['id']; ?>" 
                                    <?php echo isset($_GET['nivel']) && $_GET['nivel'] == $nivel['id'] ? 'selected' : ''; ?>>
                                <?php echo h($nivel['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo h($usuario['nome']); ?></td>
                                <td><?php echo h($usuario['email']); ?></td>
                                <td><?php echo h($usuario['telefone']); ?></td>
                                <td><?php echo h($usuario['nivel_acesso_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $usuario['status'] == 'ativo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($usuario['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <?php if (temPermissao('editar_usuarios')): ?>
                                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (temPermissao('editar_usuarios') && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <input type="hidden" name="novo_status" 
                                                       value="<?php echo $usuario['status'] == 'ativo' ? 'inativo' : 'ativo'; ?>">
                                                <button type="submit" name="alterar_status" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if (temPermissao('excluir_usuarios') && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <button type="submit" name="excluir" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 