<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function responder($dados, $status = 200) {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function exigirMetodo($metodo) {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $metodo) {
        header('Allow: ' . $metodo);
        responder(['success' => false, 'message' => 'Método HTTP não permitido.'], 405);
    }
}

function obterMeusJogos($conexao, $usuarioId) {
    $stmt = $conexao->prepare(
        "SELECT uj.id, uj.jogo_id, uj.status, uj.data_adicionado, uj.data_atualizacao,
                j.slug, j.nome, j.img, j.plataforma, j.genero, j.ano
         FROM usuario_jogos uj
         INNER JOIN jogos j ON j.id = uj.jogo_id
         WHERE uj.usuario_id = ?
         ORDER BY uj.data_adicionado DESC, j.nome ASC"
    );
    if (!$stmt) responder(['success' => false, 'message' => 'Não foi possível consultar Meus Jogos.'], 500);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $jogos = [];
    while ($jogo = $resultado->fetch_assoc()) {
        $jogo['registro_id'] = (int)$jogo['id'];
        $jogo['id'] = (int)$jogo['jogo_id'];
        $jogo['jogo_id'] = (int)$jogo['jogo_id'];
        $jogos[] = $jogo;
    }
    $stmt->close();
    return $jogos;
}

function obterJogoIdDoPayload($dados) {
    $jogoId = (int)($dados['jogo_id'] ?? 0);
    if ($jogoId <= 0) responder(['success' => false, 'message' => 'Informe um jogo válido.'], 422);
    return $jogoId;
}

function validarStatusJogo($status) {
    $permitidos = ['possuo', 'jogando', 'zerado', 'abandonado'];
    if (!in_array($status, $permitidos, true)) {
        responder(['success' => false, 'message' => 'Status de jogo inválido.'], 422);
    }
    return $status;
}

function obterMinhaLista($conexao, $usuarioId) {
    $stmt = $conexao->prepare(
        "SELECT ul.id, ul.jogo_id, ul.notificar_promocao, ul.notificar_preco,
                ul.preco_alvo, ul.desconto_minimo, ul.data_adicionado, ul.data_atualizacao,
                j.slug, j.nome, j.img, j.plataforma, j.genero, j.ano
         FROM usuario_lista ul
         INNER JOIN jogos j ON j.id = ul.jogo_id
         WHERE ul.usuario_id = ?
         ORDER BY ul.data_adicionado DESC, j.nome ASC"
    );
    if (!$stmt) responder(['success' => false, 'message' => 'Não foi possível consultar Minha Lista.'], 500);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $lista = [];
    while ($jogo = $resultado->fetch_assoc()) {
        $jogo['registro_id'] = (int)$jogo['id'];
        $jogo['id'] = (int)$jogo['jogo_id'];
        $jogo['jogo_id'] = (int)$jogo['jogo_id'];
        $jogo['notificar_promocao'] = (int)$jogo['notificar_promocao'];
        $jogo['notificar_preco'] = (int)$jogo['notificar_preco'];
        $lista[] = $jogo;
    }
    $stmt->close();
    return $lista;
}

function obterConfiguracaoLista($dados) {
    $precoAlvo = $dados['preco_alvo'] ?? null;
    $descontoMinimo = $dados['desconto_minimo'] ?? null;
    if ($precoAlvo !== null && ($precoAlvo === '' || !is_numeric($precoAlvo) || (float)$precoAlvo < 0)) {
        responder(['success' => false, 'message' => 'Preço-alvo inválido.'], 422);
    }
    if ($descontoMinimo !== null && ($descontoMinimo === '' || !is_numeric($descontoMinimo) || (float)$descontoMinimo < 0 || (float)$descontoMinimo > 100)) {
        responder(['success' => false, 'message' => 'Desconto mínimo inválido.'], 422);
    }
    return [
        $precoAlvo === '' ? null : ($precoAlvo === null ? null : (float)$precoAlvo),
        $descontoMinimo === '' ? null : ($descontoMinimo === null ? null : (float)$descontoMinimo),
        !empty($dados['notificar_promocao']) ? 1 : 0,
        !empty($dados['notificar_preco']) ? 1 : 0
    ];
}

