<?php
/**
 * Painel Administrativo - Gerenciamento de Jogos
 * Game Search - TCC
 */

require_once __DIR__ . '/../../php/admin/auth_admin.php';
require_once __DIR__ . '/../../php/config.php';

// Processar ações
$acao = $_GET['acao'] ?? 'listar';

// Buscar jogos
$jogos = [];
try {
    $result = $conexao->query(
        "SELECT j.*,
                COALESCE(
                    (SELECT MIN(p.preco)
                     FROM precos p
                     WHERE p.jogo_id = j.id),
                    0
                ) AS preco_oferta
         FROM jogos j
         ORDER BY j.nome ASC"
    );

    if ($result === false) {
        throw new RuntimeException($conexao->error);
    }

    while ($row = $result->fetch_assoc()) {
        $jogos[] = $row;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar jogos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Search — Admin Jogos</title>
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
        
        .btn-adicionar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .etiqueta {
            display: inline-block;
            padding: 2px 10px;
            border-radius: var(--border-radius-xl);
            font-size: var(--fs-xs);
            font-weight: 600;
        }
        
        .etiqueta-disponivel {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
        }
        
        .etiqueta-indisponivel {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        /* Modal */
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
            max-height: 90vh;
            overflow-y: auto;
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
        .modal-content .campo-form select,
        .modal-content .campo-form textarea {
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

        .seletor-campo {
            position: relative;
        }

        .campo-selecao {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            min-height: 50px;
            padding: 13px 16px;
            background: #fff;
            border: 2px solid transparent;
            border-radius: 10px;
            color: var(--cor-texto);
            font-family: var(--font-corpo);
            font-size: var(--fs-sm);
            font-weight: 600;
            line-height: 1.35;
            text-align: left;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
            transition: border-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }

        .campo-selecao span {
            min-width: 0;
            color: #292938;
            overflow-wrap: anywhere;
        }

        .campo-selecao::after {
            content: '\f078';
            color: #5a5a68;
            font-family: 'Font Awesome 6 Free';
            font-size: 0.7rem;
            font-weight: 900;
            transition: transform var(--transition-speed) ease;
        }

        .campo-selecao[aria-expanded="true"] {
            border-color: #2864d7;
            box-shadow: 0 0 0 3px rgba(40, 100, 215, 0.16);
        }

        .campo-selecao[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

	        .menu-selecao {
	            display: none;
	            position: absolute;
	            top: calc(100% + 4px);
	            left: 0;
	            right: 0;
	            z-index: 2;
	            box-sizing: border-box;
	            max-height: 270px;
	            overflow-y: auto;
	            overflow-x: hidden;
	            padding: 4px 0;
	            background: #fff;
	            border: 2px solid #2864d7;
	            border-radius: 10px;
	            box-shadow: 0 5px 16px rgba(0, 0, 0, 0.2);
        }

	        .menu-selecao.aberto {
            display: flex;
            flex-direction: column;
        }

	        .opcao-selecao {
	            position: relative;
	            display: flex;
	            align-items: center;
	            box-sizing: border-box;
	            width: 100%;
	            min-height: 42px;
	            padding: 9px 40px 9px 16px;
	            color: #292938;
	            font-family: var(--font-corpo);
	            font-size: var(--fs-sm);
	            line-height: 1.35;
	            overflow-wrap: anywhere;
	            word-break: break-word;
	            cursor: pointer;
	            border: 1px solid transparent;
	            border-radius: 0;
	            transition: background var(--transition-speed) ease, color var(--transition-speed) ease, border-color var(--transition-speed) ease;
        }

        .modal-content .campo-form .opcao-selecao:hover {
            background: #eef3ff;
            color: #2864d7;
        }

        .modal-content .campo-form .opcao-selecao {
            color: #292938;
        }

        .modal-content .campo-form .opcao-selecao input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .modal-content .campo-form .opcao-selecao.selecionada {
            background: #eef3ff;
            border-bottom-color: #2864d7;
            color: #2864d7;
            font-weight: 700;
        }

        .modal-content .campo-form .opcao-selecao.selecionada::after {
            content: '\f00c';
            position: absolute;
            top: 50%;
            right: 16px;
            color: #2864d7;
            font-family: 'Font Awesome 6 Free';
            font-size: 0.75rem;
            font-weight: 900;
            transform: translateY(-50%);
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
                        <li class="ativo"><a href="jogos.php"><i class="fas fa-gamepad"></i><span>Jogos</span></a></li>
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
                <span>Jogos</span>
            </nav>
            
            <div class="admin-header">
                <h1><i class="fas fa-gamepad"></i> Gerenciar Jogos</h1>
                <button class="btn-primario btn-adicionar" id="btnAdicionar">
                    <i class="fas fa-plus"></i> Adicionar Jogo
                </button>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Etária</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jogos)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--cor-texto-muted);">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                Nenhum jogo cadastrado
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($jogos as $jogo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($jogo['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($jogo['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($jogo['etaria'] ?? 'N/I'); ?></td>
                            <td>
                                <span class="etiqueta <?php echo ($jogo['status'] ?? '') === 'ativo' ? 'etiqueta-disponivel' : 'etiqueta-indisponivel'; ?>">
                                    <?php echo htmlspecialchars($jogo['status'] ?? 'N/I'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="acoes">
                                    <button class="btn-editar" data-id="<?php echo $jogo['id']; ?>" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-excluir" data-id="<?php echo $jogo['id']; ?>" data-nome="<?php echo htmlspecialchars($jogo['nome']); ?>" title="Excluir">
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
    
    <!-- Modal -->
    <div class="modal-overlay" id="modalJogo">
        <div class="modal-content">
            <h2 id="modalTitulo">Adicionar Jogo</h2>
            <form id="formJogo">
                <input type="hidden" id="jogoId" value="">
                
                <div class="campo-form">
                    <label for="nome">Nome do Jogo</label>
                    <input type="text" id="nome" required>
                </div>
                
                <div class="campo-form">
                    <label for="slug">Slug (URL amigável)</label>
                    <input type="text" id="slug" placeholder="exemplo-de-jogo">
                </div>
                
                <div class="campo-form">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" required></textarea>
                </div>
                
                
                
                <div class="campo-form">
                    <label for="campoCategorias">Gêneros</label>
                    <div class="seletor-campo" id="seletorCategorias" data-placeholder="Selecione um ou mais gêneros">
                        <button type="button" class="campo-selecao" id="campoCategorias" aria-haspopup="true" aria-expanded="false"><span>Selecione um ou mais gêneros</span></button>
                        <div class="menu-selecao" id="menuCategoriasAdmin">
                            <label class="opcao-selecao"><input type="checkbox" value="Ação e aventura"> Ação e aventura</label>
                            <label class="opcao-selecao"><input type="checkbox" value="RPG de ação"> RPG de ação</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Aventura cooperativa"> Aventura cooperativa</label>
                            <label class="opcao-selecao"><input type="checkbox" value="RPG de computador"> RPG de computador</label>
                            <label class="opcao-selecao"><input type="checkbox" value="RPG japonês"> RPG japonês</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Metroidvania"> Metroidvania</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Jogo de plataforma"> Jogo de plataforma</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Roguelike"> Roguelike</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Atire e corra"> Atire e corra</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Mundo aberto"> Mundo aberto</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Simulação"> Simulação</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Terror e sobrevivência"> Terror e sobrevivência</label>
                        </div>
                    </div>
                </div>

                <div class="campo-form">
                    <label for="campoPlataformas">Plataformas</label>
                    <div class="seletor-campo" id="seletorPlataformas" data-placeholder="Selecione uma ou mais plataformas">
                        <button type="button" class="campo-selecao" id="campoPlataformas" aria-haspopup="true" aria-expanded="false"><span>Selecione uma ou mais plataformas</span></button>
                        <div class="menu-selecao" id="menuPlataformas">
                            <label class="opcao-selecao"><input type="checkbox" value="PC"> PC</label>
                            <label class="opcao-selecao"><input type="checkbox" value="PlayStation 4"> PlayStation 4</label>
                            <label class="opcao-selecao"><input type="checkbox" value="PlayStation 5"> PlayStation 5</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Xbox One"> Xbox One</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Xbox Series X|S"> Xbox Series X|S</label>
                            <label class="opcao-selecao"><input type="checkbox" value="Nintendo Switch"> Nintendo Switch</label>
                        </div>
                    </div>
                </div>

                <div class="campo-form">
                    <label for="campoAno">Ano</label>
                    <div class="seletor-campo" id="seletorAno" data-placeholder="Selecione o ano">
                        <button type="button" class="campo-selecao" id="campoAno" aria-haspopup="true" aria-expanded="false"><span>Selecione o ano</span></button>
                        <div class="menu-selecao">
                            <?php for ($anoOpcao = 1980; $anoOpcao <= 2030; $anoOpcao++): ?>
                            <label class="opcao-selecao"><input type="radio" name="ano_opcao" value="<?php echo $anoOpcao; ?>"> <?php echo $anoOpcao; ?></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="campo-form">
                    <label for="campoEtaria">Classificação Etária</label>
                    <div class="seletor-campo" id="seletorEtaria" data-placeholder="Selecione a classificação">
                        <button type="button" class="campo-selecao" id="campoEtaria" aria-haspopup="true" aria-expanded="false"><span>Selecione a classificação</span></button>
                        <div class="menu-selecao">
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="L"> Livre</label>
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="10"> 10+</label>
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="12"> 12+</label>
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="14"> 14+</label>
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="16"> 16+</label>
                            <label class="opcao-selecao"><input type="radio" name="etaria_opcao" value="18"> 18+</label>
                        </div>
                    </div>
                </div>

                <div class="campo-form">
                    <label for="campoStatus">Status</label>
                    <div class="seletor-campo" id="seletorStatus" data-placeholder="Selecione o status">
                        <button type="button" class="campo-selecao" id="campoStatus" aria-haspopup="true" aria-expanded="false"><span>Selecione o status</span></button>
                        <div class="menu-selecao">
                            <label class="opcao-selecao"><input type="radio" name="status_opcao" value="ativo"> Ativo</label>
                            <label class="opcao-selecao"><input type="radio" name="status_opcao" value="inativo"> Inativo</label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-acoes">
                    <button type="button" class="btn-cancelar" id="btnCancelar">Cancelar</button>
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
            const modal = document.getElementById('modalJogo');
            const form = document.getElementById('formJogo');
            const modalTitulo = document.getElementById('modalTitulo');
            const btnCancelar = document.getElementById('btnCancelar');
            const btnAdicionar = document.getElementById('btnAdicionar');
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMensagem');
            const seletores = {
                genero: document.getElementById('seletorCategorias'),
                plataforma: document.getElementById('seletorPlataformas'),
                ano: document.getElementById('seletorAno'),
                etaria: document.getElementById('seletorEtaria'),
                status: document.getElementById('seletorStatus')
            };

            const estadosSeletores = Object.fromEntries(
                Object.entries(seletores).map(([chave, seletor]) => [chave, {
                    seletor,
                    campo: seletor.querySelector('.campo-selecao'),
                    menu: seletor.querySelector('.menu-selecao'),
                    opcoes: Array.from(seletor.querySelectorAll('.opcao-selecao')),
                    entradas: Array.from(seletor.querySelectorAll('input')),
                    multiplo: seletor.id === 'seletorCategorias' || seletor.id === 'seletorPlataformas'
                }])
            );

            function normalizarValores(valor) {
                return String(valor ?? '')
                    .split(/\s*\/\s*|\s*,\s*/)
                    .map(item => item.trim())
                    .filter(Boolean);
            }

            function valoresSelecionados(estado) {
                return estado.entradas
                    .filter(entrada => entrada.checked)
                    .map(entrada => entrada.value);
            }

            function atualizarSeletor(estado) {
                const valores = valoresSelecionados(estado);
                estado.opcoes.forEach(opcao => {
                    const entrada = opcao.querySelector('input');
                    opcao.classList.toggle('selecionada', entrada.checked);
                });
                estado.campo.querySelector('span').textContent = valores.length
                    ? valores.join(estado.multiplo ? ' / ' : '')
                    : estado.seletor.dataset.placeholder;
            }

            function preencherSeletor(estado, valor) {
                const valores = normalizarValores(valor);
                estado.entradas.forEach(entrada => {
                    entrada.checked = valores.includes(entrada.value);
                });
                atualizarSeletor(estado);
            }

            Object.values(estadosSeletores).forEach(estado => {
                estado.campo.addEventListener('click', (evento) => {
                    evento.stopPropagation();
                    const aberto = estado.menu.classList.toggle('aberto');
                    estado.campo.setAttribute('aria-expanded', aberto ? 'true' : 'false');
                });

                estado.entradas.forEach(entrada => {
                    entrada.addEventListener('change', () => {
                        atualizarSeletor(estado);
                        if (!estado.multiplo) {
                            estado.menu.classList.remove('aberto');
                            estado.campo.setAttribute('aria-expanded', 'false');
                        }
                    });
                });
            });
            
            function mostrarToast(mensagem, tipo = 'sucesso') {
                toastMsg.textContent = mensagem;
                toast.className = 'toast visivel ' + tipo;
                clearTimeout(toast._timeout);
                toast._timeout = setTimeout(() => {
                    toast.classList.remove('visivel');
                }, 3000);
            }
            
            function abrirModal(jogo = null) {
                modal.classList.add('ativo');
                if (jogo) {
                    modalTitulo.textContent = 'Editar Jogo';
                    document.getElementById('jogoId').value = jogo.id;
                    document.getElementById('nome').value = jogo.nome || '';
                    document.getElementById('slug').value = jogo.slug || '';
                    document.getElementById('descricao').value = jogo.descricao || '';
                    preencherSeletor(estadosSeletores.genero, jogo.genero || jogo.categoria);
                    preencherSeletor(estadosSeletores.plataforma, jogo.plataforma);
                    preencherSeletor(estadosSeletores.ano, jogo.ano);
                    preencherSeletor(estadosSeletores.etaria, jogo.etaria || 'L');
                    preencherSeletor(estadosSeletores.status, jogo.status || 'ativo');
                } else {
                    modalTitulo.textContent = 'Adicionar Jogo';
                    form.reset();
                    document.getElementById('jogoId').value = '';
                    document.getElementById('slug').value = '';
                    preencherSeletor(estadosSeletores.genero, '');
                    preencherSeletor(estadosSeletores.plataforma, '');
                    preencherSeletor(estadosSeletores.ano, '');
                    preencherSeletor(estadosSeletores.etaria, 'L');
                    preencherSeletor(estadosSeletores.status, 'ativo');
                }
            }
            
            function fecharModal() {
                modal.classList.remove('ativo');
            }
            
            // Adicionar
            btnAdicionar.addEventListener('click', () => abrirModal(null));

            document.addEventListener('click', (evento) => {
                Object.values(estadosSeletores).forEach(estado => {
                    if (!estado.seletor.contains(evento.target)) {
                        estado.menu.classList.remove('aberto');
                        estado.campo.setAttribute('aria-expanded', 'false');
                    }
                });
            });
            
            // Cancelar
            btnCancelar.addEventListener('click', fecharModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) fecharModal();
            });
            
            // Editar
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        const response = await fetch(`../../php/api/admin/jogos.php?acao=get&id=${id}`);
                        const data = await response.json();
                        if (data.success) {
                            abrirModal(data.jogo);
                        } else {
                            mostrarToast(data.message || 'Erro ao carregar jogo', 'erro');
                        }
                    } catch (error) {
                        mostrarToast('Erro ao carregar jogo', 'erro');
                    }
                });
            });
            
            // Excluir
            document.querySelectorAll('.btn-excluir').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const nome = btn.dataset.nome;
                    if (!confirm(`Tem certeza que deseja excluir o jogo "${nome}"?`)) return;
                    
                    try {
                        const response = await fetch(`../../php/api/admin/jogos.php?acao=delete&id=${id}`, {
                            method: 'DELETE'
                        });
                        const data = await response.json();
                        if (data.success) {
                            mostrarToast('Jogo excluído com sucesso');
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
                
                const id = document.getElementById('jogoId').value;
                const dados = {
                    nome: document.getElementById('nome').value,
                    slug: document.getElementById('slug').value || document.getElementById('nome').value.toLowerCase().replace(/\s+/g, '-'),
                    descricao: document.getElementById('descricao').value,
                    categoria: valoresSelecionados(estadosSeletores.genero).join(', '),
                    genero: valoresSelecionados(estadosSeletores.genero).join(', '),
                    plataforma: valoresSelecionados(estadosSeletores.plataforma).join(', '),
                    etaria: valoresSelecionados(estadosSeletores.etaria)[0] || '',
                    ano: parseInt(valoresSelecionados(estadosSeletores.ano)[0], 10) || null,
                    status: valoresSelecionados(estadosSeletores.status)[0] || ''
                };
                
                const url = id 
                    ? `../../php/api/admin/jogos.php?acao=update&id=${id}`
                    : '../../php/api/admin/jogos.php?acao=create';
                
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(dados)
                    });
                    const data = await response.json();
                    if (data.success) {
                        mostrarToast(id ? 'Jogo atualizado!' : 'Jogo adicionado!');
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