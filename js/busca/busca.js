/*
 * Funcionalidades compartilhadas de busca do catálogo.
 * Mantém a navegação global e a relevância usada pelo index e pela Biblioteca.
 */
(function inicializarModuloBusca() {
    const sinonimos = {
        witcher: ['the witcher'],
        'cavaleiro oco': ['hollow knight'],
        'cavaleiros ocos': ['hollow knight'],
        zelda: ['the legend of zelda'],
        'zelda tears': ['the legend of zelda tears'],
        ragnarok: ['god of war ragnarok'],
        'deus da guerra': ['god of war'],
        'deuses da guerra': ['god of war'],
        'anel antigo': ['elden ring']
    };

    function normalizarTexto(valor) {
        return String(valor ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\p{L}\p{N}]+/gu, ' ')
            .toLowerCase()
            .trim()
            .replace(/\s+/g, ' ');
    }

    function distanciaLevenshtein(primeiro, segundo) {
        const linha = Array.from({ length: segundo.length + 1 }, (_, indice) => indice);
        for (let indice = 1; indice <= primeiro.length; indice += 1) {
            let diagonal = linha[0];
            linha[0] = indice;
            for (let coluna = 1; coluna <= segundo.length; coluna += 1) {
                const acima = linha[coluna];
                linha[coluna] = primeiro[indice - 1] === segundo[coluna - 1]
                    ? diagonal
                    : Math.min(diagonal + 1, acima + 1, linha[coluna - 1] + 1);
                diagonal = acima;
            }
        }
        return linha[segundo.length];
    }

    function pontuarBusca(jogo, termo) {
        if (!termo) return 0;
        const nome = normalizarTexto(jogo.nome);
        const consultas = [termo, ...(sinonimos[termo] || [])];
        let melhorPontuacao = 0;
        consultas.forEach(consulta => {
            if (nome === consulta) melhorPontuacao = Math.max(melhorPontuacao, 1000);
            else if (nome.startsWith(consulta)) melhorPontuacao = Math.max(melhorPontuacao, 800);
            else if (nome.includes(consulta)) melhorPontuacao = Math.max(melhorPontuacao, 650);
            const palavrasBusca = consulta.split(' ').filter(Boolean);
            const palavrasNome = nome.split(' ').filter(Boolean);
            const palavrasEncontradas = palavrasBusca.filter(palavraBusca => palavrasNome.some(palavraNome => {
                if (palavraNome.startsWith(palavraBusca) || palavraBusca.startsWith(palavraNome)) return true;
                const limite = palavraBusca.length >= 5 ? 2 : palavraBusca.length > 3 ? 1 : 0;
                return palavraBusca.length >= 4 && distanciaLevenshtein(palavraBusca, palavraNome) <= limite;
            }));
            if (palavrasEncontradas.length === palavrasBusca.length) {
                melhorPontuacao = Math.max(melhorPontuacao, 400 + palavrasEncontradas.length * 20);
            }
        });
        return melhorPontuacao;
    }

    function obterRelevanciaBusca(jogo, termo) {
        const pontuacao = pontuarBusca(jogo, termo);
        return pontuacao || (termo ? -1 : 0);
    }

    function obterTermoDaURL() {
        return new URLSearchParams(window.location.search).get('busca') || '';
    }

    window.Busca = {
        normalizarTexto,
        distanciaLevenshtein,
        pontuarBusca,
        obterRelevanciaBusca,
        obterTermoDaURL
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.campo-busca').forEach(campoBusca => {
            campoBusca.addEventListener('keypress', evento => {
                if (evento.key !== 'Enter' || !campoBusca.value.trim()) return;
                const termo = encodeURIComponent(campoBusca.value);
                const base = window.location.pathname.includes('/pages/') ? '../' : '';
                window.location.href = `${base}index.html?busca=${termo}`;
            });
        });
    });
})();