/**
 * Normaliza um valor de interesse (array, JSON string ou CSV) em um array
 * limpo, sem duplicatas e sem valores vazios.
 *
 * Utilizado tanto para leitura (retorno da API) quanto para compatibilidade
 * com dados legados gravados como string.
 */
function normalizarInteresses($valor) {
    $lista = [];
    if (is_array($valor)) {
        $lista = $valor;
    } elseif (is_string($valor) && trim($valor) !== '') {
        $decodificado = json_decode($valor, true);
        if (is_array($decodificado)) {
            $lista = $decodificado;
        } else {
            // Compatibilidade com CSV legado
            $lista = preg_split('/\s*,\s*/', $valor, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
    }

    $limpos = [];
    foreach ($lista as $item) {
        if (!is_string($item) && !is_numeric($item)) continue;
        $texto = trim((string) $item);
        if ($texto === '') continue;
        $limpos[] = $texto;
    }
    return array_values(array_unique($limpos));
}

function obterPreferencias($conexao, $usuarioId) {
    $stmt = $conexao->prepare('SELECT desconto_minimo, preco_maximo, notificar_promocoes, notificar_queda_preco, plataformas_interesse, generos_interesse, lojas_interesse FROM usuario_preferencias WHERE usuario_id = ? LIMIT 1');
    if (!$stmt) responder(['success' => false, 'message' => 'Não foi possível consultar as preferências.'], 500);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $preferencias = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$preferencias) {
        $preferencias = ['desconto_minimo' => 0, 'preco_maximo' => null, 'notificar_promocoes' => 1, 'notificar_queda_preco' => 1, 'plataformas_interesse' => [], 'generos_interesse' => [], 'lojas_interesse' => []];
    } else {
        $preferencias['desconto_minimo'] = (float)$preferencias['desconto_minimo'];
        $preferencias['preco_maximo'] = $preferencias['preco_maximo'] === null ? null : (float)$preferencias['preco_maximo'];
        $preferencias['notificar_promocoes'] = (int)$preferencias['notificar_promocoes'];
        $preferencias['notificar_queda_preco'] = (int)$preferencias['notificar_queda_preco'];
        $preferencias['plataformas_interesse'] = normalizarInteresses($preferencias['plataformas_interesse']);
        $preferencias['generos_interesse'] = normalizarInteresses($preferencias['generos_interesse']);
        $preferencias['lojas_interesse'] = normalizarInteresses($preferencias['lojas_interesse']);
    }
    return $preferencias;
}

function obterPreferenciasPayload($dados) {
    $desconto = $dados['desconto_minimo'] ?? 0;
    $precoMaximo = $dados['preco_maximo'] ?? null;
    if (!is_numeric($desconto) || (float)$desconto < 0 || (float)$desconto > 100) responder(['success' => false, 'message' => 'Desconto mínimo inválido.'], 422);
    if ($precoMaximo !== null && $precoMaximo !== '' && (!is_numeric($precoMaximo) || (float)$precoMaximo < 0)) responder(['success' => false, 'message' => 'Preço máximo inválido.'], 422);
    $interesses = [];
    foreach (['plataformas_interesse', 'generos_interesse', 'lojas_interesse'] as $campo) {
        $valor = $dados[$campo] ?? [];
        if (!is_array($valor)) responder(['success' => false, 'message' => 'Interesses inválidos.'], 422);

        $limpos = [];
        foreach ($valor as $item) {
            if (!is_string($item) && !is_numeric($item)) continue;
            $texto = trim((string) $item);
            if ($texto === '') continue;
            $limpos[] = $texto;
        }
        $limpos = array_values(array_unique($limpos));
        $interesses[$campo] = json_encode($limpos, JSON_UNESCAPED_UNICODE);
    }
    return [(float)$desconto, $precoMaximo === null || $precoMaximo === '' ? null : (float)$precoMaximo, !empty($dados['notificar_promocoes']) ? 1 : 0, !empty($dados['notificar_queda_preco']) ? 1 : 0, $interesses];
}

/**
 * Verifica se um campo multivalor do jogo (ex.: "PC, PS5, Xbox Series")
 * contém pelo menos um dos valores de interesse do usuário.
 *
 * A comparação é feita por partes, ignorando maiúsculas/minúsculas
 * e acentuação, e removendo espaços em branco.
 */
function campoMultivalorContem($campo, array $valoresInteresse) {
    if (empty($valoresInteresse)) {
        return true; // sem preferência, aceita qualquer valor
    }

    $normalizar = static function ($texto) {
        $texto = (string) $texto;
        if (function_exists('mb_strtolower')) {
            $texto = mb_strtolower($texto, 'UTF-8');
        } else {
            $texto = strtolower($texto);
        }
        // Remove acentos de forma simples
        $texto = preg_replace('/[\x{0300}-\x{036F}]/u', '', $texto);
        return trim($texto);
    };

    $partesJogo = array_filter(array_map(
        static fn($parte) => $normalizar(trim($parte)),
        preg_split('/\s*,\s*/', (string) $campo, -1, PREG_SPLIT_NO_EMPTY) ?: []
    ));

    if (empty($partesJogo)) {
        return false;
    }

    foreach ($valoresInteresse as $interesse) {
        $interesseNormalizado = $normalizar($interesse);
        if ($interesseNormalizado === '') {
            continue;
        }
        foreach ($partesJogo as $parte) {
            if ($parte === $interesseNormalizado) {
                return true;
            }
        }
    }

    return false;
}

function obterRecomendacoes($conexao, $usuarioId) {
    $preferencias = obterPreferencias($conexao, $usuarioId);
    $resultado = $conexao->query("SELECT j.id, j.slug, j.nome, j.img, j.plataforma, j.genero,
        uj.jogo_id AS possui_jogo,
        (SELECT p.preco FROM precos p WHERE p.jogo_id = j.id AND p.disponibilidade = 'disponivel' AND p.preco > 0 ORDER BY p.preco ASC LIMIT 1) AS preco,
        (SELECT p.desconto FROM precos p WHERE p.jogo_id = j.id AND p.disponibilidade = 'disponivel' AND p.preco > 0 ORDER BY p.preco ASC LIMIT 1) AS desconto,
        (SELECT p.loja FROM precos p WHERE p.jogo_id = j.id AND p.disponibilidade = 'disponivel' AND p.preco > 0 ORDER BY p.preco ASC LIMIT 1) AS loja
        FROM jogos j LEFT JOIN usuario_jogos uj ON uj.jogo_id = j.id AND uj.usuario_id = " . (int)$usuarioId . " WHERE j.status = 'ativo' ORDER BY j.nome ASC");
    if (!$resultado) responder(['success' => false, 'message' => 'Não foi possível gerar recomendações.'], 500);
    $listaIds = [];
    $resultLista = $conexao->query('SELECT jogo_id FROM usuario_lista WHERE usuario_id = ' . (int)$usuarioId);
    if ($resultLista) while ($item = $resultLista->fetch_assoc()) $listaIds[(int)$item['jogo_id']] = true;
    $recomendacoes = [];
    while ($jogo = $resultado->fetch_assoc()) {
        $id = (int)$jogo['id'];
        $preco = $jogo['preco'] === null ? null : (float)$jogo['preco'];
        $desconto = (float)($jogo['desconto'] ?? 0);
        $naLista = isset($listaIds[$id]);
        if ($preco === null) continue;
        if (!$naLista && $preferencias['preco_maximo'] !== null && $preco > $preferencias['preco_maximo']) continue;
        if (!$naLista && $desconto < $preferencias['desconto_minimo']) continue;
        if (!$naLista && !campoMultivalorContem($jogo['plataforma'], $preferencias['plataformas_interesse'])) continue;
        if (!$naLista && !campoMultivalorContem($jogo['genero'], $preferencias['generos_interesse'])) continue;
        if (!$naLista && !campoMultivalorContem($jogo['loja'], $preferencias['lojas_interesse'])) continue;
        if ($jogo['possui_jogo'] !== null) continue;
        $recomendacoes[] = ['id' => $id, 'slug' => $jogo['slug'], 'nome' => $jogo['nome'], 'preco' => $preco, 'desconto' => $desconto, 'loja' => $jogo['loja'], 'na_lista' => $naLista, 'recomendar_compra' => !$naLista];
    }
    usort($recomendacoes, static fn($a, $b) => ((int)$b['na_lista'] <=> (int)$a['na_lista']) ?: ($b['desconto'] <=> $a['desconto']));
    return $recomendacoes;
}

function obterColunasUsuarios($conexao) {
    $colunas = [];
    $resultado = $conexao->query('SHOW COLUMNS FROM usuarios');
    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $colunas[$linha['Field']] = true;
        }
        $resultado->free();
    }
    return $colunas;
}

