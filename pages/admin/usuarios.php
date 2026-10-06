<?php
/**
 * Painel Administrativo - Gerenciamento de Usuários
 * Game Search - TCC
 */

require_once __DIR__ . '/../../php/admin/auth_admin.php';
require_once __DIR__ . '/../../php/config.php';

// Buscar usuários
$usuarios = [];
try {
    $result = $conexao->query("SELECT id, nome, usuario, email, role AS tipo, data_cadastro FROM usuarios ORDER BY id ASC");

    if ($result === false) {
        throw new RuntimeException($conexao->error);
    }

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Search — Admin Usuários</title>
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/PixelBlast.css">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .admin-header h1 {
            font-family: var(--font-titulo);
            font-size: var(--fs-xl);
            color: var(--cor-texto-destaque);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        .tabela-admin {
            width: 100%;
            border-collapse: collapse;
            background: var(--cor-lateral);
            border: 1px solid var(--cor-borda);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .tabela-admin th {
            background: var(--cor-dropdown);
            padding: 12px 16px;
            text-align: left;
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--cor-texto-muted);
            border-bottom: 1px solid var(--cor-borda);
        }
        
        .tabela-admin td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--cor-borda);
            font-family: var(--font-corpo);
            font-size: var(--fs-sm);
        }
        
        .tabela-admin tr:hover td {
            background: rgba(255,255,255,0.02);
        }
        
        .tabela-admin .acoes {
            display: flex;
            gap: 8px;
        }
        
        .tabela-admin .acoes button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-speed) ease;
        }
        
        .tabela-admin .acoes .btn-editar {
            color: var(--cor-texto-destaque);
        }
        
        .tabela-admin .acoes .btn-editar:hover {
            background: rgba(46, 0, 230, 0.15);
        }
        
        .tabela-admin .acoes .btn-excluir {
            color: #ef4444;
        }
        
        .tabela-admin .acoes .btn-excluir:hover {
            background: rgba(239, 68, 68, 0.15);
        }
        
        .etiqueta {
            display: inline-block;
            padding: 2px 10px;
            border-radius: var(--border-radius-xl);
            font-size: var(--fs-xs);
            font-weight: 600;
        }
        
        .etiqueta-admin {
            background: rgba(46, 0, 230, 0.2);
            color: var(--cor-texto-destaque);
            border: 1px solid rgba(46, 0, 230, 0.3);
        }
        
        .etiqueta-usuario {
            background: rgba(148, 163, 184, 0.15);
            color: var(--cor-texto-muted);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--cor-primaria);
            color: var(--cor-texto-sobre-destaque);
            padding: 12px 22px;
            border-radius: var(--border-radius);
            font-family: var(--font-corpo);
            font-size: var(--fs-sm);
            font-weight: 600;
            box-shadow: var(--shadow-glow);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 9999;
        }
        
        .toast.visivel {
            opacity: 1;
            transform: translateY(0);
        }
        
        .toast.erro {
            background: #ef4444;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.ativo {
            display: flex;
        }
        
        .modal-content {
            background: var(--cor-lateral);
            border: 1px solid var(--cor-borda);
            border-radius: var(--border-radius);
            padding: 32px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-content h2 {
            font-family: var(--font-titulo);
            font-size: var(--fs-lg);
            margin-bottom: 20px;
            color: var(--cor-texto-destaque);
        }
        
        .modal-content .campo-form {
            margin-bottom: 16px;
        }
        
        .modal-content .campo-form label {
            display: block;
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            color: var(--cor-texto-muted);
            margin-bottom: 4px;
        }
        
        .modal-content .campo-form input,
        .modal-content .campo-form select {
            width: 100%;
            padding: 10px 12px;
            background: var(--cor-dropdown);
            border: 1px solid var(--cor-borda-forte);
            border-radius: var(--border-radius-sm);
            color: var(--cor-texto);
            font-family: var(--font-corpo);
            font-size: var(--fs-sm);
        }
        
        .modal-content .modal-acoes {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .modal-content .modal-acoes button {
            padding: 10px 24px;
            border-radius: var(--border-radius-xl);
            border: none;
            cursor: pointer;
            font-family: var(--font-corpo);
            font-size: var(--fs-xs);
            font-weight: 700;
            transition: all var(--transition-speed) ease;
        }
        
        .modal-content .modal-acoes .btn-cancelar {
            background: var(--cor-hover);
            color: var(--cor-texto);
        }
        
        .modal-content .modal-acoes .btn-salvar {
            background: var(--cor-primaria);
            color: var(--cor-texto-sobre-destaque);
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
                        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                        <li><a href="jogos.php"><i class="fas fa-gamepad"></i><span>Jogos</span></a></li>
                        <li class="ativo"><a href="usuarios.php"><i class="fas fa-users"></i><span>Usuários</span></a></li>
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
                <span>Usuários</span>
            </nav>
            
            <div class="admin-header">
                <h1><i class="fas fa-users"></i> Gerenciar Usuários</h1>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--cor-texto-muted);">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                Nenhum usuário cadastrado
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($usuario['nome']); ?></strong></td>
                            <td>@<?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <span class="etiqueta <?php echo ($usuario['tipo'] ?? 'usuario') === 'admin' ? 'etiqueta-admin' : 'etiqueta-usuario'; ?>">
                                    <?php echo htmlspecialchars($usuario['tipo'] ?? 'usuario'); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                            <td>
                                <div class="acoes">
                                    <button class="btn-editar" data-id="<?php echo $usuario['id']; ?>" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                    <button class="btn-excluir" data-id="<?php echo $usuario['id']; ?>" data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal Editar Usuário -->
    <div class="modal-overlay" id="modalUsuario">
        <div class="modal-content">
            <h2>Editar Usuário</h2>
            <form id="formUsuario">
                <input type="hidden" id="usuarioId" value="">
                
                <div class="campo-form">
                    <label for="editNome">Nome</label>
                    <input type="text" id="editNome" required>
                </div>
                
                <div class="campo-form">
                    <label for="editUsuario">Usuário</label>
                    <input type="text" id="editUsuario" required>
                </div>
                
                <div class="campo-form">
                    <label for="editEmail">E-mail</label>
                    <input type="email" id="editEmail" required>
                </div>
                
                <div class="campo-form">
                    <label for="editTipo">Tipo</label>
                    <select id="editTipo">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="modal-acoes">
                    <button type="button" class="btn-cancelar" id="btnCancelarUsuario">Cancelar</button>
                    <button type="submit" class="btn-salvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Toast -->
    <div class="toast" id="toast">
        <span id="toastMensagem"></span>
    </div>
    
    <button class="hamburger-btn" id="hamburgerBtn" aria-label="Abrir menu" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>
    
    <script src="../../js/global.js"></script>
    <script src="../../js/navegacao/sidebar.js"></script>
    <script src="../../js/navegacao/navegacao.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalUsuario');
            const form = document.getElementById('formUsuario');
            const btnCancelar = document.getElementById('btnCancelarUsuario');
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMensagem');
            
            function mostrarToast(mensagem, tipo = 'sucesso') {
                toastMsg.textContent = mensagem;
                toast.className = 'toast visivel ' + tipo;
                clearTimeout(toast._timeout);
                toast._timeout = setTimeout(() => {
                    toast.classList.remove('visivel');
                }, 3000);
            }
            
            function fecharModal() {
                modal.classList.remove('ativo');
            }
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) fecharModal();
            });
            
            btnCancelar.addEventListener('click', fecharModal);
            
            // Editar
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        const response = await fetch(`../../php/api/admin/usuarios.php?acao=get&id=${id}`);
                        const data = await response.json();
                        if (data.success) {
                            document.getElementById('usuarioId').value = data.usuario.id;
                            document.getElementById('editNome').value = data.usuario.nome || '';
                            document.getElementById('editUsuario').value = data.usuario.usuario || '';
                            document.getElementById('editEmail').value = data.usuario.email || '';
                            document.getElementById('editTipo').value = data.usuario.tipo || 'usuario';
                            modal.classList.add('ativo');
                        } else {
                            mostrarToast(data.message || 'Erro ao carregar', 'erro');
                        }
                    } catch (error) {
                        mostrarToast('Erro ao carregar', 'erro');
                    }
                });
            });
            
            // Excluir
            document.querySelectorAll('.btn-excluir').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const nome = btn.dataset.nome;
                    if (!confirm(`Tem certeza que deseja excluir o usuário "${nome}"?`)) return;
                    
                    try {
                        const response = await fetch(`../../php/api/admin/usuarios.php?acao=delete&id=${id}`, {
                            method: 'DELETE'
                        });
                        const data = await response.json();
                        if (data.success) {
                            mostrarToast('Usuário excluído com sucesso');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            mostrarToast(data.message || 'Erro ao excluir', 'erro');
                        }
                    } catch (error) {
                        mostrarToast('Erro ao excluir', 'erro');
                    }
                });
            });
            
            // Submit
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const id = document.getElementById('usuarioId').value;
                const dados = {
                    nome: document.getElementById('editNome').value,
                    usuario: document.getElementById('editUsuario').value,
                    email: document.getElementById('editEmail').value,
                    tipo: document.getElementById('editTipo').value
                };
                
                try {
                    const response = await fetch(`../../php/api/admin/usuarios.php?acao=update&id=${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(dados)
                    });
                    const data = await response.json();
                    if (data.success) {
                        mostrarToast('Usuário atualizado!');
                        fecharModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        mostrarToast(data.message || 'Erro ao salvar', 'erro');
                    }
                } catch (error) {
                    mostrarToast('Erro ao salvar', 'erro');
                }
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="../../js/PixelBlast.js"></script>

</html>
