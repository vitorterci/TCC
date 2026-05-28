/**
 * Projeto TCC - Game Search
 * Script principal para controle de interface, filtros e carrossel multimídia
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

// Dados para o carrossel (Suporta YouTube, Vídeo Local e Imagem)
const slidesDestaque = [
    {
        tipo: "youtube",
        src: "https://youtu.be/fubBetdSZcw?si=Nnp4Hu3O4VtqQS3z"
    },
    {
        tipo: "imagem",
        src: "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1000&q=80"
    },
    {
        tipo: "video",
        src: "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4"
    }
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
let intervaloAutoPlay;

/**
 * Converte URL do YouTube para formato embed amigável
 * @param {string} url 
 * @returns {string}
 */
function obterUrlEmbedYouTube(url) {
    const videoId = url.split('v=')[1] || url.split('/').pop().split('?')[0];
    return `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&loop=1&playlist=${videoId}&controls=0&showinfo=0`;
}

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
 * Inicializa o carrossel de destaques com suporte multimídia
 */
function inicializarCarrossel() {
    if (!carrosselTrilho || !carrosselIndicadores) return;

    // Criar slides dinamicamente
    carrosselTrilho.innerHTML = slidesDestaque.map((slide, index) => {
        let conteudo = '';
        if (slide.tipo === 'youtube') {
            conteudo = `<iframe src="${obterUrlEmbedYouTube(slide.src)}" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
        } else if (slide.tipo === 'video') {
            conteudo = `<video muted loop playsinline><source src="${slide.src}" type="video/mp4"></video>`;
        } else {
            conteudo = `<img src="${slide.src}" alt="Destaque ${index + 1}">`;
        }
        return `<div class="carrossel__item ${index === 0 ? 'carrossel__item--ativo' : ''}">${conteudo}</div>`;
    }).join('');

    // Criar indicadores
    carrosselIndicadores.innerHTML = slidesDestaque.map((_, index) => `
        <button class="indicador ${index === 0 ? 'indicador--ativo' : ''}" data-index="${index}"></button>
    `).join('');

    // Eventos dos indicadores
    document.querySelectorAll('.indicador').forEach(botao => {
        botao.addEventListener('click', () => {
            const index = parseInt(botao.getAttribute('data-index'));
            moverCarrossel(index);
            reiniciarAutoPlay();
        });
    });

    iniciarAutoPlay();
    atualizarEstadoVideos();
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

    // Atualizar classes dos itens para controle de vídeo
    document.querySelectorAll('.carrossel__item').forEach((item, i) => {
        item.classList.toggle('carrossel__item--ativo', i === indice);
    });

    atualizarEstadoVideos();
}

/**
 * Controla a reprodução de vídeos locais baseada na visibilidade
 */
function atualizarEstadoVideos() {
    const itens = document.querySelectorAll('.carrossel__item');
    itens.forEach((item, i) => {
        const video = item.querySelector('video');
        if (video) {
            if (i === indiceCarrosselAtual) {
                video.play().catch(e => console.log("Auto-play bloqueado pelo navegador"));
            } else {
                video.pause();
            }
        }
    });
}

/**
 * Inicia o ciclo automático do carrossel
 */
function iniciarAutoPlay() {
    intervaloAutoPlay = setInterval(() => {
        let proximo = (indiceCarrosselAtual + 1) % slidesDestaque.length;
        moverCarrossel(proximo);
    }, 8000);
}

/**
 * Reinicia o temporizador do auto-play após interação manual
 */
function reiniciarAutoPlay() {
    clearInterval(intervaloAutoPlay);
    iniciarAutoPlay();
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

// Event Listeners para Busca e Filtros
if (campoBusca) {
    campoBusca.addEventListener('input', (e) => {
        termoPesquisa = e.target.value;
        aplicarFiltros();
    });
}

botoesFiltro.forEach(botao => {
    botao.addEventListener('click', () => {
        botoesFiltro.forEach(b => b.classList.remove('botao-filtro--ativo'));
        botao.classList.add('botao-filtro--ativo');
        categoriaAtiva = botao.getAttribute('data-categoria');
        aplicarFiltros();
    });
});

// Inicialização ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    renderizarJogos(listaDeJogos);
    inicializarCarrossel();
    console.log("Game Search: Carrossel Multimídia e Filtros Inicializados.");
});
