/* ─── PREFERÊNCIAS: ANIMAÇÕES ────────────────────────────────────────────── */

window.Preferencias = window.Preferencias || {};

window.Preferencias.aplicarAnimacoes = function () {
    const animacoesAtivas = localStorage.getItem('pref_animacoes') !== 'false';
    if (!animacoesAtivas) {
        document.body.classList.add('sem-animacoes');
    } else {
        document.body.classList.remove('sem-animacoes');
    }
};
