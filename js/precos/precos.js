/* ─── PREÇOS — NORMALIZAÇÃO E COMPARAÇÃO COMPARTILHADA ──────────────────────── */

(() => {
    'use strict';

    const precosPorSlug = new Map();
    const requisicoesPorSlug = new Map();

    function normalizarPreco(valor) {
        const numero = typeof valor === 'string'
            ? Number(valor.replace(',', '.'))
            : Number(valor);
        return Number.isFinite(numero) && numero >= 0 ? numero : null;
    }

    function normalizarMoeda(moeda) {
        const codigo = String(moeda || 'BRL').trim().toUpperCase();
        return /^[A-Z]{3}$/.test(codigo) ? codigo : 'BRL';
    }

    function formatarMoeda(valor, moeda = 'BRL') {
        const preco = normalizarPreco(valor);
        if (preco === null) return 'Preço indisponível';
        const codigo = normalizarMoeda(moeda);
        try {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: codigo
            }).format(preco);
        } catch (erro) {
            return `${preco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${codigo}`;
        }
    }

    function formatarPrecoComparado(valor, moeda = 'BRL') {
        const preco = normalizarPreco(valor);
        return preco === null ? 'Preço indisponível' : formatarMoeda(preco, moeda);
    }

    function obterSlug(jogo) {
        return String(jogo?.slug || '').trim();
    }

    function extrairMenorOferta(dados) {
        const melhorOferta = dados?.melhor_oferta;
        if (melhorOferta && typeof melhorOferta === 'object') {
            const valor = normalizarPreco(melhorOferta.preco ?? melhorOferta.preco_atual);
            return valor !== null && valor > 0
                ? { valor, moeda: normalizarMoeda(melhorOferta.moeda ?? dados.moeda) }
                : null;
        }

        const menorPreco = dados?.menor_preco ?? dados?.menorPreco;
        const valor = menorPreco && typeof menorPreco === 'object'
            ? normalizarPreco(menorPreco.preco ?? menorPreco.valor)
            : normalizarPreco(menorPreco);
        if (valor === null || valor <= 0) return null;
        return { valor, moeda: normalizarMoeda(dados?.moeda) };
    }

    async function buscarMenorPreco(jogo, endpoint) {
        const slug = obterSlug(jogo);
        if (!slug) return null;

        if (precosPorSlug.has(slug)) {
            return precosPorSlug.get(slug);
        }
        if (requisicoesPorSlug.has(slug)) {
            return requisicoesPorSlug.get(slug);
        }

        const requisicao = fetch(`${endpoint}?acao=buscar&slug=${encodeURIComponent(slug)}`)
            .then(resposta => {
                if (!resposta.ok) throw new Error(`HTTP ${resposta.status}`);
                return resposta.json();
            })
            .then(extrairMenorOferta)
            .catch(erro => {
                console.error(`Erro ao carregar preço de ${slug}:`, erro);
                return null;
            })
            .then(oferta => {
                precosPorSlug.set(slug, oferta);
                requisicoesPorSlug.delete(slug);
                return oferta;
            });

        requisicoesPorSlug.set(slug, requisicao);
        return requisicao;
    }

    async function carregarPrecosDosJogos(jogos, endpoint) {
        const jogosUnicos = new Map();
        jogos.forEach(jogo => {
            const slug = obterSlug(jogo);
            if (slug) jogosUnicos.set(slug, jogo);
        });
        await Promise.all([...jogosUnicos.values()].map(jogo => buscarMenorPreco(jogo, endpoint)));
    }

    function obterPrecoComparado(jogo) {
        const oferta = precosPorSlug.get(obterSlug(jogo));
        const valor = oferta?.valor ?? null;
        return {
            valor,
            moeda: oferta?.moeda || null,
            classe: valor === null
                ? 'indisponivel'
                : valor <= 50
                    ? 'baixo'
                    : valor <= 150
                        ? 'medio'
                        : 'alto',
            texto: formatarPrecoComparado(valor, oferta?.moeda || 'BRL')
        };
    }

    function obterOfertasComparadas(dados) {
        return (Array.isArray(dados?.precos) ? dados.precos : [])
            .filter(oferta => oferta.disponivel !== false && oferta.disponibilidade !== 'indisponivel')
            .map(oferta => ({
                ...oferta,
                preco: normalizarPreco(oferta.preco ?? oferta.preco_atual),
                precoOriginal: normalizarPreco(oferta.preco_original ?? oferta.preco_antigo),
                moeda: normalizarMoeda(oferta.moeda),
                desconto: normalizarPreco(oferta.desconto ?? oferta.desconto_percentual) ?? 0
            }))
            .filter(oferta => oferta.preco !== null && oferta.preco > 0)
            .sort((a, b) => {
                const prioridadeA = a.moeda === 'BRL' ? 0 : 1;
                const prioridadeB = b.moeda === 'BRL' ? 0 : 1;
                if (prioridadeA !== prioridadeB) return prioridadeA - prioridadeB;
                if (a.moeda !== b.moeda) return a.moeda.localeCompare(b.moeda);
                return a.preco - b.preco || String(a.loja || '').localeCompare(String(b.loja || ''));
            });
    }

    window.normalizarPreco = normalizarPreco;
    window.formatarPrecoComparado = formatarPrecoComparado;
    window.formatarMoeda = formatarMoeda;
    window.buscarMenorPreco = buscarMenorPreco;
    window.carregarPrecosDosJogos = carregarPrecosDosJogos;
    window.obterPrecoComparado = obterPrecoComparado;
    window.obterOfertasComparadas = obterOfertasComparadas;
})();
