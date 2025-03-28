<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controle de Usuários</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            border: none;
        }
        .card-title {
            color: #495057;
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-lock"></i> Sistema de Controle de Usuários
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <?php if (temPermissao('cadastrar_usuarios')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'cadastro.php' ? 'active' : ''; ?>" href="cadastro.php">
                            <i class="bi bi-person-plus"></i> Cadastrar Usuário
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (temPermissao('visualizar_usuarios')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>" href="usuarios.php">
                            <i class="bi bi-people"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (temPermissao('visualizar_relatorios')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'relatorios.php' ? 'active' : ''; ?>" href="relatorios.php">
                            <i class="bi bi-graph-up"></i> Relatórios
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">
                            <i class="bi bi-person-circle"></i> Meu Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="py-4">
</body>
</html> 