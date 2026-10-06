/*
 * Filtros compartilhados do catálogo e da Biblioteca.
 * Este arquivo centraliza seleção, aplicação, remoção e limpeza dos filtros.
 */

let listaDeJogos = [];
let categoriaAtiva = 'todos';
let plataformaAtiva = 'todos';
let generoAtivo = 'todos';
let etariaAtiva = 'todos';
let anoAtivo = 'todos';
let precoAtivo = 'todos';
let termoPesquisa = '';
let jogosFiltradosAtuais = [];

const mapaFiltros = {
    'menu-categorias': 'categoria',
    'menu-plataforma': 'plataforma',
    'menu-genero': 'genero',
    'menu-etaria': 'etaria',
    'menu-ano': 'ano',
    'menu-preco': 'preco'
};

const campoBusca = document.querySelector('.campo-busca');
let termoInicialBusca = Busca.obterTermoDaURL();
if (campoBusca && termoInicialBusca) campoBusca.value = termoInicialBusca;
termoPesquisa = termoInicialBusca;

function obterCodigoEtariaFiltro(valor) {
    const texto = Busca.normalizarTexto(valor);
    if (!texto) return 'nao-informada';
    if (texto === 'l' || texto === '0' || texto.includes('livre')) return 'L';

    const correspondencia = texto.match(/(?:^|[^0-9])(10|12|14|16|18)(?:[^0-9]|$)/);
    return correspondencia ? correspondencia[1] : 'nao-informada';
}

function obterFiltrosAtivos() {
    return {
        categoria: categoriaAtiva,
        plataforma: plataformaAtiva,
        genero: generoAtivo,
        etaria: etariaAtiva,
        ano: anoAtivo,
        preco: precoAtivo
    };
}

function definirFiltroAtivo(menuId, valor) {
    const propriedade = mapaFiltros[menuId];
    if (!propriedade) return;
    const valorNormalizado = Busca.normalizarTexto(valor) || 'todos';

    if (propriedade === 'categoria') categoriaAtiva = valorNormalizado;
    if (propriedade === 'plataforma') plataformaAtiva = valorNormalizado;
    if (propriedade === 'genero') generoAtivo = valorNormalizado;
    if (propriedade === 'etaria') etariaAtiva = valor === 'L' ? 'L' : valorNormalizado;
    if (propriedade === 'ano') anoAtivo = valorNormalizado;
    if (propriedade === 'preco') precoAtivo = valorNormalizado;
}

function plataformaCorresponde(valor, filtro) {
    const plataforma = Busca.normalizarTexto(valor);
    if (!plataforma || filtro === 'todos') return true;
    const partes = plataforma.split(/[,/|+]+/).map(parte => parte.trim()).filter(Boolean);
    return partes.some(parte => parte === filtro || parte.startsWith(`${filtro} `));
}

function obterPrecoFiltro(jogo) {
    const precoComparado = typeof window.obterPrecoComparado === 'function'
        ? window.obterPrecoComparado(jogo)
        : { valor: null };
    return precoComparado.valor;
}

function precoCorresponde(preco, filtro) {
    if (filtro === 'todos') return true;
    if (filtro === 'gratis') return false;
    if (filtro === 'baixo') return preco > 0 && preco <= 50;
    if (filtro === 'medio') return preco > 50 && preco <= 150;
    if (filtro === 'alto') return preco > 150;
    return true;
}

function aplicarFiltros() {
    const termo = Busca.normalizarTexto(termoPesquisa);
    const filtros = obterFiltrosAtivos();

    jogosFiltradosAtuais = listaDeJogos
        .map((jogo, indice) => ({ jogo, indice, relevancia: Busca.obterRelevanciaBusca(jogo, termo) }))
        .filter(({ relevancia }) => !termo || relevancia >= 0)
        .filter(({ jogo }) => {
            const categoriaCorrespondente = filtros.categoria === 'todos'
                || Busca.normalizarTexto(jogo.categoria) === filtros.categoria;
            const plataformaCorrespondente = plataformaCorresponde(jogo.plataforma, filtros.plataforma);
            const generoCorrespondente = filtros.genero === 'todos'
                || Busca.normalizarTexto(jogo.genero) === filtros.genero;
            const etariaCorrespondente = filtros.etaria === 'todos'
                || obterCodigoEtariaFiltro(jogo.etaria) === filtros.etaria;
            const anoCorrespondente = filtros.ano === 'todos'
                || String(jogo.ano ?? '') === filtros.ano;
            const precoCorrespondente = precoCorresponde(obterPrecoFiltro(jogo), filtros.preco);

            return categoriaCorrespondente && plataformaCorrespondente && generoCorrespondente
                && etariaCorrespondente && anoCorrespondente && precoCorrespondente;
        })
        .sort((primeiro, segundo) => segundo.relevancia - primeiro.relevancia || primeiro.indice - segundo.indice)
        .map(({ jogo }) => jogo);

    if (typeof reiniciarPaginacao === 'function') reiniciarPaginacao();
    if (typeof renderizarJogos === 'function') renderizarJogos(jogosFiltradosAtuais);
    atualizarBotoesFiltro();
}

