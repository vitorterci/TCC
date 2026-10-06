/* Censura por faixa etária. */
function jogoDeveSerCensurado(jogo) {
    if (!jogo || !jogo.etaria) return false;
    const texto = String(jogo.etaria).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    if (!texto || texto === 'l' || texto === '0' || texto.includes('livre')) {
        return false;
    }
    const match = texto.match(/(?:^|[^0-9])(10|12|14|16|18)(?:[^0-9]|$)/);
    const idadeJogo = match ? parseInt(match[1], 10) : 0;
    if (idadeJogo === 0) return false;

    const censuraAutoAtiva = localStorage.getItem('pref_censura_auto') !== 'false';
    const idadeManual = parseInt(localStorage.getItem('pref_idade_manual') || '18', 10);
    
    let idadePermitida = 18;
    const usuarioLogado = window.usuarioLogado || JSON.parse(localStorage.getItem('usuarioLogado') || 'null');

    if (censuraAutoAtiva && usuarioLogado) {
        // O perfil não possui data de nascimento; usar o limite padrão do filtro.
        idadePermitida = 18;
    } else {
        idadePermitida = idadeManual;
    }

    return idadeJogo > idadePermitida;
}

window.jogoDeveSerCensurado = jogoDeveSerCensurado;
