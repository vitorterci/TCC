// ════════════════════════════════════════════════════════════════════════════
// BLOCO 1 — Lógica principal da página de configuração
// ════════════════════════════════════════════════════════════════════════════

// ── CONFIGURAÇÕES DA PÁGINA ────────────────────────────────────────────

const API_URL = "../php/api/user.php";
const COR_PADRAO = "#2e00e6";

// ── ELEMENTOS ──────────────────────────────────────────────────────────
const elements = {
  // Dados do usuário
  fotoPerfil: document.getElementById("fotoPerfil"),
  fotoPerfilInput: document.getElementById("fotoPerfilInput"),
  btnFotoPerfil: document.getElementById("btnFotoPerfil"),
  nomeDescricao: document.getElementById("nome-descricao"),
  nomeValor: document.getElementById("nome-valor"),
  nomeEdicao: document.getElementById("nome-edicao"),
  usuarioDescricao: document.getElementById("usuario-descricao"),
  usuarioValor: document.getElementById("usuario-valor"),
  usuarioEdicao: document.getElementById("usuario-edicao"),
  emailDescricao: document.getElementById("email-descricao"),
  emailValor: document.getElementById("email-valor"),
  emailEdicao: document.getElementById("email-edicao"),
  dataCadastro: document.getElementById("data-cadastro"),
  dataCadastroValor: document.getElementById("data-cadastro-valor"),
  roleDescricao: document.getElementById("role-descricao"),
  roleValor: document.getElementById("role-valor"),

  // Preferências e Círculo Cromático (Inline)
  circuloCor: document.getElementById("circuloCor"),
  cursorCor: document.getElementById("cursorCor"),
  rangeSat: document.getElementById("rangeSat"),
  valorSat: document.getElementById("valorSat"),
  rangeLum: document.getElementById("rangeLum"),
  valorLum: document.getElementById("valorLum"),
  previewAmostra: document.getElementById("previewAmostra"),
  previewHex: document.getElementById("previewHex"),
  previewHexLabel: document.getElementById("previewHexLabel"),
  btnResetCor: document.getElementById("btnResetCor"),

  // Botões
  btnSalvar: document.getElementById("btn-salvar"),
  btnExcluirConta: document.getElementById("btn-excluir-conta"),
  btnAlterarSenha: document.getElementById("btn-alterar-senha"),

  // Modal
  modalSenha: document.getElementById("modal-senha"),
  senhaAtual: document.getElementById("senha-atual"),
  novaSenha: document.getElementById("nova-senha"),
  confirmarNovaSenha: document.getElementById("confirmar-nova-senha"),
  modalSenhaCancelar: document.getElementById("modal-senha-cancelar"),
  modalSenhaSalvar: document.getElementById("modal-senha-salvar"),
  modalSenhaMensagem: document.getElementById("modal-senha-mensagem"),

  // Toast
  toast: document.getElementById("toast"),
  toastMensagem: document.getElementById("toastMensagem"),
};

let dadosUsuario = {};
let corSelecionada = COR_PADRAO;

// ── FUNÇÕES AUXILIARES ──────────────────────────────────────────────

function hexParaRgb(hex) {
  const resultado = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return resultado
    ? {
        r: parseInt(resultado[1], 16),
        g: parseInt(resultado[2], 16),
        b: parseInt(resultado[3], 16),
      }
    : null;
}

function mostrarToast(mensagem, tipo = "sucesso") {
  const toast = elements.toast;
  const msg = elements.toastMensagem;
  msg.textContent = mensagem;
  toast.className = "toast visivel " + tipo;
  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(
    () => toast.classList.remove("visivel"),
    4000,
  );
}

// ── Funções do Círculo Cromático e HSL ──────────────────────────────
let hueAtual = 240;
let satAtual = 100;
let lumAtual = 50;

function hslParaHex(h, s, l) {
  l /= 100;
  s /= 100;
  const a = s * Math.min(l, 1 - l);
  const f = (n) => {
    const k = (n + h / 30) % 12;
    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
    return Math.round(255 * color)
      .toString(16)
      .padStart(2, "0");
  };
  return `#${f(0)}${f(8)}${f(4)}`;
}

function hexParaHsl(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  if (!result) return { h: 240, s: 100, l: 50 };
  let r = parseInt(result[1], 16) / 255;
  let g = parseInt(result[2], 16) / 255;
  let b = parseInt(result[3], 16) / 255;

  let max = Math.max(r, g, b),
    min = Math.min(r, g, b);
  let h,
    s,
    l = (max + min) / 2;

  if (max === min) {
    h = s = 0;
  } else {
    let d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch (max) {
      case r:
        h = (g - b) / d + (g < b ? 6 : 0);
        break;
      case g:
        h = (b - r) / d + 2;
        break;
      case b:
        h = (r - g) / d + 4;
        break;
    }
    h /= 6;
  }
  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    l: Math.round(l * 100),
  };
}

