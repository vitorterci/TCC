<?php
/**
 * API DE JOGOS - GAME SEARCH
 *
 * Compatível com a estrutura REAL da tabela `jogos`.
 *
 * A tabela `jogos` NÃO possui:
 * - preco
 * - desconto
 *
 * Os preços são responsabilidade da API de comparação de preços.
 */

require_once '../config.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

mysqli_report(MYSQLI_REPORT_OFF);

if (!isset($conexao) || !($conexao instanceof mysqli)) {
    http_response_code(500);

    responder([
        'success' => false,
        'sucesso' => false,
        'message' => 'Conexão com o banco de dados não disponível.'
    ]);

    exit;
}

if ($conexao->connect_errno) {
    http_response_code(500);

    responder([
        'success' => false,
        'sucesso' => false,
        'message' => 'Erro na conexão com o banco de dados.',
        'error' => $conexao->connect_error
    ]);

    exit;
}

$conexao->set_charset('utf8mb4');

$acao = strtolower(trim($_GET['acao'] ?? ''));

switch ($acao) {

    case 'listar':
        listarJogos($conexao);
        break;

    case 'buscar':
        buscarJogo($conexao);
        break;

    case 'cadastrar':
        cadastrarJogo($conexao);
        break;

    case 'atualizar':
        atualizarJogo($conexao);
        break;

    case 'excluir':
        excluirJogo($conexao);
        break;

    default:

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Ação inválida solicitada.'
        ]);

        break;
}


/*
|--------------------------------------------------------------------------
| LISTAR JOGOS
|--------------------------------------------------------------------------
*/

function listarJogos(mysqli $conexao)
{
    $sql = "
        SELECT
            id,
            slug,
            nome,
            descricao,
            img,
            categoria,
            COALESCE((
                SELECT GROUP_CONCAT(DISTINCT p.nome ORDER BY p.id SEPARATOR ', ')
                FROM jogo_plataforma jp
                INNER JOIN plataformas p ON p.id = jp.plataforma_id
                WHERE jp.jogo_id = jogos.id
            ), plataforma) AS plataforma,
            COALESCE((
                SELECT GROUP_CONCAT(DISTINCT g.nome ORDER BY g.id SEPARATOR ', ')
                FROM jogo_genero jg
                INNER JOIN generos g ON g.id = jg.genero_id
                WHERE jg.jogo_id = jogos.id
            ), genero) AS genero,
            etaria,
            ano,
            status,
            avaliacao_gamplay AS avaliacao_gameplay,
            avaliacao_graficos,
            avaliacao_historia,
            data_lancamento,
            data_cadastro,
            data_atualizacao,
            (
                SELECT MIN(p.preco)
                FROM precos p
                WHERE p.jogo_id = jogos.id
                  AND p.disponibilidade = 'disponivel'
                  AND p.preco > 0
            ) AS preco
        FROM jogos
        ORDER BY id DESC
    ";

    $result = $conexao->query($sql);

    if ($result === false) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Não foi possível consultar os jogos cadastrados.',
            'error' => $conexao->error
        ]);

        return;
    }

    $jogos = [];

    while ($row = $result->fetch_assoc()) {
        $jogos[] = prepararJogo($row);
    }

    $result->free();

    responder([
        'success' => true,
        'sucesso' => true,
        'jogos' => $jogos,
        'total' => count($jogos)
    ]);
}


/*
|--------------------------------------------------------------------------
| BUSCAR JOGO
|--------------------------------------------------------------------------
|
| Por ID:
| jogo.php?acao=buscar&id=3
|
| Por slug:
| jogo.php?acao=buscar&slug=elden-ring
|--------------------------------------------------------------------------
*/

