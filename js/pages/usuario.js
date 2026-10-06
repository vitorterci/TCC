/**
 * usuario.js
 * Fonte única dos dados e funções da conta do usuário.
 * Usado por:
 * - perfil.html
 * - configuracao.html
 */

const Usuario = (() => {

    const API = '../php/api/user.php';

    let dadosUsuario = null;

    /**
     * Carrega os dados do usuário logado.
     */
    async function carregar() {
        try {
            const resposta = await fetch(`${API}?acao=get`, {
                method: 'GET',
                credentials: 'include',
                cache: 'no-store'
            });

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.success) {
                throw new Error(
                    resultado.message || 'Não foi possível carregar os dados do usuário.'
                );
            }

            dadosUsuario =
                resultado.usuario ||
                resultado.data ||
                resultado;

            return dadosUsuario;

        } catch (erro) {
            console.error('Erro ao carregar usuário:', erro);
            throw erro;
        }
    }


    /**
     * Retorna os dados atualmente carregados.
     */
    function obter() {
        return dadosUsuario;
    }


    /**
     * Retorna um campo específico do usuário.
     */
    function obterCampo(campo) {
        return dadosUsuario ? dadosUsuario[campo] : null;
    }


    /**
     * Verifica se existe usuário carregado.
     */
    function estaLogado() {
        return dadosUsuario !== null;
    }


    /**
     * Atualiza nome, usuário e e-mail.
     */
    async function atualizar(dados) {
        try {
            const resposta = await fetch(`${API}?acao=update`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            });

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.success) {
                throw new Error(
                    resultado.message || 'Não foi possível atualizar os dados.'
                );
            }

            const usuarioAtualizado =
                resultado.usuario ||
                resultado.data ||
                null;

            if (usuarioAtualizado) {
                dadosUsuario = {
                    ...dadosUsuario,
                    ...usuarioAtualizado
                };
            } else if (dadosUsuario) {
                dadosUsuario = {
                    ...dadosUsuario,
                    ...dados
                };
            }

            return dadosUsuario;

        } catch (erro) {
            console.error('Erro ao atualizar usuário:', erro);
            throw erro;
        }
    }


    /**
     * Altera a senha do usuário.
     */
    async function alterarSenha(senhaAtual, novaSenha) {
        try {
            const resposta = await fetch(`${API}?acao=update_password`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    senha_atual: senhaAtual,
                    nova_senha: novaSenha
                })
            });

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.success) {
                throw new Error(
                    resultado.message || 'Não foi possível alterar a senha.'
                );
            }

            return resultado;

        } catch (erro) {
            console.error('Erro ao alterar senha:', erro);
            throw erro;
        }
    }


    /**
     * Envia uma nova foto de perfil.
     */
    async function atualizarFoto(arquivo) {

        if (!arquivo) {
            throw new Error('Nenhuma imagem foi selecionada.');
        }

        const tiposPermitidos = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!tiposPermitidos.includes(arquivo.type)) {
            throw new Error(
                'Formato inválido. Use JPG, PNG ou WEBP.'
            );
        }

        const tamanhoMaximo = 5 * 1024 * 1024;

        if (arquivo.size > tamanhoMaximo) {
            throw new Error(
                'A imagem deve ter no máximo 5 MB.'
            );
        }

        const formData = new FormData();
        formData.append('foto', arquivo);

        try {
            const resposta = await fetch(
                `${API}?acao=update_photo`,
                {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                }
            );

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.success) {
                throw new Error(
                    resultado.message ||
                    'Não foi possível atualizar a foto.'
                );
            }

            const novaFoto =
                resultado.foto_perfil ||
                resultado.usuario?.foto_perfil ||
                resultado.data?.foto_perfil;

            if (dadosUsuario && novaFoto) {
                dadosUsuario.foto_perfil = novaFoto;
            }

            return resultado;

        } catch (erro) {
            console.error('Erro ao atualizar foto:', erro);
            throw erro;
        }
    }


    /**
     * Faz logout do usuário.
     */
    async function sair() {
        try {
            const resposta = await fetch(
                `${API}?acao=logout`,
                {
                    method: 'POST',
                    credentials: 'include'
                }
            );

            const resultado = await resposta.json();

            dadosUsuario = null;

            return resultado;

        } catch (erro) {
            console.error('Erro ao sair:', erro);

            dadosUsuario = null;

            throw erro;
        }
    }


    /**
     * Retorna o caminho da foto de perfil.
     */
    function obterFoto() {
        if (!dadosUsuario || !dadosUsuario.foto_perfil) {
            return null;
        }

        return dadosUsuario.foto_perfil;
    }


    /**
     * Retorna o nome para exibição.
     */
    function obterNome() {
        return dadosUsuario?.nome || '';
    }


    /**
     * Retorna o nome de usuário.
     */
    function obterUsuario() {
        return dadosUsuario?.usuario || '';
    }


    /**
     * Retorna o e-mail.
     */
    function obterEmail() {
        return dadosUsuario?.email || '';
    }


    /**
     * Retorna a função/role.
     */
    function obterRole() {
        return dadosUsuario?.role || 'usuario';
    }


    /**
     * Retorna a data de cadastro.
     */
    function obterDataCadastro() {
        return dadosUsuario?.data_cadastro || '';
    }


    return {
        carregar,
        obter,
        obterCampo,
        estaLogado,
        atualizar,
        alterarSenha,
        atualizarFoto,
        sair,
        obterFoto,
        obterNome,
        obterUsuario,
        obterEmail,
        obterRole,
        obterDataCadastro
    };

})();