function atualizarCorPorHsl(
  h,
  s,
  l,
  atualizarSliders = true,
  corExata = null,
) {
  hueAtual = h;
  satAtual = s;
  lumAtual = l;

  // Presets podem fornecer o HEX original para evitar perda por arredondamento HSL.
  const hex = corExata || hslParaHex(h, s, l);
  aplicarCorPreview(hex);

  if (atualizarSliders && elements.rangeSat && elements.rangeLum) {
    elements.rangeSat.value = s;
    elements.valorSat.textContent = `${s}%`;
    elements.rangeLum.value = l;
    elements.valorLum.textContent = `${l}%`;
  }

  if (elements.cursorCor && elements.circuloCor) {
    const raioMax = elements.circuloCor.clientWidth / 2 || 90;
    const rad = (h - 90) * (Math.PI / 180);
    const dist = (s / 100) * (raioMax - 12);
    const cx = raioMax + Math.cos(rad) * dist;
    const cy = raioMax + Math.sin(rad) * dist;
    elements.cursorCor.style.left = `${cx}px`;
    elements.cursorCor.style.top = `${cy}px`;
  }
}

function manipularInteracaoCirculo(e) {
  if (!elements.circuloCor) return;
  const rect = elements.circuloCor.getBoundingClientRect();
  const clientX = e.touches ? e.touches[0].clientX : e.clientX;
  const clientY = e.touches ? e.touches[0].clientY : e.clientY;

  const x = clientX - rect.left;
  const y = clientY - rect.top;
  const raioMax = rect.width / 2;
  const dx = x - raioMax;
  const dy = y - raioMax;

  let distancia = Math.hypot(dx, dy);
  if (distancia > raioMax) distancia = raioMax;

  let angulo = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
  if (angulo < 0) angulo += 360;

  const saturacao = Math.round((distancia / raioMax) * 100);

  atualizarCorPorHsl(angulo, saturacao, lumAtual);
}

function aplicarCorPreview(cor) {
  corSelecionada = cor;
  if (elements.previewAmostra)
    elements.previewAmostra.style.backgroundColor = cor;
  if (elements.previewHex) elements.previewHex.textContent = cor;
  if (elements.previewHexLabel)
    elements.previewHexLabel.textContent = cor;

  // Aplica a cor e recalcula o texto sobre o destaque em tempo real.
  document.documentElement.style.setProperty("--cor-primaria", cor);
  const corHover = hslParaHex(
    hueAtual,
    satAtual,
    Math.min(lumAtual + 10, 100),
  );
  document.documentElement.style.setProperty(
    "--cor-primaria-hover",
    corHover,
  );
  if (window.Preferencias) {
    window.Preferencias.aplicarContraste(cor);
  }
}

function atualizarBotoesTema(temaAtual) {
  document.querySelectorAll(".btn-tema").forEach((btn) => {
    if (btn.getAttribute("data-tema") === temaAtual) {
      btn.style.background = "var(--cor-primaria)";
      btn.style.color = "var(--cor-texto-sobre-destaque, #fff)";
      btn.style.borderColor = "var(--cor-primaria)";
    } else {
      btn.style.background = "var(--cor-card)";
      btn.style.color = "var(--cor-texto)";
      btn.style.borderColor = "var(--cor-borda)";
    }
  });
}

function atualizarBotoesIdade(idadeAtual) {
  document.querySelectorAll(".btn-idade-manual").forEach((btn) => {
    if (btn.getAttribute("data-idade") === String(idadeAtual)) {
      btn.style.background = "var(--cor-primaria)";
      btn.style.color = "var(--cor-texto-sobre-destaque, #fff)";
      btn.style.borderColor = "var(--cor-primaria)";
    } else {
      btn.style.background = "var(--cor-card)";
      btn.style.color = "var(--cor-texto)";
      btn.style.borderColor = "var(--cor-borda)";
    }
  });
}

function atualizarEstadoFiltroManual(autoAtivo) {
  const blocoManual = document.getElementById("bloco-filtro-manual");
  if (!blocoManual) return;
  if (autoAtivo) {
    blocoManual.style.opacity = "0.4";
    blocoManual.style.pointerEvents = "none";
  } else {
    blocoManual.style.opacity = "1";
    blocoManual.style.pointerEvents = "auto";
  }
}

