/**
 * Projeto TCC - Game Search
 * Script principal para controle de interface, filtros e carrossel
 */

// Dados de exemplo para os jogos
const listaDeJogos = [
    { id: 1, nome: "Minecraft", categoria: "indie", img: "img/minecraft.jpg" },
    { id: 2, nome: "The Escapists", categoria: "estratégia", img: "img/escapists.png" },
    { id: 3, nome: "Graveyard Keeper", categoria: "indie", img: "img/graveyard_keeper.jpg" },
    { id: 4, nome: "Towerborne", categoria: "ação", img: "img/towerborne.jpg" },
    { id: 5, nome: "Hades", categoria: "roguelike", img: "img/hades.jpg" },
    { id: 6, nome: "FIFA 23", categoria: "esporte", img: "img/fifa.jpg" },
    { id: 7, nome: "Resident Evil", categoria: "terror", img: "img/resident.jpg" },
    { id: 8, nome: "Among Us", categoria: "multiplayer", img: "img/among.jpg" }
];

// Dados para o carrossel
const slidesDestaque = [
    { type: "image", src: "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1000&q=80" },
    { type: "image", src: "https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1000&q=80" },
    { type: "image", src: "https://images.unsplash.com/photo-1552820728-8b83bb6b773f?auto=format&fit=crop&w=1000&q=80" }
];

// Seleção de elementos do DOM
const gradeJogos = document.getElementById('grade-jogos');
const campoBusca = document.getElementById('campo-busca');
const botoesFiltro = document.querySelectorAll('.botao-filtro');
const carrosselTrilho = document.getElementById('carrossel-trilho');
const carrosselIndicadores = document.getElementById('carrossel-indicadores');

// Estado da aplicação
let indiceCarrosselAtual = 0;
let categoriaAtiva = "todos";
let termoPesquisa = "";

/**
 * Renderiza a grade de jogos baseada em uma lista
 * @param {Array} lista - Lista de objetos de jogos
 */
function renderizarJogos(lista) {
    if (!gradeJogos) return;

    if (lista.length === 0) {
        gradeJogos.innerHTML = '<p style="grid-column: 1/-1; text-align: center;">Nenhum jogo encontrado.</p>';
        return;
    }

    gradeJogos.innerHTML = lista.map(jogo => `
        <article class="card-jogo" title="${jogo.nome}">
            <img src="${jogo.img}" alt="${jogo.nome}" class="card-jogo__imagem" onerror="this.src='https://via.placeholder.com/200x300?text=${jogo.nome}'">
        </article>
    `).join('');
}

/**
 * Filtra a lista de jogos com base na categoria e termo de busca
 */
function aplicarFiltros() {
    const jogosFiltrados = listaDeJogos.filter(jogo => {
        const correspondeCategoria = categoriaAtiva === "todos" || jogo.categoria === categoriaAtiva;
        const correspondeBusca = jogo.nome.toLowerCase().includes(termoPesquisa.toLowerCase());
        return correspondeCategoria && correspondeBusca;
    });
    renderizarJogos(jogosFiltrados);
}

/**
 * Inicializa o carrossel de destaques
 */
function inicializarCarrossel() {
    if (!carrosselTrilho || !carrosselIndicadores) return;

    // Criar slides
    carrosselTrilho.innerHTML = slidesDestaque.map((slide, index) => `
        <div class="carrossel__item">
            <img src="${slide.src}" alt="Destaque ${index + 1}">
        </div>
    `).join('');

    // Criar indicadores
    carrosselIndicadores.innerHTML = slidesDestaque.map((_, index) => `
        <button class="indicador ${index === 0 ? 'indicador--ativo' : ''}" data-index="${index}"></button>
    `).join('');

    // Eventos dos indicadores
    document.querySelectorAll('.indicador').forEach(botao => {
        botao.addEventListener('click', () => {
            const index = parseInt(botao.getAttribute('data-index'));
            moverCarrossel(index);
        });
    });

    // Auto-play
    setInterval(() => {
        let proximo = (indiceCarrosselAtual + 1) % slidesDestaque.length;
        moverCarrossel(proximo);
    }, 5000);
}

/**
 * Move o carrossel para um índice específico
 * @param {number} indice 
 */
function moverCarrossel(indice) {
    indiceCarrosselAtual = indice;
    carrosselTrilho.style.transform = `translateX(-${indice * 100}%)`;
    
    // Atualizar indicadores
    document.querySelectorAll('.indicador').forEach((ind, i) => {
        ind.classList.toggle('indicador--ativo', i === indice);
    });
}

// Event Listeners
if (campoBusca) {
    campoBusca.addEventListener('input', (e) => {
        termoPesquisa = e.target.value;
        aplicarFiltros();
    });
}

botoesFiltro.forEach(botao => {
    botao.addEventListener('click', () => {
        // Atualizar UI dos botões
        botoesFiltro.forEach(b => b.classList.remove('botao-filtro--ativo'));
        botao.classList.add('botao-filtro--ativo');

        // Atualizar estado e filtrar
        categoriaAtiva = botao.getAttribute('data-categoria');
        aplicarFiltros();
    });
});

// Inicialização ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    renderizarJogos(listaDeJogos);
    inicializarCarrossel();
    console.log("Aplicação Game Search inicializada com sucesso!");
});
