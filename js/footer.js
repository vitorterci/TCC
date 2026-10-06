/* Footer global. */
function inicializarFooter() {
    if (document.querySelector('.site-footer')) return;

    const caminho = window.location.pathname.replace(/\\/g, '/');
    let prefixo = '';
    if (caminho.includes('/pages/admin/')) {
        prefixo = '../../';
    } else if (caminho.includes('/pages/')) {
        prefixo = '../';
    }

    // Página atual (só o nome do arquivo) para marcar link ativo
    const paginaAtual = decodeURIComponent(
        window.location.pathname.split('/').pop() || 'index.html'
    ).toLowerCase();

    // Helper para marcar o link ativo
    const linkAtivo = (hrefArquivo) =>
        hrefArquivo.toLowerCase() === paginaAtual ? ' class="ativo" aria-current="page"' : '';

    const footer = document.createElement('footer');
    footer.className = 'site-footer';
    footer.innerHTML = `
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <div class="site-footer__logo">
                    <img src="${prefixo}img/logo.png" alt="Game Search" loading="lazy">
                </div>
                <p>Encontre, compare e descubra os melhores preços dos seus jogos favoritos em um só lugar.</p>
            </div>

            <nav class="site-footer__nav" aria-label="Navegação">
                <h2 class="site-footer__heading">Navegação</h2>
                <div class="site-footer__links">
                    <a href="${prefixo}index.html"${linkAtivo('index.html')}>Início</a>
                    <a href="${prefixo}pages/apresentação.html"${linkAtivo('apresentação.html')}>Apresentação</a>
                    <a href="${prefixo}pages/biblioteca.html"${linkAtivo('biblioteca.html')}>Biblioteca</a>
                    <a href="${prefixo}pages/sobre.html"${linkAtivo('sobre.html')}>Sobre nós</a>
                    <a href="${prefixo}pages/perfil.html"${linkAtivo('perfil.html')}>Perfil</a>
                </div>
            </nav>

            <nav class="site-footer__nav" aria-label="Suporte">
                <h2 class="site-footer__heading">Suporte</h2>
                <div class="site-footer__links">
                    <a href="${prefixo}pages/feedback.html"${linkAtivo('feedback.html')}>Feedback</a>
                    <a href="${prefixo}pages/ajuda.html"${linkAtivo('ajuda.html')}>Ajuda</a>
                    <a href="${prefixo}pages/configuracao.html"${linkAtivo('configuracao.html')}>Configurações</a>
                </div>
            </nav>

            <div class="site-footer__tools">
                <div class="site-footer__actions">
                    <button
                        type="button"
                        class="site-footer__tools-toggle"
                        aria-expanded="false"
                        aria-controls="footer-tools-menu"
                    >
                        <span>Ferramentas</span>
                        <span class="site-footer__tools-arrow" aria-hidden="true"></span>
                    </button>

                    <button
                        type="button"
                        class="site-footer__top"
                        aria-label="Voltar ao topo"
                    >
                         <span>Voltar ao topo</span>
                    </button>
                </div>

                <div class="site-footer__tools-panel" id="footer-tools-menu" hidden>
                    <div class="site-footer__tools-panel-inner">
                        <div class="site-footer__tools-grid">
                            <a href="https://github.com" target="_blank" rel="noopener noreferrer">GitHub</a>
                            <a href="https://code.visualstudio.com" target="_blank" rel="noopener noreferrer">VS Code</a>
                            <a href="https://www.mysql.com" target="_blank" rel="noopener noreferrer">MySQL</a>

                            <a href="https://css-tricks.com/snippets/css/a-guide-to-flexbox/" target="_blank" rel="noopener noreferrer">Flexbox</a>
                            <a href="https://www.reactbits.dev" target="_blank" rel="noopener noreferrer">React Bits</a>
                            <a href="https://www.canva.com" target="_blank" rel="noopener noreferrer">Canva</a>

                            <a href="https://developer.mozilla.org/pt-BR/docs/Web/HTML" target="_blank" rel="noopener noreferrer">HTML5</a>
                            <a href="https://developer.mozilla.org/pt-BR/docs/Web/CSS" target="_blank" rel="noopener noreferrer">CSS3</a>
                            <a href="https://developer.mozilla.org/pt-BR/docs/Web/JavaScript" target="_blank" rel="noopener noreferrer">JavaScript</a>

                            <a href="https://www.php.net" target="_blank" rel="noopener noreferrer">PHP</a>
                            <a href="https://www.apachefriends.org" target="_blank" rel="noopener noreferrer">XAMPP</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <span>© ${new Date().getFullYear()} Game Search — Todos os direitos reservados.</span>
        </div>
    `;

    const layout = document.querySelector('.layout-principal');
    if (layout && layout.parentNode) {
        layout.parentNode.insertBefore(footer, layout.nextSibling);
    } else {
        document.body.appendChild(footer);
    }

    // ── Voltar ao topo ─────────────────────────────────────────────────
    const btnTopo = footer.querySelector('.site-footer__top');
    btnTopo?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ── Menu Ferramentas (abre dentro do footer, abaixo das ações) ─────
    const toolsToggle = footer.querySelector('.site-footer__tools-toggle');
    const toolsPanel = footer.querySelector('#footer-tools-menu');
    const toolsWrapper = footer.querySelector('.site-footer__tools');

    if (toolsToggle && toolsPanel && toolsWrapper) {
        const abrirMenu = () => {
            toolsToggle.setAttribute('aria-expanded', 'true');
            toolsPanel.hidden = false;
            void toolsPanel.offsetHeight;
            toolsPanel.classList.add('aberto');
        };
        const fecharMenu = () => {
            toolsToggle.setAttribute('aria-expanded', 'false');
            toolsPanel.classList.remove('aberto');
            const finalizar = () => {
                if (!toolsPanel.classList.contains('aberto')) toolsPanel.hidden = true;
                toolsPanel.removeEventListener('transitionend', finalizar);
            };
            toolsPanel.addEventListener('transitionend', finalizar);
        };

        toolsToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const aberto = toolsToggle.getAttribute('aria-expanded') === 'true';
            aberto ? fecharMenu() : abrirMenu();
        });

        document.addEventListener('click', (e) => {
            if (toolsToggle.getAttribute('aria-expanded') !== 'true') return;
            if (!toolsWrapper.contains(e.target)) fecharMenu();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && toolsToggle.getAttribute('aria-expanded') === 'true') {
                fecharMenu();
                toolsToggle.focus();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', inicializarFooter);