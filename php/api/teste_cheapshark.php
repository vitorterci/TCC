<?php

$url = 'https://www.cheapshark.com/api/1.0/games';

$parametros = [
    'title' => 'The Witcher 3: Wild Hunt',
    'limit' => 20
];

$url .= '?' . http_build_query($parametros);

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,

    // Temporário devido ao FiltroWeb
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,

    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: TCC-GameSearch/1.0'
    ]
]);

$resposta = curl_exec($ch);

$erro = curl_error($ch);

$status = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');

echo "URL:\n";
echo $url . "\n\n";

echo "HTTP:\n";
echo $status . "\n\n";

echo "ERRO:\n";
echo ($erro ?: 'NENHUM') . "\n\n";

echo "RESPOSTA DA CHEAPSHARK:\n";
echo "========================\n\n";

echo $resposta ?: '(vazia)';