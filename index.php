<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Definir página atual para o menu
$current_page = 'index.php';

$mensagem = '';
$tipo_mensagem = '';

// Buscar estatísticas
try {
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuários por nível de acesso
    $stmt = $pdo->query("
        SELECT na.nome, COUNT(*) as total 
        FROM usuarios u 
        JOIN niveis_acesso na ON u.nivel_acesso_id = na.id 
        GROUP BY na.id, na.nome
    ");
    $usuarios_por_nivel = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Usuários ativos/inativos
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as total 
        FROM usuarios 
        GROUP BY status
    ");
    $usuarios_por_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Últimos usuários cadastrados
    $stmt = $pdo->query("
        SELECT u.*, na.nome as nivel_acesso_nome 
        FROM usuarios u 
        JOIN niveis_acesso na ON u.nivel_acesso_id = na.id 
        ORDER BY u.data_cadastro DESC 
        LIMIT 5
    ");
    $ultimos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Registros por mês
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(u.data_cadastro, '%Y-%m') as mes,
            COUNT(*) as total
        FROM usuarios u
        WHERE u.data_cadastro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(u.data_cadastro, '%Y-%m') 
        ORDER BY mes DESC
    ");
    $registros_por_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Distribuição de domínios de email
    $stmt = $pdo->query("
        SELECT 
            SUBSTRING_INDEX(u.email, '@', -1) as dominio,
            COUNT(*) as total
        FROM usuarios u
        GROUP BY SUBSTRING_INDEX(u.email, '@', -1) 
        ORDER BY total DESC 
        LIMIT 5
    ");
    $dominios_email = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $mensagem = "Erro ao buscar estatísticas: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

require_once 'includes/header.php';
?>

<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Cards com totais -->
    <div class="row mb-4">
        <!-- Total de Usuários -->
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-people"></i> Total de Usuários
                    </h5>
                    <h2 class="card-text"><?php echo $total_usuarios; ?></h2>
                </div>
            </div>
        </div>

        <!-- Usuários Ativos -->
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-person-check"></i> Usuários Ativos
                    </h5>
                    <h2 class="card-text">
                        <?php 
                        $ativos = array_filter($usuarios_por_status, function($item) {
                            return $item['status'] == 'ativo';
                        });
                        echo count($ativos) > 0 ? current($ativos)['total'] : 0;
                        ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- Usuários Inativos -->
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-person-x"></i> Usuários Inativos
                    </h5>
                    <h2 class="card-text">
                        <?php 
                        $inativos = array_filter($usuarios_por_status, function($item) {
                            return $item['status'] == 'inativo';
                        });
                        echo count($inativos) > 0 ? current($inativos)['total'] : 0;
                        ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Tabelas -->
    <div class="row">
        <!-- Usuários por Nível de Acesso -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-shield-lock"></i> Usuários por Nível de Acesso
                    </h5>
                    <canvas id="graficoNiveis"></canvas>
                </div>
            </div>
        </div>

        <!-- Registros por Mês -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up"></i> Registros nos Últimos 12 Meses
                    </h5>
                    <canvas id="graficoMeses"></canvas>
                </div>
            </div>
        </div>

        <!-- Domínios de Email -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-envelope"></i> Top 5 Domínios de Email
                    </h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Domínio</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dominios_email as $dominio): ?>
                                    <tr>
                                        <td><?php echo h($dominio['dominio']); ?></td>
                                        <td><?php echo $dominio['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Usuários Cadastrados -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-clock-history"></i> Últimos Usuários Cadastrados
                    </h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Nível</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos_usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo h($usuario['nome']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $usuario['nivel_acesso_id'] == 1 ? 'danger' : 
                                                    ($usuario['nivel_acesso_id'] == 2 ? 'warning' : 'info'); 
                                            ?>">
                                                <?php echo h($usuario['nivel_acesso_nome']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados para os gráficos
const dadosNiveis = {
    labels: <?php echo json_encode(array_column($usuarios_por_nivel, 'nome')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($usuarios_por_nivel, 'total')); ?>,
        backgroundColor: [
            '#dc3545', // Vermelho para Administrador
            '#ffc107', // Amarelo para Gerente
            '#0dcaf0'  // Azul claro para Usuário
        ]
    }]
};

const dadosMeses = {
    labels: <?php echo json_encode(array_map(function($item) {
        return date('M/Y', strtotime($item['mes'] . '-01'));
    }, $registros_por_mes)); ?>,
    datasets: [{
        label: 'Registros',
        data: <?php echo json_encode(array_column($registros_por_mes, 'total')); ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13, 110, 253, 0.1)',
        tension: 0.1,
        fill: true
    }]
};

// Configurações dos gráficos
const configNiveis = {
    type: 'pie',
    data: dadosNiveis,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
};

const configMeses = {
    type: 'line',
    data: dadosMeses,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
};

// Criar os gráficos
new Chart(document.getElementById('graficoNiveis'), configNiveis);
new Chart(document.getElementById('graficoMeses'), configMeses);
</script>

<?php require_once 'includes/footer.php'; ?> 