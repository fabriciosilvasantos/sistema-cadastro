<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se tem permissão para visualizar relatórios
if (!temPermissao('visualizar_relatorios')) {
    header('Location: index.php');
    exit;
}

// Definir página atual para o menu
$current_page = 'relatorios.php';

// Filtros
$where = [];
$params = [];

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $where[] = "(u.nome LIKE ? OR u.email LIKE ?)";
    $params[] = "%{$_GET['busca']}%";
    $params[] = "%{$_GET['busca']}%";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "u.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['nivel']) && !empty($_GET['nivel'])) {
    $where[] = "u.nivel_acesso_id = ?";
    $params[] = $_GET['nivel'];
}

if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
    $where[] = "u.data_cadastro >= ?";
    $params[] = $_GET['data_inicio'] . " 00:00:00";
}

if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
    $where[] = "u.data_cadastro <= ?";
    $params[] = $_GET['data_fim'] . " 23:59:59";
}

try {
    // Lista de usuários
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
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar níveis de acesso para o filtro
    $stmt = $pdo->query("SELECT * FROM niveis_acesso ORDER BY nome");
    $niveis_acesso = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $mensagem = "Erro ao carregar relatórios: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Relatório de Usuários</h2>
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
                <div class="col-md-3">
                    <label for="busca" class="form-label">Buscar</label>
                    <input type="text" 
                           class="form-control" 
                           id="busca" 
                           name="busca" 
                           value="<?php echo $_GET['busca'] ?? ''; ?>"
                           placeholder="Nome ou email">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?php echo isset($_GET['status']) && $_GET['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo isset($_GET['status']) && $_GET['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="nivel" class="form-label">Nível</label>
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
                <div class="col-md-2">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" 
                           class="form-control" 
                           id="data_inicio" 
                           name="data_inicio" 
                           value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" 
                           class="form-control" 
                           id="data_fim" 
                           name="data_fim" 
                           value="<?php echo $_GET['data_fim'] ?? ''; ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Lista de Usuários</h5>
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
                            <?php if (temPermissao('editar_usuarios')): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo h($usuario['nome']); ?></td>
                                <td><?php echo h($usuario['email']); ?></td>
                                <td><?php echo h($usuario['telefone']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $usuario['nivel_acesso_id'] == 1 ? 'danger' : 
                                            ($usuario['nivel_acesso_id'] == 2 ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo h($usuario['nivel_acesso_nome']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $usuario['status'] == 'ativo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($usuario['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                                <?php if (temPermissao('editar_usuarios')): ?>
                                    <td>
                                        <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 