/* ─── BIBLIOTECA — LISTAGEM, CARDS E PAGINAÇÃO ─────────────────────────────── */

const ITENS_POR_PAGINA = 10;
let paginaAtual = 1;
let totalPaginas = 1;

const gradeJogosBiblioteca = document.getElementById('gradeJogosBiblioteca');
const contadorCatalogo = document.getElementById('catalogoContador');
const paginacaoCatalogo = document.getElementById('catalogoPaginacao');
const botaoPaginaAnterior = document.getElementById('btnPaginaAnterior');
const botaoPaginaProxima = document.getElementById('btnPaginaProxima');
const informacaoPagina = document.getElementById('infoPagina');
let carregamentoPrecos = null;

function garantirComparadorDePrecos() {
    if (typeof window.carregarPrecosDosJogos === 'function') {
        return Promise.resolve();
    }

    if (carregamentoPrecos) return carregamentoPrecos;

    carregamentoPrecos = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = '../js/precos/precos.js';
        script.onload = resolve;
        script.onerror = () => reject(new Error('Não foi possível carregar o comparador de preços.'));
        document.head.appendChild(script);
    });

    return carregamentoPrecos;
}

function criarCardBiblioteca(jogo, indice) {
    return window.criarMarkupCard(jogo, indice, { paginaBase: '../', prefixoLinks: '', incluirMenu: false });
}

function atualizarContadorCatalogo(totalItens, inicio, fim) {
    if (!contadorCatalogo) return;

    if (totalItens === 0) {
        contadorCatalogo.textContent = '';
        return;
    }

    contadorCatalogo.textContent = `Exibindo ${inicio + 1}–${fim} de ${totalItens} jogos`;
}

function atualizarPaginacao() {
    if (!paginacaoCatalogo || !botaoPaginaAnterior || !botaoPaginaProxima || !informacaoPagina) return;

    const possuiMaisDeUmaPagina = totalPaginas > 1;
    paginacaoCatalogo.hidden = !possuiMaisDeUmaPagina;
    informacaoPagina.textContent = `Página ${paginaAtual} de ${totalPaginas}`;
    botaoPaginaAnterior.disabled = paginaAtual <= 1;
    botaoPaginaProxima.disabled = paginaAtual >= totalPaginas;
    botaoPaginaAnterior.setAttribute('aria-label', 'Ir para a página anterior');
    botaoPaginaProxima.setAttribute('aria-label', 'Ir para a próxima página');
}

function renderizarEstadoVazio() {
    if (!gradeJogosBiblioteca) return;

    gradeJogosBiblioteca.innerHTML = `
        <div class="estado-biblioteca" role="status">
            <i class="fas fa-search" aria-hidden="true"></i>
            <p>Nenhum jogo encontrado com os filtros selecionados.</p>
            <small>Tente remover um filtro ou alterar o termo de busca.</small>
        </div>`;
}

function renderizarJogos(lista) {
    if (!gradeJogosBiblioteca) return;

    const totalItens = lista.length;
    totalPaginas = Math.max(1, Math.ceil(totalItens / ITENS_POR_PAGINA));
    if (paginaAtual > totalPaginas) paginaAtual = totalPaginas;

    const inicio = (paginaAtual - 1) * ITENS_POR_PAGINA;
    const jogosDaPagina = lista.slice(inicio, inicio + ITENS_POR_PAGINA);

    if (jogosDaPagina.length === 0) {
        renderizarEstadoVazio();
        atualizarContadorCatalogo(0, 0, 0);
        atualizarPaginacao();
        return;
    }

    gradeJogosBiblioteca.innerHTML = jogosDaPagina.map(criarCardBiblioteca).join('');
    atualizarContadorCatalogo(totalItens, inicio, inicio + jogosDaPagina.length);
    atualizarPaginacao();
    inicializarCardsExpansiveis({ modo: 'biblioteca', jogos: listaDeJogos });
}

function reiniciarPaginacao() {
    paginaAtual = 1;
}

function configurarPaginacao() {
    botaoPaginaAnterior?.addEventListener('click', () => {
        if (paginaAtual <= 1) return;
        paginaAtual -= 1;
        renderizarJogos(jogosFiltradosAtuais);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    botaoPaginaProxima?.addEventListener('click', () => {
        if (paginaAtual >= totalPaginas) return;
        paginaAtual += 1;
        renderizarJogos(jogosFiltradosAtuais);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

async function carregarJogosBiblioteca() {
    try {
        const resposta = await fetch('../php/api/jogo.php?acao=listar');
        if (!resposta.ok) throw new Error('Erro na resposta do servidor');

        const dados = await resposta.json();
        if (!dados.success) throw new Error(dados.message || 'Não foi possível carregar os jogos');

        listaDeJogos = Array.isArray(dados.jogos) ? dados.jogos : [];
        await garantirComparadorDePrecos();
        await window.carregarPrecosDosJogos(listaDeJogos, '../php/api/precos.php');
        aplicarFiltros();
    } catch (erro) {
        console.error('Erro ao carregar jogos da biblioteca:', erro);
        if (!gradeJogosBiblioteca) return;

        gradeJogosBiblioteca.innerHTML = `
            <div class="estado-biblioteca estado-biblioteca--erro" role="alert">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                <p>Não foi possível carregar a biblioteca no momento.</p>
                <small>Verifique a conexão com o servidor e tente novamente.</small>
                <button class="botao-detalhes" type="button" id="botaoTentarBiblioteca">Tentar novamente</button>
            </div>`;

        document.getElementById('botaoTentarBiblioteca')?.addEventListener('click', carregarJogosBiblioteca);
        atualizarContadorCatalogo(0, 0, 0);
        if (paginacaoCatalogo) paginacaoCatalogo.hidden = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    configurarPaginacao();
    carregarJogosBiblioteca();
});
