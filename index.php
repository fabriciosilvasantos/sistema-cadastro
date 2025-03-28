<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Estatísticas gerais
try {
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuários cadastrados hoje
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE DATE(data_cadastro) = CURDATE()");
    $usuarios_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuários cadastrados na última semana
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE data_cadastro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $usuarios_semana = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuários cadastrados no último mês
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE data_cadastro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $usuarios_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cadastros por mês (últimos 6 meses)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(data_cadastro, '%Y-%m') as mes,
            COUNT(*) as total
        FROM usuarios
        WHERE data_cadastro >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
        ORDER BY mes DESC
    ");
    $cadastros_por_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 domínios de email
    $stmt = $pdo->query("
        SELECT 
            SUBSTRING_INDEX(email, '@', -1) as dominio,
            COUNT(*) as total
        FROM usuarios
        GROUP BY SUBSTRING_INDEX(email, '@', -1)
        ORDER BY total DESC
        LIMIT 5
    ");
    $top_dominios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $mensagem = "Erro ao gerar relatórios: " . $e->getMessage();
    $tipo_mensagem = "danger";
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Dashboard</h2>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total de Usuários</h5>
                                <p class="card-text display-4"><?php echo $total_usuarios; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Cadastros Hoje</h5>
                                <p class="card-text display-4"><?php echo $usuarios_hoje; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Última Semana</h5>
                                <p class="card-text display-4"><?php echo $usuarios_semana; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Último Mês</h5>
                                <p class="card-text display-4"><?php echo $usuarios_mes; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Cadastros por Mês -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Cadastros nos Últimos 6 Meses</h5>
                        <canvas id="graficoCadastros"></canvas>
                    </div>
                </div>

                <!-- Top 5 Domínios -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 Domínios de Email</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Domínio</th>
                                        <th>Total</th>
                                        <th>Porcentagem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_dominios as $dominio): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($dominio['dominio']); ?></td>
                                            <td><?php echo $dominio['total']; ?></td>
                                            <td>
                                                <?php 
                                                $porcentagem = ($dominio['total'] / $total_usuarios) * 100;
                                                echo number_format($porcentagem, 2) . '%';
                                                ?>
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
    </div>
</div>

<!-- Script para o gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o gráfico
    const dados = <?php echo json_encode(array_reverse($cadastros_por_mes)); ?>;
    
    // Configuração do gráfico
    const ctx = document.getElementById('graficoCadastros').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.map(item => {
                const data = new Date(item.mes + '-01');
                return data.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
            }),
            datasets: [{
                label: 'Cadastros',
                data: dados.map(item => item.total),
                borderColor: '#4a90e2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
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
    });
</script>

<?php require_once 'includes/footer.php'; ?> 