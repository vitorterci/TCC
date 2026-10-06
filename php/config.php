<?php
/**
 * Configuração centralizada do banco de dados do projeto TCC.
 * A aplicação utiliza exclusivamente o banco MySQL tcc.
 */

if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

$scriptPath = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$isPageRequest = strpos($scriptPath, '/pages/') !== false;

// As páginas HTML também carregam esta configuração por meio da autenticação.
// Os cabeçalhos JSON/CORS devem ser enviados somente para endpoints PHP.
if (!$isPageRequest && !headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

if (!$isPageRequest && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'tcc';

mysqli_report(MYSQLI_REPORT_OFF);
$conexao = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($conexao->connect_errno) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível conectar ao banco de dados MySQL tcc.'
    ]);
    exit;
}

$conexao->set_charset('utf8mb4');

function executarConsulta($conexao, $sql, $tipos = '', $params = []) {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'message' => 'Erro na preparação da consulta.'];
    }

    if ($tipos !== '' && $params !== []) {
        $stmt->bind_param($tipos, ...$params);
    }

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => 'Erro na execução da consulta.'];
    }

    return ['success' => true, 'stmt' => $stmt];
}

function normalizarEtaria($valor) {
    $texto = strtolower(trim((string)($valor ?? '')));
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;

    if ($texto === '') {
        return 'N/I';
    }
    if ($texto === 'l' || $texto === '0' || strpos($texto, 'livre') !== false) {
        return 'L';
    }
    if (preg_match('/(?:^|[^0-9])(10|12|14|16|18)(?:[^0-9]|$)/', $texto, $correspondencia)) {
        return $correspondencia[1];
    }

    return 'N/I';
}
?>
