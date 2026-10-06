/* ─── PREFERÊNCIAS ───────────────────────────────────────────────────────── */

const Preferencias = window.Preferencias = window.Preferencias || {};

Preferencias.aplicarTudo = function () {
    this.aplicarCor();
    this.aplicarAnimacoes();
    this.aplicarTema(localStorage.getItem('pref_tema') || 'sistema');

    // Reaplica o fundo personalizado (independente do tema).
    // Só executa se o módulo estiver carregado; caso contrário, é ignorado.
    if (
        window.FundoPersonalizado &&
        typeof window.FundoPersonalizado.aplicar === "function"
    ) {
        try {
            window.FundoPersonalizado.aplicar();
        } catch (erro) {
            console.warn("[Preferencias] Falha ao reaplicar fundo personalizado:", erro);
        }
    }
};