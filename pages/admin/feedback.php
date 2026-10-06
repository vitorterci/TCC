<?php
/**
 * Painel Administrativo - Gerenciamento de Feedbacks
 * Game Search - TCC
 */

require_once __DIR__ . '/../../php/admin/auth_admin.php';
require_once __DIR__ . '/../../php/config.php';

// Buscar feedbacks
$feedbacks = [];
try {
    $result = $conexao->query("SELECT * FROM feedback ORDER BY data_envio DESC");
    if ($result === false) {
        error_log("Erro ao buscar feedbacks: " . $conexao->error);
    } else {
        while ($row = $result->fetch_assoc()) {
            $feedbacks[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar feedbacks: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Search — Admin Feedbacks</title>
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
        
        .tabela-admin .acoes .btn-visualizar {
            color: var(--cor-texto-destaque);
        }
        
        .tabela-admin .acoes .btn-visualizar:hover {
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
        
        .etiqueta-pendente {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }
        
        .etiqueta-lido {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
        }
        
        .etiqueta-respondido {
            background: rgba(46, 0, 230, 0.2);
            color: var(--cor-texto-destaque);
        }
        
        .estrelas {
            color: #fbbf24;
        }
        
        .estrelas .vazia {
            color: var(--cor-texto-muted);
            opacity: 0.3;
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
            max-width: 600px;
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
        
        .modal-content .campo-form textarea,
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
        
        .modal-content .campo-form textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-content .campo-form .info-label {
            padding: 8px 12px;
            background: var(--cor-dropdown);
            border-radius: var(--border-radius-sm);
            font-family: var(--font-corpo);
            font-size: var(--fs-sm);
            color: var(--cor-texto);
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
        
        .feedback-mensagem {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                        <li><a href="usuarios.php"><i class="fas fa-users"></i><span>Usuários</span></a></li>
                        <li class="ativo"><a href="feedback.php"><i class="fas fa-envelope"></i><span>Feedbacks</span></a></li>
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
                <span>Feedbacks</span>
            </nav>
            
            <div class="admin-header">
                <h1><i class="fas fa-envelope"></i> Gerenciar Feedbacks</h1>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Mensagem</th>
                            <th>Avaliação</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--cor-texto-muted);">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                Nenhum feedback recebido
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['nome'] ?? 'Anônimo'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['email'] ?? 'N/I'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['tipo_feedback']); ?></td>
                            <td class="feedback-mensagem"><?php echo htmlspecialchars($feedback['mensagem']); ?></td>
                            <td>
                                <?php if ($feedback['avaliacao']): ?>
                                <span class="estrelas">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $feedback['avaliacao']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="fas fa-star vazia"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <?php else: ?>
                                N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="etiqueta 
                                    <?php echo $feedback['status'] === 'pendente' ? 'etiqueta-pendente' : 
                                        ($feedback['status'] === 'lido' ? 'etiqueta-lido' : 'etiqueta-respondido'); ?>">
                                    <?php echo htmlspecialchars($feedback['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($feedback['data_envio'])); ?></td>
                            <td>
                                <div class="acoes">
                                    <button class="btn-visualizar" data-id="<?php echo $feedback['id']; ?>" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-excluir" data-id="<?php echo $feedback['id']; ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
    
    <!-- Modal Visualizar Feedback -->
    <div class="modal-overlay" id="modalFeedback">
        <div class="modal-content">
            <h2>Detalhes do Feedback</h2>
            <form id="formFeedback">
                <input type="hidden" id="feedbackId" value="">
                
                <div class="campo-form">
                    <label>Nome</label>
                    <div class="info-label" id="viewNome">---</div>
                </div>
                
                <div class="campo-form">
                    <label>E-mail</label>
                    <div class="info-label" id="viewEmail">---</div>
                </div>
                
                <div class="campo-form">
                    <label>Tipo</label>
                    <div class="info-label" id="viewTipo">---</div>
                </div>
                
                <div class="campo-form">
                    <label>Mensagem</label>
                    <div class="info-label" id="viewMensagem" style="white-space: pre-wrap; max-height: 150px; overflow-y: auto;">---</div>
                </div>
                
                <div class="campo-form">
                    <label>Avaliação</label>
                    <div class="info-label" id="viewAvaliacao">---</div>
                </div>
                
                <div class="campo-form">
                    <label>Status</label>
                    <select id="editStatus">
                        <option value="pendente">Pendente</option>
                        <option value="lido">Lido</option>
                        <option value="respondido">Respondido</option>
                    </select>
                </div>
                
                <div class="modal-acoes">
                    <button type="button" class="btn-cancelar" id="btnCancelarFeedback">Cancelar</button>
                    <button type="submit" class="btn-salvar">Atualizar Status</button>
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
            const modal = document.getElementById('modalFeedback');
            const form = document.getElementById('formFeedback');
            const btnCancelar = document.getElementById('btnCancelarFeedback');
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
            
            // Visualizar
            document.querySelectorAll('.btn-visualizar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        const response = await fetch(`../../php/api/admin/feedback.php?acao=get&id=${id}`);
                        const data = await response.json();
                        if (data.success) {
                            document.getElementById('feedbackId').value = data.feedback.id;
                            document.getElementById('viewNome').textContent = data.feedback.nome || 'Anônimo';
                            document.getElementById('viewEmail').textContent = data.feedback.email || 'N/I';
                            document.getElementById('viewTipo').textContent = data.feedback.tipo_feedback || 'N/I';
                            document.getElementById('viewMensagem').textContent = data.feedback.mensagem || '---';
                            
                            const avaliacao = data.feedback.avaliacao;
                            if (avaliacao) {
                                let stars = '';
                                for (let i = 1; i <= 5; i++) {
                                    stars += i <= avaliacao ? '⭐' : '☆';
                                }
                                document.getElementById('viewAvaliacao').textContent = stars + ' (' + avaliacao + '/5)';
                            } else {
                                document.getElementById('viewAvaliacao').textContent = 'Não avaliado';
                            }
                            
                            document.getElementById('editStatus').value = data.feedback.status || 'pendente';
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
                    if (!confirm('Tem certeza que deseja excluir este feedback?')) return;
                    
                    try {
                        const response = await fetch(`../../php/api/admin/feedback.php?acao=delete&id=${id}`, {
                            method: 'DELETE'
                        });
                        const data = await response.json();
                        if (data.success) {
                            mostrarToast('Feedback excluído com sucesso');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            mostrarToast(data.message || 'Erro ao excluir', 'erro');
                        }
                    } catch (error) {
                        mostrarToast('Erro ao excluir', 'erro');
                    }
                });
            });
            
            // Submit - Atualizar status
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const id = document.getElementById('feedbackId').value;
                const status = document.getElementById('editStatus').value;
                
                try {
                    const response = await fetch(`../../php/api/admin/feedback.php?acao=update_status&id=${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: status })
                    });
                    const data = await response.json();
                    if (data.success) {
                        mostrarToast('Status atualizado!');
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

</body>
</html>
