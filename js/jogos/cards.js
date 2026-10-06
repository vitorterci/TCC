/* ─── CARDS DE JOGOS — DADOS E RENDERIZAÇÃO COMPARTILHADOS ──────────────── */

(function () {
    function escaparHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizarEtaria(valor) {
        const texto = String(valor ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

        if (!texto) return { codigo: 'nao-informada', rotulo: 'N/I', classe: 'etaria-nao-informada' };
        if (texto === 'l' || texto === '0' || texto.includes('livre')) {
            return { codigo: 'L', rotulo: 'L', classe: 'etaria-L' };
        }

        const correspondencia = texto.match(/(?:^|[^0-9])(10|12|14|16|18)(?:[^0-9]|$)/);
        if (correspondencia) {
            return {
                codigo: correspondencia[1],
                rotulo: correspondencia[1],
                classe: `etaria-${correspondencia[1]}`
            };
        }

        return { codigo: 'nao-informada', rotulo: 'N/I', classe: 'etaria-nao-informada' };
    }

    function obterInformacoesPreco(jogo) {
        const comparacao = typeof window.obterPrecoComparado === 'function'
            ? window.obterPrecoComparado(jogo)
            : { valor: null, classe: 'indisponivel', texto: 'Preço indisponível' };
        const precoNumerico = comparacao.valor;

        if (precoNumerico === null || precoNumerico === undefined) {
            return { classe: 'indisponivel', texto: 'Preço indisponível' };
        }

        if (precoNumerico === 0) {
            return { classe: 'gratis', texto: 'Grátis' };
        }

        return { classe: comparacao.classe, texto: comparacao.texto };
    }

    function criarMarkupCard(jogo, indice = 0, opcoes = {}) {
        const paginaBase = opcoes.paginaBase ?? '';
        const prefixoLinks = opcoes.prefixoLinks ?? paginaBase;
        const caminhoFallback = opcoes.caminhoFallback ?? `${paginaBase}img/naoencontrada.png`;
        const contextual = Boolean(opcoes.contextual);
        const incluirMenu = opcoes.incluirMenu ?? contextual;
        const preco = obterInformacoesPreco(jogo);
        const nome = escaparHtml(jogo.nome || 'Jogo sem nome');
        const descricao = escaparHtml(jogo.descricao || (contextual ? 'Descrição não informada.' : 'Descrição não disponível.'));
        const categoria = escaparHtml(jogo.categoria || 'Sem categoria');
        const genero = escaparHtml(jogo.genero || 'Não informado');
        const plataforma = escaparHtml(jogo.plataforma || 'Não informada');
        const informacaoEtaria = normalizarEtaria(jogo.etaria);
        const etaria = escaparHtml(informacaoEtaria.rotulo);
        const ano = escaparHtml(jogo.ano || '—');
        const status = escaparHtml(jogo.status || 'Não informado');
        const imagem = escaparHtml(window.obterCaminhoImagem(jogo.img, jogo.slug));
        const classeStatus = String(jogo.status || '').toLowerCase() === 'disponivel'
            ? 'status-disponivel'
            : 'status-em-breve';
        const urlDetalhes = `${prefixoLinks}pagina.html?id=${encodeURIComponent(jogo.id)}`;
        const urlBiblioteca = `${prefixoLinks}biblioteca.html`;
        const atributosInteracao = contextual
            ? `role="button" tabindex="0" aria-expanded="false" aria-controls="painel-contextual-jogo" aria-label="Abrir informações de ${nome}"`
            : '';
        const overlayCensura = typeof jogoDeveSerCensurado === 'function' && jogoDeveSerCensurado(jogo)
            ? `
        <div class="card-censura-overlay">
            <i class="fas fa-lock"></i>
            <h3 class="card-censura-titulo">🔒 Conteúdo restrito</h3>
            <p class="card-censura-sub">Classificação: ${etaria} anos</p>
        </div>`
            : '';
        const menu = incluirMenu
            ? '<button class="menu-trigger" type="button" aria-label="Abrir menu" aria-expanded="false">⋮</button>'
            : '';
        const icones = contextual ? '' : ' aria-hidden="true"';

        return `
        <article class="card-jogo animar-entrada${contextual ? ' card-jogo--contextual' : ''}" data-id="${escaparHtml(jogo.id)}" style="animation-delay:${indice * 0.08}s" ${atributosInteracao}>
            ${overlayCensura}
            <div class="card-jogo-sidebar">
                <img src="${imagem}" alt="Capa de ${nome}" loading="lazy" onerror="this.onerror=null; this.src='${caminhoFallback}'">
                <span class="badge-etaria ${informacaoEtaria.classe}">${etaria}</span>
                ${menu}
            </div>
            <div class="card-jogo-content">
                <h2 class="card-jogo-titulo"><a href="${urlDetalhes}" title="Ver detalhes de ${nome}">${nome}</a></h2>
                <p class="card-jogo-descricao">${descricao}</p>
                <div class="card-jogo-info">
                    <a href="${urlBiblioteca}?categoria=${encodeURIComponent(jogo.categoria || '')}" class="info-tag link-tag" title="Filtrar por categoria ${categoria}"><i class="fas fa-tag"${icones}></i> ${categoria}</a>
                    <a href="${urlBiblioteca}?genero=${encodeURIComponent(jogo.genero || '')}" class="info-tag link-tag" title="Filtrar por gênero ${genero}"><i class="fas fa-gamepad"${icones}></i> ${genero}</a>
                    <a href="${urlBiblioteca}?plataforma=${encodeURIComponent(jogo.plataforma || '')}" class="info-tag link-tag" title="Filtrar por plataforma ${plataforma}"><i class="fas fa-desktop"${icones}></i> ${plataforma}</a>
                    <span class="info-tag"><i class="fas fa-shield-alt"${icones}></i> ${etaria}</span>
                    <span class="info-tag"><i class="fas fa-calendar"${icones}></i> ${ano}</span>
                    <span class="info-tag info-preco ${preco.classe}"><i class="fas fa-dollar-sign"${icones}></i> ${preco.texto}</span>
                    <span class="info-tag info-status ${classeStatus}"><i class="fas fa-circle"${icones}></i> ${status}</span>
                </div>
                <button class="botao-detalhes" data-id="${escaparHtml(jogo.id)}" type="button"><i class="fas fa-chevron-right"${icones}></i> Ver detalhes</button>
            </div>
        </article>`;
    }

    window.escaparHtml = escaparHtml;
    window.normalizarEtaria = normalizarEtaria;
    window.obterInformacoesPreco = obterInformacoesPreco;
    window.criarMarkupCard = criarMarkupCard;
})();