function buscarJogo(mysqli $conexao)
{
    $id = intval($_GET['id'] ?? 0);
    $slug = trim($_GET['slug'] ?? '');

    if ($id <= 0 && $slug === '') {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Informe o ID ou o slug do jogo.'
        ]);

        return;
    }

    $campos = "
        id,
        slug,
        nome,
        descricao,
        img,
        categoria,
        COALESCE((
            SELECT GROUP_CONCAT(DISTINCT p.nome ORDER BY p.id SEPARATOR ', ')
            FROM jogo_plataforma jp
            INNER JOIN plataformas p ON p.id = jp.plataforma_id
            WHERE jp.jogo_id = jogos.id
        ), plataforma) AS plataforma,
        COALESCE((
            SELECT GROUP_CONCAT(DISTINCT g.nome ORDER BY g.id SEPARATOR ', ')
            FROM jogo_genero jg
            INNER JOIN generos g ON g.id = jg.genero_id
            WHERE jg.jogo_id = jogos.id
        ), genero) AS genero,
        etaria,
        ano,
        status,
        avaliacao_gamplay AS avaliacao_gameplay,
        avaliacao_graficos,
        avaliacao_historia,
        data_lancamento,
        data_cadastro,
        data_atualizacao
    ";

    if ($id > 0) {

        $sql = "
            SELECT $campos
            FROM jogos
            WHERE id = ?
            LIMIT 1
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {

            http_response_code(500);

            responder([
                'success' => false,
                'sucesso' => false,
                'message' => 'Erro ao preparar consulta do jogo.',
                'error' => $conexao->error
            ]);

            return;
        }

        $stmt->bind_param('i', $id);

    } else {

        $sql = "
            SELECT $campos
            FROM jogos
            WHERE slug = ?
            LIMIT 1
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {

            http_response_code(500);

            responder([
                'success' => false,
                'sucesso' => false,
                'message' => 'Erro ao preparar consulta do jogo.',
                'error' => $conexao->error
            ]);

            return;
        }

        $stmt->bind_param('s', $slug);
    }

    if (!$stmt->execute()) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao consultar o jogo.',
            'error' => $stmt->error
        ]);

        $stmt->close();

        return;
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {

        http_response_code(404);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Jogo não encontrado.',
            'jogo' => null
        ]);

        $stmt->close();

        return;
    }

    $jogo = $result->fetch_assoc();

    $jogo = prepararJogo($jogo);

    $result->free();
    $stmt->close();

    responder([
        'success' => true,
        'sucesso' => true,
        'jogo' => $jogo
    ]);
}


/*
|--------------------------------------------------------------------------
| CADASTRAR JOGO
|--------------------------------------------------------------------------
*/

