(() => {
    'use strict';

    const qs = new URLSearchParams(window.location.search);
    const id = qs.get('id') || '';
    const slug = qs.get('slug') || '';
    const apiJogo = '../php/api/jogo.php';
    const apiPrecos = '../php/api/precos.php';
    const apiUsuario = '../php/api/user.php';
    let jogoAtual = null;

    const escapeHtml = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (caractere) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[caractere]));

    const IMAGEM_FALLBACK = '../img/naoencontrada.png';

    const valorIdentificador = () => {
        if (id && /^\d+$/.test(id)) return `id=${encodeURIComponent(id)}`;
        return `slug=${encodeURIComponent(slug)}`;
    };

    const preencherJogo = (jogo) => {
    jogoAtual = jogo;

    /* Registro inicial no histórico (preço provisório).
       O preço real é atualizado logo depois em renderizarOfertas(),
       reaproveitando o menor preço já calculado na comparação. */
    if (window.Historico && typeof window.Historico.adicionar === 'function') {
        window.Historico.adicionar({
            id: jogo.id,
            slug: jogo.slug,
            nome: jogo.nome,
            imagem: jogo.img,
            preco: 'Grátis'
        });
    }

    const texto = (seletor, valor) => {
        const elemento = document.querySelector(seletor);
        if (elemento) elemento.textContent = valor ?? '';
    };
    // ... restante inalterado

        texto('#jogo-titulo', jogo.nome || 'Jogo não encontrado');
        texto('#breadcrumb-nome', jogo.nome || 'Jogo');
        texto('#jogo-plataforma', jogo.plataforma || 'Não informado');
        texto('#jogo-genero', jogo.genero || 'Não informado');
        texto('#jogo-ano', jogo.ano || 'Não informado');
        texto('#jogo-descricao', jogo.descricao || 'Descrição não disponível.');

        const capa = document.querySelector('#jogo-capa');
        if (capa) {
            capa.alt = `Capa de ${jogo.nome || 'jogo'}`;
            capa.onerror = () => {
                if (capa.src.endsWith(IMAGEM_FALLBACK)) return;
                capa.src = IMAGEM_FALLBACK;
            };
            capa.src = window.obterCaminhoImagem(jogo.img, jogo.slug);
        }
        const etaria = document.querySelector('#jogo-etaria');
        if (etaria) {
            const classificacao = jogo.etaria || 'N/I';
            etaria.textContent = classificacao;
            etaria.className = `badge-etaria etaria-${classificacao}`;
        }

        const avaliacoes = [jogo.avaliacao_gameplay, jogo.avaliacao_graficos, jogo.avaliacao_historia];
        document.querySelectorAll('.avaliacao-barra').forEach((barra, indice) => {
            const valor = Number(avaliacoes[indice] || 0);
            const fill = barra.querySelector('.avaliacao-fill');
            const saida = barra.querySelector('.avaliacao-valor');
            if (fill) fill.style.width = `${Math.max(0, Math.min(100, valor * 10))}%`;
            if (saida) saida.textContent = valor.toFixed(1);
        });
    };

    const atualizarBotaoMeusJogos = (possui) => {
        const botao = document.querySelector('#btnMeusJogos');
        if (!botao) return;
        botao.setAttribute('aria-pressed', possui ? 'true' : 'false');
        botao.innerHTML = possui
            ? '<i class="fas fa-check"></i> Nos Meus Jogos'
            : '<i class="fas fa-plus"></i> Meus Jogos';
    };

    const atualizarBotaoMinhaLista = (naLista) => {
        const botao = document.querySelector('#btnMinhaLista');
        if (!botao) return;
        botao.setAttribute('aria-pressed', naLista ? 'true' : 'false');
        botao.innerHTML = naLista
            ? '<i class="fas fa-check"></i> Na Minha Lista'
            : '<i class="far fa-heart"></i> Adicionar à Lista';
    };

    const consultarMeusJogos = async () => {
        if (!jogoAtual) return;
        try {
            const resposta = await fetch(`${apiUsuario}?acao=meus_jogos`, {
                credentials: 'include', cache: 'no-store'
            });
            if (!resposta.ok) return;
            const dados = await resposta.json();
            const possui = (dados.jogos || []).some(jogo => Number(jogo.jogo_id) === Number(jogoAtual.id));
            atualizarBotaoMeusJogos(possui);
        } catch (erro) {
            console.warn('Não foi possível consultar Meus Jogos.', erro);
        }
    };

    const alternarMeusJogos = async () => {
        const botao = document.querySelector('#btnMeusJogos');
        if (!botao || !jogoAtual) return;
        const possui = botao.getAttribute('aria-pressed') === 'true';
        botao.disabled = true;
        try {
            const resposta = await fetch(`${apiUsuario}?acao=${possui ? 'remover_jogo' : 'adicionar_jogo'}`, {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ jogo_id: Number(jogoAtual.id) })
            });
            const dados = await resposta.json();
            if (resposta.status === 401) {
                window.location.href = `login.html?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                return;
            }
            if (!resposta.ok || !dados.success) throw new Error(dados.message || 'Não foi possível atualizar Meus Jogos.');
            atualizarBotaoMeusJogos(!possui);
        } catch (erro) {
            alert(erro.message || 'Não foi possível atualizar Meus Jogos.');
        } finally {
            botao.disabled = false;
        }
    };

    const consultarMinhaLista = async () => {
        if (!jogoAtual) return;
        try {
            const resposta = await fetch(`${apiUsuario}?acao=minha_lista`, { credentials: 'include', cache: 'no-store' });
            if (!resposta.ok) return;
            const dados = await resposta.json();
            atualizarBotaoMinhaLista((dados.lista || []).some(jogo => Number(jogo.jogo_id) === Number(jogoAtual.id)));
        } catch (erro) {
            console.warn('Não foi possível consultar Minha Lista.', erro);
        }
    };

    const alternarMinhaLista = async () => {
        const botao = document.querySelector('#btnMinhaLista');
        if (!botao || !jogoAtual) return;
        const naLista = botao.getAttribute('aria-pressed') === 'true';
        botao.disabled = true;
        try {
            const resposta = await fetch(`${apiUsuario}?acao=${naLista ? 'remover_lista' : 'adicionar_lista'}`, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ jogo_id: Number(jogoAtual.id) })
            });
            const dados = await resposta.json();
            if (resposta.status === 401) {
                window.location.href = `login.html?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                return;
            }
            if (!resposta.ok || !dados.success) throw new Error(dados.message || 'Não foi possível atualizar Minha Lista.');
            atualizarBotaoMinhaLista(!naLista);
        } catch (erro) {
            alert(erro.message || 'Não foi possível atualizar Minha Lista.');
        } finally {
            botao.disabled = false;
        }
    };

    const renderizarOfertas = (dados) => {
    const lista = document.querySelector('#lista-precos');
    const contador = document.querySelector('#totalPlataformas');
    const atualizado = document.querySelector('#atualizado-precos');
    if (!lista) return;

    const ofertas = window.obterOfertasComparadas(dados);
    const melhorOferta = dados.melhor_oferta && typeof dados.melhor_oferta === 'object'
        ? dados.melhor_oferta
        : null;
    const menorPreco = melhorOferta ? Number(melhorOferta.preco) : null;

    /* Atualiza o registro do histórico com o menor preço já calculado.
       Reaproveita o mesmo fluxo de ofertas — nenhuma consulta nova.
       O dedupe do Historico.adicionar() evita duplicar o item. */
    if (jogoAtual && window.Historico && typeof window.Historico.adicionar === 'function') {
        const precoFormatado = menorPreco != null && Number.isFinite(menorPreco) && typeof window.formatarMoeda === 'function'
            ? window.formatarMoeda(menorPreco, melhorOferta.moeda)
            : 'Preço indisponível';
        window.Historico.adicionar({
            id: jogoAtual.id,
            slug: jogoAtual.slug,
            nome: jogoAtual.nome,
            imagem: jogoAtual.img,
            preco: precoFormatado
        });
    }

        if (contador) contador.textContent = `${ofertas.length} ${ofertas.length === 1 ? 'oferta' : 'ofertas'}`;
        if (atualizado) atualizado.textContent = dados.atualizado_em
            ? `Ofertas reais atualizadas em ${dados.atualizado_em}${dados.cache ? ' (cache)' : ''}`
            : 'Ofertas fornecidas pela IsThereAnyDeal';

        if (!ofertas.length) {
            lista.innerHTML = `<div class="loading"><p>${escapeHtml(dados.mensagem || 'Nenhuma oferta encontrada para este jogo.')}</p></div>`;
            return;
        }

        lista.innerHTML = ofertas.map((oferta, indice) => {
            const melhor = melhorOferta !== null
                && ((melhorOferta.id != null && String(oferta.id) === String(melhorOferta.id))
                    || (melhorOferta.id == null && indice === 0));
            const precoAtual = oferta.preco;
            const precoOriginal = oferta.precoOriginal;
            const desconto = oferta.desconto;
            const icone = escapeHtml(oferta.icone || 'fas fa-store');
            const cor = escapeHtml(oferta.cor || '#4ade80');
            const urlOriginal = String(oferta.url_oferta || oferta.url || '').trim();
            let urlValida = false;
            try {
                const urlAnalisada = new URL(urlOriginal);
                urlValida = ['https:', 'http:'].includes(urlAnalisada.protocol);
            } catch (erro) {
                urlValida = false;
            }
            const url = urlValida ? escapeHtml(urlOriginal) : '';
            const acaoOferta = url
                ? `<a class="btn-comprar" href="${url}" target="_blank" rel="noopener noreferrer">Comprar <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>`
                : '<span class="btn-comprar" aria-disabled="true">Link indisponível</span>';
            return `<article class="oferta-item${melhor ? ' melhor-oferta' : ''}">
                <div class="oferta-plataforma">
                    <span class="plataforma-icone" style="background:${cor}"><i class="${icone}" aria-hidden="true"></i></span>
                    <div><strong class="plataforma-nome">${escapeHtml(oferta.loja_nome || oferta.loja || 'Loja')}</strong>${melhor ? '<span class="melhor-tag">Melhor preço</span>' : ''}</div>
                </div>
                <div class="oferta-preco-antigo">${precoOriginal !== null && precoOriginal > precoAtual ? window.formatarMoeda(precoOriginal, oferta.moeda) : '—'}</div>
                <div class="oferta-preco-atual">${window.formatarMoeda(precoAtual, oferta.moeda)}</div>
                <div class="oferta-desconto">${desconto > 0 ? `<span class="desconto-tag">-${desconto}%</span>` : '—'}</div>
                <div class="oferta-detalhes"><span>${escapeHtml(oferta.plataforma || 'PC')} <b aria-hidden="true">•</b> ${oferta.disponivel ? 'Disponível' : 'Indisponível'} <b aria-hidden="true">•</b> ${escapeHtml(oferta.data_atualizacao || '')}</span></div>
                <div class="oferta-acoes">${acaoOferta}</div>
            </article>`;
        }).join('');
    };

    const carregar = async () => {
        if (!id && !slug) {
            renderizarOfertas({ precos: [], mensagem: 'Nenhum jogo foi selecionado.' });
            return;
        }
        try {
            const respostaJogo = await fetch(`${apiJogo}?acao=buscar&${valorIdentificador()}`);
            const dadosJogo = await respostaJogo.json();
            if (!respostaJogo.ok || !dadosJogo.success || !dadosJogo.jogo) {
                throw new Error(dadosJogo.message || 'Jogo não encontrado.');
            }
            preencherJogo(dadosJogo.jogo);
            await consultarMeusJogos();
            await consultarMinhaLista();

            const respostaPrecos = await fetch(`${apiPrecos}?${valorIdentificador()}`);
            const dadosPrecos = await respostaPrecos.json();
            if (!respostaPrecos.ok || !dadosPrecos.success) {
                throw new Error(dadosPrecos.error || dadosPrecos.message || 'Não foi possível consultar as ofertas.');
            }
            renderizarOfertas(dadosPrecos);
        } catch (erro) {
            const lista = document.querySelector('#lista-precos');
            if (lista) lista.innerHTML = `<div class="loading"><p>${escapeHtml(erro.message || 'Não foi possível carregar os dados.')}</p></div>`;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('#btnMeusJogos')?.addEventListener('click', alternarMeusJogos);
        document.querySelector('#btnMinhaLista')?.addEventListener('click', alternarMinhaLista);
        carregar();
    });
})();
