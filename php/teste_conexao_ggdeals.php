<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';

echo "=== TESTE DE CONEXÃO ===\n\n";

// PHP
echo "PHP: " . PHP_VERSION . "\n";

// cURL
echo "cURL: ";
echo function_exists('curl_init') ? "OK\n" : "ERRO - cURL não habilitado\n";

// MySQL
echo "MySQL: ";

if (isset($conexao) && $conexao instanceof mysqli) {
    if ($conexao->connect_errno) {
        echo "ERRO\n";
        echo "Código: {$conexao->connect_errno}\n";
        echo "Mensagem: {$conexao->connect_error}\n";
    } else {
        echo "OK\n";
        echo "Banco selecionado: " . $conexao->query("SELECT DATABASE()")->fetch_row()[0] . "\n";
    }
} else {
    echo "ERRO - variável \$conexao não encontrada\n";
}

// API Key
echo "\nGGDEALS_API_KEY: ";

$apiKey = getenv('GGDEALS_API_KEY');

if ($apiKey !== false && trim($apiKey) !== '') {
    echo "CONFIGURADA\n";
    echo "Tamanho da chave: " . strlen(trim($apiKey)) . " caracteres\n";
} else {
    echo "NÃO CONFIGURADA\n";
}

// Região
echo "GGDEALS_REGION: ";
echo getenv('GGDEALS_REGION') ?: 'br (padrão)';

echo "\n\n=== FIM DO TESTE ===\n";