<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sistema de Controle de Usuários</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <?php if (temPermissao($pdo, $_SESSION['usuario_id'], 'cadastrar_usuarios')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'cadastro.php' ? 'active' : ''; ?>" href="cadastro.php">
                                <i class="bi bi-person-plus"></i> Novo Cadastro
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (temPermissao($pdo, $_SESSION['usuario_id'], 'visualizar_relatorios')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'lista.php' ? 'active' : ''; ?>" href="lista.php">
                                <i class="bi bi-people"></i> Lista de Usuários
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
                    <?php if (temPermissao('ver_relatorios')): ?>
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
            <?php endif; ?>
        </div>
    </div>
</nav> 