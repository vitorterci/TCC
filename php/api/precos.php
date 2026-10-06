<?php

/**
 * API DE PREÇOS - GAMESEARCH
 *
 * Fluxo oficial (QA-005):
 *
 * slug
 *   ↓
 * catálogo simulado (_tcc_catalogo.php)
 *   ↓
 * SteamFake / GOGFake / EpicFake
 *   ↓
 * normalização das ofertas
 *   ↓
 * menor preço primeiro
 *   ↓
 * JSON
 *
 * Estrutura real utilizada:
 *
 * catálogo simulado (via _tcc_catalogo.php):
 * - id
 * - slug
 * - nome
 * - descricao
 * - img
 * - categoria
 * - plataforma
 * - genero
 * - etaria
 * - ano
 * - status
 * - preco_atual
 * - preco_original
 * - desconto
 * - disponivel
 * - disponibilidade
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/*
|--------------------------------------------------------------------------
| OPTIONS / CORS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO
|--------------------------------------------------------------------------
*/

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../../simulados/_tcc_catalogo.php';
} catch (Throwable $e) {
    responder([
        'success' => false,
        'sucesso' => false,
        'error' => 'Erro ao carregar a configuração do banco de dados.',
        'precos' => []
    ], 500);
}

/*
|--------------------------------------------------------------------------
| CONEXÃO (apenas validação de ambiente)
|--------------------------------------------------------------------------
|
| A conexão MySQL não é mais usada como fonte de ofertas.
| Mantida apenas para validação de ambiente e compatibilidade.
|
*/

$con = null;

