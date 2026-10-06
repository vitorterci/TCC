<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function precosResponder(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function precosFalha(string $mensagem, int $status = 503): void
{
    precosResponder([
        'success' => false,
        'sucesso' => false,
        'precos' => [],
        'total' => 0,
        'menor_preco' => null,
        'menorPreco' => null,
        'melhor_oferta' => null,
        'precos_simulados' => false,
        'origem' => 'api_real',
        'error' => $mensagem
    ], $status);
}

function precosLog(string $mensagem, array $contexto = []): void
{
    $diretorio = __DIR__ . '/../logs';
    if (!is_dir($diretorio)) {
        @mkdir($diretorio, 0755, true);
    }
    $linha = '[' . date('Y-m-d H:i:s') . '] [ITAD] ' . $mensagem;
    if ($contexto !== []) {
        $linha .= ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    @file_put_contents($diretorio . '/precos_itad_' . date('Y-m-d') . '.log', $linha . PHP_EOL, FILE_APPEND);
}

function precosParametro(string $nome): string
{
    return trim((string)($_GET[$nome] ?? ''));
}

function precosColunasJogos(mysqli $conexao): array
{
    $colunas = [];
    $resultado = $conexao->query('SHOW COLUMNS FROM `jogos`');
    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $colunas[(string)$linha['Field']] = true;
        }
        $resultado->free();
    }
    return $colunas;
}

function precosSelecionarColuna(array $colunas, string $preferida, string $alternativa, string $alias, string $fallback): string
{
    if (isset($colunas[$preferida])) {
        return '`' . $preferida . '` AS `' . $alias . '`';
    }
    if ($alternativa !== '' && isset($colunas[$alternativa])) {
        return '`' . $alternativa . '` AS `' . $alias . '`';
    }
    return $fallback . ' AS `' . $alias . '`';
}

function precosBuscarJogo(mysqli $conexao, string $id, string $slug, string $nome): ?array
{
    $colunas = precosColunasJogos($conexao);
    foreach (['id', 'slug', 'nome'] as $obrigatoria) {
        if (!isset($colunas[$obrigatoria])) {
            precosLog('Coluna obrigatória ausente na tabela jogos', ['coluna' => $obrigatoria]);
            return null;
        }
    }

    $campos = [
        '`id`', '`slug`', '`nome`',
        precosSelecionarColuna($colunas, 'descricao', '', 'descricao', "''"),
        precosSelecionarColuna($colunas, 'img', 'imagem', 'img', "''"),
        precosSelecionarColuna($colunas, 'categoria', '', 'categoria', "''"),
        precosSelecionarColuna($colunas, 'plataforma', '', 'plataforma', "'PC'"),
        precosSelecionarColuna($colunas, 'genero', '', 'genero', "''"),
        precosSelecionarColuna($colunas, 'etaria', 'classificacao', 'etaria', "''"),
        precosSelecionarColuna($colunas, 'ano', '', 'ano', "''"),
        precosSelecionarColuna($colunas, 'status', '', 'status', "'ativo'")
    ];

    if ($id !== '') {
        if (!ctype_digit($id) || (int)$id < 1) {
            return null;
        }
        $condicao = '`id` = ?';
        $tipo = 'i';
        $valor = (int)$id;
    } elseif ($slug !== '') {
        $condicao = '`slug` = ?';
        $tipo = 's';
        $valor = $slug;
    } else {
        $condicao = '`nome` = ?';
        $tipo = 's';
        $valor = $nome;
    }

    if (isset($colunas['status'])) {
        $condicao .= " AND `status` = 'ativo'";
    } elseif (isset($colunas['disponivel'])) {
        $condicao .= ' AND `disponivel` = 1';
    }

    $sql = 'SELECT ' . implode(', ', $campos) . ' FROM `jogos` WHERE ' . $condicao . ' LIMIT 1';
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        precosLog('Falha ao preparar consulta do jogo', ['erro' => $conexao->error]);
        return null;
    }
    $stmt->bind_param($tipo, $valor);
    if (!$stmt->execute()) {
        precosLog('Falha ao executar consulta do jogo', ['erro' => $stmt->error]);
        $stmt->close();
        return null;
    }
    $resultado = $stmt->get_result();
    $jogo = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $jogo ?: null;
}