function carregarPreferenciasLocal() {
  const corSalva = localStorage.getItem("pref_cor") || COR_PADRAO;
  const temaSalvo = localStorage.getItem("pref_tema") || "sistema";
  const censuraAuto =
    localStorage.getItem("pref_censura_auto") !== "false";
  const idadeManual = localStorage.getItem("pref_idade_manual") || "18";

  const hsl = hexParaHsl(corSalva);
  atualizarCorPorHsl(hsl.h, hsl.s, hsl.l);

  const toggleCensura = document.getElementById("toggle-censura-auto");
  if (toggleCensura) toggleCensura.checked = censuraAuto;

  atualizarBotoesTema(temaSalvo);
  atualizarBotoesIdade(idadeManual);
  atualizarEstadoFiltroManual(censuraAuto);
  if (window.Preferencias && window.Preferencias.aplicarTema) {
    window.Preferencias.aplicarTema(temaSalvo);
  }
}

// ── CARREGAR DADOS DO USUÁRIO ─────────────────────────────────────

async function carregarDadosUsuario() {
  try {
    const response = await fetch(`${API_URL}?acao=get`, {
      credentials: "include",
    });
    const data = await response.json();

    const painelConta = document.getElementById("painel-conta");
    const zonaPerigo = document.querySelector(".zona-perigo");
    const painelNaoLogado = document.getElementById("painel-nao-logado");

    if (!data.success) {
      if (painelConta) painelConta.style.display = "none";
      if (zonaPerigo) zonaPerigo.style.display = "none";
      if (painelNaoLogado) painelNaoLogado.style.display = "block";
      carregarPreferenciasLocal();
      return;
    }

    if (painelConta) painelConta.style.display = "block";
    if (zonaPerigo) zonaPerigo.style.display = "block";
    if (painelNaoLogado) painelNaoLogado.style.display = "none";

    dadosUsuario = data.user;
    preencherDadosUsuario(dadosUsuario);
    carregarPreferenciasLocal();
  } catch (error) {
    console.error("Erro ao carregar dados:", error);
    mostrarToast("Erro ao conectar com o servidor", "erro");
  }
}

function preencherDadosUsuario(usuario) {
  if (elements.fotoPerfil) {
    elements.fotoPerfil.src = usuario.foto_perfil
      ? `../${usuario.foto_perfil}?v=${Date.now()}`
      : "../img/logo.png";
  }

  // Nome
  elements.nomeDescricao.textContent = usuario.nome || "Não informado";
  elements.nomeValor.textContent = usuario.nome || "---";
  elements.nomeEdicao.value = usuario.nome || "";

  // Usuário
  elements.usuarioDescricao.textContent =
    usuario.usuario || "Não informado";
  elements.usuarioValor.textContent = usuario.usuario || "---";
  elements.usuarioEdicao.value = usuario.usuario || "";

  // E-mail
  elements.emailDescricao.textContent = usuario.email || "Não informado";
  elements.emailValor.textContent = usuario.email || "---";
  elements.emailEdicao.value = usuario.email || "";

  // Data de cadastro
  const data = usuario.data_cadastro
    ? new Date(usuario.data_cadastro)
    : null;
  const dataFormatada =
    data && !Number.isNaN(data.getTime())
      ? data.toLocaleDateString("pt-BR")
      : "Não informado";
  elements.dataCadastro.textContent = dataFormatada;
  elements.dataCadastroValor.textContent = dataFormatada;

  // Perfil de acesso
  const role = usuario.role === "admin" ? "Administrador" : "Usuário";
  elements.roleDescricao.textContent = role;
  elements.roleValor.textContent = role;
}

async function enviarFotoPerfil() {
  const arquivo = elements.fotoPerfilInput?.files?.[0];
  if (!arquivo) return;
  if (arquivo.size > 5 * 1024 * 1024) {
    mostrarToast("A imagem deve ter no máximo 5 MB", "erro");
    elements.fotoPerfilInput.value = "";
    return;
  }
  const formulario = new FormData();
  formulario.append("foto", arquivo);
  try {
    const resposta = await fetch(`${API_URL}?acao=upload_photo`, {
      method: "POST",
      credentials: "include",
      body: formulario,
    });
    const dados = await resposta.json();
    if (!dados.success) {
      mostrarToast(
        dados.message || "Não foi possível atualizar a foto",
        "erro",
      );
      return;
    }
    dadosUsuario = dados.user || dadosUsuario;
    preencherDadosUsuario(dadosUsuario);
    localStorage.setItem("usuarioLogado", JSON.stringify(dadosUsuario));
    mostrarToast(dados.message || "Foto atualizada com sucesso");
  } catch (erro) {
    console.error("Erro ao enviar foto:", erro);
    mostrarToast("Erro ao conectar com o servidor", "erro");
  } finally {
    elements.fotoPerfilInput.value = "";
  }
}

// ── EDIÇÃO INLINE E ALTERAÇÃO DE SENHA ─────────────────────────────
function obterContainerCampo(campo) {
  return document.querySelector(`.opcao-config[data-campo="${campo}"]`);
}

