/* ─── FUNDO PERSONALIZADO ──────────────────────────────────────────────────
 *
 * Sistema de fundo personalizado do Game Search.
 *
 * IMPORTANTE — ESCOPO DE PERSISTÊNCIA:
 * Esta implementação é LOCAL ao NAVEGADOR/DISPOSITIVO do usuário.
 *   • Preferências ficam em `localStorage` (chave `pref_fundo_personalizado`).
 *   • Imagem enviada fica em `IndexedDB` (DB `game-search-preferencias`,
 *     object store `fundo`, chave `imagem`).
 *   • NÃO sincroniza automaticamente entre dispositivos.
 *   • NÃO grava nada no servidor, MySQL ou endpoints PHP existentes.
 *
 * API global: window.FundoPersonalizado
 *   - aplicar()
 *   - definirModo(modo)
 *   - definirCor(hex)
 *   - definirGradiente(cor1, cor2, angulo)
 *   - definirImagem(file, posicao?, tamanho?)
 *   - definirPosicaoImagem(posicao)
 *   - definirTamanhoImagem(tamanho)
 *   - removerImagem()
 *   - restaurarPadrao()
 *   - obterConfiguracao()
 * ──────────────────────────────────────────────────────────────────────── */

(function () {
  "use strict";

  const CHAVE_STORAGE = "pref_fundo_personalizado";

  // IndexedDB dedicado — não mexe em outros bancos locais do projeto.
  const DB_NOME = "game-search-preferencias";
  const DB_VERSAO = 1;
  const DB_STORE = "fundo";
  const DB_CHAVE_IMG = "imagem";

  const TAMANHO_MAX = 5 * 1024 * 1024; // 5 MB
  const MIMES_OK = ["image/jpeg", "image/png", "image/webp", "image/gif"];
  const EXT_OK = ["jpg", "jpeg", "png", "webp", "gif"];

  const ID_ELEMENTO = "gs-fundo-personalizado";

  const CONFIG_PADRAO = {
    modo: "padrao", // "padrao" | "cor" | "gradiente" | "imagem"
    cor: "#0d0d1f",
    gradienteCor1: "#2e00e6",
    gradienteCor2: "#ec4899",
    gradienteAngulo: 135,
    imagemPosicao: "center",
    imagemTamanho: "cover",
    imagemOpacidade: 1,
  };

  const POSICOES_OK = [
    "center",
    "center top",
    "center bottom",
    "left center",
    "right center",
  ];
  const TAMANHOS_OK = ["cover", "contain", "auto"];
  const MODOS_OK = ["padrao", "cor", "gradiente", "imagem"];

  let config = Object.assign({}, CONFIG_PADRAO);
  let urlImagemAtual = null; // Blob URL atual da imagem carregada.

  /* ── Configuração (localStorage) ─────────────────────────────────────── */

  function salvarConfig() {
    try {
      localStorage.setItem(CHAVE_STORAGE, JSON.stringify(config));
    } catch (erro) {
      console.warn("[FundoPersonalizado] Falha ao salvar configuração:", erro);
    }
  }

  function carregarConfig() {
    try {
      const raw = localStorage.getItem(CHAVE_STORAGE);
      if (!raw) return config;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== "object") return config;
      config = Object.assign({}, CONFIG_PADRAO, parsed);
      if (!MODOS_OK.includes(config.modo)) config.modo = "padrao";
      if (!POSICOES_OK.includes(config.imagemPosicao)) {
        config.imagemPosicao = CONFIG_PADRAO.imagemPosicao;
      }
      if (!TAMANHOS_OK.includes(config.imagemTamanho)) {
        config.imagemTamanho = CONFIG_PADRAO.imagemTamanho;
      }
    } catch (erro) {
      console.warn(
        "[FundoPersonalizado] Configuração inválida, usando padrão:",
        erro,
      );
      config = Object.assign({}, CONFIG_PADRAO);
    }
    return config;
  }

  /* ── IndexedDB ───────────────────────────────────────────────────────── */

  function abrirDB() {
    return new Promise((resolve, reject) => {
      if (!window.indexedDB) {
        reject(new Error("IndexedDB indisponível"));
        return;
      }
      const req = indexedDB.open(DB_NOME, DB_VERSAO);
      req.onupgradeneeded = () => {
        const db = req.result;
        if (!db.objectStoreNames.contains(DB_STORE)) {
          db.createObjectStore(DB_STORE);
        }
      };
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  function salvarImagemDB(blob) {
    return abrirDB().then(
      (db) =>
        new Promise((resolve, reject) => {
          const tx = db.transaction(DB_STORE, "readwrite");
          tx.objectStore(DB_STORE).put(blob, DB_CHAVE_IMG);
          tx.oncomplete = () => {
            db.close();
            resolve(true);
          };
          tx.onerror = () => {
            const erro = tx.error;
            db.close();
            reject(erro);
          };
        }),
    );
  }

  function obterImagemDB() {
    return abrirDB().then(
      (db) =>
        new Promise((resolve, reject) => {
          const tx = db.transaction(DB_STORE, "readonly");
          const req = tx.objectStore(DB_STORE).get(DB_CHAVE_IMG);
          req.onsuccess = () => {
            const valor = req.result || null;
            db.close();
            resolve(valor);
          };
          req.onerror = () => {
            const erro = req.error;
            db.close();
            reject(erro);
          };
        }),
    );
  }

  function removerImagemDB() {
    return abrirDB().then(
      (db) =>
        new Promise((resolve, reject) => {
          const tx = db.transaction(DB_STORE, "readwrite");
          tx.objectStore(DB_STORE).delete(DB_CHAVE_IMG);
          tx.oncomplete = () => {
            db.close();
            resolve(true);
          };
          tx.onerror = () => {
            const erro = tx.error;
            db.close();
            reject(erro);
          };
        }),
    );
  }

  /* ── Validação ───────────────────────────────────────────────────────── */

  function obterExtensao(nome) {
    const partes = String(nome || "").toLowerCase().split(".");
    return partes.length > 1 ? partes.pop() : "";
  }

  function validarImagem(file) {
    if (!file) return "Nenhum arquivo selecionado.";
    const mime = String(file.type || "").toLowerCase();
    const ext = obterExtensao(file.name);

    if (!MIMES_OK.includes(mime) || !EXT_OK.includes(ext)) {
      return "Formato inválido. Use JPG, PNG, WebP ou GIF.";
    }
    if (file.size > TAMANHO_MAX) {
      return "A imagem deve ter no máximo 5 MB.";
    }
    return null;
  }

  /* ── Elemento de fundo ───────────────────────────────────────────────── */

  function obterElemento() {
    let el = document.getElementById(ID_ELEMENTO);
    if (el) return el;
    if (!document.body) return null;
    el = document.createElement("div");
    el.id = ID_ELEMENTO;
    el.setAttribute("aria-hidden", "true");
    // Insere como PRIMEIRO filho do body — fica atrás de todo o conteúdo
    // via `z-index: -1` (ver css/components/fundo-personalizado.css).
    document.body.insertBefore(el, document.body.firstChild);
    return el;
  }

  function limparEstilosElemento(el) {
    el.style.background = "";
    el.style.backgroundColor = "";
    el.style.backgroundImage = "";
    el.style.backgroundPosition = "";
    el.style.backgroundSize = "";
    el.style.backgroundRepeat = "";
    el.style.opacity = "1";
    el.style.display = "";
  }

  /* ── Aplicação ───────────────────────────────────────────────────────── */

  function aplicar() {
    const el = obterElemento();
    if (!el) return;
    limparEstilosElemento(el);

    if (!config || config.modo === "padrao") {
      el.style.display = "none";
      return;
    }

    el.style.display = "block";

    if (config.modo === "cor") {
      el.style.backgroundColor = config.cor || CONFIG_PADRAO.cor;
      return;
    }

    if (config.modo === "gradiente") {
      el.style.background = `linear-gradient(${config.gradienteAngulo}deg, ${config.gradienteCor1}, ${config.gradienteCor2})`;
      return;
    }

    if (config.modo === "imagem") {
      // Base escura por baixo, caso a imagem não cubra 100% da viewport.
      el.style.backgroundColor = "#0d0d1f";
      el.style.opacity = String(
        typeof config.imagemOpacidade === "number"
          ? config.imagemOpacidade
          : 1,
      );
      if (urlImagemAtual) {
        el.style.backgroundImage = `url("${urlImagemAtual}")`;
        el.style.backgroundPosition = config.imagemPosicao || "center";
        el.style.backgroundSize =
          config.imagemTamanho === "auto"
            ? "auto"
            : config.imagemTamanho || "cover";
        el.style.backgroundRepeat = "no-repeat";
      } else {
        // Tenta buscar a imagem persistida no IndexedDB.
        carregarImagemEAplicar();
      }
    }
  }

  function carregarImagemEAplicar() {
    obterImagemDB()
      .then((blob) => {
        if (!blob || config.modo !== "imagem") return;
        if (urlImagemAtual) {
          try {
            URL.revokeObjectURL(urlImagemAtual);
          } catch (_) {}
        }
        urlImagemAtual = URL.createObjectURL(blob);
        aplicar();
      })
      .catch((erro) => {
        console.warn(
          "[FundoPersonalizado] Falha ao carregar imagem do IndexedDB:",
          erro,
        );
      });
  }

  /* ── API pública ─────────────────────────────────────────────────────── */

  function obterConfiguracao() {
    return Object.assign({}, config);
  }

  function definirModo(modo) {
    if (!MODOS_OK.includes(modo)) return;
    config.modo = modo;
    salvarConfig();
    aplicar();
  }

  function definirCor(cor) {
    if (typeof cor !== "string" || !cor.trim()) return;
    config.cor = cor.trim();
    config.modo = "cor";
    salvarConfig();
    aplicar();
  }

  function definirGradiente(cor1, cor2, angulo) {
    if (typeof cor1 === "string" && cor1.trim()) {
      config.gradienteCor1 = cor1.trim();
    }
    if (typeof cor2 === "string" && cor2.trim()) {
      config.gradienteCor2 = cor2.trim();
    }
    if (typeof angulo === "number" && Number.isFinite(angulo)) {
      let a = Math.round(angulo);
      a = ((a % 360) + 360) % 360;
      config.gradienteAngulo = a;
    }
    config.modo = "gradiente";
    salvarConfig();
    aplicar();
  }

  function definirPosicaoImagem(posicao) {
    if (!POSICOES_OK.includes(posicao)) return;
    config.imagemPosicao = posicao;
    salvarConfig();
    aplicar();
  }

  function definirTamanhoImagem(tamanho) {
    if (!TAMANHOS_OK.includes(tamanho)) return;
    config.imagemTamanho = tamanho;
    salvarConfig();
    aplicar();
  }

  function definirOpacidadeImagem(valor) {
    const v = Math.max(0, Math.min(1, Number(valor)));
    if (!Number.isFinite(v)) return;
    config.imagemOpacidade = v;
    salvarConfig();
    aplicar();
  }

  // Retorna Promise<{ ok: boolean, erro?: string }>.
  function definirImagem(file, posicao, tamanho) {
    const erro = validarImagem(file);
    if (erro) {
      // Não apaga imagem anterior nem altera config atual.
      return Promise.resolve({ ok: false, erro });
    }
    return salvarImagemDB(file)
      .then(() => {
        if (urlImagemAtual) {
          try {
            URL.revokeObjectURL(urlImagemAtual);
          } catch (_) {}
        }
        urlImagemAtual = URL.createObjectURL(file);
        if (POSICOES_OK.includes(posicao)) config.imagemPosicao = posicao;
        if (TAMANHOS_OK.includes(tamanho)) config.imagemTamanho = tamanho;
        config.modo = "imagem";
        salvarConfig();
        aplicar();
        return { ok: true };
      })
      .catch((e) => {
        console.warn(
          "[FundoPersonalizado] Falha ao armazenar imagem no IndexedDB:",
          e,
        );
        return {
          ok: false,
          erro: "Não foi possível salvar a imagem neste navegador.",
        };
      });
  }

  function removerImagem() {
    return removerImagemDB()
      .catch((erro) => {
        console.warn(
          "[FundoPersonalizado] Falha ao remover imagem do IndexedDB:",
          erro,
        );
      })
      .then(() => {
        if (urlImagemAtual) {
          try {
            URL.revokeObjectURL(urlImagemAtual);
          } catch (_) {}
          urlImagemAtual = null;
        }
        if (config.modo === "imagem") config.modo = "padrao";
        salvarConfig();
        aplicar();
      });
  }

  function restaurarPadrao() {
    // Remove a imagem persistida e volta a configuração ao padrão.
    removerImagemDB().catch(() => {});
    if (urlImagemAtual) {
      try {
        URL.revokeObjectURL(urlImagemAtual);
      } catch (_) {}
      urlImagemAtual = null;
    }
    config = Object.assign({}, CONFIG_PADRAO);
    salvarConfig();
    aplicar();
  }

  /* ── Bootstrap ───────────────────────────────────────────────────────── */

  function init() {
    carregarConfig();

    const iniciar = () => {
      aplicar();
      if (config.modo === "imagem") carregarImagemEAplicar();
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", iniciar, { once: true });
    } else {
      iniciar();
    }
  }

  init();

  window.FundoPersonalizado = {
    aplicar,
    definirModo,
    definirCor,
    definirGradiente,
    definirImagem,
    removerImagem,
    restaurarPadrao,
    obterConfiguracao,
    definirPosicaoImagem,
    definirTamanhoImagem,
    definirOpacidadeImagem,
    // Exposição do nome da chave para debug — não usar em outros lugares.
    _chaveStorage: CHAVE_STORAGE,
  };
})();