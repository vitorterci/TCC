/* ── Navegação dinâmica e estado da sessão ── */
document.addEventListener('DOMContentLoaded', async () => {
    const base = window.location.pathname.includes('/pages/') ? '../' : '';
    const linksNavegacao = [...document.querySelectorAll('.barra-lateral .menu-navegacao a')];
    const linkEntrar = linksNavegacao
        .find(link => link.getAttribute('href')?.endsWith('login.html') && link.textContent.trim() === 'Entrar');
    const linkConfiguracao = linksNavegacao
        .find(link => link.getAttribute('href')?.endsWith('configuracao.html'));
    let botaoSair = document.getElementById('botaoSair');
    const navLogin = document.querySelector('.nav-login');
    const navPerfil = document.getElementById('navPerfil');
    const navConfiguracao = document.getElementById('navConfiguracao');
    const navSair = document.getElementById('navSair');

    // A seção de usuário é repetida nas páginas estáticas; criar o item aqui
    // mantém o botão disponível em todas elas sem duplicar marcação.
    if (!botaoSair && linkConfiguracao) {
        const menuUsuario = linkConfiguracao.closest('ul');
        if (menuUsuario) {
            const itemSair = document.createElement('li');
            itemSair.innerHTML = `
                <button type="button" class="botao-sair" id="botaoSair" aria-label="Sair da conta">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Sair da conta</span>
                </button>`;
            menuUsuario.appendChild(itemSair);
            botaoSair = itemSair.querySelector('#botaoSair');
        }
    }

    const exibirEntrar = (exibir) => {
        if (linkEntrar) linkEntrar.closest('li').style.display = exibir ? '' : 'none';
    };

    const exibirSair = (exibir) => {
        if (botaoSair) botaoSair.closest('li').style.display = exibir ? '' : 'none';
        if (navSair) navSair.style.display = exibir ? 'inline-flex' : 'none';
    };

    const exibirCabecalho = (usuarioLogado) => {
        if (navLogin) navLogin.style.display = usuarioLogado ? 'none' : 'inline-flex';
        if (navPerfil) navPerfil.style.display = usuarioLogado ? 'inline-flex' : 'none';
        if (navConfiguracao) navConfiguracao.style.display = usuarioLogado ? 'inline-flex' : 'none';
    };

    const usuarioPersistido = Boolean(localStorage.getItem('usuarioLogado'));
    exibirEntrar(!usuarioPersistido);
    exibirSair(usuarioPersistido);
    exibirCabecalho(usuarioPersistido);

    const menus = document.querySelectorAll('.barra-lateral .menu-navegacao');
    const menuPrincipal = menus[0]?.querySelector('ul');
    const bibliotecaExiste = [...document.querySelectorAll('.barra-lateral .menu-navegacao a')]
        .some(link => link.getAttribute('href')?.endsWith('biblioteca.html'));
    let itemAdmin = document.getElementById('itemAdmin');
    const menuUsuario = linkConfiguracao?.closest('ul');

    // Todas as páginas devem ter o mesmo item, inicialmente oculto.
    // Em /pages/, o caminho precisa voltar para a raiz antes de entrar em pages/admin.
    if (!itemAdmin && menuUsuario) {
        itemAdmin = document.createElement('li');
        itemAdmin.id = 'itemAdmin';
        itemAdmin.style.display = 'none';
        itemAdmin.innerHTML = `
            <a href="${base}pages/admin/index.php">
                <i class="fas fa-shield-halved"></i>
                <span>Administrador</span>
            </a>`;
        menuUsuario.appendChild(itemAdmin);
    } else if (itemAdmin) {
        itemAdmin.style.display = 'none';
    }
    
    if (menuPrincipal && !bibliotecaExiste) {
        const itemBiblioteca = document.createElement('li');
        const caminhoBiblioteca = base ? 'biblioteca.html' : 'pages/biblioteca.html';
        itemBiblioteca.innerHTML = `<a href="${caminhoBiblioteca}"><i class="fas fa-book"></i><span>Biblioteca</span></a>`;
        menuPrincipal.appendChild(itemBiblioteca);
    }

    const botoesSair = [...new Set([
        botaoSair,
        navSair,
        ...document.querySelectorAll('[data-logout]')
    ].filter(Boolean))];
    const encerrarSessao = async () => {
        if (!botoesSair.length) return;

        botoesSair.forEach(botao => {
            botao.disabled = true;
            botao.setAttribute('aria-busy', 'true');
        });

        try {
            await fetch(`${base}php/logout.php`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });
        } catch (erro) {
            // Mesmo sem resposta do servidor, limpamos o estado local e retornamos ao login.
        } finally {
            localStorage.removeItem('usuarioLogado');
            const caminhoLogin = 'pages/login.html';
            window.location.replace(`${base}${caminhoLogin}`);
        }
    };

    botoesSair.forEach(botao => botao.addEventListener('click', encerrarSessao));

    try {
        const resposta = await fetch(`${base}php/api/user.php?acao=get`, { credentials: 'include' });
        const dados = await resposta.json();
        const usuarioLogado = Boolean(dados.success && dados.user);
        const usuarioAdmin = usuarioLogado &&
            String(dados.user.role || '').toLowerCase() === 'admin';
        
        exibirEntrar(!usuarioLogado);
        exibirSair(usuarioLogado);
        exibirCabecalho(usuarioLogado);
        
        if (usuarioLogado) {
            window.usuarioLogado = dados.user;
            localStorage.setItem('usuarioLogado', JSON.stringify(dados.user));
        
            const itemAdminAtual = document.getElementById('itemAdmin');
            if (itemAdminAtual) itemAdminAtual.style.display = usuarioAdmin ? '' : 'none';
        } else {
            localStorage.removeItem('usuarioLogado');
            window.usuarioLogado = null;
        
            const itemAdminAtual = document.getElementById('itemAdmin');
            if (itemAdminAtual) itemAdminAtual.style.display = 'none';
        }
    } catch (erro) {
        // Mantém o estado persistido quando o backend estiver indisponível.
    }

    /* ── Marcar item ativo na sidebar ── */
    const paginaAtual = decodeURIComponent(window.location.pathname.split('/').pop() || 'index.html');
    document.querySelectorAll('.menu-navegacao li').forEach(li => li.classList.remove('ativo'));
    document.querySelectorAll('.menu-navegacao li a').forEach(link => {
        const href = decodeURIComponent(link.getAttribute('href')?.split('/').pop() || '');
        if (href === paginaAtual) {
            link.closest('li')?.classList.add('ativo');
        }
    });
});