function colunaOuLiteral($colunas, $coluna, $alias, $literal = 'NULL') {
    return isset($colunas[$coluna]) ? $coluna : $literal . ' AS ' . $alias;
}

function obterUsuario($conexao, $usuarioId) {
    $colunas = obterColunasUsuarios($conexao);
    $role = isset($colunas['role']) ? 'role' : (isset($colunas['cargo']) ? 'cargo AS role' : "'usuario' AS role");
    $ativo = colunaOuLiteral($colunas, 'ativo', 'ativo', '1');
    $atualizacao = colunaOuLiteral($colunas, 'data_atualizacao', 'data_atualizacao', 'data_cadastro');
    $foto = colunaOuLiteral($colunas, 'foto_perfil', 'foto_perfil');
    $preferenciaCor = colunaOuLiteral($colunas, 'preferencias_cor', 'preferencias_cor');
    $preferenciaAnimacoes = colunaOuLiteral($colunas, 'preferencias_animacoes', 'preferencias_animacoes', '0');

    $sql = "SELECT id, nome, usuario, email, {$foto}, data_cadastro, {$atualizacao}, {$role}, {$ativo}, {$preferenciaCor}, {$preferenciaAnimacoes}
            FROM usuarios WHERE id = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);
    if (!$stmt) responder(['success' => false, 'message' => 'Não foi possível consultar a conta.'], 500);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || (int)$usuario['ativo'] !== 1) return null;

    $usuario['id'] = (int)$usuario['id'];
    $usuario['ativo'] = (int)$usuario['ativo'];
    $usuario['preferencias'] = [];
    if (!empty($usuario['preferencias_cor'])) {
        $usuario['preferencias'][] = 'Cor de destaque: ' . $usuario['preferencias_cor'];
    }
    $usuario['preferencias'][] = ((int)$usuario['preferencias_animacoes'] === 1)
        ? 'Animações ativadas'
        : 'Animações desativadas';

    $usuario['jogos'] = [];
    $usuario['jogos_cadastrados'] = 0;
    if ($resultado = $conexao->query('SELECT COUNT(*) AS total FROM jogos')) {
        $usuario['jogos_cadastrados'] = (int)($resultado->fetch_assoc()['total'] ?? 0);
        $resultado->free();
    }
    return $usuario;
}