function cadastrarJogo(mysqli $conexao)
{
    $dados = obterDadosEntrada();

    if (!$dados) {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Dados JSON inválidos ou ausentes.'
        ]);

        return;
    }

    $nome = trim($dados['nome'] ?? '');
    $descricao = trim($dados['descricao'] ?? '');

    if ($nome === '' || $descricao === '') {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Nome e descrição são obrigatórios.'
        ]);

        return;
    }

    $slug = trim($dados['slug'] ?? '');

    if ($slug === '') {
        $slug = gerarSlugJogo($nome);
    }

    $img = valorNulo($dados['img'] ?? null);
    $categoria = valorNulo($dados['categoria'] ?? null);
    $plataforma = valorNulo($dados['plataforma'] ?? null);
    $genero = valorNulo($dados['genero'] ?? null);

    /*
     * Usa normalizarEtaria() do config.php.
     */
    $etaria = normalizarEtaria($dados['etaria'] ?? null);

    $ano = normalizarAnoJogo($dados['ano'] ?? null);

    $status = $dados['status'] ?? 'ativo';

    if (!in_array($status, ['ativo', 'inativo'], true)) {
        $status = 'ativo';
    }

    $avaliacaoGameplay = normalizarDecimalJogo(
        $dados['avaliacao_gameplay']
        ?? $dados['avaliacao_gamplay']
        ?? null
    );

    $avaliacaoGraficos = normalizarDecimalJogo(
        $dados['avaliacao_graficos'] ?? null
    );

    $avaliacaoHistoria = normalizarDecimalJogo(
        $dados['avaliacao_historia'] ?? null
    );

    $dataLancamento = valorNulo(
        $dados['data_lancamento'] ?? null
    );

    /*
     * Verificar slug duplicado.
     */

    $checkSql = "
        SELECT id
        FROM jogos
        WHERE slug = ?
        LIMIT 1
    ";

    $checkStmt = $conexao->prepare($checkSql);

    if (!$checkStmt) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao verificar slug.',
            'error' => $conexao->error
        ]);

        return;
    }

    $checkStmt->bind_param('s', $slug);
    $checkStmt->execute();

    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {

        $checkStmt->close();

        http_response_code(409);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Já existe um jogo com este slug.'
        ]);

        return;
    }

    $checkStmt->close();

    /*
     * INSERT.
     *
     * Não existe preco/desconto.
     */

    $sql = "
        INSERT INTO jogos (
            slug,
            nome,
            descricao,
            img,
            categoria,
            plataforma,
            genero,
            etaria,
            ano,
            status,
            avaliacao_gamplay,
            avaliacao_graficos,
            avaliacao_historia,
            data_lancamento
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao preparar cadastro do jogo.',
            'error' => $conexao->error
        ]);

        return;
    }

    /*
     * 8 strings:
     * slug, nome, descricao, img, categoria,
     * plataforma, genero, etaria
     *
     * 1 inteiro:
     * ano
     *
     * 1 string:
     * status
     *
     * 3 doubles:
     * avaliações
     *
     * 1 string:
     * data_lancamento
     */

    $stmt->bind_param(
        'ssssssssisddds',
        $slug,
        $nome,
        $descricao,
        $img,
        $categoria,
        $plataforma,
        $genero,
        $etaria,
        $ano,
        $status,
        $avaliacaoGameplay,
        $avaliacaoGraficos,
        $avaliacaoHistoria,
        $dataLancamento
    );

    /*
     * Corrige espaços acidentais do tipo de bind_param.
     */
    $tipos = 'ssssssssisddds';

    $stmt->close();

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao preparar cadastro do jogo.',
            'error' => $conexao->error
        ]);

        return;
    }

    $stmt->bind_param(
        $tipos,
        $slug,
        $nome,
        $descricao,
        $img,
        $categoria,
        $plataforma,
        $genero,
        $etaria,
        $ano,
        $status,
        $avaliacaoGameplay,
        $avaliacaoGraficos,
        $avaliacaoHistoria,
        $dataLancamento
    );

    if (!$stmt->execute()) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao cadastrar jogo.',
            'error' => $stmt->error
        ]);

        $stmt->close();

        return;
    }

    $novoId = $conexao->insert_id;

    $stmt->close();
    sincronizarRelacoesNormalizadas($conexao, $novoId, $plataforma, $genero);

    responder([
        'success' => true,
        'sucesso' => true,
        'message' => 'Jogo cadastrado com sucesso!',
        'id' => $novoId,
        'slug' => $slug
    ]);
}


/*
|--------------------------------------------------------------------------
| ATUALIZAR JOGO
|--------------------------------------------------------------------------
*/