/* ── Controle de acesso refletido na navegação ── */
function isUsuarioLogado() {
    return Boolean(localStorage.getItem('usuarioLogado'));
}

function isAdmin() {
    try {
        const usuario = JSON.parse(localStorage.getItem('usuarioLogado'));
        return usuario && (usuario.role === 'admin');
    } catch {
        return false;
    }
}

function atualizarInterfaceUsuario() {
    const logado = isUsuarioLogado();
    const admin = isAdmin();
    const linkEntrar = document.querySelector('.barra-lateral .menu-navegacao a[href*="login.html"]');
    const linkPerfil = document.querySelector('.barra-lateral .menu-navegacao a[href*="perfil.html"]');
    const linkConfig = document.querySelector('.barra-lateral .menu-navegacao a[href*="configuracao.html"]');
    const linkBiblioteca = document.querySelector('.barra-lateral .menu-navegacao a[href*="biblioteca.html"]');

    if (linkEntrar) linkEntrar.closest('li').style.display = logado ? 'none' : '';
    if (linkPerfil) linkPerfil.closest('li').style.display = logado ? '' : 'none';
    if (linkConfig) linkConfig.closest('li').style.display = logado ? '' : 'none';
    if (linkBiblioteca) linkBiblioteca.closest('li').style.display = logado ? '' : 'none';

    const botaoSair = document.getElementById('botaoSair');
    if (botaoSair) botaoSair.closest('li').style.display = logado ? '' : 'none';

    const itemAdminAtual = document.getElementById('itemAdmin');
    if (itemAdminAtual) itemAdminAtual.style.display = admin ? '' : 'none';

    const breadcrumbAdmin = document.querySelector('.breadcrumb a[href*="admin"]');
    if (breadcrumbAdmin) breadcrumbAdmin.style.display = admin ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    atualizarInterfaceUsuario();

    window.addEventListener('storage', () => atualizarInterfaceUsuario());
});

window.isUsuarioLogado = isUsuarioLogado;
window.isAdmin = isAdmin;
window.atualizarInterfaceUsuario = atualizarInterfaceUsuario;
