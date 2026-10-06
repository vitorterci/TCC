/* ─── Histórico de jogos acessados recentemente ──────────────────────────────
 * Persistência em LocalStorage, sem backend.
 * Chave: gameSearch.historico
 * Estrutura: [{ id, slug, nome, imagem, preco }]  (mais recente primeiro)
 *
 * Renderização: painel compacto no topo do <main class="conteudo-principal">,
 * com cards horizontais contendo capa + título + preço + botão ×.
 * ──────────────────────────────────────────────────────────────────────────── */
(() => {
    'use strict';

    const CHAVE = 'gameSearch.historico';
    const LIMITE = 10;
    const ID_CONTAINER = 'historico-container';
    const ID_LISTA = 'historico-lista';
    const PRECO_FALLBACK = 'Grátis';

    /* ── Leitura / escrita ─────────────────────────────────────────────────── */

    const ler = () => {
        try {
            const bruto = localStorage.getItem(CHAVE);
            if (!bruto) return [];
            const dados = JSON.parse(bruto);
            return Array.isArray(dados)
                ? dados.filter(item => item && (item.slug || item.id))
                : [];
        } catch (erro) {
            console.warn('Histórico: falha ao ler LocalStorage.', erro);
            return [];
        }
    };

    const escrever = (lista) => {
        try {
            localStorage.setItem(CHAVE, JSON.stringify(lista.slice(0, LIMITE)));
        } catch (erro) {
            console.warn('Histórico: falha ao gravar LocalStorage.', erro);
        }
    };

    /* ── API pública ───────────────────────────────────────────────────────── */

    const adicionar = (jogo) => {
        if (!jogo || (!jogo.slug && !jogo.id)) return;
        const item = {
            id: jogo.id ?? null,
            slug: jogo.slug ?? '',
            nome: jogo.nome ?? 'Jogo',
            imagem: jogo.imagem ?? '',
            preco: (jogo.preco != null && jogo.preco !== '') ? jogo.preco : PRECO_FALLBACK
        };
        const atual = ler();
        const semDuplicata = atual.filter(outro =>
            !(outro.slug && item.slug && outro.slug === item.slug) &&
            !(outro.id != null && item.id != null && Number(outro.id) === Number(item.id))
        );
        semDuplicata.unshift(item);
        escrever(semDuplicata);
        renderizar();
    };

    const remover = (slug) => {
        const atual = ler();
        const nova = atual.filter(item => item.slug !== slug);
        escrever(nova);
        renderizar();
    };

    const limpar = () => {
        try { localStorage.removeItem(CHAVE); } catch (erro) { /* noop */ }
        renderizar();
    };

    const listar = () => ler();

    /* ── Helpers ───────────────────────────────────────────────────────────── */

    const construirUrl = (slug) => {
        const base = window.location.pathname.includes('/pages/') ? '../' : '';
        return `${base}pages/detalhes-jogo.html?slug=${encodeURIComponent(slug || '')}`;
    };

    const construirImagem = (item) => {
        if (typeof window.obterCaminhoImagem === 'function') {
            return window.obterCaminhoImagem(item?.imagem, item?.slug);
        }
        const base = window.location.pathname.includes('/pages/') ? '../' : '';
        return `${base}img/naoencontrada.png`;
    };

    const escapar = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[c]));

    const slugAtual = () => {
        try {
            const qs = new URLSearchParams(window.location.search);
            return qs.get('slug') || '';
        } catch { return ''; }
    };

    /* ── Renderização ──────────────────────────────────────────────────────── */

    const montarItem = (item) => {
        const url = construirUrl(item.slug);
        const img = construirImagem(item);
        const nome = escapar(item.nome || 'Jogo');
        const preco = escapar(item.preco || PRECO_FALLBACK);
        const ativo = item.slug && item.slug === slugAtual();
        return `
            <li class="historico-item${ativo ? ' historico-item--ativo' : ''}"
                data-slug="${escapar(item.slug)}">
                <a class="historico-link" href="${url}" title="${nome}">
                    <span class="historico-thumb-wrap">
                        <img class="historico-thumb" src="${img}" alt="" loading="lazy"
                             onerror="this.onerror=null;this.src='${escapar(construirImagem({}))}'">
                    </span>
                    <span class="card-mini-info">
                        <span class="card-mini-titulo">${nome}</span>
                        <span class="card-mini-preco">${preco}</span>
                    </span>
                </a>
                <button type="button" class="btn-fechar"
                        data-acao="remover"
                        data-slug="${escapar(item.slug)}"
                        aria-label="Remover ${nome} do histórico">×</button>
            </li>`;
    };

    const localizarMain = () =>
        document.querySelector('main.conteudo-principal') ||
        document.querySelector('main') ||
        document.querySelector('.conteudo-principal');

    const renderizar = () => {
        const main = localizarMain();
        if (!main) return;

        const itens = ler();
        const existente = document.getElementById(ID_CONTAINER);

        if (!itens.length) {
            existente?.remove();
            return;
        }

        let container = existente;
        if (!container) {
            container = document.createElement('section');
            container.id = ID_CONTAINER;
            container.className = 'historico';
            container.setAttribute('aria-label', 'Jogos acessados recentemente');
            container.innerHTML = `
                <header class="historico-header">
                    <h2 class="historico-titulo">
                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                        Acessados recentemente
                    </h2>
                    <button type="button" class="historico-limpar" data-acao="limpar"
                            aria-label="Limpar histórico">
                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        <span>Limpar</span>
                    </button>
                </header>
                <ul id="${ID_LISTA}" class="historico-lista"></ul>`;
            main.insertBefore(container, main.firstChild);
        }

        const lista = container.querySelector(`#${ID_LISTA}`);
        if (!lista) return;
        lista.innerHTML = itens.map(montarItem).join('');
    };

    /* ── Delegação de eventos (uma vez) ────────────────────────────────────── */

    const ligarEventos = () => {
        document.addEventListener('click', (evento) => {
            const botao = evento.target.closest('[data-acao]');
            if (!botao) return;
            const acao = botao.getAttribute('data-acao');
            if (acao === 'remover') {
                /* Impede que o clique propague para o <a> do card. */
                evento.preventDefault();
                evento.stopPropagation();
                remover(botao.getAttribute('data-slug'));
            } else if (acao === 'limpar') {
                evento.preventDefault();
                evento.stopPropagation();
                limpar();
            }
        });

        window.addEventListener('storage', (evento) => {
            if (evento.key === CHAVE) renderizar();
        });
    };

    /* ── Bootstrap ─────────────────────────────────────────────────────────── */

    const inicializar = () => {
        ligarEventos();
        renderizar();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar, { once: true });
    } else {
        inicializar();
    }

    window.Historico = { adicionar, remover, limpar, listar, renderizar };
})();