<?php

/**
 * Painel Administrativo - Index
 * Game Search - TCC
 */

require_once __DIR__ . '/../../php/admin/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Search — Painel Admin</title>
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/PixelBlast.css">
    <style>
        .dashboard-stats,
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: clamp(14px, 1.8vw, 22px);
            align-items: stretch;
            width: 100%;
            max-width: 1100px;
        }

        .dashboard-stats {
            margin-bottom: 34px;
        }

        .a {}

        .stat-card,
        .admin-card {
            min-width: 0;
            text-decoration: none;
            background: var(--cor-lateral);
            border: 1px solid var(--cor-borda);
            border-radius: var(--border-radius);
            transition: transform var(--transition-speed) ease,
                border-color var(--transition-speed) ease,
                box-shadow var(--transition-speed) ease;
        }

        .stat-card {
            display: flex;
            min-height: 132px;
            padding: clamp(16px, 1.8vw, 22px) 14px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
        }

        .stat-card:hover,
        .admin-card:hover {
            transform: translateY(-4px);
            border-color: var(--cor-primaria);
            box-shadow: var(--shadow-glow);
        }

        .stat-card i {
            margin-bottom: 10px;
            color: var(--cor-texto-destaque);
            font-size: 2rem;
        }

        .stat-card .numero {
            color: var(--cor-texto-destaque);
            font-family: var(--font-titulo);
            font-size: var(--fs-xl);
            font-weight: 700;
            line-height: 1.1;
        }

        .stat-card .label {
            color: var(--cor-texto-muted);
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            line-height: 1.4;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .admin-card {
            display: flex;
            min-height: 190px;
            padding: 24px 18px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            color: var(--cor-texto);
        }

        .admin-card i {
            flex: 0 0 auto;
            margin-bottom: 14px;
            color: var(--cor-texto-destaque);
            font-size: 2.5rem;
        }

        .admin-card h3 {
            margin: 0 0 8px;
            font-family: var(--font-titulo);
            font-size: var(--fs-base);
            line-height: 1.35;
        }

        .admin-card p {
            max-width: 24ch;
            margin: 0;
            color: var(--cor-texto-muted);
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            line-height: 1.5;
        }

        .admin-section-title {
            width: 100%;
            max-width: 1100px;
            margin: 0 0 18px;
            color: var(--cor-texto-destaque);
            font-family: var(--font-titulo);
            font-size: var(--fs-lg);
            line-height: 1.3;
        }

        @media (max-width: 900px) {

            .dashboard-stats,
            .admin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {

            .dashboard-stats,
            .admin-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-stats {
                margin-bottom: 28px;
            }

            .admin-header {
                align-items: flex-start;
            }

            .admin-header-acoes {
                width: 100%;
                justify-content: space-between;
            }
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1100px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-header h1 {
            min-width: 0;
            margin: 0;
            font-family: var(--font-titulo);
            font-size: var(--fs-xl);
            color: var(--cor-texto-destaque);
        }

        .admin-header-acoes {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            min-width: 0;
        }

        .admin-header-acoes span {
            min-width: 0;
            color: var(--cor-texto-muted);
            font-family: var(--font-corpo);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            max-width: 1100px;
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            color: var(--cor-texto-muted);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: var(--cor-texto-muted);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--cor-texto-destaque);
        }

        .breadcrumb i {
            font-size: 0.6rem;
        }

        .layout-principal {
            position: relative;
            z-index: 1;
        }

        .conteudo-principal {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <div class="layout-principal">

        <!-- Sidebar -->
        <aside class="barra-lateral" id="sidebarDrawer">
            <div class="sidebar-logo">
                <a href="../../index.html" aria-label="Ir para a página inicial">
                    <img src="../../img/logo2.png" alt="Game Search"></a>
            </div>
            <div class="sidebar-secao">
                <h3 class="sidebar-titulo">Administração</h3>
                <nav class="menu-navegacao">
                    <ul>
                        <li class="ativo"><a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                        <li><a href="jogos.php"><i class="fas fa-gamepad"></i><span>Jogos</span></a></li>
                        <li><a href="usuarios.php"><i class="fas fa-users"></i><span>Usuários</span></a></li>
                        <li><a href="feedback.php"><i class="fas fa-envelope"></i><span>Feedbacks</span></a></li>
                    </ul>
                </nav>
            </div>

            <div class="sidebar-secao">
                <h3 class="sidebar-titulo">Navegação</h3>
                <nav class="menu-navegacao">
                    <ul>
                        <li><a href="../../index.html"><i class="fas fa-home"></i><span>Início</span></a></li>
                        <li><a href="../biblioteca.html"><i class="fas fa-book"></i><span>Biblioteca</span></a></li>
                        <li><a href="../perfil.html"><i class="fas fa-user"></i><span>Perfil</span></a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="drawer-overlay" id="drawerOverlay"></div>

        <!-- Conteúdo -->
        <main class="conteudo-principal">
            <header class="barra-superior">
                <div class="container-busca">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="BUSCAR" class="campo-busca" autocomplete="off">
                </div>
            </header>

            <nav class="breadcrumb" aria-label="Navegação">
                <a href="../../index.html"><i class="fas fa-home"></i> Início</a>
                <i class="fas fa-chevron-right"></i>
                <span>Admin</span>
                <i class="fas fa-chevron-right"></i>
                <span>Dashboard</span>
            </nav>

            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="admin-header-acoes">
                    <span>
                        <i class="fas fa-user-shield"></i>
                        <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador'); ?>
                    </span>
                    <button type="button" class="btn-secundario" data-logout style="padding: 6px 16px; font-size: var(--fs-xs);">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </div>
            </div>

            <?php
            // Buscar estatísticas
            $stats = [
                'jogos' => 0,
                'usuarios' => 0,
                'feedbacks' => 0
            ];

            try {
                // Contar jogos
                $result = $conexao->query("SELECT COUNT(*) as total FROM jogos");
                if ($row = $result->fetch_assoc()) {
                    $stats['jogos'] = $row['total'];
                }

                // Contar usuários
                $result = $conexao->query("SELECT COUNT(*) as total FROM usuarios");
                if ($row = $result->fetch_assoc()) {
                    $stats['usuarios'] = $row['total'];
                }

                // Contar feedbacks
                $result = $conexao->query("SELECT COUNT(*) as total FROM feedback");
                if ($row = $result->fetch_assoc()) {
                    $stats['feedbacks'] = $row['total'];
                }
            } catch (Exception $e) {
                error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            }
            ?>

            <div class="dashboard-stats">
                <a href="jogos.php" class="stat-card"> <i class="fas fa-gamepad"></i>
                    <div class="numero"><?php echo $stats['jogos']; ?></div>
                    <div class="label">Jogos</div>
                </a>
                <a href="usuarios.php" class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="numero"><?php echo $stats['usuarios']; ?></div>
                    <div class="label">Usuários</div>
                </a>

                <a href="feedback.php" class="stat-card">
                    <i class="fas fa-envelope"></i>
                    <div class="numero"><?php echo $stats['feedbacks']; ?></div>
                    <div class="label">Feedbacks</div>
                </a>

            </div>

            <h2 class="admin-section-title">
                <i class="fas fa-cog"></i> Gerenciamento
            </h2>

            <div class="admin-grid">
                <a href="jogos.php" class="admin-card">
                    <i class="fas fa-gamepad"></i>
                    <h3>Gerenciar Jogos</h3>
                    <p>Adicionar, editar e remover jogos do catálogo</p>
                </a>
                <a href="usuarios.php" class="admin-card">
                    <i class="fas fa-users"></i>
                    <h3>Gerenciar Usuários</h3>
                    <p>Visualizar e gerenciar contas de usuários</p>
                </a>
                <a href="feedback.php" class="admin-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Gerenciar Feedbacks</h3>
                    <p>Visualizar e responder feedbacks dos usuários</p>
                </a>
            </div>
        </main>
    </div>

    <button class="hamburger-btn" id="hamburgerBtn" aria-label="Abrir menu" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../../js/global.js"></script>
    <script src="../../js/navegacao/sidebar.js"></script>
    <script src="../../js/navegacao/navegacao.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="../../js/PixelBlast.js"></script>

</body>

</html>