function atualizarJogo(mysqli $conexao)
{
    $dados = obterDadosEntrada();

    if (!$dados) {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Dados JSON inválidos ou ausentes.'
        ]);

        return;
    }

    $id = intval($dados['id'] ?? 0);

    if ($id <= 0) {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'ID do jogo não fornecido.'
        ]);

        return;
    }

    $nome = trim($dados['nome'] ?? '');
    $descricao = trim($dados['descricao'] ?? '');

    if ($nome === '' || $descricao === '') {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Nome e descrição são obrigatórios.'
        ]);

        return;
    }

    $slug = trim($dados['slug'] ?? '');

    if ($slug === '') {
        $slug = gerarSlugJogo($nome);
    }

    $img = valorNulo($dados['img'] ?? null);
    $categoria = valorNulo($dados['categoria'] ?? null);
    $plataforma = valorNulo($dados['plataforma'] ?? null);
    $genero = valorNulo($dados['genero'] ?? null);

    /*
     * Usa a função existente no config.php.
     */
    $etaria = normalizarEtaria($dados['etaria'] ?? null);

    $ano = normalizarAnoJogo($dados['ano'] ?? null);

    $status = $dados['status'] ?? 'ativo';

    if (!in_array($status, ['ativo', 'inativo'], true)) {
        $status = 'ativo';
    }

    $avaliacaoGameplay = normalizarDecimalJogo(
        $dados['avaliacao_gameplay']
        ?? $dados['avaliacao_gamplay']
        ?? null
    );

    $avaliacaoGraficos = normalizarDecimalJogo(
        $dados['avaliacao_graficos'] ?? null
    );

    $avaliacaoHistoria = normalizarDecimalJogo(
        $dados['avaliacao_historia'] ?? null
    );

    $dataLancamento = valorNulo(
        $dados['data_lancamento'] ?? null
    );

    $sql = "
        UPDATE jogos
        SET
            slug = ?,
            nome = ?,
            descricao = ?,
            img = ?,
            categoria = ?,
            plataforma = ?,
            genero = ?,
            etaria = ?,
            ano = ?,
            status = ?,
            avaliacao_gamplay = ?,
            avaliacao_graficos = ?,
            avaliacao_historia = ?,
            data_lancamento = ?,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id = ?
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao preparar atualização do jogo.',
            'error' => $conexao->error
        ]);

        return;
    }

    $stmt->bind_param(
        'sssssssssisdddsi',
        $slug,
        $nome,
        $descricao,
        $img,
        $categoria,
        $plataforma,
        $genero,
        $etaria,
        $ano,
        $status,
        $avaliacaoGameplay,
        $avaliacaoGraficos,
        $avaliacaoHistoria,
        $dataLancamento,
        $id
    );

    if (!$stmt->execute()) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao atualizar jogo.',
            'error' => $stmt->error
        ]);

        $stmt->close();

        return;
    }

    $stmt->close();
    sincronizarRelacoesNormalizadas($conexao, $id, $plataforma, $genero);

    responder([
        'success' => true,
        'sucesso' => true,
        'message' => 'Jogo atualizado com sucesso!',
        'id' => $id
    ]);
}


/*
|--------------------------------------------------------------------------
| EXCLUIR JOGO
|--------------------------------------------------------------------------
*/

function excluirJogo(mysqli $conexao)
{
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {

        http_response_code(400);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'ID inválido para exclusão.'
        ]);

        return;
    }

    $sql = "
        DELETE FROM jogos
        WHERE id = ?
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao preparar exclusão do jogo.',
            'error' => $conexao->error
        ]);

        return;
    }

    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {

        http_response_code(500);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Erro ao excluir jogo.',
            'error' => $stmt->error
        ]);

        $stmt->close();

        return;
    }

    if ($stmt->affected_rows === 0) {

        $stmt->close();

        http_response_code(404);

        responder([
            'success' => false,
            'sucesso' => false,
            'message' => 'Jogo não encontrado.'
        ]);

        return;
    }

    $stmt->close();

    responder([
        'success' => true,
        'sucesso' => true,
        'message' => 'Jogo excluído com sucesso!',
        'id' => $id
    ]);
}


function sincronizarRelacoesNormalizadas(mysqli $conexao, int $jogoId, ?string $plataforma, ?string $genero): void
{
    $conexao->query("DELETE FROM jogo_plataforma WHERE jogo_id = " . $jogoId);
    $conexao->query("DELETE FROM jogo_genero WHERE jogo_id = " . $jogoId);

    $inserirPlataforma = $conexao->prepare(
        'INSERT IGNORE INTO plataformas (nome) VALUES (?)'
    );
    $vincularPlataforma = $conexao->prepare(
        'INSERT IGNORE INTO jogo_plataforma (jogo_id, plataforma_id)
         SELECT ?, id FROM plataformas WHERE nome = ?'
    );
    foreach (preg_split('/\s*,\s*/', (string)$plataforma, -1, PREG_SPLIT_NO_EMPTY) as $nome) {
        $inserirPlataforma->bind_param('s', $nome);
        $inserirPlataforma->execute();
        $vincularPlataforma->bind_param('is', $jogoId, $nome);
        $vincularPlataforma->execute();
    }
    $inserirPlataforma->close();
    $vincularPlataforma->close();

    $inserirGenero = $conexao->prepare(
        'INSERT IGNORE INTO generos (nome) VALUES (?)'
    );
    $vincularGenero = $conexao->prepare(
        'INSERT IGNORE INTO jogo_genero (jogo_id, genero_id)
         SELECT ?, id FROM generos WHERE nome = ?'
    );
    foreach (preg_split('/\s*,\s*/', (string)$genero, -1, PREG_SPLIT_NO_EMPTY) as $nome) {
        $inserirGenero->bind_param('s', $nome);
        $inserirGenero->execute();
        $vincularGenero->bind_param('is', $jogoId, $nome);
        $vincularGenero->execute();
    }
    $inserirGenero->close();
    $vincularGenero->close();
}

