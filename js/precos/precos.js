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

    function formatarPrecoComparado(valor) {
        const preco = normalizarPreco(valor);
        return preco === null
            ? 'Preço indisponível'
            : `R$ ${preco.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
    }

    function formatarMoeda(valor) {
        return Number(valor || 0).toLocaleString('pt-BR', {
            style: 'currency', currency: 'BRL'
        });
    }

    function obterSlug(jogo) {
        return String(jogo?.slug || '').trim();
    }

    function obterMenorPreco(dados) {
        const menorPreco = dados?.menor_preco ?? dados?.menorPreco;
        if (menorPreco && typeof menorPreco === 'object') {
            return normalizarPreco(menorPreco.preco ?? menorPreco.valor);
        }
        return normalizarPreco(menorPreco);
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
            .then(obterMenorPreco)
            .catch(erro => {
                console.error(`Erro ao carregar preço de ${slug}:`, erro);
                return null;
            })
            .then(preco => {
                precosPorSlug.set(slug, preco);
                requisicoesPorSlug.delete(slug);
                return preco;
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
        const preco = precosPorSlug.get(obterSlug(jogo));
        return {
            valor: preco,
            classe: preco === null || preco === undefined
                ? 'indisponivel'
                : preco <= 50
                    ? 'baixo'
                    : preco <= 150
                        ? 'medio'
                        : 'alto',
            texto: formatarPrecoComparado(preco)
        };
    }

    function obterOfertasComparadas(dados) {
        return (Array.isArray(dados?.precos) ? dados.precos : [])
            .filter(oferta => oferta.disponivel !== false && oferta.disponibilidade !== 'indisponivel')
            .map(oferta => ({
                ...oferta,
                preco: normalizarPreco(oferta.preco ?? oferta.preco_atual) ?? 0,
                precoOriginal: normalizarPreco(oferta.preco_original ?? oferta.preco_antigo) ?? 0,
                desconto: normalizarPreco(oferta.desconto ?? oferta.desconto_percentual) ?? 0
            }))
            .filter(oferta => oferta.preco > 0)
            .sort((a, b) => a.preco - b.preco || String(a.loja || '').localeCompare(String(b.loja || '')));
    }

    window.normalizarPreco = normalizarPreco;
    window.formatarPrecoComparado = formatarPrecoComparado;
    window.formatarMoeda = formatarMoeda;
    window.buscarMenorPreco = buscarMenorPreco;
    window.carregarPrecosDosJogos = carregarPrecosDosJogos;
    window.obterPrecoComparado = obterPrecoComparado;
    window.obterOfertasComparadas = obterOfertasComparadas;
})();
