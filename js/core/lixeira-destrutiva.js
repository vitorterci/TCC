/**
 * Lixeira Destrutiva — Game Search
 * Componente reutilizável para ações de exclusão com animação.
 * 
 * Uso:
 *   const lixeira = new LixeiraDestrutiva(botao, {
 *     onDelete: async () => { ... },  // Executa a exclusão real
 *     onError: (err) => { ... },       // Tratamento de erro
 *     onSuccess: () => { ... },        // Após exclusão bem-sucedida
 *     target: elemento,                // Elemento que será "destruído"
 *     confirmMessage: '...',           // Mensagem de confirmação
 *     size: 'sm' | 'md' | 'lg',        // Tamanho
 *     inline: false,                   // Modo inline (com texto)
 *     text: 'Remover',                 // Texto no modo inline
 *   });
 */

(function () {
  'use strict';

  const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  class LixeiraDestrutiva {
    constructor(elemento, opcoes = {}) {
      this.el = typeof elemento === 'string'
        ? document.querySelector(elemento)
        : elemento;

      if (!this.el) {
        console.warn('[LixeiraDestrutiva] Elemento não encontrado.');
        return;
      }

      this.opcoes = {
        onDelete: null,
        onError: null,
        onSuccess: null,
        target: null,
        confirmMessage: null,
        size: 'md',
        inline: false,
        text: '',
        ...opcoes,
      };

      this.estado = 'idle'; // idle | deleting | error
      this.particulas = [];

      this._inicializar();
    }

    _inicializar() {
      // Se o elemento não é um button, cria a estrutura interna
      if (this.el.tagName !== 'BUTTON') {
        this.el.setAttribute('role', 'button');
        this.el.setAttribute('tabindex', '0');
      }

      this.el.setAttribute('type', 'button');
      this.el.classList.add('lixeira-destrutiva');

      // Aplica tamanho
      if (this.opcoes.size === 'sm') {
        this.el.classList.add('lixeira-destrutiva--sm');
      } else if (this.opcoes.size === 'lg') {
        this.el.classList.add('lixeira-destrutiva--lg');
      }

      // Modo inline
      if (this.opcoes.inline) {
        this.el.classList.add('lixeira-destrutiva--inline');
      }

      // Acessibilidade
      if (!this.el.getAttribute('aria-label')) {
        this.el.setAttribute(
          'aria-label',
          this.opcoes.text || 'Excluir item'
        );
      }
      if (!this.el.getAttribute('title')) {
        this.el.setAttribute(
          'title',
          this.opcoes.text || 'Excluir'
        );
      }

      // Constrói estrutura visual
      this._construirEstrutura();

      // Eventos
      this._vincularEventos();
    }

    _construirEstrutura() {
      // Limpa conteúdo existente (ícones do FontAwesome, etc.)
      this.el.innerHTML = '';

      const corpo = document.createElement('span');
      corpo.className = 'lixeira-destrutiva__corpo';

      // Alça
      const alca = document.createElement('span');
      alca.className = 'lixeira-destrutiva__alca';

      // Tampa
      const tampa = document.createElement('span');
      tampa.className = 'lixeira-destrutiva__tampa';

      // Balde
      const balde = document.createElement('span');
      balde.className = 'lixeira-destrutiva__balde';

      // Lixo (preenchimento animado)
      const lixo = document.createElement('span');
      lixo.className = 'lixeira-destrutiva__lixo';

      balde.appendChild(lixo);

      corpo.appendChild(alca);
      corpo.appendChild(tampa);
      corpo.appendChild(balde);

      this.el.appendChild(corpo);

      // Texto (modo inline)
      if (this.opcoes.inline && this.opcoes.text) {
        const texto = document.createElement('span');
        texto.className = 'lixeira-destrutiva__texto';
        texto.textContent = this.opcoes.text;
        this.el.appendChild(texto);
      }

      // Partículas (criadas dinamicamente)
      this._criarParticulas();
    }

    _criarParticulas() {
      // Remove partículas antigas
      this.particulas.forEach(p => p.remove());
      this.particulas = [];

      if (REDUCED_MOTION) return;

      const quantidade = 8;

      for (let i = 0; i < quantidade; i++) {
        const particula = document.createElement('span');
        particula.className = 'lixeira-destrutiva__particula';

        // Distribui as partículas em círculo
        const angulo = (i / quantidade) * Math.PI * 2;
        const distancia = 12 + Math.random() * 10;

        particula.style.setProperty(
          '--particula-x',
          `${Math.cos(angulo) * distancia}px`
        );
        particula.style.setProperty(
          '--particula-y',
          `${Math.sin(angulo) * distancia}px`
        );

        // Delay aleatório
        particula.style.animationDelay = `${0.15 + Math.random() * 0.2}s`;

        this.el.appendChild(particula);
        this.particulas.push(particula);
      }
    }

    _vincularEventos() {
      this._handlerClick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        this._executar();
      };

      this._handlerKeydown = (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this._executar();
        }
      };

      this.el.addEventListener('click', this._handlerClick);
      this.el.addEventListener('keydown', this._handlerKeydown);
    }

    async _executar() {
      if (this.estado !== 'idle') return;

      // 1. Confirmação existente
      if (this.opcoes.confirmMessage) {
        if (!window.confirm(this.opcoes.confirmMessage)) {
          return;
        }
      }

      // 2. Inicia animação
      this._iniciarAnimacao();

      // 3. Executa exclusão real
      try {
        if (typeof this.opcoes.onDelete === 'function') {
          await this.opcoes.onDelete();
        }

        // 4. Sucesso — finaliza animação
        this._finalizarAnimacao();

        if (typeof this.opcoes.onSuccess === 'function') {
          this.opcoes.onSuccess();
        }
      } catch (erro) {
        // 5. Erro — restaura estado
        this._cancelarAnimacao();

        if (typeof this.opcoes.onError === 'function') {
          this.opcoes.onError(erro);
        } else {
          console.error('[LixeiraDestrutiva] Erro na exclusão:', erro);
        }
      }
    }

    _iniciarAnimacao() {
      this.estado = 'deleting';
      this.el.classList.add('is-deleting');
      this.el.disabled = true;

      // Marca o elemento alvo como sendo destruído
      if (this.opcoes.target) {
        this.opcoes.target.classList.add('is-being-destroyed');
      }

      // Vibração (se suportado)
      if (navigator.vibrate) {
        navigator.vibrate(30);
      }
    }

    _finalizarAnimacao() {
      this.estado = 'idle';
      this.el.classList.remove('is-deleting');
      this.el.disabled = false;

      // Remove o elemento alvo da interface
      if (this.opcoes.target) {
        // Aguarda a animação CSS terminar
        const tempoAnimacao = REDUCED_MOTION ? 200 : 550;
        setTimeout(() => {
          this.opcoes.target.remove();
        }, tempoAnimacao);
      }

      // Recria partículas para próxima interação
      this._criarParticulas();
    }

    _cancelarAnimacao() {
      this.estado = 'idle';
      this.el.classList.remove('is-deleting');
      this.el.disabled = false;

      // Remove classe do alvo
      if (this.opcoes.target) {
        this.opcoes.target.classList.remove('is-being-destroyed');
      }

      // Recria partículas
      this._criarParticulas();
    }

    /**
     * Atualiza o elemento alvo (útil para listas dinâmicas)
     */
    setTarget(novoAlvo) {
      this.opcoes.target = novoAlvo;
    }

    /**
     * Remove listeners e limpa o componente
     */
    destroy() {
      this.el.removeEventListener('click', this._handlerClick);
      this.el.removeEventListener('keydown', this._handlerKeydown);
      this.particulas.forEach(p => p.remove());
      this.particulas = [];
    }
  }

  // Expõe globalmente
  window.LixeiraDestrutiva = LixeiraDestrutiva;

  /**
   * Helper: aplica lixeira destrutiva a todos os botões com
   * [data-lixeira-destrutiva] em um container.
   */
  window.aplicarLixeirasDestrutivas = function (container = document) {
    const botoes = container.querySelectorAll('[data-lixeira-destrutiva]');
    const instancias = [];

    botoes.forEach((botao) => {
      const config = botao.dataset;

      const instancia = new LixeiraDestrutiva(botao, {
        onDelete: config.lixeiraOnDelete
          ? window[config.lixeiraOnDelete]
          : null,
        confirmMessage: config.lixeiraConfirm || null,
        size: config.lixeiraSize || 'md',
        inline: config.lixeiraInline === 'true',
        text: config.lixeiraText || '',
      });

      instancias.push(instancia);
    });

    return instancias;
  };

})();