/* ─── ATALHO GLOBAL E PERSONALIZÁVEL DE TEMA ─────────────────────────────── */

(() => {
    if (window.__atalhoTemaInicializado) return;
    window.__atalhoTemaInicializado = true;

    const chaves = {
        ativo: 'pref_atalho_tema_ativo',
        estilo: 'pref_atalho_tema',
        posicao: 'pref_atalho_tema_posicao',
        tamanho: 'pref_atalho_tema_tamanho',
        icone: 'pref_icone_tema'
    };
    const opcoes = {
        estilo: ['cristal-magico', 'circular', 'sol-lua', 'gif', 'minimalista', 'imagem', 'padrao-classico'],
        posicao: ['superior-direito', 'inferior-direito', 'inferior-esquerdo', 'barra-lateral-inferior'],
        tamanho: ['pequeno', 'medio', 'grande']
    };

    // Ícones de estado do tema — usados pelo estilo "gif" (Pac-Man).
    const ICONES_ESTADO_TEMA = ['claro.gif', 'escuro.gif'];
    const ICONE_PADRAO = 'pixel bloc.png';

    // Catálogo de personagens disponíveis em img/tema/.
    // Exclui claro.gif e escuro.gif (Pac-Man usa o estilo "gif").
    const PERSONAGENS = [
        { nome: 'Pixel Bloc', arquivo: 'pixel bloc.png' },
        { nome: 'Sonic',      arquivo: 'sonic.gif' },
        { nome: 'Mario',      arquivo: 'mario.gif' },
        { nome: 'Eggman',     arquivo: 'eggman.gif' },
        { nome: 'Game Boy',   arquivo: 'gameboy.gif' },
        { nome: 'Moeda',      arquivo: 'moeda.gif' },
        { nome: 'Andrey',     arquivo: 'andrey.gif' },
        { nome: 'Gnomo', arquivo:'gnomo.gif'},
       { nome: 'Vitor', arquivo:'vitor.gif'}
    ];

    // ─── CONFIGURAÇÃO INDIVIDUAL POR ESTILO/PERSONAGEM ────────────────────
    // Cada entrada ajusta a área visual interna da imagem.
    // - `escala`: multiplicador sobre a área base (1 = padrão).
    // - `ajusteX`: deslocamento horizontal em % do próprio ícone.
    // - `ajusteY`: deslocamento vertical em % do próprio ícone.
    // - `proporcao`: proporção alvo dentro do container (width/height).
    //
    // IMPORTANTE: fora da sidebar, Pac-Man e Game Boy continuam usando
    // escala=1 com proporção. Dentro da sidebar, uma regra CSS específica
    // faz o botão ocupar 100% da largura útil (ver atalho-tema.css).
    const CONFIG_ICONES = {
        // Pac-Man é largo e baixo: proporção horizontal preservada.
        pacman:  { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '4 / 1' },
        // Game Boy é alto e estreito: proporção vertical preservada.
        gameboy: { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1.4' },
        // Sonic é quadrado: escala padrão.
        sonic:   { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1' },
        // Mario é quadrado: escala padrão.
        mario:   { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1' },
        // Eggman é ligeiramente largo.
        eggman:  { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1.3 / 1' },
        // Moeda é quadrada.
        moeda:   { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1' },
        // Pixel Bloc é quadrado.
        'pixel-bloc':  { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1' },
        // Video Games é largo.
        'video-games': { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1.6 / 1' },
        // Fallback para qualquer ícone não mapeado.
        _default: { escala: 1, ajusteX: 0, ajusteY: 0, proporcao: '1 / 1' }
    };

    // Mapeia o nome do arquivo real para a chave em CONFIG_ICONES.
    const chaveConfigPorArquivo = (arquivo) => {
        const nome = String(arquivo || '').toLowerCase();
        if (nome === 'claro.gif' || nome === 'escuro.gif') return 'pacman';
        if (nome === 'gameboy.gif')   return 'gameboy';
        if (nome === 'sonic.gif')     return 'sonic';
        if (nome === 'mario.gif')     return 'mario';
        if (nome === 'eggman.gif')    return 'eggman';
        if (nome === 'moeda.gif')     return 'moeda';
        if (nome === 'pixel bloc.png') return 'pixel-bloc';
        if (nome === 'tarturuga.gif') return 'tarturuga';
        return '_default';
    };

    const obterConfigIcone = (arquivo) => {
        const chave = chaveConfigPorArquivo(arquivo);
        return CONFIG_ICONES[chave] || CONFIG_ICONES._default;
    };

    const script = document.currentScript;

    // Retorna a URL base da RAIZ do projeto.
    const obterBaseRaiz = () => {
        const baseMeta = document.querySelector('meta[name="base-path"]');
        if (baseMeta && baseMeta.content.trim()) {
            return new URL(baseMeta.content.trim(), document.baseURI);
        }
        const caminho = window.location.pathname;
        if (caminho.includes('/pages/admin/')) return new URL('../../', document.baseURI);
        if (caminho.includes('/pages/'))       return new URL('../', document.baseURI);
        return new URL('./', document.baseURI);
    };
    const baseRaiz = obterBaseRaiz();

    // Monta a URL absoluta de uma imagem em img/tema/.
    const urlIconeTema = (nomeArquivo) => {
        const nome = String(nomeArquivo || '').trim();
        const arquivo = nome || ICONE_PADRAO;
        return new URL(`img/tema/${arquivo}`, baseRaiz).href;
    };

    const gifs = {
        claro:  urlIconeTema('claro.gif'),
        escuro: urlIconeTema('escuro.gif')
    };
    const cristalMagico = urlIconeTema('moeda.gif');

    // Normaliza o nome do ícone salvo.
    const normalizarIcone = (valor) => {
        const nome = String(valor || '').trim();
        if (!nome) return ICONE_PADRAO;
        if (nome.includes('/') || nome.includes('\\')) return ICONE_PADRAO;
        if (ICONES_ESTADO_TEMA.includes(nome.toLowerCase())) return ICONE_PADRAO;
        return nome;
    };

    const ler = (campo, padrao) => {
        const valor = localStorage.getItem(chaves[campo]);
        return opcoes[campo]?.includes(valor) ? valor : padrao;
    };
    const configuracao = () => ({
        ativo: localStorage.getItem(chaves.ativo) !== 'false',
        estilo: ler('estilo', 'gif'),
        posicao: ler('posicao', 'superior-direito'),
        tamanho: ler('tamanho', 'medio'),
        icone: normalizarIcone(localStorage.getItem(chaves.icone))
    });
    const temaAtual = () => {
        const salvo = localStorage.getItem('pref_tema');
        if (salvo === 'claro' || salvo === 'escuro') return salvo;
        if (salvo === 'sistema') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'escuro' : 'claro';
        }
        return document.documentElement.classList.contains('tema-escuro') ? 'escuro' : 'claro';
    };
    // Comportamento sempre CLARO ↔ ESCURO.
    const proximoTema = (tema) => (tema === 'escuro' ? 'claro' : 'escuro');

    const conteudoEstilo = (estilo, tema, icone) => {
        if (estilo === 'gif') {
            return `<img class="atalho-tema-gif" src="${gifs[tema]}" alt="" draggable="false">`;
        }
        if (estilo === 'circular') {
            return '<span class="atalho-tema-circulo" aria-hidden="true"><span></span></span>';
        }
        if (estilo === 'sol-lua') {
            return '<span class="atalho-tema-sol-lua" aria-hidden="true"><span class="atalho-tema-sol">☀</span><span class="atalho-tema-lua">☾</span></span>';
        }
        if (estilo === 'minimalista') {
            return '<span class="atalho-tema-minimalista" aria-hidden="true"></span>';
        }
        if (estilo === 'imagem') {
            return `<img class="atalho-tema-imagem" src="${urlIconeTema(icone)}" alt="" draggable="false">`;
        }
      if (estilo === 'padrao-classico') {
            return `
                <span class="neon-toggle-trilha" aria-hidden="true">
                    <span class="neon-toggle-lado neon-toggle-claro">
                        <span class="neon-toggle-texto">DAY MODE</span>
                    </span>
                    <span class="neon-toggle-lado neon-toggle-escuro">
                        <span class="neon-toggle-texto">NIGHT MODE</span>
                    </span>
                    <span class="neon-toggle-slider">
                        <svg class="neon-toggle-icone icone-sol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="12" y1="2" x2="12" y2="4"></line>
                            <line x1="12" y1="20" x2="12" y2="22"></line>
                            <line x1="4.93" y1="4.93" x2="6.34" y2="6.34"></line>
                            <line x1="17.66" y1="17.66" x2="19.07" y2="19.07"></line>
                            <line x1="2" y1="12" x2="4" y2="12"></line>
                            <line x1="20" y1="12" x2="22" y2="12"></line>
                            <line x1="4.93" y1="19.07" x2="6.34" y2="17.66"></line>
                            <line x1="17.66" y1="6.34" x2="19.07" y2="4.93"></line>
                        </svg>
                        <svg class="neon-toggle-icone icone-lua" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </span>
                </span>`;
        }
        return `<img class="atalho-tema-cristal-img" src="${cristalMagico}" alt="" draggable="false">`;
    };
    const criarBotao = () => {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'atalho-tema atalho-tema-global';
        botao.id = 'atalhoTemaGlobal';
        botao.setAttribute('data-atalho-tema-global', '');
        botao.addEventListener('click', () => {
            const config = configuracao();
            if (!window.Preferencias || typeof window.Preferencias.aplicarTema !== 'function') return;
            const tema = temaAtual();
            const proximo = proximoTema(tema);
            botao.classList.remove('cristal-ativando');
            void botao.offsetWidth;
            botao.classList.add('cristal-ativando');
            window.Preferencias.aplicarTema(proximo);
            sincronizar();
        });
        return botao;
    };
    const atualizarBotao = (botao) => {
        const config = configuracao();
        const tema = temaAtual();
        const proximo = proximoTema(tema);
        const estilos = ['cristal-magico', 'circular', 'sol-lua', 'gif', 'minimalista', 'imagem', 'padrao-classico'];
        botao.classList.remove('atalho-tema-cristal', ...estilos.map(item => `atalho-tema-estilo-${item}`));
        if (config.estilo === 'cristal-magico') botao.classList.add('atalho-tema-cristal');
        botao.classList.add(`atalho-tema-estilo-${config.estilo}`);
        botao.dataset.estilo = config.estilo;
        botao.dataset.posicao = config.posicao;
        botao.dataset.tamanho = config.tamanho;
        botao.dataset.icone = config.icone;
        botao.dataset.temaAtual = localStorage.getItem('pref_tema') === 'sistema' ? 'sistema' : tema;
        botao.dataset.temaEfetivo = tema;
        botao.setAttribute('aria-pressed', tema === 'escuro' ? 'true' : 'false');
        const acao = `modo ${proximo}`;
        botao.setAttribute('aria-label', `Alternar para ${acao}`);
        botao.setAttribute('title', `Alternar tema — ${acao}`);

        // Arquivo real sendo exibido — usado pelo CSS para regras específicas.
        const arquivoAtual = config.estilo === 'gif'
            ? (tema === 'escuro' ? 'escuro.gif' : 'claro.gif')
            : config.icone;
        botao.dataset.arquivo = arquivoAtual;

        // Compensação individual por estilo/personagem (fora da sidebar).
        const cfg = obterConfigIcone(arquivoAtual);
        botao.style.setProperty('--atalho-icon-escala',    cfg.escala);
        botao.style.setProperty('--atalho-icon-ajuste-x',  `${cfg.ajusteX}%`);
        botao.style.setProperty('--atalho-icon-ajuste-y',  `${cfg.ajusteY}%`);
        botao.style.setProperty('--atalho-icon-proporcao', cfg.proporcao);

        const conteudo = conteudoEstilo(config.estilo, tema, config.icone);
        if (botao.innerHTML !== conteudo) botao.innerHTML = conteudo;
    };
    const sincronizar = () => {
        const config = configuracao();
        let botao = document.getElementById('atalhoTemaGlobal');
        if (!config.ativo) {
            botao?.remove();
        } else {
            if (!botao) botao = criarBotao();
            const destino = obterDestino(config);
            if (botao.parentElement !== destino) destino.appendChild(botao);
            atualizarBotao(botao);
        }
    };
    const obterDestino = config => {
        if (config.posicao !== 'barra-lateral-inferior') return document.body;
        return document.querySelector('.barra-lateral') || document.body;
    };

    // API pública.
    const definirIcone = (nomeArquivo) => {
        const nome = normalizarIcone(nomeArquivo);
        localStorage.setItem(chaves.icone, nome);
        sincronizar();
        return nome;
    };
    const obterIcone = () => configuracao().icone;

    const filtrarIconesDisponiveis = (nomes) => {
        if (!Array.isArray(nomes)) return [];
        return nomes
            .map(nome => String(nome || '').trim())
            .filter(nome => nome && !nome.includes('/') && !nome.includes('\\'))
            .filter(nome => !ICONES_ESTADO_TEMA.includes(nome.toLowerCase()));
    };

    const listarPersonagens = () => PERSONAGENS.map(p => ({ ...p }));

    window.AtalhoTema = {
        sincronizar,
        definirIcone,
        obterIcone,
        filtrarIconesDisponiveis,
        listarPersonagens,
        urlIconeTema,
        obterConfigIcone,
        CONFIG_ICONES,
        ICONE_PADRAO,
        ICONES_ESTADO_TEMA,
        PERSONAGENS
    };
    const inicializar = () => sincronizar();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar, { once: true });
    } else {
        inicializar();
    }
    const mediaSistema = window.matchMedia('(prefers-color-scheme: dark)');
    const atualizarSistema = () => {
        if (localStorage.getItem('pref_tema') === 'sistema' || !localStorage.getItem('pref_tema')) sincronizar();
    };
    if (typeof mediaSistema.addEventListener === 'function') mediaSistema.addEventListener('change', atualizarSistema);
    else if (typeof mediaSistema.addListener === 'function') mediaSistema.addListener(atualizarSistema);
    window.addEventListener('storage', evento => {
        if (evento.key === null || Object.values(chaves).includes(evento.key) || evento.key === 'pref_tema') sincronizar();
    });
})();