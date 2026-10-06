<?php
/**
 * GAME SEARCH - LISTAGEM DE JOGOS
 *
 * Endpoint:
 * php/games.php
 *
 * Banco:
 * MySQL - tcc
 *
 * Fluxo:
 * jogos.id
 *    ↓
 * precos.jogo_id
 *    ↓
 * menor preço disponível
 *
 * IMPORTANTE:
 * A tabela jogos NÃO possui preço.
 * O preço vem exclusivamente da tabela precos.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| Buscar jogos
|--------------------------------------------------------------------------
|
| Campos utilizados:
| - jogos.id
| - jogos.slug
| - jogos.nome
| - jogos.descricao
| - jogos.img
| - jogos.categoria
| - jogos.plataforma
| - jogos.genero
| - jogos.etaria
| - jogos.ano
| - jogos.status
| - jogos.avaliacao_gamplay
| - jogos.avaliacao_graficos
| - jogos.avaliacao_historia
| - jogos.data_cadastro
| - jogos.data_atualizacao
|
| O preço é obtido da tabela precos.
|
*/

$sql = "
    SELECT
        j.id,
        j.slug,
        j.nome,
        j.descricao,
        j.img,
        j.categoria,
        COALESCE((
            SELECT GROUP_CONCAT(DISTINCT p.nome ORDER BY p.id SEPARATOR ', ')
            FROM jogo_plataforma jp
            INNER JOIN plataformas p ON p.id = jp.plataforma_id
            WHERE jp.jogo_id = j.id
        ), j.plataforma) AS plataforma,
        COALESCE((
            SELECT GROUP_CONCAT(DISTINCT g.nome ORDER BY g.id SEPARATOR ', ')
            FROM jogo_genero jg
            INNER JOIN generos g ON g.id = jg.genero_id
            WHERE jg.jogo_id = j.id
        ), j.genero) AS genero,
        j.etaria,
        j.ano,
        j.status,

        j.avaliacao_gamplay AS avaliacao_gameplay,
        j.avaliacao_graficos,
        j.avaliacao_historia,

        j.data_cadastro,
        j.data_atualizacao,

        /*
         * Menor preço disponível para o jogo.
         */
        (
            SELECT p.preco
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS preco,

        /*
         * Desconto correspondente à oferta mais barata.
         */
        (
            SELECT p.desconto
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS desconto,

        /*
         * Preço antigo correspondente à menor oferta.
         */
        (
            SELECT p.preco_antigo
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS preco_antigo,

        /*
         * Loja que possui a menor oferta.
         */
        (
            SELECT p.loja
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS loja_menor_preco,

        /*
         * Plataforma da menor oferta.
         */
        (
            SELECT p.plataforma
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS plataforma_preco,

        /*
         * Data da atualização da menor oferta.
         */
        (
            SELECT p.data_atualizacao
            FROM precos p
            WHERE p.jogo_id = j.id
              AND p.disponibilidade = 'disponivel'
              AND p.preco > 0
            ORDER BY p.preco ASC
            LIMIT 1
        ) AS preco_atualizado

    FROM jogos j

    WHERE j.status = 'ativo'

    ORDER BY j.id DESC
";

try {

    $resultado = $conexao->query($sql);

    if (!$resultado) {
        throw new RuntimeException(
            $conexao->error
        );
    }

    $jogos = [];

    while ($row = $resultado->fetch_assoc()) {

        /*
        |--------------------------------------------------------------------------
        | ID
        |--------------------------------------------------------------------------
        */

        $row['id'] = (int) $row['id'];

        /*
        |--------------------------------------------------------------------------
        | Ano
        |--------------------------------------------------------------------------
        */

        $row['ano'] = $row['ano'] !== null
            ? (int) $row['ano']
            : null;

        /*
        |--------------------------------------------------------------------------
        | Classificação etária
        |--------------------------------------------------------------------------
        */

        $row['etaria'] = normalizarEtaria(
            $row['etaria'] ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | Preço
        |--------------------------------------------------------------------------
        |
        | Se o jogo não possui preço cadastrado, retorna 0.
        | Isso permite que o frontend continue exibindo o jogo.
        |
        */

        $row['preco'] = (
            $row['preco'] !== null &&
            $row['preco'] !== ''
        )
            ? (float) $row['preco']
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Preço antigo
        |--------------------------------------------------------------------------
        */

        $row['preco_antigo'] = (
            $row['preco_antigo'] !== null &&
            $row['preco_antigo'] !== ''
        )
            ? (float) $row['preco_antigo']
            : null;

        /*
        |--------------------------------------------------------------------------
        | Desconto
        |--------------------------------------------------------------------------
        */

        $row['desconto'] = (
            $row['desconto'] !== null &&
            $row['desconto'] !== ''
        )
            ? (float) $row['desconto']
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Avaliações
        |--------------------------------------------------------------------------
        */

        $row['avaliacao_gameplay'] = (
            $row['avaliacao_gameplay'] !== null &&
            $row['avaliacao_gameplay'] !== ''
        )
            ? (float) $row['avaliacao_gameplay']
            : 0.0;

        $row['avaliacao_graficos'] = (
            $row['avaliacao_graficos'] !== null &&
            $row['avaliacao_graficos'] !== ''
        )
            ? (float) $row['avaliacao_graficos']
            : 0.0;

        $row['avaliacao_historia'] = (
            $row['avaliacao_historia'] !== null &&
            $row['avaliacao_historia'] !== ''
        )
            ? (float) $row['avaliacao_historia']
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Compatibilidade com o JavaScript
        |--------------------------------------------------------------------------
        |
        | O home.js trabalha com data_lancamento.
        | A estrutura REAL de jogos não possui essa coluna.
        |
        | Portanto não inventamos uma coluna no banco.
        | Retornamos null.
        |
        */

        $row['data_lancamento'] = null;

        /*
        |--------------------------------------------------------------------------
        | Adicionar jogo
        |--------------------------------------------------------------------------
        */

        $jogos[] = $row;
    }

    /*
    |--------------------------------------------------------------------------
    | Resposta
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        $jogos,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'sucesso' => false,
        'message' => 'Não foi possível consultar os jogos cadastrados.',
        'error' => $e->getMessage(),
        'jogos' => []
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conexao) && $conexao instanceof mysqli) {
    $conexao->close();
}