function sincronizarSessao($usuario) {
    $_SESSION['usuario_id'] = (int)$usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuário';
    $_SESSION['usuario_usuario'] = $usuario['usuario'] ?? '';
    $_SESSION['usuario_email'] = $usuario['email'] ?? '';
    $_SESSION['usuario_role'] = $usuario['role'] ?? 'usuario';
    $_SESSION['usuario_ativo'] = (int)($usuario['ativo'] ?? 1);
}

function caminhoFotoSeguro($caminho) {
    $prefixo = 'uploads/perfil/';
    if (!is_string($caminho) || $caminho === '') {
        return null;
    }

    // strpos() é compatível com PHP 7.x e garante que o caminho comece
    // exatamente dentro do diretório permitido para fotos de perfil.
    return strpos($caminho, $prefixo) === 0 ? $caminho : null;
}

function tamanhoTexto($texto) {
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }

    return strlen(utf8_decode($texto));
}

function excluirArquivoFoto($caminho) {
    $seguro = caminhoFotoSeguro($caminho);
    if ($seguro) {
        $arquivo = dirname(__DIR__, 2) . '/' . $seguro;
        if (is_file($arquivo)) @unlink($arquivo);
    }
}

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
if ($usuarioId <= 0) responder(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);

$acao = $_GET['acao'] ?? '';
if ($acao === 'get') {
    $usuario = obterUsuario($conexao, $usuarioId);
    if (!$usuario) responder(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
    sincronizarSessao($usuario);
    responder(['success' => true, 'user' => $usuario]);
}

if ($acao === 'meus_jogos') {
    responder(['success' => true, 'jogos' => obterMeusJogos($conexao, $usuarioId)]);
}

if ($acao === 'minha_lista') {
    responder(['success' => true, 'lista' => obterMinhaLista($conexao, $usuarioId)]);
}

if ($acao === 'preferencias') {
    responder(['success' => true, 'preferencias' => obterPreferencias($conexao, $usuarioId)]);
}

if ($acao === 'atualizar_preferencias') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    [$desconto, $precoMaximo, $notificarPromocoes, $notificarQuedaPreco, $interesses] = obterPreferenciasPayload($dados);
    $stmt = $conexao->prepare(
        'INSERT INTO usuario_preferencias (usuario_id, desconto_minimo, preco_maximo, notificar_promocoes, notificar_queda_preco, plataformas_interesse, generos_interesse, lojas_interesse)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE desconto_minimo = VALUES(desconto_minimo), preco_maximo = VALUES(preco_maximo), notificar_promocoes = VALUES(notificar_promocoes), notificar_queda_preco = VALUES(notificar_queda_preco), plataformas_interesse = VALUES(plataformas_interesse), generos_interesse = VALUES(generos_interesse), lojas_interesse = VALUES(lojas_interesse), data_atualizacao = CURRENT_TIMESTAMP'
    );
    $stmt->bind_param('iddiisss', $usuarioId, $desconto, $precoMaximo, $notificarPromocoes, $notificarQuedaPreco, $interesses['plataformas_interesse'], $interesses['generos_interesse'], $interesses['lojas_interesse']);
    $sucesso = $stmt->execute();
    $stmt->close();
    responder($sucesso ? ['success' => true, 'message' => 'Preferências atualizadas.', 'preferencias' => obterPreferencias($conexao, $usuarioId)] : ['success' => false, 'message' => 'Não foi possível salvar as preferências.'], $sucesso ? 200 : 500);
}

