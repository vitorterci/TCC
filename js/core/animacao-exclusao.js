/* ───────────────────────────────────────────────
   AnimacaoExclusao — Game Search
   Encapsula a coreografia de exclusão em um botão já existente.
   Não cria nem substitui o botão: apenas decora o elemento passado.
   ─────────────────────────────────────────────── */
(() => {
  'use strict';

  const TIMELINE = [
    ['is-busy',   0],
    ['is-shrink', 0],
    ['is-open',   300],
    ['is-drop',   420],
    ['is-close',  1500],
    ['is-dot',    1600],
    ['is-check',  2000],
    ['is-out',    3000]
  ];
  const TODAS = TIMELINE.map(t => t[0]);

  const REDUZIDO =
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const montarEstrutura = (btn) => {
    // Se já foi montado, não duplica
    if (btn.querySelector('.anim-excluir-content')) return;

    // Preserva o conteúdo original como label
    const labelOriginal = (btn.textContent || 'Excluir').trim();

    btn.classList.add('btn-excluir-animado');
    btn.innerHTML = `
      <span class="paper" aria-hidden="true"></span>
      <span class="content anim-excluir-content">
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
        <span class="label">${labelOriginal}</span>
      </span>
      <span class="status" aria-hidden="true">
        <svg viewBox="0 0 10 10"><path d="M1.8 5.2 4.2 7.6 8.4 2.6"/></svg>
      </span>
    `;
    btn.setAttribute('aria-label', 'Excluir comentário');
    btn.setAttribute('aria-live', 'polite');
  };

  const limpar = (btn) => {
    TODAS.forEach(c => btn.classList.remove(c));
  };

  /**
   * Executa a animação completa.
   * @param {HTMLButtonElement} btn
   * @param {Object} opts
   * @param {Function} opts.onDelete  async — deve lançar em caso de erro
   * @param {Function} [opts.onSuccess]
   * @param {Function} [opts.onError]  recebe (erro)
   * @returns {Promise<boolean>}  true em sucesso, false em erro
   */
  const executar = async (btn, opts = {}) => {
    if (!btn || btn.dataset.animRunning === '1') return false;
    const { onDelete, onSuccess, onError } = opts;
    if (typeof onDelete !== 'function') {
      throw new Error('AnimacaoExclusao: onDelete é obrigatório.');
    }

    montarEstrutura(btn);
    btn.dataset.animRunning = '1';
    btn.disabled = true;
    btn.setAttribute('aria-label', 'Excluindo comentário, aguarde');

    // Modo reduzido: sem coreografia, apenas estado final visível.
    if (REDUZIDO) {
      btn.classList.add('is-busy', 'is-shrink', 'is-dot');
      try {
        await onDelete();
        btn.classList.add('is-check');
        setTimeout(() => {
          limpar(btn);
          btn.disabled = false;
          btn.dataset.animRunning = '0';
          btn.setAttribute('aria-label', 'Excluir comentário');
          onSuccess && onSuccess();
        }, 400);
        return true;
      } catch (e) {
        limpar(btn);
        btn.disabled = false;
        btn.dataset.animRunning = '0';
        btn.setAttribute('aria-label', 'Excluir comentário');
        onError && onError(e);
        return false;
      }
    }

    // Modo normal: agenda as fases
    const timers = TIMELINE.map(([cls, ms]) =>
      setTimeout(() => {
        if (cls === 'is-close') btn.classList.remove('is-open');
        btn.classList.add(cls);
      }, ms)
    );

    try {
      await onDelete();
      // Deixa o check visível por um instante
      setTimeout(() => {
        timers.forEach(clearTimeout);
        limpar(btn);
        btn.disabled = false;
        btn.dataset.animRunning = '0';
        btn.setAttribute('aria-label', 'Excluir comentário');
        onSuccess && onSuccess();
      }, 400);
      return true;
    } catch (e) {
      timers.forEach(clearTimeout);
      limpar(btn);
      btn.disabled = false;
      btn.dataset.animRunning = '0';
      btn.setAttribute('aria-label', 'Excluir comentário');
      onError && onError(e);
      return false;
    }
  };

  window.AnimacaoExclusao = { executar, montarEstrutura, limpar };
})();