function abrirModoEdicao(campo) {
  const container = obterContainerCampo(campo);
  const input = container?.querySelector(".campo-edicao");
  if (!container || !input) return;
  container.classList.add("editando");
  input.focus();
  input.select();
}

function fecharModoEdicao(campo) {
  const campos = campo ? [campo] : ["nome", "usuario", "email"];
  campos.forEach((nomeCampo) => {
    const container = obterContainerCampo(nomeCampo);
    if (container) container.classList.remove("editando");
  });
}

async function salvarEdicaoInline(campo) {
  const container = obterContainerCampo(campo);
  const input = container?.querySelector(".campo-edicao");
  if (!input) return;
  const valor = input.value.trim();
  if (!valor) {
    mostrarToast("Preencha o campo antes de salvar", "erro");
    input.focus();
    return;
  }
  if (campo === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
    mostrarToast("Informe um e-mail válido", "erro");
    input.focus();
    return;
  }
  await salvarDadosUsuario(campo, valor);
}

function abrirModalSenha() {
  if (!elements.modalSenha) return;
  elements.modalSenha.style.display = "flex";
  elements.modalSenha.setAttribute("aria-hidden", "false");
  if (elements.modalSenhaMensagem) {
    elements.modalSenhaMensagem.textContent = "";
    elements.modalSenhaMensagem.style.display = "none";
  }
  elements.senhaAtual?.focus();
}

function fecharModalSenha() {
  if (!elements.modalSenha) return;
  elements.modalSenha.style.display = "none";
  elements.modalSenha.setAttribute("aria-hidden", "true");
  [
    elements.senhaAtual,
    elements.novaSenha,
    elements.confirmarNovaSenha,
  ].forEach((input) => {
    if (input) input.value = "";
  });
}

async function alterarSenha() {
  const senhaAtual = elements.senhaAtual?.value || "";
  const novaSenha = elements.novaSenha?.value || "";
  const confirmarSenha = elements.confirmarNovaSenha?.value || "";
  if (!senhaAtual || !novaSenha || !confirmarSenha) {
    mostrarToast("Preencha todos os campos da senha", "erro");
    return;
  }
  if (novaSenha.length < 6) {
    mostrarToast("A nova senha deve ter pelo menos 6 caracteres", "erro");
    return;
  }
  if (novaSenha !== confirmarSenha) {
    mostrarToast("As senhas não coincidem", "erro");
    return;
  }
  const botao = elements.modalSenhaSalvar;
  if (botao) botao.disabled = true;
  try {
    const response = await fetch(`${API_URL}?acao=update_password`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        senha_atual: senhaAtual,
        nova_senha: novaSenha,
        confirmar_senha: confirmarSenha,
      }),
    });
    const data = await response.json();
    if (!data.success) {
      mostrarToast(
        data.message || "Não foi possível alterar a senha",
        "erro",
      );
      return;
    }
    fecharModalSenha();
    mostrarToast(data.message || "Senha alterada com sucesso", "sucesso");
  } catch (error) {
    console.error("Erro ao alterar senha:", error);
    mostrarToast("Erro ao conectar com o servidor", "erro");
  } finally {
    if (botao) botao.disabled = false;
  }
}

// ── SALVAR DADOS DO USUÁRIO ──────────────────────────────────────

async function salvarDadosUsuario(campo, valor) {
  const btnSalvar = elements.btnSalvar;
  btnSalvar.classList.add("carregando");
  btnSalvar.disabled = true;

  try {
    const payload = {
      nome: elements.nomeEdicao.value,
      usuario: elements.usuarioEdicao.value,
      email: elements.emailEdicao.value,
    };

    const response = await fetch(`${API_URL}?acao=update`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (data.success) {
      // Atualizar dados exibidos
      if (data.user) {
        preencherDadosUsuario({
          ...dadosUsuario,
          ...data.user,
        });
        dadosUsuario = { ...dadosUsuario, ...data.user };
      }
      mostrarToast(data.message, "sucesso");
      // Sair do modo de edição
      fecharModoEdicao();
    } else {
      mostrarToast(data.message, "erro");
    }
  } catch (error) {
    console.error("Erro ao salvar:", error);
    mostrarToast("Erro ao salvar alterações", "erro");
  } finally {
    btnSalvar.classList.remove("carregando");
    btnSalvar.disabled = false;
  }
}

// ── SALVAR PREFERÊNCIAS LOCAIS ───────────────────────────────────

function salvarPreferencias() {
  localStorage.setItem("pref_cor", corSelecionada);
  if (window.Preferencias) window.Preferencias.aplicarTudo();
  mostrarToast("Preferências salvas neste dispositivo", "sucesso");
}

// ── EXCLUIR CONTA ──────────────────────────────────────────────────

async function excluirConta() {
  if (
    !confirm(
      "⚠️ Tem certeza que deseja excluir sua conta permanentemente? Esta ação não pode ser desfeita.",
    )
  ) {
    return;
  }

  const confirmacao = window.prompt(
    "Digite EXCLUIR para confirmar a exclusão:",
  );
  if (confirmacao !== "EXCLUIR") {
    if (confirmacao !== null)
      mostrarToast("Exclusão cancelada: confirmação inválida", "erro");
    return;
  }

  try {
    const response = await fetch(`${API_URL}?acao=delete`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
    });

    const data = await response.json();

    if (data.success) {
      localStorage.removeItem("usuarioLogado");
      [
        "pref_cor",
        "pref_tema",
        "pref_censura_auto",
        "pref_idade_manual",
      ].forEach((chave) => localStorage.removeItem(chave));
      mostrarToast("Conta excluída com sucesso", "sucesso");
      setTimeout(() => {
        window.location.href = "login.html";
      }, 1500);
    } else {
      mostrarToast(data.message, "erro");
    }
  } catch (error) {
    console.error("Erro ao excluir conta:", error);
    mostrarToast("Erro ao excluir conta", "erro");
  }
}