if ($acao === 'recomendacoes') {
    responder(['success' => true, 'recomendacoes' => obterRecomendacoes($conexao, $usuarioId), 'preferencias' => obterPreferencias($conexao, $usuarioId)]);
}

if ($acao === 'adicionar_lista') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    [$precoAlvo, $descontoMinimo, $notificarPromocao, $notificarPreco] = obterConfiguracaoLista($dados);
    $check = $conexao->prepare('SELECT id FROM jogos WHERE id = ? LIMIT 1');
    $check->bind_param('i', $jogoId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $check->close();
        responder(['success' => false, 'message' => 'Jogo não encontrado.'], 404);
    }
    $check->close();
    $stmt = $conexao->prepare(
        'INSERT INTO usuario_lista (usuario_id, jogo_id, notificar_promocao, notificar_preco, preco_alvo, desconto_minimo)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE notificar_promocao = VALUES(notificar_promocao), notificar_preco = VALUES(notificar_preco), preco_alvo = VALUES(preco_alvo), desconto_minimo = VALUES(desconto_minimo), data_atualizacao = CURRENT_TIMESTAMP'
    );
    $stmt->bind_param('iiiidd', $usuarioId, $jogoId, $notificarPromocao, $notificarPreco, $precoAlvo, $descontoMinimo);
    $sucesso = $stmt->execute();
    $stmt->close();
    responder($sucesso ? ['success' => true, 'message' => 'Jogo adicionado à Minha Lista.'] : ['success' => false, 'message' => 'Não foi possível adicionar o jogo à lista.'], $sucesso ? 200 : 500);
}

if ($acao === 'atualizar_lista') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    [$precoAlvo, $descontoMinimo, $notificarPromocao, $notificarPreco] = obterConfiguracaoLista($dados);
    $stmt = $conexao->prepare('UPDATE usuario_lista SET notificar_promocao = ?, notificar_preco = ?, preco_alvo = ?, desconto_minimo = ? WHERE usuario_id = ? AND jogo_id = ?');
    $stmt->bind_param('iiddii', $notificarPromocao, $notificarPreco, $precoAlvo, $descontoMinimo, $usuarioId, $jogoId);
    $sucesso = $stmt->execute();
    $stmt->close();
    responder($sucesso ? ['success' => true, 'message' => 'Configurações da lista atualizadas.'] : ['success' => false, 'message' => 'Não foi possível atualizar a lista.'], $sucesso ? 200 : 500);
}

