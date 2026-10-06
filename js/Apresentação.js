document.addEventListener('DOMContentLoaded', () => {
    // ── Splash Screen ──
    const splash = document.getElementById('splash-screen');
    if (splash) {
        setTimeout(() => {
            splash.classList.add('hidden');
            setTimeout(() => splash.remove(), 800);
        }, 500);
    }

    // ── Intersection Observer para Fade-in ──
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in, .reveal').forEach(el => observer.observe(el));

    // ── Efeito Shuffle nos textos (VERSÃO RÁPIDA) ──
    function shuffleText(element, targetText, options = {}) {
        const {
            duration = 0.35,
            shuffleDirection = 'right',
            shuffleTimes = 1,
            ease = 'power3.out',
            stagger = 0.03,
            threshold = 0.1,
            triggerOnce = true,
            triggerOnHover = false,
            respectReducedMotion = true,
            loop = false,
            loopDelay = 0
        } = options;

        // Se o usuário prefere movimento reduzido, aplicamos o texto diretamente
        if (respectReducedMotion && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            element.textContent = targetText;
            return;
        }

        const chars = targetText.split('');
        const total = chars.length;
        let isAnimating = false;
        let timerId = null;

        function randomChar() {
            const pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*?';
            return pool[Math.floor(Math.random() * pool.length)];
        }

        function startAnimationWithStagger() {
            if (isAnimating) return;
            // Preenche com caracteres aleatórios
            let initialText = '';
            for (let i = 0; i < total; i++) {
                initialText += randomChar();
            }
            element.textContent = initialText;
            isAnimating = true;
            let index = 0;

            // MAIS RÁPIDO: menos trocas por caractere
            const maxShuffles = shuffleTimes * 3 + 2; // antes era *5+3

            function processNext() {
                if (index >= total) {
                    isAnimating = false;
                    element.textContent = targetText; // garantia
                    if (loop) {
                        timerId = setTimeout(() => {
                            resetAndStart();
                        }, loopDelay * 1000);
                    }
                    return;
                }
                const targetChar = chars[index];
                let shuffleCount = 0;

                function shuffleChar() {
                    if (shuffleCount >= maxShuffles) {
                        const currentText = element.textContent.split('');
                        currentText[index] = targetChar;
                        element.textContent = currentText.join('');
                        index++;
                        // Agenda o próximo caractere com stagger
                        setTimeout(processNext, stagger * 1000);
                        return;
                    }
                    const currentText = element.textContent.split('');
                    currentText[index] = randomChar();
                    element.textContent = currentText.join('');
                    shuffleCount++;
                    // MAIS RÁPIDO: intervalos menores (30ms → 5ms)
                    const delay = Math.max(5, 30 - (shuffleCount / maxShuffles) * 25);
                    setTimeout(shuffleChar, delay);
                }

                shuffleChar();
            }

            processNext();
        }

        function resetAndStart() {
            if (timerId) {
                clearTimeout(timerId);
                timerId = null;
            }
            isAnimating = false;
            startAnimationWithStagger();
        }

        // Inicia pela primeira vez
        resetAndStart();

        // Trigger on hover
        if (triggerOnHover) {
            element.addEventListener('mouseenter', () => {
                if (loop) {
                    resetAndStart();
                } else if (!isAnimating) {
                    resetAndStart();
                }
            });
        }

        return resetAndStart;
    }

    // Aplicar ao título (GAME e SEARCH) com stagger mais rápido
    const gameSpan = document.querySelector('.hero-titulo .game');
    const searchSpan = document.querySelector('.hero-titulo .search');
    const subtitulo = document.querySelector('.hero-subtitulo');

    if (gameSpan) {
        shuffleText(gameSpan, 'GAME', {
            stagger: 0.02,          // mais rápido
            shuffleTimes: 1,
            triggerOnHover: true,
            loop: false,
            respectReducedMotion: true
        });
    }

    if (searchSpan) {
        shuffleText(searchSpan, 'SEARCH', {
            stagger: 0.02,          // mais rápido
            shuffleTimes: 1,
            triggerOnHover: true,
            loop: false,
            respectReducedMotion: true
        });
    }

    // O subtítulo permanece estático para nunca aparecer parcialmente ilegível
    // durante o carregamento ou em capturas rápidas da página.
});