// ── EVENTOS ────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  // Aplicar a preferência local imediatamente e sincronizar com a API em seguida
  carregarPreferenciasLocal();

  // Carregar dados do usuário
  carregarDadosUsuario();

  // Eventos de Tema
  document.querySelectorAll(".btn-tema").forEach((btn) => {
    btn.addEventListener("click", () => {
      const tema = btn.getAttribute("data-tema");
      if (window.Preferencias && window.Preferencias.aplicarTema) {
        window.Preferencias.aplicarTema(tema);
      }
      atualizarBotoesTema(tema);
      mostrarToast(`Tema ${tema} aplicado com sucesso!`);
    });
  });

  // Eventos de Filtro Manual de Idade
  document.querySelectorAll(".btn-idade-manual").forEach((btn) => {
    btn.addEventListener("click", () => {
      const idade = btn.getAttribute("data-idade");
      localStorage.setItem("pref_idade_manual", idade);
      atualizarBotoesIdade(idade);
      mostrarToast(
        `Filtro manual definido para ${idade === "0" ? "Livre" : idade + " anos"}`,
      );
    });
  });

  // Evento Toggle Censura Automática
  const toggleCensura = document.getElementById("toggle-censura-auto");
  if (toggleCensura) {
    toggleCensura.addEventListener("change", () => {
      const ativo = toggleCensura.checked;
      localStorage.setItem("pref_censura_auto", ativo);
      atualizarEstadoFiltroManual(ativo);
      mostrarToast(
        ativo
          ? "Censura automática ativada (filtro manual ignorado)"
          : "Censura automática desativada (filtro manual ativado)",
      );
    });
  }

  // ── Interação com o Círculo Cromático ──
  let arrastandoCirculo = false;
  if (elements.circuloCor) {
    elements.circuloCor.addEventListener("mousedown", (e) => {
      arrastandoCirculo = true;
      manipularInteracaoCirculo(e);
    });
    elements.circuloCor.addEventListener(
      "touchstart",
      (e) => {
        arrastandoCirculo = true;
        manipularInteracaoCirculo(e);
      },
      { passive: true },
    );
  }

  window.addEventListener("mousemove", (e) => {
    if (arrastandoCirculo) manipularInteracaoCirculo(e);
  });
  window.addEventListener("mouseup", () => {
    arrastandoCirculo = false;
  });
  window.addEventListener(
    "touchmove",
    (e) => {
      if (arrastandoCirculo) manipularInteracaoCirculo(e);
    },
    { passive: true },
  );
  window.addEventListener("touchend", () => {
    arrastandoCirculo = false;
  });

  // ── Sliders de Saturação e Luminosidade ──
  if (elements.rangeSat) {
    elements.rangeSat.addEventListener("input", (e) => {
      satAtual = parseInt(e.target.value);
      elements.valorSat.textContent = `${satAtual}%`;
      atualizarCorPorHsl(hueAtual, satAtual, lumAtual, false);
    });
  }

  if (elements.rangeLum) {
    elements.rangeLum.addEventListener("input", (e) => {
      lumAtual = parseInt(e.target.value);
      elements.valorLum.textContent = `${lumAtual}%`;
      atualizarCorPorHsl(hueAtual, satAtual, lumAtual, false);
    });
  }

  // ── Resetar cor ──
  if (elements.btnResetCor) {
    elements.btnResetCor.addEventListener("click", () => {
      const hsl = hexParaHsl(COR_PADRAO);
      atualizarCorPorHsl(hsl.h, hsl.s, hsl.l);
      mostrarToast("Cor restaurada para o padrão");
    });
  }

  // ── Paleta de Cores Padrão (Presets) ──
  document
    .querySelectorAll(".circulo-cromatico__cor-preset")
    .forEach((botaoPreset) => {
      botaoPreset.addEventListener("click", () => {
        const corHex = botaoPreset.dataset.cor;
        if (corHex) {
          const hsl = hexParaHsl(corHex);
          if (hsl) {
            atualizarCorPorHsl(
              hsl.h,
              hsl.s,
              hsl.l,
              true,
              corHex.toLowerCase(),
            );
            mostrarToast(`Cor aplicada: ${corHex}`);
          }
        }
      });
    });

  // ── Salvar preferências ──
  elements.btnSalvar.addEventListener("click", salvarPreferencias);

  // ── Botões de edição inline ──
  document.querySelectorAll(".btn-editar").forEach((btn) => {
    btn.addEventListener("click", () => {
      const campo = btn.dataset.campo;
      abrirModoEdicao(campo);
    });
  });

  document.querySelectorAll(".btn-cancelar-edicao").forEach((btn) => {
    btn.addEventListener("click", () => {
      const campo = btn.dataset.campo;
      fecharModoEdicao(campo);
      // Restaurar valor original
      const container = document.querySelector(
        `.opcao-config[data-campo="${campo}"]`,
      );
      if (container) {
        const input = container.querySelector(".campo-edicao");
        if (input && dadosUsuario[campo]) {
          input.value = dadosUsuario[campo];
        }
      }
    });
  });

  document.querySelectorAll(".btn-salvar-edicao").forEach((btn) => {
    btn.addEventListener("click", () => {
      const campo = btn.dataset.campo;
      salvarEdicaoInline(campo);
    });
  });

  // Enter nos campos de edição
  document.querySelectorAll(".campo-edicao").forEach((input) => {
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        const container = input.closest(".opcao-config");
        const campo = container?.dataset.campo;
        if (campo) salvarEdicaoInline(campo);
      }
      if (e.key === "Escape") {
        const container = input.closest(".opcao-config");
        const campo = container?.dataset.campo;
        if (campo) fecharModoEdicao(campo);
      }
    });
  });

  // ── Alterar senha ──
  elements.btnAlterarSenha.addEventListener("click", abrirModalSenha);
  elements.modalSenhaCancelar.addEventListener("click", fecharModalSenha);
  elements.modalSenhaSalvar.addEventListener("click", alterarSenha);

  // Fechar modal com ESC
  elements.modalSenha.addEventListener("keydown", (e) => {
    if (e.key === "Escape") fecharModalSenha();
  });

  // ── Excluir conta ──
  elements.btnExcluirConta.addEventListener("click", excluirConta);

  // ── Toast ao clicar ──
  elements.toast.addEventListener("click", () => {
    elements.toast.classList.remove("visivel");
  });

  elements.btnFotoPerfil?.addEventListener("click", () =>
    elements.fotoPerfilInput?.click(),
  );
  elements.fotoPerfilInput?.addEventListener("change", enviarFotoPerfil);
});