function precosItadRequisicao(string $caminho, string $apiKey, string $metodo = 'GET', array $query = [], ?array $corpo = null): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'status' => 0, 'dados' => null, 'erro' => 'Extensão cURL indisponível.'];
    }

    $url = 'https://api.isthereanydeal.com' . $caminho;
    if ($query !== []) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
    $cabecalhos = ['Accept: application/json', 'key: ' . $apiKey];
    if ($corpo !== null) {
        $cabecalhos[] = 'Content-Type: application/json';
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $cabecalhos,
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);
    if ($corpo !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $resposta = curl_exec($curl);
    $erroCurl = curl_error($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($resposta === false) {
        return ['ok' => false, 'status' => $status, 'dados' => null, 'erro' => $erroCurl ?: 'Falha de rede.'];
    }
    $dados = json_decode($resposta, true);
    if ($status < 200 || $status >= 300 || !is_array($dados)) {
        return ['ok' => false, 'status' => $status, 'dados' => null, 'erro' => 'Resposta HTTP ou JSON inválido.'];
    }
    return ['ok' => true, 'status' => $status, 'dados' => $dados, 'erro' => null];
}

function precosBuscarMapeamento(mysqli $conexao, int $jogoId): ?array
{
    $stmt = $conexao->prepare('SELECT external_id, external_slug, precos_atualizados_em FROM jogo_provedores WHERE jogo_id = ? AND provedor = \'isthereanydeal\' LIMIT 1');
    if (!$stmt) {
        precosLog('Falha ao preparar leitura do mapeamento', ['erro' => $conexao->error]);
        return null;
    }
    $stmt->bind_param('i', $jogoId);
    if (!$stmt->execute()) {
        precosLog('Falha ao executar leitura do mapeamento', ['erro' => $stmt->error]);
        $stmt->close();
        return null;
    }
    $resultado = $stmt->get_result();
    $mapa = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $mapa ?: null;
}

function precosSalvarMapeamento(mysqli $conexao, int $jogoId, ?string $externalId, ?string $externalSlug, bool $precosConsultados): void
{
    $dataAtualizada = $precosConsultados ? date('Y-m-d H:i:s') : null;
    $stmt = $conexao->prepare(
        'INSERT INTO jogo_provedores (jogo_id, provedor, external_id, external_slug, precos_atualizados_em) VALUES (?, \'isthereanydeal\', ?, ?, ?) '
        . 'ON DUPLICATE KEY UPDATE external_id = VALUES(external_id), external_slug = VALUES(external_slug), '
        . 'precos_atualizados_em = COALESCE(VALUES(precos_atualizados_em), precos_atualizados_em)'
    );
    if (!$stmt) {
        precosLog('Falha ao preparar gravação do mapeamento', ['erro' => $conexao->error]);
        return;
    }
    $stmt->bind_param('isss', $jogoId, $externalId, $externalSlug, $dataAtualizada);
    if (!$stmt->execute()) {
        precosLog('Falha ao gravar mapeamento', ['erro' => $stmt->error]);
    }
    $stmt->close();
}

function precosNormalizarOfertas(array $respostaItad, int $jogoId): array
{
    $itens = $respostaItad['deals'] ?? [];
    if (!is_array($itens)) {
        return [];
    }
    $ofertas = [];
    foreach ($itens as $deal) {
        if (!is_array($deal)) {
            continue;
        }
        $preco = $deal['price']['amount'] ?? null;
        $moeda = strtoupper((string)($deal['price']['currency'] ?? ''));
        $loja = trim((string)($deal['shop']['name'] ?? ''));
        $url = trim((string)($deal['url'] ?? ''));
        if (!is_numeric($preco) || !is_finite((float)$preco) || (float)$preco <= 0 || !preg_match('/^[A-Z]{3}$/', $moeda) || $loja === '') {
            continue;
        }
        $partesUrl = parse_url($url);
        if (!$partesUrl || !in_array(strtolower((string)($partesUrl['scheme'] ?? '')), ['https', 'http'], true) || empty($partesUrl['host'])) {
            continue;
        }

        $precoAntigo = $deal['regular']['amount'] ?? null;
        if (!is_numeric($precoAntigo) || (float)$precoAntigo < (float)$preco) {
            $precoAntigo = null;
        } else {
            $precoAntigo = round((float)$precoAntigo, 2);
        }
        $desconto = isset($deal['cut']) && is_numeric($deal['cut'])
            ? max(0, min(100, round((float)$deal['cut'], 2)))
            : (($precoAntigo !== null && $precoAntigo > 0) ? round((1 - (float)$preco / $precoAntigo) * 100, 2) : 0.0);
        $plataformas = [];
        foreach (($deal['platforms'] ?? []) as $plataforma) {
            if (is_array($plataforma) && !empty($plataforma['name'])) {
                $plataformas[] = trim((string)$plataforma['name']);
            }
        }
        $timestamp = date('Y-m-d H:i:s');
        if (!empty($deal['timestamp'])) {
            try {
                $timestamp = (new DateTimeImmutable((string)$deal['timestamp']))->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                // Mantém a data local quando o provedor envia timestamp inválido.
            }
        }
        $ofertas[] = [
            'jogo_id' => $jogoId,
            'loja_id' => (int)($deal['shop']['id'] ?? 0),
            'loja' => $loja,
            'loja_nome' => $loja,
            'plataforma' => $plataformas ? implode(', ', array_unique($plataformas)) : 'PC',
            'preco' => round((float)$preco, 2),
            'preco_antigo' => $precoAntigo,
            'desconto' => $desconto,
            'moeda' => $moeda,
            'disponibilidade' => 'disponivel',
            'disponivel' => true,
            'url' => $url,
            'url_oferta' => $url,
            'data_atualizacao' => $timestamp,
            'cache_atualizado_em' => date('Y-m-d H:i:s'),
            'origem' => 'api_real',
            'external_id' => (string)($deal['id'] ?? '')
        ];
    }
    return $ofertas;
}

function precosCarregarCache(mysqli $conexao, int $jogoId, string $limite): array
{
    $stmt = $conexao->prepare(
        'SELECT id, jogo_id, loja_id, loja, plataforma, preco, preco_antigo, desconto, moeda, disponibilidade, disponivel, url, url_oferta, data_atualizacao, cache_atualizado_em, external_id '
        . 'FROM precos_cache_itad WHERE jogo_id = ? AND cache_atualizado_em >= ? AND disponivel = 1 AND preco > 0 '
        . 'ORDER BY CASE WHEN moeda = \'BRL\' THEN 0 ELSE 1 END, moeda ASC, preco ASC, loja ASC'
    );
    if (!$stmt) {
        precosLog('Falha ao preparar leitura do cache', ['erro' => $conexao->error]);
        return [];
    }
    $stmt->bind_param('is', $jogoId, $limite);
    if (!$stmt->execute()) {
        precosLog('Falha ao executar leitura do cache', ['erro' => $stmt->error]);
        $stmt->close();
        return [];
    }
    $resultado = $stmt->get_result();
    $ofertas = [];
    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $linha['preco'] = (float)$linha['preco'];
            $linha['preco_antigo'] = $linha['preco_antigo'] !== null ? (float)$linha['preco_antigo'] : null;
            $linha['desconto'] = (float)$linha['desconto'];
            $linha['disponivel'] = (bool)$linha['disponivel'];
            $linha['origem'] = 'api_real';
            $ofertas[] = $linha;
        }
    }
    $stmt->close();
    return $ofertas;
}

