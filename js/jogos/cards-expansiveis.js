/* ─── CARDS EXPANSÍVEIS — EVENTOS E ESTADO DE INTERAÇÃO ─────────────────── */

(function () {
    let cardAberto = null;
    let cliqueTimer = null;

    function fecharCard(card) {
        card?.classList.remove('open', 'open-left');
        card?.setAttribute('aria-expanded', 'false');
        if (cardAberto === card) cardAberto = null;
    }

    function abrirCard(card) {
        const rect = card.getBoundingClientRect();
        card.classList.toggle('open-left', window.innerWidth - rect.right < 220);
        card.classList.add('open');
        card.setAttribute('aria-expanded', 'true');
        cardAberto = card;
    }

    function alternarCard(card) {
        if (card.classList.contains('open')) fecharCard(card);
        else {
            if (cardAberto && cardAberto !== card) fecharCard(cardAberto);
            abrirCard(card);
        }
    }

    function inicializarCardsExpansiveis(opcoes = {}) {
        const modo = opcoes.modo || 'biblioteca';
        const cards = opcoes.cards || document.querySelectorAll('.card-jogo');
        const jogos = opcoes.jogos || [];
        const prefixoDetalhes = opcoes.prefixoDetalhes ?? (modo === 'home' ? 'pages/' : '');
        const cardsArray = Array.from(cards);

        cardsArray.forEach(card => {
            if (card.dataset.eventosInicializados === 'true') return;
            card.dataset.eventosInicializados = 'true';
            const id = Number(card.dataset.id);
            const abrirDetalhes = () => {
                window.location.href = `${prefixoDetalhes}pagina.html?id=${encodeURIComponent(id)}`;
            };
            const menuTrigger = card.querySelector('.menu-trigger');

            if (modo === 'home') {
                menuTrigger?.addEventListener('click', evento => {
                    evento.stopPropagation();
                    alternarCard(card);
                });
                card.addEventListener('click', evento => {
                    if (evento.target.closest('.botao-detalhes') || evento.target.closest('.menu-trigger')) return;
                    abrirDetalhes();
                });
                card.addEventListener('keydown', evento => {
                    if (evento.key !== 'Enter' && evento.key !== ' ') return;
                    if (evento.target.closest('.botao-detalhes') || evento.target.closest('.menu-trigger')) return;
                    evento.preventDefault();
                    abrirDetalhes();
                });
            } else {
                card.addEventListener('click', evento => {
                    if (evento.target.closest('.botao-detalhes')) return;
                    if (cliqueTimer) {
                        clearTimeout(cliqueTimer);
                        cliqueTimer = null;
                        abrirDetalhes();
                    } else {
                        cliqueTimer = setTimeout(() => {
                            cliqueTimer = null;
                            alternarCard(card);
                        }, 250);
                    }
                });
                card.addEventListener('keydown', evento => {
                    if (evento.key !== 'Enter' && evento.key !== ' ') return;
                    if (evento.target.closest('.botao-detalhes')) return;
                    evento.preventDefault();
                    alternarCard(card);
                });
            }

            card.querySelector('.botao-detalhes')?.addEventListener('click', evento => {
                evento.stopPropagation();
                abrirDetalhes();
            });
        });

        return cardsArray;
    }

    window.inicializarCardsExpansiveis = inicializarCardsExpansiveis;
})();