// ════════════════════════════════════════════════════════════════════════════
// BLOCO 2 — Interface do Atalho de tema (integração com window.AtalhoTema)
// ════════════════════════════════════════════════════════════════════════════

// Preferências do atalho global de tema — persistidas apenas no dispositivo.
document.addEventListener("DOMContentLoaded", () => {
  const controles = {
    ativo: document.getElementById("atalhoTemaAtivo"),
    estilo: document.getElementById("atalhoTemaEstilo"),
    posicao: document.getElementById("atalhoTemaPosicao"),
    tamanho: document.getElementById("atalhoTemaTamanho"),
  };
  const chaves = {
    ativo: "pref_atalho_tema_ativo",
    estilo: "pref_atalho_tema",
    posicao: "pref_atalho_tema_posicao",
    tamanho: "pref_atalho_tema_tamanho",
    icone: "pref_icone_tema",
  };
  const padroes = {
    estilo: "gif",
    posicao: "superior-direito",
    tamanho: "medio",
  };

  // Reconstrói o <select> de Estilo:
  // Opção 1: Pac-Man (estilo "gif" — troca claro/escuro).
  // Opção 2: Padrão/Clássico (switch pílula sem imagem).
  // Opções 3..N: personagens do catálogo (estilo "imagem" + pref_icone_tema).
  const reconstruirMenuEstilo = () => {
    const select = controles.estilo;
    if (!select || !window.AtalhoTema) return;

    const personagens = window.AtalhoTema.listarPersonagens();
    const iconeAtual = window.AtalhoTema.obterIcone();
    const estiloAtualSalvo = localStorage.getItem(chaves.estilo);

    select.innerHTML = "";

    const optPacMan = document.createElement("option");
    optPacMan.value = "gif";
    optPacMan.textContent = "Pac-Man (claro/escuro)";
    select.appendChild(optPacMan);

    const optClassico = document.createElement("option");
    optClassico.value = "padrao-classico";
    optClassico.textContent = "Padrão/Clássico";
    select.appendChild(optClassico);

    personagens.forEach((p) => {
      const opt = document.createElement("option");
      opt.value = `imagem:${p.arquivo}`;
      opt.textContent = p.nome;
      select.appendChild(opt);
    });

    let valorSelecionado;
    if (estiloAtualSalvo === "imagem") {
      valorSelecionado = `imagem:${iconeAtual}`;
      if (![...select.options].some((o) => o.value === valorSelecionado)) {
        valorSelecionado = "gif";
      }
    } else if (estiloAtualSalvo === "padrao-classico") {
      valorSelecionado = "padrao-classico";
    } else {
      valorSelecionado = "gif";
    }
    select.value = valorSelecionado;
  };

  // ── Estado inicial ────────────────────────────────────────────────────
  controles.ativo.checked = localStorage.getItem(chaves.ativo) !== "false";
  const textoEstadoAtalho = controles.ativo.parentElement.querySelector("span");
  const atualizarTextoEstado = () => {
    textoEstadoAtalho.textContent = controles.ativo.checked ? "Ativado" : "Desativado";
  };
  atualizarTextoEstado();

  ["posicao", "tamanho"].forEach((campo) => {
    const salvo = localStorage.getItem(chaves[campo]) || padroes[campo];
    if ([...controles[campo].options].some((opcao) => opcao.value === salvo)) {
      controles[campo].value = salvo;
    }
  });

  reconstruirMenuEstilo();

  // ── Salvamento ────────────────────────────────────────────────────────
  const salvarAtalho = () => {
    localStorage.setItem(chaves.ativo, String(controles.ativo.checked));
    atualizarTextoEstado();

    const valorEstilo = controles.estilo.value;
    if (valorEstilo === "gif") {
      localStorage.setItem(chaves.estilo, "gif");
    } else if (valorEstilo === "padrao-classico") {
      localStorage.setItem(chaves.estilo, "padrao-classico");
    } else if (valorEstilo.startsWith("imagem:")) {
      const arquivo = valorEstilo.slice("imagem:".length);
      localStorage.setItem(chaves.estilo, "imagem");
      localStorage.setItem(chaves.icone, arquivo);
    }

    ["posicao", "tamanho"].forEach((campo) => {
      localStorage.setItem(chaves[campo], controles[campo].value);
    });

    window.AtalhoTema?.sincronizar();
  };

  Object.values(controles).forEach((controle) => {
    controle.addEventListener("change", salvarAtalho);
  });

  salvarAtalho();
}, { once: true });