function precosSalvarCache(mysqli $conexao, int $jogoId, array $ofertas): bool
{
    if (!$conexao->begin_transaction()) {
        precosLog('Não foi possível iniciar transação de cache', ['erro' => $conexao->error]);
        return false;
    }
    $apagar = $conexao->prepare('DELETE FROM precos_cache_itad WHERE jogo_id = ?');
    if (!$apagar) {
        $conexao->rollback();
        precosLog('Falha ao preparar substituição de cache', ['erro' => $conexao->error]);
        return false;
    }
    $apagar->bind_param('i', $jogoId);
    if (!$apagar->execute()) {
        precosLog('Falha ao apagar cache anterior', ['erro' => $apagar->error]);
        $apagar->close();
        $conexao->rollback();
        return false;
    }
    $apagar->close();

    $stmt = $conexao->prepare(
        'INSERT INTO precos_cache_itad (jogo_id, loja_id, loja, plataforma, preco, preco_antigo, desconto, moeda, disponibilidade, disponivel, url, url_oferta, data_atualizacao, cache_atualizado_em, external_id) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        $conexao->rollback();
        precosLog('Falha ao preparar gravação de ofertas', ['erro' => $conexao->error]);
        return false;
    }
    foreach ($ofertas as $oferta) {
        $lojaId = (int)$oferta['loja_id'];
        $loja = (string)$oferta['loja'];
        $plataforma = (string)$oferta['plataforma'];
        $preco = (float)$oferta['preco'];
        $precoAntigo = $oferta['preco_antigo'] !== null ? (float)$oferta['preco_antigo'] : null;
        $desconto = (float)$oferta['desconto'];
        $moeda = (string)$oferta['moeda'];
        $disponibilidade = (string)$oferta['disponibilidade'];
        $url = (string)$oferta['url'];
        $urlOferta = (string)$oferta['url_oferta'];
        $dataAtualizacao = (string)$oferta['data_atualizacao'];
        $cacheAtualizadoEm = (string)$oferta['cache_atualizado_em'];
        $externalId = (string)$oferta['external_id'];
        $stmt->bind_param('iissdsdsssssss', $jogoId, $lojaId, $loja, $plataforma, $preco, $precoAntigo, $desconto, $moeda, $disponibilidade, $url, $urlOferta, $dataAtualizacao, $cacheAtualizadoEm, $externalId);
        if (!$stmt->execute()) {
            precosLog('Falha ao salvar oferta em cache', ['erro' => $stmt->error, 'loja' => $loja]);
            $stmt->close();
            $conexao->rollback();
            return false;
        }
    }
    $stmt->close();
    if (!$conexao->commit()) {
        $conexao->rollback();
        precosLog('Falha ao confirmar atualização do cache', ['erro' => $conexao->error]);
        return false;
    }
    return true;
}

