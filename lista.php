<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Processar exclusão
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $mensagem = "Usuário excluído com sucesso!";
        $tipo_mensagem = "success";
    } catch(PDOException $e) {
        $mensagem = "Erro ao excluir usuário: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Filtros
$where = [];
$params = [];

if (!empty($_GET['nome'])) {
    $where[] = "nome LIKE ?";
    $params[] = "%{$_GET['nome']}%";
}

if (!empty($_GET['email'])) {
    $where[] = "email LIKE ?";
    $params[] = "%{$_GET['email']}%";
}

if (!empty($_GET['telefone'])) {
    $where[] = "telefone LIKE ?";
    $params[] = "%{$_GET['telefone']}%";
}

// Ordenação
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'nome';
$direcao = isset($_GET['direcao']) ? $_GET['direcao'] : 'ASC';

// Construir query
$sql = "SELECT * FROM usuarios";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY $ordem $direcao";

// Executar query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Lista de Usuários</h2>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="filtros">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo $_GET['nome'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo $_GET['email'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                   value="<?php echo $_GET['telefone'] ?? ''; ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?ordem=nome&direcao=<?php echo $ordem == 'nome' && $direcao == 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                       class="text-white text-decoration-none">
                                        Nome
                                        <?php if ($ordem == 'nome'): ?>
                                            <i class="bi bi-sort-<?php echo $direcao == 'ASC' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?ordem=email&direcao=<?php echo $ordem == 'email' && $direcao == 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                       class="text-white text-decoration-none">
                                        Email
                                        <?php if ($ordem == 'email'): ?>
                                            <i class="bi bi-sort-<?php echo $direcao == 'ASC' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Telefone</th>
                                <th>
                                    <a href="?ordem=data_cadastro&direcao=<?php echo $ordem == 'data_cadastro' && $direcao == 'ASC' ? 'DESC' : 'ASC'; ?>" 
                                       class="text-white text-decoration-none">
                                        Data Cadastro
                                        <?php if ($ordem == 'data_cadastro'): ?>
                                            <i class="bi bi-sort-<?php echo $direcao == 'ASC' ? 'up' : 'down'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                                    <td>
                                        <a href="editar.php?id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-primary btn-action">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" name="excluir" class="btn btn-sm btn-danger btn-action">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 