if ($acao === 'remover_lista') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    $stmt = $conexao->prepare('DELETE FROM usuario_lista WHERE usuario_id = ? AND jogo_id = ?');
    $stmt->bind_param('ii', $usuarioId, $jogoId);
    $sucesso = $stmt->execute();
    $removeu = $stmt->affected_rows > 0;
    $stmt->close();
    responder($sucesso && $removeu ? ['success' => true, 'message' => 'Jogo removido da Minha Lista.'] : ['success' => false, 'message' => 'Jogo não encontrado na Minha Lista.'], $sucesso && $removeu ? 200 : 404);
}

if ($acao === 'adicionar_jogo') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    $status = validarStatusJogo((string)($dados['status'] ?? 'possuo'));

    $check = $conexao->prepare('SELECT id FROM jogos WHERE id = ? LIMIT 1');
    $check->bind_param('i', $jogoId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $check->close();
        responder(['success' => false, 'message' => 'Jogo não encontrado.'], 404);
    }
    $check->close();

    $stmt = $conexao->prepare(
        'INSERT INTO usuario_jogos (usuario_id, jogo_id, status) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status), data_atualizacao = CURRENT_TIMESTAMP'
    );
    $stmt->bind_param('iis', $usuarioId, $jogoId, $status);
    $sucesso = $stmt->execute();
    $stmt->close();
    responder($sucesso
        ? ['success' => true, 'message' => 'Jogo adicionado aos Meus Jogos.']
        : ['success' => false, 'message' => 'Não foi possível adicionar o jogo.'], $sucesso ? 200 : 500);
}

if ($acao === 'atualizar_jogo') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    $status = validarStatusJogo((string)($dados['status'] ?? ''));
    $stmt = $conexao->prepare('UPDATE usuario_jogos SET status = ? WHERE usuario_id = ? AND jogo_id = ?');
    $stmt->bind_param('sii', $status, $usuarioId, $jogoId);
    $sucesso = $stmt->execute();
    $alterou = $stmt->affected_rows > 0;
    $stmt->close();
    responder($sucesso && $alterou
        ? ['success' => true, 'message' => 'Status atualizado.']
        : ['success' => false, 'message' => 'Jogo não encontrado nos Meus Jogos.'], $sucesso && $alterou ? 200 : 404);
}

if ($acao === 'remover_jogo') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $jogoId = obterJogoIdDoPayload($dados);
    $stmt = $conexao->prepare('DELETE FROM usuario_jogos WHERE usuario_id = ? AND jogo_id = ?');
    $stmt->bind_param('ii', $usuarioId, $jogoId);
    $sucesso = $stmt->execute();
    $removeu = $stmt->affected_rows > 0;
    $stmt->close();
    responder($sucesso && $removeu
        ? ['success' => true, 'message' => 'Jogo removido dos Meus Jogos.']
        : ['success' => false, 'message' => 'Jogo não encontrado nos Meus Jogos.'], $sucesso && $removeu ? 200 : 404);
}

if ($acao === 'update') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $nome = trim((string)($dados['nome'] ?? ''));
    $usuario = trim((string)($dados['usuario'] ?? ''));
    $email = trim((string)($dados['email'] ?? ''));
    if ($nome === '' || $usuario === '' || $email === '') responder(['success' => false, 'message' => 'Todos os campos são obrigatórios.'], 422);
    if (tamanhoTexto($nome) > 100 || tamanhoTexto($usuario) > 50 || tamanhoTexto($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) responder(['success' => false, 'message' => 'Informe dados válidos para nome, usuário e e-mail.'], 422);

    $check = $conexao->prepare('SELECT id FROM usuarios WHERE (usuario = ? OR email = ?) AND id <> ? LIMIT 1');
    $check->bind_param('ssi', $usuario, $email, $usuarioId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        responder(['success' => false, 'message' => 'Usuário ou e-mail já cadastrado por outro usuário.'], 409);
    }
    $check->close();

    $stmt = $conexao->prepare('UPDATE usuarios SET nome = ?, usuario = ?, email = ? WHERE id = ?');
    $stmt->bind_param('sssi', $nome, $usuario, $email, $usuarioId);
    $sucesso = $stmt->execute();
    $stmt->close();
    if (!$sucesso) responder(['success' => false, 'message' => 'Erro ao atualizar usuário.'], 500);

    $atualizado = obterUsuario($conexao, $usuarioId);
    sincronizarSessao($atualizado);
    responder(['success' => true, 'message' => 'Dados atualizados com sucesso!', 'user' => $atualizado]);
}