function precosMontarResposta(array $jogo, array $ofertas, bool $cache, ?string $atualizadoEm = null): array
{
    $ofertas = array_values(array_filter($ofertas, static function (array $oferta): bool {
        return !empty($oferta['disponivel']) && isset($oferta['preco']) && is_numeric($oferta['preco']) && (float)$oferta['preco'] > 0;
    }));
    usort($ofertas, static function (array $a, array $b): int {
        $aBrl = ($a['moeda'] ?? '') === 'BRL' ? 0 : 1;
        $bBrl = ($b['moeda'] ?? '') === 'BRL' ? 0 : 1;
        if ($aBrl !== $bBrl) return $aBrl <=> $bBrl;
        if (($a['moeda'] ?? '') !== ($b['moeda'] ?? '')) return strcmp((string)$a['moeda'], (string)$b['moeda']);
        return ((float)$a['preco'] <=> (float)$b['preco']) ?: strcmp((string)($a['loja'] ?? ''), (string)($b['loja'] ?? ''));
    });

    $moedas = array_values(array_unique(array_map(static fn(array $oferta): string => (string)$oferta['moeda'], $ofertas)));
    $brl = array_values(array_filter($ofertas, static fn(array $oferta): bool => ($oferta['moeda'] ?? '') === 'BRL'));
    $grupoComparavel = $brl !== [] ? $brl : (count($moedas) === 1 ? $ofertas : []);
    $melhor = $grupoComparavel[0] ?? null;
    $jogoResposta = [
        'id' => (int)$jogo['id'],
        'slug' => (string)$jogo['slug'],
        'nome' => (string)$jogo['nome'],
        'descricao' => (string)($jogo['descricao'] ?? ''),
        'img' => (string)($jogo['img'] ?? ''),
        'categoria' => (string)($jogo['categoria'] ?? ''),
        'plataforma' => (string)($jogo['plataforma'] ?? 'PC'),
        'genero' => (string)($jogo['genero'] ?? ''),
        'etaria' => (string)($jogo['etaria'] ?? ''),
        'ano' => (string)($jogo['ano'] ?? ''),
        'status' => (string)($jogo['status'] ?? 'ativo')
    ];
    return [
        'success' => true,
        'sucesso' => true,
        'jogo' => $jogoResposta,
        'precos' => $ofertas,
        'total' => count($ofertas),
        'menor_preco' => $melhor['preco'] ?? null,
        'menorPreco' => $melhor['preco'] ?? null,
        'moeda' => $melhor['moeda'] ?? null,
        'melhor_oferta' => $melhor,
        'precos_simulados' => false,
        'origem' => 'api_real',
        'cache' => $cache,
        'atualizado_em' => $atualizadoEm ?: ($ofertas[0]['data_atualizacao'] ?? null),
        'mensagem' => $ofertas === [] ? 'Nenhuma oferta disponível no momento.' : null
    ];
}