// ════════════════════════════════════════════════════════════════════════════
// BLOCO 3 — Interface do Fundo personalizado (window.FundoPersonalizado)
// ════════════════════════════════════════════════════════════════════════════

// ── FUNDO PERSONALIZADO — MENU FLUTUANTE "MODO DE FUNDO" ────────────────────
// A persistência continua 100% local (localStorage + IndexedDB).
// Esta camada cuida apenas da UI: abrir/fechar dropdown, marcar opção ativa
// e mostrar somente os controles do modo selecionado.
document.addEventListener("DOMContentLoaded", () => {
  const FP = window.FundoPersonalizado;
  if (!FP) return;

  // Dropdown
  const toggle = document.getElementById("fundoModoToggle");
  const menu = document.getElementById("fundoModoMenu");
  const opcoes = document.querySelectorAll(".fundo-modo-opcao");
  const controles = document.querySelectorAll(".fundo-controles");

  // Controles específicos
  const corInput = document.getElementById("fundoCorSolida");
  const grad1 = document.getElementById("fundoGradCor1");
  const grad2 = document.getElementById("fundoGradCor2");
  const gradAng = document.getElementById("fundoGradAngulo");
  const gradAngVal = document.getElementById("fundoGradAnguloValor");
  const presets = document.querySelectorAll(".fundo-preset");
  const imgInput = document.getElementById("fundoImagemInput");
  const imgPos = document.getElementById("fundoImagemPosicao");
  const imgTam = document.getElementById("fundoImagemTamanho");
  const btnRestaurar = document.getElementById("fundoRestaurar");

  const cfg = FP.obterConfiguracao();

  // ── Estado inicial dos inputs ──
  if (corInput) corInput.value = cfg.cor || "#0d0d1f";
  if (grad1) grad1.value = cfg.gradienteCor1 || "#2e00e6";
  if (grad2) grad2.value = cfg.gradienteCor2 || "#ec4899";
  if (gradAng) {
    gradAng.value = cfg.gradienteAngulo ?? 135;
    if (gradAngVal) gradAngVal.textContent = `${gradAng.value}°`;
  }
  if (imgPos) imgPos.value = cfg.imagemPosicao || "center";
  if (imgTam) imgTam.value = cfg.imagemTamanho || "cover";

  // ── Estado visual do seletor ──
  function atualizarModosUI(modo) {
    opcoes.forEach((btn) => {
      const ativo = btn.dataset.modo === modo;
      btn.classList.toggle("ativo", ativo);
      btn.setAttribute("aria-selected", ativo ? "true" : "false");
    });
    controles.forEach((c) => {
      c.classList.toggle("ativo", c.dataset.controle === modo);
    });
  }

  // ── Abrir / fechar menu ──
  function abrirMenu() {
    if (!toggle || !menu) return;
    toggle.setAttribute("aria-expanded", "true");
    menu.classList.add("aberto");
  }

  function fecharMenu() {
    if (!toggle || !menu) return;
    toggle.setAttribute("aria-expanded", "false");
    menu.classList.remove("aberto");
  }

  toggle?.addEventListener("click", (e) => {
    e.stopPropagation();
    const aberto = toggle.getAttribute("aria-expanded") === "true";
    aberto ? fecharMenu() : abrirMenu();
  });

  // Clique fora fecha
  document.addEventListener("click", (e) => {
    if (!menu || !toggle) return;
    if (menu.contains(e.target) || toggle.contains(e.target)) return;
    fecharMenu();
  });

  // Esc fecha
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") fecharMenu();
  });

  // Navegação por teclado dentro do menu
  menu?.addEventListener("keydown", (e) => {
    const itens = Array.from(menu.querySelectorAll(".fundo-modo-opcao"));
    const idx = itens.indexOf(document.activeElement);
    if (e.key === "Escape") {
      e.preventDefault();
      fecharMenu();
      toggle?.focus();
      return;
    }
    if (e.key === "ArrowDown") {
      e.preventDefault();
      itens[(idx + 1) % itens.length]?.focus();
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      itens[(idx - 1 + itens.length) % itens.length]?.focus();
    } else if (e.key === "Home") {
      e.preventDefault();
      itens[0]?.focus();
    } else if (e.key === "End") {
      e.preventDefault();
      itens[itens.length - 1]?.focus();
    }
  });

  // ── Seleção de modo ──
  opcoes.forEach((btn) => {
    btn.addEventListener("click", () => {
      const modo = btn.dataset.modo;
      FP.definirModo(modo);
      atualizarModosUI(modo);
      fecharMenu();
      toggle?.focus();
    });
  });

  // ── Estado inicial ──
  atualizarModosUI(cfg.modo || "padrao");
  fecharMenu();

  // ── Cor sólida ──
  corInput?.addEventListener("input", () => {
    FP.definirCor(corInput.value);
    atualizarModosUI("cor");
  });

  // ── Gradiente ──
  const aplicarGradiente = () => {
    if (!grad1 || !grad2 || !gradAng) return;
    FP.definirGradiente(grad1.value, grad2.value, parseInt(gradAng.value, 10));
    atualizarModosUI("gradiente");
  };
  grad1?.addEventListener("input", aplicarGradiente);
  grad2?.addEventListener("input", aplicarGradiente);
  gradAng?.addEventListener("input", () => {
    if (gradAngVal) gradAngVal.textContent = `${gradAng.value}°`;
    aplicarGradiente();
  });

  presets.forEach((p) => {
    p.addEventListener("click", () => {
      if (!grad1 || !grad2) return;
      grad1.value = p.dataset.c1;
      grad2.value = p.dataset.c2;
      aplicarGradiente();
    });
  });

  // ── Imagem ──
  imgInput?.addEventListener("change", async () => {
    const file = imgInput.files?.[0];
    if (!file) return;
    const res = await FP.definirImagem(file, imgPos.value, imgTam.value);
    if (!res || !res.ok) {
      alert(res?.erro || "Erro ao processar imagem.");
      imgInput.value = "";
      return;
    }
    atualizarModosUI("imagem");
  });

  imgPos?.addEventListener("change", () => {
    FP.definirPosicaoImagem(imgPos.value);
  });

  imgTam?.addEventListener("change", () => {
    FP.definirTamanhoImagem(imgTam.value);
  });

  // ── Restaurar padrão ──
  btnRestaurar?.addEventListener("click", () => {
    FP.restaurarPadrao();
    atualizarModosUI("padrao");
    const c = FP.obterConfiguracao();
    if (corInput) corInput.value = c.cor;
    if (grad1) grad1.value = c.gradienteCor1;
    if (grad2) grad2.value = c.gradienteCor2;
    if (gradAng) {
      gradAng.value = c.gradienteAngulo;
      if (gradAngVal) gradAngVal.textContent = `${c.gradienteAngulo}°`;
    }
    if (imgInput) imgInput.value = "";
    if (imgPos) imgPos.value = c.imagemPosicao;
    if (imgTam) imgTam.value = c.imagemTamanho;
  });
});