if ($acao === 'update_password') {
    exigirMetodo('POST');
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    $senhaAtual = (string)($dados['senha_atual'] ?? '');
    $novaSenha = (string)($dados['nova_senha'] ?? '');
    $confirmarSenha = (string)($dados['confirmar_senha'] ?? '');
    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') responder(['success' => false, 'message' => 'Preencha todos os campos.'], 422);
    if ($novaSenha !== $confirmarSenha) responder(['success' => false, 'message' => 'As senhas não coincidem.'], 422);
    if (strlen($novaSenha) < 6) responder(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'], 422);

    $stmt = $conexao->prepare('SELECT senha FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) responder(['success' => false, 'message' => 'Senha atual incorreta.'], 422);

    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
    $stmt->bind_param('si', $hash, $usuarioId);
    $sucesso = $stmt->execute();
    $stmt->close();
    responder($sucesso ? ['success' => true, 'message' => 'Senha alterada com sucesso!'] : ['success' => false, 'message' => 'Erro ao alterar senha.'], $sucesso ? 200 : 500);
}

if ($acao === 'upload_photo') {
    exigirMetodo('POST');
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) responder(['success' => false, 'message' => 'Selecione uma imagem válida.'], 422);
    $arquivo = $_FILES['foto'];
    if ((int)$arquivo['size'] > 5 * 1024 * 1024) responder(['success' => false, 'message' => 'A imagem deve ter no máximo 5 MB.'], 422);

    $informacoesImagem = @getimagesize($arquivo['tmp_name']);
    $mime = is_array($informacoesImagem) ? ($informacoesImagem['mime'] ?? '') : '';
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($tipos[$mime])) responder(['success' => false, 'message' => 'Tipo de imagem não permitido. Use JPG, PNG, GIF ou WEBP.'], 422);

    $diretorio = dirname(__DIR__, 2) . '/uploads/perfil';
    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) responder(['success' => false, 'message' => 'Não foi possível preparar o armazenamento da imagem.'], 500);
    $nomeArquivo = 'usuario_' . $usuarioId . '_' . bin2hex(random_bytes(12)) . '.' . $tipos[$mime];
    $destino = $diretorio . '/' . $nomeArquivo;
    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) responder(['success' => false, 'message' => 'Não foi possível salvar a imagem.'], 500);

    $atual = obterUsuario($conexao, $usuarioId);
    $caminho = 'uploads/perfil/' . $nomeArquivo;
    $stmt = $conexao->prepare('UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
    $stmt->bind_param('si', $caminho, $usuarioId);
    if (!$stmt->execute()) {
        $stmt->close();
        @unlink($destino);
        responder(['success' => false, 'message' => 'Não foi possível atualizar a foto.'], 500);
    }
    $stmt->close();
    excluirArquivoFoto($atual['foto_perfil'] ?? null);
    responder(['success' => true, 'message' => 'Foto de perfil atualizada com sucesso!', 'user' => obterUsuario($conexao, $usuarioId)]);
}

if ($acao === 'delete') {
    exigirMetodo('POST');
    $atual = obterUsuario($conexao, $usuarioId);
    if (!$atual) responder(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
    $stmt = $conexao->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $usuarioId);
    $sucesso = $stmt->execute();
    $stmt->close();
    if (!$sucesso) responder(['success' => false, 'message' => 'Não foi possível excluir a conta.'], 500);
    excluirArquivoFoto($atual['foto_perfil'] ?? null);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    responder(['success' => true, 'message' => 'Conta excluída com sucesso.']);
}

responder(['success' => false, 'message' => 'Ação inválida.'], 400);
?>