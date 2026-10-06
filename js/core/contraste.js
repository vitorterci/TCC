/* ─── PREFERÊNCIAS: CONTRASTE E COR ──────────────────────────────────────── */

window.Preferencias = window.Preferencias || {};

window.Preferencias.aplicarCor = function () {
    const cor = localStorage.getItem('pref_cor') || '#2e00e6';
    document.documentElement.style.setProperty('--cor-primaria', cor);
    this.aplicarContraste(cor);
};

// Mantém os textos independentes da cor escolhida e calcula a melhor cor sobre o destaque.
window.Preferencias.aplicarContraste = function (cor) {
    const rgb = this.hexToRgb(cor);
    if (!rgb) return;

    const rgbStr = `${rgb.r}, ${rgb.g}, ${rgb.b}`;
    const luminanciaFundo = this.calcularLuminancia(rgb);
    const contrasteEscuro = this.calcularContrasteRelativo(luminanciaFundo, 0);
    const contrasteClaro = this.calcularContrasteRelativo(luminanciaFundo, 1);
    const textoSobreDestaque = contrasteEscuro >= contrasteClaro ? '#000000' : '#ffffff';
    const textoSobreDestaqueHover = contrasteEscuro >= contrasteClaro ? '#000000' : '#ffffff';

    document.documentElement.style.setProperty('--cor-primaria-rgb', rgbStr);
    document.documentElement.style.setProperty('--cor-texto-sobre-destaque', textoSobreDestaque);
    document.documentElement.style.setProperty('--cor-texto-sobre-destaque-hover', textoSobreDestaqueHover);
    document.documentElement.style.setProperty('--shadow-glow', `0 0 20px rgba(${rgbStr}, 0.25)`);
    document.documentElement.style.setProperty('--shadow-glow-hover', `0 0 35px rgba(${rgbStr}, 0.45)`);
};

// Converte RGB em luminância relativa para a comparação de contraste.
window.Preferencias.calcularLuminancia = function ({ r, g, b }) {
    const ajustarCanal = canal => {
        const valor = canal / 255;
        return valor <= 0.03928 ? valor / 12.92 : ((valor + 0.055) / 1.055) ** 2.4;
    };

    return (0.2126 * ajustarCanal(r)) + (0.7152 * ajustarCanal(g)) + (0.0722 * ajustarCanal(b));
};

window.Preferencias.calcularContrasteRelativo = function (luminanciaFundo, luminanciaTexto) {
    const maisClara = Math.max(luminanciaFundo, luminanciaTexto);
    const maisEscura = Math.min(luminanciaFundo, luminanciaTexto);
    return (maisClara + 0.05) / (maisEscura + 0.05);
};

window.Preferencias.hexToRgb = function (hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
};