if (isset($conexao) && $conexao instanceof mysqli) {
    $con = $conexao;
} elseif (isset($conn) && $conn instanceof mysqli) {
    $con = $conn;
} elseif (isset($mysqli) && $mysqli instanceof mysqli) {
    $con = $mysqli;
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO DE RESPOSTA
|--------------------------------------------------------------------------
*/

function responder(array $dados, int $status = 200): void
{
    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_PRETTY_PRINT
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| FUNÇÕES AUXILIARES
|--------------------------------------------------------------------------
*/

function valorGet(string $chave): string
{
    if (!isset($_GET[$chave])) {
        return '';
    }

    return trim((string) $_GET[$chave]);
}

function normalizarPreco($valor): float
{
    if ($valor === null || $valor === '') {
        return 0.0;
    }

    return round((float) $valor, 2);
}

function normalizarDesconto($valor): float
{
    if ($valor === null || $valor === '') {
        return 0.0;
    }

    return round((float) $valor, 2);
}

/*
|--------------------------------------------------------------------------
| OBTER JOGO DO CATÁLOGO SIMULADO
|--------------------------------------------------------------------------
|
| Substitui as antigas obterJogoPorSlug(), obterJogoPorId() e
| obterJogoPorNome() que consultavam a tabela `jogos` do banco.
|
| Agora o jogo é localizado diretamente no catálogo simulado.
|
*/

function obterJogoDoCatalogo(string $slug): ?array
{
    $lojas = [
        'SteamFake' => '/tcc/simulados/steamfake/produto.php?slug=',
        'GOGFake' => '/tcc/simulados/gogfake/detalhes.php?slug=',
        'EpicFake' => '/tcc/simulados/epicfake/jogo.php?slug=',
    ];

    foreach ($lojas as $loja => $caminho) {
        $jogos = tcc_catalogo_buscar($loja, ['slug' => $slug, 'ofertas' => true]);
        $jogo = $jogos[0] ?? null;

        if ($jogo) {
            return $jogo;
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| OBTER JOGO DO CATÁLOGO POR ID
|--------------------------------------------------------------------------
|
| Compatibilidade com parâmetro ?id=.
| Percorre todas as lojas simuladas e procura pelo ID.
|
*/

function obterJogoDoCatalogoPorId(int $id): ?array
{
    $lojas = [
        'SteamFake',
        'GOGFake',
        'EpicFake',
    ];

    foreach ($lojas as $loja) {
        $jogos = tcc_catalogo_buscar($loja, ['ofertas' => true]);

        foreach ($jogos as $jogo) {
            if ((int) ($jogo['id'] ?? 0) === $id) {
                return $jogo;
            }
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| OBTER JOGO DO CATÁLOGO POR NOME
|--------------------------------------------------------------------------
|
| Compatibilidade com parâmetro ?jogo=.
| Percorre todas as lojas simuladas e procura pelo nome (case-insensitive).
|
*/

function obterJogoDoCatalogoPorNome(string $nome): ?array
{
    $lojas = [
        'SteamFake',
        'GOGFake',
        'EpicFake',
    ];

    $nomeNormalizado = mb_strtolower($nome, 'UTF-8');

    foreach ($lojas as $loja) {
        $jogos = tcc_catalogo_buscar($loja, ['ofertas' => true]);

        foreach ($jogos as $jogo) {
            $nomeJogo = mb_strtolower((string) ($jogo['nome'] ?? ''), 'UTF-8');

            if ($nomeJogo === $nomeNormalizado) {
                return $jogo;
            }
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| BUSCAR PREÇOS DO JOGO (CATÁLOGO SIMULADO)
|--------------------------------------------------------------------------
|
| Usa exclusivamente o catálogo simulado via _tcc_catalogo.php.
| Não consulta mais a tabela `precos` do banco.
|
*/

function obterPrecosDoJogo(string $slug): array
{
    $precos = [];

    $lojas = [
        'SteamFake' => '/tcc/simulados/steamfake/produto.php?slug=',
        'GOGFake' => '/tcc/simulados/gogfake/detalhes.php?slug=',
        'EpicFake' => '/tcc/simulados/epicfake/jogo.php?slug=',
    ];

    foreach ($lojas as $loja => $caminho) {
        $jogos = tcc_catalogo_buscar($loja, ['slug' => $slug, 'ofertas' => true]);
        $jogo = $jogos[0] ?? null;
        if (!$jogo) continue;

        $precoAtual = normalizarPreco($jogo['preco_atual'] ?? null);
        $precoOriginal = normalizarPreco($jogo['preco_original'] ?? $precoAtual);
        $disponivel = ($jogo['disponivel'] ?? true) !== false
            && ($jogo['disponibilidade'] ?? 'disponivel') === 'disponivel';
        if (!$disponivel || $precoAtual <= 0 || !is_finite($precoAtual)) continue;

        $precos[] = [
            'id' => (int)$jogo['id'],
            'jogo_id' => (int)$jogo['id'],
            'slug' => (string)$jogo['slug'],
            'loja' => $loja,
            'plataforma' => (string)($jogo['plataforma'] ?: 'PC'),
            'preco' => $precoAtual,
            'preco_antigo' => $precoOriginal,
            'moeda' => 'BRL',
            'url' => $caminho . rawurlencode($jogo['slug']),
            'disponibilidade' => 'disponivel',
            'disponivel' => true,
            'desconto' => (int)$jogo['desconto'],
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
    }

    usort($precos, static function (array $a, array $b): int {
        return $a['preco'] <=> $b['preco'] ?: strcmp($a['loja'], $b['loja']);
    });

    return $precos;
}

/*
|--------------------------------------------------------------------------
| MENOR PREÇO
|--------------------------------------------------------------------------
*/

function obterMenorPreco(array $precos): ?float
{
    if (empty($precos)) {
        return null;
    }

    $menor = null;

    foreach ($precos as $item) {

        if (
            !isset($item['preco']) ||
            !is_numeric($item['preco'])
        ) {
            continue;
        }

        $preco = (float) $item['preco'];

        if ($preco <= 0) {
            continue;
        }

        if ($menor === null || $preco < $menor) {
            $menor = $preco;
        }
    }

    return $menor !== null
        ? round($menor, 2)
        : null;
}

/*
|--------------------------------------------------------------------------
| LOJA DO MENOR PREÇO
|--------------------------------------------------------------------------
*/

function obterOfertaMaisBarata(array $precos): ?array
{
    if (empty($precos)) {
        return null;
    }

    $menor = null;

    foreach ($precos as $item) {

        if (
            !isset($item['preco']) ||
            !is_numeric($item['preco'])
        ) {
            continue;
        }

        $preco = (float) $item['preco'];

        if ($preco <= 0) {
            continue;
        }

        if (
            $menor === null ||
            $preco < (float) $menor['preco']
        ) {
            $menor = $item;
        }
    }

    return $menor;
}

/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS
|--------------------------------------------------------------------------
*/

function obterEstatisticas(array $precos): array
{
    if (empty($precos)) {
        return [
            'quantidade_ofertas' => 0,
            'quantidade_lojas' => 0,
            'menor_preco' => null,
            'maior_preco' => null,
            'media_preco' => null
        ];
    }

    $valores = [];
    $lojas = [];

    foreach ($precos as $item) {

        if (
            isset($item['preco']) &&
            is_numeric($item['preco'])
        ) {
            $preco = (float) $item['preco'];

            if ($preco > 0) {
                $valores[] = $preco;
            }
        }

        if (!empty($item['loja'])) {
            $lojas[] = $item['loja'];
        }
    }

    $lojas = array_values(array_unique($lojas));

    if (empty($valores)) {
        return [
            'quantidade_ofertas' => count($precos),
            'quantidade_lojas' => count($lojas),
            'menor_preco' => null,
            'maior_preco' => null,
            'media_preco' => null
        ];
    }

    return [
        'quantidade_ofertas' => count($precos),
        'quantidade_lojas' => count($lojas),
        'menor_preco' => round(min($valores), 2),
        'maior_preco' => round(max($valores), 2),
        'media_preco' => round(
            array_sum($valores) / count($valores),
            2
        )
    ];
}

/*
|--------------------------------------------------------------------------
| RESPOSTA PRINCIPAL
|--------------------------------------------------------------------------
*/

function montarResposta(array $jogo, array $precos): array
{
    $menorPreco = obterMenorPreco($precos);
    $melhorOferta = obterOfertaMaisBarata($precos);
    $estatisticas = obterEstatisticas($precos);

    return [
        'success' => true,
        'sucesso' => true,

        'jogo' => [
            'id' => (int) ($jogo['id'] ?? 0),
            'slug' => (string) ($jogo['slug'] ?? ''),
            'nome' => (string) ($jogo['nome'] ?? ''),
            'descricao' => (string) ($jogo['descricao'] ?? ''),
            'img' => (string) ($jogo['img'] ?? ''),
            'categoria' => (string) ($jogo['categoria'] ?? ''),
            'plataforma' => (string) ($jogo['plataforma'] ?? 'PC'),
            'genero' => (string) ($jogo['genero'] ?? ''),
            'etaria' => (string) ($jogo['etaria'] ?? ''),
            'ano' => (string) ($jogo['ano'] ?? ''),
            'status' => (string) ($jogo['status'] ?? 'ativo')
        ],

        /*
         * Menor preço geral.
         */
        'menor_preco' => $menorPreco,

        /*
         * Compatibilidade com possíveis códigos
         * do frontend que usam "menorPreco".
         */
        'menorPreco' => $menorPreco,

        /*
         * Loja que possui o menor preço.
         */
        'melhor_oferta' => $melhorOferta,

        /*
         * Lista completa ordenada pelo menor preço.
         */
        'precos' => $precos,

        /*
         * Quantidade de ofertas.
         */
        'total' => count($precos),
        'precos_simulados' => true,

        /*
         * Estatísticas.
         */
        'estatisticas' => $estatisticas
    ];
}

/*
|--------------------------------------------------------------------------
| ENTRADA
|--------------------------------------------------------------------------
*/

$acao = strtolower(valorGet('acao')) ?: 'buscar';

$id = valorGet('id');
$slug = valorGet('slug');
$nome = valorGet('jogo');

/*
|--------------------------------------------------------------------------
| AÇÃO PADRÃO
|--------------------------------------------------------------------------
|
| Se não informar "acao", assumimos buscar.
|
*/

if ($acao === '') {
    $acao = 'buscar';
}

/*
|--------------------------------------------------------------------------
| VALIDAR AÇÃO
|--------------------------------------------------------------------------
*/

$acoesPermitidas = [
    'buscar',
    'listar',
    'precos'
];

if (!in_array($acao, $acoesPermitidas, true)) {

    responder([
        'success' => false,
        'sucesso' => false,
        'error' => 'Ação inválida.',
        'acao' => $acao,
        'acoes_permitidas' => $acoesPermitidas,
        'precos' => []
    ], 400);
}

/*
|--------------------------------------------------------------------------
| VALIDAR PARÂMETRO
|--------------------------------------------------------------------------
|
| Prioridade:
|
| 1. slug
| 2. id
| 3. jogo
|
*/

if ($slug === '' && $id === '' && $nome === '') {

    responder([
        'success' => false,
        'sucesso' => false,
        'error' => 'Informe o slug do jogo.',
        'exemplo' => 'precos.php?acao=buscar&slug=elden-ring',
        'precos' => []
    ], 400);
}

/*
|--------------------------------------------------------------------------
| LOCALIZAR JOGO NO CATÁLOGO SIMULADO
|--------------------------------------------------------------------------
*/

$jogo = null;

/*
 * SLUG = fluxo oficial
 */
if ($slug !== '') {

    $jogo = obterJogoDoCatalogo($slug);
}

/*
 * ID = compatibilidade
 */ elseif ($id !== '') {

    if (!ctype_digit($id)) {

        responder([
            'success' => false,
            'sucesso' => false,
            'error' => 'ID do jogo inválido.',
            'precos' => []
        ], 400);
    }

    $jogo = obterJogoDoCatalogoPorId((int) $id);
}

/*
 * NOME = compatibilidade
 */ elseif ($nome !== '') {

    $jogo = obterJogoDoCatalogoPorNome($nome);
}

/*
|--------------------------------------------------------------------------
| JOGO NÃO ENCONTRADO
|--------------------------------------------------------------------------
*/

if (!$jogo) {

    responder([
        'success' => false,
        'sucesso' => false,
        'error' => 'Jogo não encontrado.',
        'slug_consultado' => $slug !== '' ? $slug : null,
        'id_consultado' => $id !== '' ? $id : null,
        'nome_consultado' => $nome !== '' ? $nome : null,
        'precos' => []
    ], 404);
}

/*
|--------------------------------------------------------------------------
| VERIFICAR STATUS
|--------------------------------------------------------------------------
|
| No catálogo simulado, o status é derivado de "disponivel".
|
*/

if (
    isset($jogo['disponivel']) &&
    $jogo['disponivel'] === false
) {

    responder([
        'success' => false,
        'sucesso' => false,
        'error' => 'Este jogo está inativo.',
        'jogo' => [
            'id' => (int) ($jogo['id'] ?? 0),
            'slug' => (string) ($jogo['slug'] ?? ''),
            'nome' => (string) ($jogo['nome'] ?? '')
        ],
        'precos' => []
    ], 404);
}

/*
|--------------------------------------------------------------------------
| BUSCAR PREÇOS
|--------------------------------------------------------------------------
*/

$precos = obterPrecosDoJogo(
    (string) ($jogo['slug'] ?? $slug)
);

/*
|--------------------------------------------------------------------------
| RETORNAR RESULTADO
|--------------------------------------------------------------------------
*/

$resposta = montarResposta(
    $jogo,
    $precos
);

/*
|--------------------------------------------------------------------------
| MENSAGEM QUANDO NÃO HÁ PREÇOS
|--------------------------------------------------------------------------
*/

if (empty($precos)) {

    $resposta['mensagem'] =
        'Jogo encontrado, mas não existem ofertas disponíveis cadastradas para este jogo.';
}

/*
|--------------------------------------------------------------------------
| RESPOSTA FINAL
|--------------------------------------------------------------------------
*/

responder($resposta, 200);
