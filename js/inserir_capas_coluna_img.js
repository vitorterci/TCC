/**
 * Script para inserir os caminhos das capas de jogos na coluna 'img' já existente na tabela 'jogos'.
 * Projeto TCC - Desenvolvimento Web
 */

const mysql = require('mysql2/promise');

// Configuração da conexão com o banco de dados MySQL
const configuracaoBanco = {
    host: 'localhost',
    user: 'root',
    password: 'sua_senha',
    database: 'tcc_jogos_db'
};

// Lista de jogos com seus respectivos títulos, anos e caminhos de imagem para a coluna 'img'
const listaJogos = [
    {
        titulo: 'Grand Theft Auto V',
        img: '/capas_jogos/gta_v.jpg',
        anoLancamento: '2013'
    },
    {
        titulo: 'God of War Ragnarök',
        img: '/capas_jogos/god_of_war_ragnarok.png',
        anoLancamento: '2022'
    },
    {
        titulo: 'The Last of Us Part II',
        img: '/capas_jogos/the_last_of_us_2.jpg',
        anoLancamento: '2020'
    },
    {
        titulo: 'Super Mario Odyssey',
        img: '/capas_jogos/mario_odyssey.jpg',
        anoLancamento: '2017'
    },
    {
        titulo: 'Minecraft',
        img: '/capas_jogos/minecraft.png',
        anoLancamento: '2011'
    },
    {
        titulo: 'The Witcher 3: Wild Hunt',
        img: '/capas_jogos/witcher_3.png',
        anoLancamento: '2015'
    },
    {
        titulo: 'Elden Ring',
        img: '/capas_jogos/elden_ring.jpg',
        anoLancamento: '2022'
    },
    {
        titulo: 'Horizon Forbidden West',
        img: '/capas_jogos/horizon_forbidden_west.jpg',
        anoLancamento: '2022'
    },
    {
        titulo: 'Doom',
        img: '/capas_jogos/doom.jpg',
        anoLancamento: '1993'
    },
    {
        titulo: 'Mega Man',
        img: '/capas_jogos/mega_man.jpg',
        anoLancamento: '1987'
    }
];

async function inserirCaminhosImagens() {
    let conexao;
    try {
        // Conecta ao banco de dados
        conexao = await mysql.createConnection(configuracaoBanco);
        console.log('Conexão com o banco de dados estabelecida com sucesso.');

        // Instrução SQL utilizando especificamente a coluna 'img' conforme solicitado
        const consultaSql = `
            INSERT INTO jogos (titulo, img, ano_lancamento) 
            VALUES (?, ?, ?)
        `;

        for (const jogo of listaJogos) {
            await conexao.execute(consultaSql, [jogo.titulo, jogo.img, jogo.anoLancamento]);
            console.log(`Registro do jogo "${jogo.titulo}" inserido com o caminho "${jogo.img}" na coluna img.`);
        }

        console.log('Todos os registros foram inseridos com sucesso!');
    } catch (erro) {
        console.error('Erro ao inserir registros no banco de dados:', erro.message);
    } finally {
        if (conexao) {
            await conexao.end();
            console.log('Conexão com o banco de dados encerrada.');
        }
    }
}

// Executa a rotina de inserção
inserirCaminhosImagens();
