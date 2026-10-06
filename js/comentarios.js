(() => {
  'use strict';

  const api = '../php/api/comentarios.php';
  const MAX = 1000;

  // Mesma fonte de identificação usada por detalhes-jogo.js:
  // lê "id" ou "slug" diretamente da query string.
  const qs = new URLSearchParams(window.location.search);
  const idUrl = qs.get('id') || '';
  const slugUrl = qs.get('slug') || '';

  const escapar = (v) =>
    String(v ?? '').replace(/[&<>'"]/g, (c) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#039;',
      '"': '&quot;',
    }[c]));

  const formatarData = (iso) => {
    if (!iso) return '';
    const d = new Date(iso.replace(' ', 'T'));
    if (isNaN(d)) return iso;
    return d.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
  };

  const obterFoto = (caminho) => {
    if (!caminho) return '../img/naoencontrada.png';
    if (/^https?:\/\//i.test(caminho)) return caminho;
    return '../' + caminho.replace(/^\/+/, '');
  };

  let jogoIdResolvido = null;

  const resolverJogoId = async () => {
    if (jogoIdResolvido) return jogoIdResolvido;

    if (idUrl && /^\d+$/.test(idUrl)) {
      jogoIdResolvido = Number(idUrl);
      return jogoIdResolvido;
    }

    if (!slugUrl) return null;

    try {
      const r = await fetch(
        `../php/api/jogo.php?acao=buscar&slug=${encodeURIComponent(slugUrl)}`
      );
      const d = await r.json();
      if (d && d.success && d.jogo) {
        jogoIdResolvido = Number(d.jogo.id);
        return jogoIdResolvido;
      }
    } catch (e) {
      console.warn('Falha ao resolver slug → id', e);
    }

    return null;
  };

  const el = {
    lista:    () => document.querySelector('#comentarios-lista'),
    total:    () => document.querySelector('#comentarios-total'),
    form:     () => document.querySelector('#comentario-form'),
    textarea: () => document.querySelector('#comentario-texto'),
    aviso:    () => document.querySelector('#comentarios-aviso'),
    erro:     () => document.querySelector('#comentarios-erro'),
  };

  const mostrarErro = (msg) => {
    const e = el.erro();
    if (!e) return;
    e.textContent = msg || '';
    e.hidden = !msg;
  };

  const renderizar = (comentarios) => {
    const lista = el.lista();
    const total = el.total();
    if (!lista) return;

    if (total) {
      total.textContent = `${comentarios.length} ${
        comentarios.length === 1 ? 'comentário' : 'comentários'
      }`;
    }

    if (!comentarios.length) {
      lista.innerHTML = `<div class="comentarios-vazio">
        <i class="far fa-comment-dots" aria-hidden="true"></i>
        <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
      </div>`;
      return;
    }

    lista.innerHTML = comentarios
      .map((c) => {
        const foto = escapar(obterFoto(c.usuario_foto));
        const nome = escapar(c.usuario_nome || 'Usuário');
        const texto = escapar(c.comentario).replace(/\n/g, '<br>');
        const data = escapar(formatarData(c.data_criacao));
        const editado =
          c.data_atualizacao &&
          c.data_criacao &&
          c.data_atualizacao !== c.data_criacao;

        const acoes =
          c.pode_editar || c.pode_excluir
            ? `
        <div class="comentario-acoes">
          ${
            c.pode_editar
              ? `<button type="button" class="btn-acao" data-acao="editar" data-id="${c.id}"><i class="fas fa-pen"></i> Editar</button>`
              : ''
          }
          ${
            c.pode_excluir
              ? `<button type="button" class="btn-acao btn-acao-perigo" data-acao="excluir" data-id="${c.id}"><i class="fas fa-trash"></i> Excluir</button>`
              : ''
          }
        </div>`
            : '';

        return `<article class="comentario" data-id="${c.id}">
        <header class="comentario-cabecalho">
          <img class="comentario-avatar" src="${foto}" alt="Foto de ${nome}" loading="lazy"
               onerror="this.src='../img/naoencontrada.png'">
          <div class="comentario-meta">
            <strong class="comentario-autor">${nome}</strong>
            <span class="comentario-data">${data}${editado ? ' · editado' : ''}</span>
          </div>
        </header>
        <p class="comentario-texto">${texto}</p>
        ${acoes}
      </article>`;
      })
      .join('');
  };

  const prepararBotoesExclusao = () => {
    const botoes = el.lista()?.querySelectorAll('button[data-acao="excluir"]') || [];

    botoes.forEach((btn) => {
      if (btn.classList.contains('btn-excluir-animado')) return;

      btn.classList.remove('btn-acao', 'btn-acao-perigo');
      btn.classList.add('btn-excluir-animado');
      btn.setAttribute('aria-label', 'Excluir comentário');
      btn.setAttribute('title', 'Excluir comentário');
      btn.setAttribute('aria-live', 'polite');

      btn.innerHTML = `
        <span class="paper" aria-hidden="true"></span>
        <span class="content">
          <span class="trash" aria-hidden="true">
            <svg viewBox="0 0 20 24" fill="none" stroke="currentColor"
                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path class="body-fill"
                    d="M3.5 8h13l-1 13.2a1.5 1.5 0 0 1-1.5 1.3H6a1.5 1.5 0 0 1-1.5-1.3z"/>
              <g class="lid">
                <path d="M1.5 5h17M7.5 5V3.2A1.2 1.2 0 0 1 8.7 2h2.6a1.2 1.2 0 0 1 1.2 1.2V5"/>
              </g>
              <path d="M8 11.5v7M12 11.5v7" opacity=".55"/>
            </svg>
          </span>
          <span class="label">Excluir</span>
        </span>
        <span class="status" aria-hidden="true">
          <svg viewBox="0 0 10 10"><path d="M1.8 5.2 4.2 7.6 8.4 2.6"/></svg>
        </span>`;
    });
  };

  const carregar = async () => {
    const jogoId = await resolverJogoId();
    if (!jogoId) {
      mostrarErro('Não foi possível identificar o jogo para carregar comentários.');
      return;
    }

    try {
      const r = await fetch(`${api}?jogo_id=${jogoId}`, {
        credentials: 'include',
        cache: 'no-store',
      });
      const d = await r.json();
      if (!r.ok || !d.success) {
        throw new Error(d.message || 'Erro ao carregar comentários.');
      }

      renderizar(d.comentarios || []);
      prepararBotoesExclusao();

      const aviso = el.aviso();
      const form = el.form();
      if (aviso && form) {
        if (d.autenticado) {
          aviso.hidden = true;
          form.hidden = false;
        } else {
          aviso.hidden = false;
          form.hidden = true;
          const redirect = encodeURIComponent(
            window.location.pathname + window.location.search
          );
          aviso.innerHTML = `Faça <a href="login.html?redirect=${redirect}">login</a> para comentar.`;
        }
      }
    } catch (e) {
      mostrarErro(e.message || 'Erro ao carregar comentários.');
    }
  };

  const publicar = async (ev) => {
    ev.preventDefault();
    const ta = el.textarea();
    if (!ta) return;

    const texto = ta.value.trim();
    if (!texto) return;

    const jogoId = await resolverJogoId();
    if (!jogoId) return;

    ta.disabled = true;
    try {
      const r = await fetch(api, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jogo_id: jogoId, comentario: texto }),
      });
      const d = await r.json();

      if (r.status === 401) {
        const redirect = encodeURIComponent(
          window.location.pathname + window.location.search
        );
        window.location.href = `login.html?redirect=${redirect}`;
        return;
      }

      if (!r.ok || !d.success) {
        throw new Error(d.message || 'Erro ao publicar.');
      }

      ta.value = '';
      await carregar();
    } catch (e) {
      mostrarErro(e.message || 'Erro ao publicar comentário.');
    } finally {
      ta.disabled = false;
    }
  };

  const editar = async (id, artigo) => {
    const p = artigo.querySelector('.comentario-texto');
    if (!p) return;

    const original = p.textContent;
    const novo = window.prompt('Editar comentário:', original);
    if (novo === null) return;

    const texto = novo.trim();
    if (!texto || texto === original) return;

    try {
      const r = await fetch(`${api}?id=${id}`, {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comentario: texto }),
      });
      const d = await r.json();
      if (!r.ok || !d.success) {
        throw new Error(d.message || 'Erro ao editar.');
      }
      await carregar();
    } catch (e) {
      mostrarErro(e.message || 'Erro ao editar comentário.');
    }
  };

  const classesAnimacaoExclusao = [
    'is-busy',
    'is-shrink',
    'is-open',
    'is-drop',
    'is-close',
    'is-dot',
    'is-check',
    'is-out',
  ];

  const esperar = (ms) =>
    new Promise((resolve) => setTimeout(resolve, ms));

  const limparAnimacaoExclusao = (btn) => {
    classesAnimacaoExclusao.forEach((classe) =>
      btn.classList.remove(classe)
    );
    btn.disabled = false;
    btn.setAttribute('aria-label', 'Excluir comentário');
  };

  const iniciarAnimacaoExclusao = async (btn) => {
    /*
     * Primeiro aplica o estado visual e entrega um frame ao navegador.
     * Isso evita que a requisição DELETE seja percebida apenas como
     * "botão travado por alguns segundos".
     */
    btn.classList.add('is-busy', 'is-shrink');

    await new Promise((resolve) =>
      requestAnimationFrame(() =>
        requestAnimationFrame(resolve)
      )
    );

    btn.classList.add('is-open');
    await esperar(120);
    btn.classList.add('is-drop');
    await esperar(1080);
    btn.classList.remove('is-open');
    btn.classList.add('is-close');
    await esperar(100);
    btn.classList.add('is-dot');
    await esperar(400);
    btn.classList.add('is-check');

    return 2200;
  };

  const excluir = async (id, btn, artigo) => {
    if (!btn || !artigo || btn.classList.contains('is-busy')) return;

    artigo.classList.add('is-being-deleted');
    btn.disabled = true;
    btn.setAttribute('aria-label', 'Excluindo comentário, aguarde');

    const inicio = performance.now();

    /*
     * A animação começa imediatamente. O DELETE roda em paralelo,
     * sem bloquear a primeira etapa visual.
     */
    const animacao = iniciarAnimacaoExclusao(btn);

    try {
      const r = await fetch(`${api}?id=${id}`, {
        method: 'DELETE',
        credentials: 'include',
      });
      const d = await r.json();

      if (!r.ok || !d.success) {
        throw new Error(d.message || 'Erro ao excluir.');
      }

      const duracao = await animacao;
      const restante = Math.max(0, duracao - (performance.now() - inicio));
      if (restante) await esperar(restante);

      btn.classList.add('is-out');
      await esperar(250);
      await carregar();
    } catch (e) {
      artigo.classList.remove('is-being-deleted');
      limparAnimacaoExclusao(btn);
      mostrarErro(e.message || 'Erro ao excluir comentário.');
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    el.form()?.addEventListener('submit', publicar);

    el.lista()?.addEventListener('click', (ev) => {
      const btn = ev.target.closest('button[data-acao]');
      if (!btn) return;

      const id = Number(btn.dataset.id);
      const artigo = btn.closest('.comentario');

      if (btn.dataset.acao === 'editar') editar(id, artigo);
      if (btn.dataset.acao === 'excluir') excluir(id, btn, artigo);
    });

    const ta = el.textarea();
    if (ta) {
      ta.addEventListener('input', () => {
        if (ta.value.length > MAX) {
          ta.value = ta.value.slice(0, MAX);
        }
      });
    }

    carregar();
  });
})();