try {
    require_once __DIR__ . '/../config.php';
} catch (Throwable $erro) {
    precosLog('Falha ao carregar configuração do banco', ['erro' => $erro->getMessage()]);
    precosFalha('Serviço de preços temporariamente indisponível.');
}

if (!isset($conexao) || !($conexao instanceof mysqli) || $conexao->connect_errno) {
    precosFalha('Serviço de preços temporariamente indisponível.');
}

$id = precosParametro('id');
$slug = precosParametro('slug');
$nome = precosParametro('jogo');
if ($id === '' && $slug === '' && $nome === '') {
    precosFalha('Informe o slug, id ou nome do jogo.', 400);
}
$jogo = precosBuscarJogo($conexao, $id, $slug, $nome);
if (!$jogo) {
    precosFalha('Jogo não encontrado.', 404);
}

$ttl = getenv('ITAD_CACHE_TTL');
$ttl = ($ttl !== false && ctype_digit((string)$ttl)) ? max(60, min(86400, (int)$ttl)) : 3600;
$forcar = filter_var($_GET['forcar'] ?? false, FILTER_VALIDATE_BOOLEAN);
$agora = date('Y-m-d H:i:s');
$limite = date('Y-m-d H:i:s', time() - $ttl);
$jogoId = (int)$jogo['id'];
$mapa = precosBuscarMapeamento($conexao, $jogoId);
$externalId = $mapa['external_id'] ?? null;

if (!$forcar && $mapa && !empty($mapa['precos_atualizados_em']) && $mapa['precos_atualizados_em'] >= $limite) {
    $cache = precosCarregarCache($conexao, $jogoId, $limite);
    precosResponder(precosMontarResposta($jogo, $cache, true, (string)$mapa['precos_atualizados_em']));
}

$apiKey = trim((string)getenv('ITAD_API_KEY'));
if ($apiKey === '') {
    precosLog('ITAD_API_KEY não configurada', ['jogo' => $jogo['slug']]);
    precosFalha('Serviço de preços temporariamente indisponível. Configure ITAD_API_KEY no ambiente do PHP.');
}

if (!$externalId) {
    $lookup = precosItadRequisicao('/games/lookup/v1', $apiKey, 'GET', ['title' => (string)$jogo['nome']]);
    if (!$lookup['ok']) {
        precosLog('Falha na busca/matching do jogo', ['jogo' => $jogo['slug'], 'http_status' => $lookup['status'], 'erro' => $lookup['erro']]);
        precosFalha('Serviço de preços temporariamente indisponível.');
    }
    $jogoExterno = $lookup['dados']['game'] ?? null;
    if (empty($lookup['dados']['found']) || !is_array($jogoExterno) || empty($jogoExterno['id'])) {
        precosSalvarMapeamento($conexao, $jogoId, null, null, true);
        precosResponder(precosMontarResposta($jogo, [], false, $agora));
    }
    $externalId = (string)$jogoExterno['id'];
    precosSalvarMapeamento($conexao, $jogoId, $externalId, isset($jogoExterno['slug']) ? (string)$jogoExterno['slug'] : null, false);
    $mapa = ['external_id' => $externalId, 'external_slug' => $jogoExterno['slug'] ?? null];
}

$precosResposta = precosItadRequisicao('/games/prices/v3', $apiKey, 'POST', ['country' => 'BR'], [$externalId]);
if (!$precosResposta['ok']) {
    precosLog('Falha na consulta de preços', ['jogo' => $jogo['slug'], 'external_id' => $externalId, 'http_status' => $precosResposta['status'], 'erro' => $precosResposta['erro']]);
    precosFalha('Serviço de preços temporariamente indisponível.');
}

$registroJogo = null;
foreach ($precosResposta['dados'] as $item) {
    if (is_array($item) && (string)($item['id'] ?? '') === $externalId) {
        $registroJogo = $item;
        break;
    }
}
$ofertas = precosNormalizarOfertas($registroJogo ?? [], $jogoId);
if (!precosSalvarCache($conexao, $jogoId, $ofertas)) {
    precosFalha('Serviço de preços temporariamente indisponível.');
}
precosSalvarMapeamento($conexao, $jogoId, $externalId, isset($mapa['external_slug']) ? (string)$mapa['external_slug'] : null, true);
precosResponder(precosMontarResposta($jogo, $ofertas, false, $agora));
