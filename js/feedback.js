/**
 * Gerenciamento do Formulário de Feedback
 * Projeto: TCC (Game Search)
 * Envio assíncrono via Fetch API para php/feedback.php
 */

document.addEventListener('DOMContentLoaded', () => {
    const formularioFeedback = document.getElementById('formulario-feedback');
    const mensagemFeedback = document.getElementById('mensagem-feedback');
    const btnEnviar = document.getElementById('btn-enviar-feedback');

    // Interatividade opcional para estrelas de avaliação
    const estrelas = document.querySelectorAll('.stars-rating i');
    let avaliacaoSelecionada = 5;

    estrelas.forEach((estrela, index) => {
        estrela.style.cursor = 'pointer';
        estrela.addEventListener('click', () => {
            avaliacaoSelecionada = index + 1;
            estrelas.forEach((e, i) => {
                if (i <= index) {
                    e.classList.remove('far');
                    e.classList.add('fas');
                    e.style.color = '#f5c542';
                } else {
                    e.classList.remove('fas');
                    e.classList.add('far');
                    e.style.color = '#f5c542';
                }
            });
        });
    });

    // Interatividade para as tags rápidas de problemas
    const tagsProblema = document.querySelectorAll('.tag-problema');
    const campoMensagem = document.getElementById('mensagem');

    tagsProblema.forEach(tag => {
        tag.addEventListener('click', () => {
            const textoTag = tag.textContent;
            if (campoMensagem) {
                if (campoMensagem.value) {
                    campoMensagem.value += ' - ' + textoTag;
                } else {
                    campoMensagem.value = 'Problema relatado: ' + textoTag;
                }
                campoMensagem.focus();
            }
        });
    });

    if (formularioFeedback) {
        if (btnEnviar) {
            btnEnviar.dataset.textoOriginal = btnEnviar.innerHTML;
        }

        formularioFeedback.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const tipo = document.getElementById('tipo').value;
            const mensagem = document.getElementById('mensagem').value.trim();

            // Validação frontend
            if (!mensagem) {
                exibirMensagem('O campo de mensagem é obrigatório.', 'erro');
                return;
            }

            if (email && !validarEmail(email)) {
                exibirMensagem('Informe um endereço de e-mail válido.', 'erro');
                return;
            }

            // Estado de carregamento
            setLoading(true);

            try {
                const resposta = await fetch('../php/feedback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, email, tipo, mensagem, avaliacao: avaliacaoSelecionada })
                });

                const resultado = await resposta.json();

                if (resultado.success) {
                    exibirMensagem(resultado.message, 'sucesso');
                    formularioFeedback.reset();
                } else {
                    exibirMensagem(resultado.message || 'Erro ao enviar feedback.', 'erro');
                }
            } catch (erro) {
                console.error('Erro na requisição:', erro);
                exibirMensagem('Erro de conexão com o servidor. Tente novamente mais tarde.', 'erro');
            } finally {
                setLoading(false);
            }
        });
    }

    function exibirMensagem(texto, tipo) {
        if (!mensagemFeedback) return;
        mensagemFeedback.textContent = texto;
        mensagemFeedback.style.display = 'block';

        if (tipo === 'sucesso') {
            mensagemFeedback.style.backgroundColor = 'rgba(40, 167, 69, 0.2)';
            mensagemFeedback.style.color = '#28a745';
            mensagemFeedback.style.border = '1px solid #28a745';
        } else {
            mensagemFeedback.style.backgroundColor = 'rgba(220, 53, 69, 0.2)';
            mensagemFeedback.style.color = '#dc3545';
            mensagemFeedback.style.border = '1px solid #dc3545';
        }

        setTimeout(() => {
            mensagemFeedback.style.display = 'none';
        }, 6000);
    }

    function validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function setLoading(carregando) {
        if (!btnEnviar) return;
        btnEnviar.disabled = carregando;
        if (carregando) {
            btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Enviando...</span>';
        } else {
            btnEnviar.innerHTML = btnEnviar.dataset.textoOriginal;
        }
    }
});
