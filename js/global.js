/* ─── FUNCIONALIDADES GLOBAIS ──────────────────────────────────────────────── */

// Resolve as capas de jogos para a raiz correta do projeto.
// A pasta oficial de imagens de jogos é `games/` (na raiz do projeto).
// A mesma função é usada pelo index, /pages/ e /pages/admin/.
window.obterCaminhoImagem = function (imagem, slug) {
  const caminhoPagina = window.location.pathname;
  let prefixo = "";
  if (caminhoPagina.includes("/pages/admin/")) {
    prefixo = "../../";
  } else if (caminhoPagina.includes("/pages/")) {
    prefixo = "../";
  }

  const fallback = `${prefixo}img/naoencontrada.png`;
  const valor = String(imagem || "").trim();

  if (!valor) return fallback;

  if (/^(https?:|data:)/i.test(valor) || valor.startsWith("/")) return valor;

  let caminho = valor
    .replace(/^(?:\.\.\/|\.\/)+/, "")
    .replace(/^assets\/img\/games\//i, "games/")
    .replace(/^assets\/img\//i, "img/");

  if (caminho.startsWith("games/")) {
    return `${prefixo}${caminho}`;
  }

  if (!caminho.includes("/")) {
    return `${prefixo}games/${caminho}`;
  }

  if (caminho.startsWith("img/")) {
    return `${prefixo}${caminho}`;
  }

  return `${prefixo}games/${caminho}`;
};

/* Os módulos core e os comportamentos específicos são carregados aqui para
 * manter compatibilidade com as páginas que incluem apenas este arquivo. */
(() => {
  const caminhoGlobal = document.currentScript?.src || "";
  const baseModulos = caminhoGlobal.slice(
    0,
    caminhoGlobal.lastIndexOf("/") + 1,
  );
  const modulos = [
    "core/preferencias.js",
    "core/tema.js",
    "core/atalho-tema.js",
    "core/contraste.js",
    "core/animacoes.js",
    // Fundo personalizado (local ao navegador — localStorage + IndexedDB).
    "core/fundo-personalizado.js",
    "busca/busca.js",
    "censura.js",
    "footer.js",
  ];
  document.write(
    modulos
      .map((modulo) => `<script src="${baseModulos}${modulo}"><\/script>`)
      .join(""),
  );
})();

// Aplicar preferências somente após os módulos core injetados acima estarem disponíveis.
const aplicarPreferenciasGlobais = () => {
  if (
    window.Preferencias &&
    typeof window.Preferencias.aplicarTudo === "function"
  ) {
    window.Preferencias.aplicarTudo();
  }
};
document.addEventListener("DOMContentLoaded", aplicarPreferenciasGlobais, {
  once: true,
});