/*
|--------------------------------------------------------------------------
| PREPARAR JOGO
|--------------------------------------------------------------------------
*/

function prepararJogo(array $jogo): array
{
    $jogo['id'] = intval($jogo['id'] ?? 0);

    $jogo['ano'] = (
        isset($jogo['ano']) && $jogo['ano'] !== ''
    )
        ? intval($jogo['ano'])
        : null;

    $jogo['avaliacao_gameplay'] =
        isset($jogo['avaliacao_gameplay'])
            ? floatval($jogo['avaliacao_gameplay'])
            : 0;

    $jogo['avaliacao_gamplay'] =
        $jogo['avaliacao_gameplay'];

    $jogo['avaliacao_graficos'] =
        isset($jogo['avaliacao_graficos'])
            ? floatval($jogo['avaliacao_graficos'])
            : 0;

    $jogo['avaliacao_historia'] =
        isset($jogo['avaliacao_historia'])
            ? floatval($jogo['avaliacao_historia'])
            : 0;

    /*
     * Usa a função existente em config.php.
     */
    $jogo['etaria'] = normalizarEtaria(
        $jogo['etaria'] ?? null
    );

    $camposTexto = [
        'slug',
        'nome',
        'descricao',
        'img',
        'categoria',
        'plataforma',
        'genero',
        'status'
    ];

    foreach ($camposTexto as $campo) {

        if (!isset($jogo[$campo])) {
            $jogo[$campo] = '';
        }
    }

    return $jogo;
}


/*
|--------------------------------------------------------------------------
| NORMALIZAR ANO
|--------------------------------------------------------------------------
*/

function normalizarAnoJogo($valor)
{
    if ($valor === null || $valor === '') {
        return null;
    }

    $ano = intval($valor);

    return $ano > 0 ? $ano : null;
}


/*
|--------------------------------------------------------------------------
| NORMALIZAR DECIMAL
|--------------------------------------------------------------------------
*/

function normalizarDecimalJogo($valor)
{
    if ($valor === null || $valor === '') {
        return 0;
    }

    if (is_string($valor)) {
        $valor = str_replace(',', '.', $valor);
    }

    return floatval($valor);
}


/*
|--------------------------------------------------------------------------
| GERAR SLUG
|--------------------------------------------------------------------------
*/

function gerarSlugJogo($texto)
{
    $texto = trim($texto);

    $transliterado = @iconv(
        'UTF-8',
        'ASCII//TRANSLIT//IGNORE',
        $texto
    );

    if ($transliterado !== false) {
        $texto = $transliterado;
    }

    $texto = strtolower($texto);

    $texto = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $texto
    );

    $texto = trim($texto, '-');

    return $texto !== '' ? $texto : 'jogo';
}


/*
|--------------------------------------------------------------------------
| CONVERTER VAZIO PARA NULL
|--------------------------------------------------------------------------
*/

function valorNulo($valor)
{
    if ($valor === null) {
        return null;
    }

    if (is_string($valor) && trim($valor) === '') {
        return null;
    }

    return $valor;
}


/*
|--------------------------------------------------------------------------
| OBTER DADOS DE ENTRADA
|--------------------------------------------------------------------------
*/

function obterDadosEntrada()
{
    $conteudo = file_get_contents('php://input');

    if ($conteudo !== false && trim($conteudo) !== '') {

        $dados = json_decode($conteudo, true);

        if (
            json_last_error() === JSON_ERROR_NONE &&
            is_array($dados)
        ) {
            return $dados;
        }
    }

    if (!empty($_POST)) {
        return $_POST;
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| RESPOSTA JSON
|--------------------------------------------------------------------------
*/

function responder(array $dados)
{
    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}


/*
|--------------------------------------------------------------------------
| FECHAR CONEXÃO
|--------------------------------------------------------------------------
*/

$conexao->close();

?>