function atualizarBotoesFiltro() {
    Object.entries(mapaFiltros).forEach(([menuId, propriedade]) => {
        const botao = document.querySelector(`.botao-filtro-principal[data-menu="${menuId}"]`);
        if (!botao) return;
        const filtros = obterFiltrosAtivos();
        botao.classList.toggle('ativo', filtros[propriedade] !== 'todos');
        botao.setAttribute('aria-expanded', document.getElementById(menuId)?.classList.contains('show') ? 'true' : 'false');
    });
}

function resetarFiltro(menuId) {
    const menu = document.getElementById(menuId);
    const opcaoTodos = menu?.querySelector('.opcao-filtro[data-filtro="todos"]');
    if (!menu || !opcaoTodos) return;

    menu.querySelectorAll('.opcao-filtro').forEach(opcao => opcao.classList.remove('selecionada'));
    opcaoTodos.classList.add('selecionada');
    definirFiltroAtivo(menuId, 'todos');
    menu.classList.remove('show');
    aplicarFiltros();
}

function inicializarFiltros() {
    document.querySelectorAll('.botao-filtro-principal').forEach(botao => {
        if (botao.dataset.filtroInicializado === 'true') return;
        botao.dataset.filtroInicializado = 'true';
        botao.addEventListener('click', evento => {
            evento.stopPropagation();
            const menu = document.getElementById(botao.dataset.menu);
            if (!menu) return;
            document.querySelectorAll('.menu-flutuante.show').forEach(aberto => {
                if (aberto !== menu) aberto.classList.remove('show');
            });
            const menuAberto = menu.classList.toggle('show');
            botao.setAttribute('aria-expanded', menuAberto ? 'true' : 'false');
            atualizarBotoesFiltro();
        });
    });

    document.querySelectorAll('.btn-reset-filtro').forEach(botao => {
        if (botao.dataset.filtroInicializado === 'true') return;
        botao.dataset.filtroInicializado = 'true';
        botao.addEventListener('click', evento => {
            evento.stopPropagation();
            resetarFiltro(botao.dataset.menu);
        });
    });

    document.querySelectorAll('.opcao-filtro[data-filtro]').forEach(opcao => {
        if (opcao.dataset.filtroInicializado === 'true') return;
        opcao.dataset.filtroInicializado = 'true';
        opcao.addEventListener('click', evento => {
            evento.stopPropagation();
            const menu = opcao.closest('.menu-flutuante');
            if (!menu) return;
            menu.querySelectorAll('.opcao-filtro').forEach(item => item.classList.remove('selecionada'));
            opcao.classList.add('selecionada');
            definirFiltroAtivo(menu.id, opcao.dataset.filtro);
            menu.classList.remove('show');
            aplicarFiltros();
        });
    });

    if (campoBusca && campoBusca.dataset.filtroInicializado !== 'true') {
        campoBusca.dataset.filtroInicializado = 'true';
        campoBusca.addEventListener('input', () => {
            termoPesquisa = campoBusca.value;
            aplicarFiltros();
        });
    }

    atualizarBotoesFiltro();
}

document.addEventListener('click', () => {
    document.querySelectorAll('.menu-flutuante.show').forEach(menu => menu.classList.remove('show'));
    atualizarBotoesFiltro();
});

document.addEventListener('keydown', evento => {
    if (evento.key !== 'Escape') return;
    document.querySelectorAll('.menu-flutuante.show').forEach(menu => menu.classList.remove('show'));
    atualizarBotoesFiltro();
});

document.addEventListener('DOMContentLoaded', inicializarFiltros);
inicializarFiltros();
