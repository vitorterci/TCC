/**
 * Gerenciamento da página de Login e Cadastro
 * Integração com PHP Backend via Fetch API
 */

document.addEventListener('DOMContentLoaded', () => {
    const botaoRegistrar = document.getElementById('registrar');
    const botaoLogar = document.getElementById('logar');
    const containerAutenticacao = document.getElementById('autenticacao');
    const linkIrParaCadastro = document.getElementById('ir-para-cadastro');
    const linkIrParaLogin = document.getElementById('ir-para-login');

    // --- Funções de navegação entre telas ---
    const ativarCadastro = () => {
        if (containerAutenticacao) containerAutenticacao.classList.add('ativo');
    };

    const ativarLogin = () => {
        if (containerAutenticacao) containerAutenticacao.classList.remove('ativo');
    };

    if (botaoRegistrar) botaoRegistrar.addEventListener('click', ativarCadastro);
    if (botaoLogar) botaoLogar.addEventListener('click', ativarLogin);
    if (linkIrParaCadastro) linkIrParaCadastro.addEventListener('click', (e) => {
        e.preventDefault();
        ativarCadastro();
    });
    if (linkIrParaLogin) linkIrParaLogin.addEventListener('click', (e) => {
        e.preventDefault();
        ativarLogin();
    });

    // --- Função para exibir mensagens ---
    function mostrarMensagem(elemento, mensagem, tipo) {
        if (!elemento) return;
        elemento.textContent = mensagem;
        elemento.className = 'formulario__mensagem ' + tipo;
        elemento.style.display = 'block';
        
        // Auto-esconder após 5 segundos
        clearTimeout(elemento._timeout);
        elemento._timeout = setTimeout(() => {
            elemento.style.display = 'none';
        }, 5000);
    }

    // --- Função para habilitar/desabilitar botão com loading ---
    function setLoading(botao, isLoading) {
        if (!botao) return;
        botao.disabled = isLoading;
        if (isLoading) {
            botao.innerHTML = '<span class="spinner"></span> Carregando...';
        } else {
            botao.innerHTML = botao.dataset.textoOriginal || botao.textContent;
        }
    }

    // --- CADASTRO ---
    const formCadastro = document.getElementById('formulario-cadastro');
    const mensagemCadastro = document.getElementById('mensagem-cadastro');
    const btnCadastrar = document.getElementById('btn-cadastrar');

    if (formCadastro) {
        // Salvar texto original do botão
        if (btnCadastrar) btnCadastrar.dataset.textoOriginal = btnCadastrar.innerHTML;

        formCadastro.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Coletar dados
            const nome = formCadastro.elements.nome.value.trim();
            const usuario = formCadastro.elements.usuario.value.trim();
            const email = formCadastro.elements.email.value.trim();
            const senha = formCadastro.elements.senha.value;
            const confirmarSenha = formCadastro.elements.confirmar_senha.value;

            // Validações
            if (!nome || !usuario || !email || !senha || !confirmarSenha) {
                mostrarMensagem(mensagemCadastro, 'Preencha todos os campos', 'erro');
                return;
            }

            if (senha !== confirmarSenha) {
                mostrarMensagem(mensagemCadastro, 'As senhas não coincidem!', 'erro');
                return;
            }

            if (senha.length < 6) {
                mostrarMensagem(mensagemCadastro, 'A senha deve ter pelo menos 6 caracteres', 'erro');
                return;
            }

            // Enviar para o backend
            setLoading(btnCadastrar, true);

            try {
                const response = await fetch('../php/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, usuario, email, senha })
                });

                const data = await response.json();
                
                if (data.success) {
                    mostrarMensagem(mensagemCadastro, data.message, 'sucesso');
                    // Limpar formulário
                    formCadastro.reset();
                    // Mudar para tela de login após 1.5s
                    setTimeout(() => ativarLogin(), 1500);
                } else {
                    mostrarMensagem(mensagemCadastro, data.message, 'erro');
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarMensagem(mensagemCadastro, 'Erro ao conectar com o servidor. Verifique se o backend está rodando.', 'erro');
            } finally {
                setLoading(btnCadastrar, false);
            }
        });
    }

    // --- Destino após autenticação ---
    const CHAVE_RETORNO_LOGIN = 'retornoAposLogin';

    function obterDestinoSeguro(url) {
        if (!url) return null;
        try {
            const destino = new URL(url, window.location.href);
            if (destino.origin !== window.location.origin) return null;
            if (destino.pathname.endsWith('/login.html') || destino.pathname.endsWith('login.html')) return null;
            return `${destino.pathname}${destino.search}${destino.hash}`;
        } catch (erro) {
            return null;
        }
    }

    function registrarPaginaAnterior() {
        const destinoAtual = sessionStorage.getItem(CHAVE_RETORNO_LOGIN);
        if (destinoAtual) return;

        const destinoReferrer = obterDestinoSeguro(document.referrer);
        if (destinoReferrer) {
            sessionStorage.setItem(CHAVE_RETORNO_LOGIN, destinoReferrer);
        }
    }

    function obterDestinoAposLogin() {
        const destino = obterDestinoSeguro(sessionStorage.getItem(CHAVE_RETORNO_LOGIN));
        sessionStorage.removeItem(CHAVE_RETORNO_LOGIN);
        return destino || '../index.html';
    }

    registrarPaginaAnterior();

    // --- LOGIN ---
    const formLogin = document.getElementById('formulario-login');
    const mensagemLogin = document.getElementById('mensagem-login');
    const btnLogar = document.getElementById('btn-logar');

    if (formLogin) {
        if (btnLogar) btnLogar.dataset.textoOriginal = btnLogar.innerHTML;

        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = formLogin.elements.email.value.trim();
            const senha = formLogin.elements.senha.value;

            if (!email || !senha) {
                mostrarMensagem(mensagemLogin, 'Preencha todos os campos', 'erro');
                return;
            }

            setLoading(btnLogar, true);

            try {
                const response = await fetch('../php/login.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, senha })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Persistir os dados mínimos da sessão para reconhecer o usuário
                    // quando a página for aberta novamente.
                    localStorage.setItem('usuarioLogado', JSON.stringify({
                        ...data.user,
                        _persistidoEm: Date.now()
                    }));
                    mostrarMensagem(mensagemLogin, data.message, 'sucesso');
                    
                    // Retornar à página que levou o usuário ao login.
                    setTimeout(() => {
                        window.location.href = obterDestinoAposLogin();
                    }, 1000);
                } else {
                    mostrarMensagem(mensagemLogin, data.message, 'erro');
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarMensagem(mensagemLogin, 'Erro ao conectar com o servidor.', 'erro');
            } finally {
                setLoading(btnLogar, false);
            }
        });
    }

    // --- Restaurar sessão persistida ---
    async function restaurarSessaoPersistida() {
        const usuarioPersistido = localStorage.getItem('usuarioLogado');
        if (!usuarioPersistido || !window.location.pathname.includes('login.html')) return;

        try {
            const resposta = await fetch('../php/api/user.php?acao=get', {
                credentials: 'include'
            });
            const dados = await resposta.json();

            if (dados.success && dados.user) {
                localStorage.setItem('usuarioLogado', JSON.stringify({
                    ...dados.user,
                    _persistidoEm: Date.now()
                }));
                window.location.replace(obterDestinoAposLogin());
            } else {
                // O servidor não reconhece mais a sessão; não manter um login falso.
                localStorage.removeItem('usuarioLogado');
            }
        } catch (erro) {
            // Sem conexão, manter a tela de login para não ocultar o estado real.
            console.error('Não foi possível restaurar a sessão:', erro);
        }
    }

    restaurarSessaoPersistida();
});