<?php
// php/verificar_imagens.php
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

$sql = "SELECT id, nome, img FROM jogos ORDER BY id";
$result = $conexao->query($sql);

if (!$result) {
    echo "Erro ao buscar jogos.\n";
    exit;
}

echo "VERIFICANDO IMAGENS DOS JOGOS\n";
echo "==============================\n\n";

$semImagem = [];
$comImagem = [];
$caminhosInvalidos = [];

while ($row = $result->fetch_assoc()) {
    $caminho = $row['img'] ?? '';
    $caminhoCompleto = __DIR__ . '/../' . $caminho;
    
    if (empty($caminho)) {
        $semImagem[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'caminho' => '(vazio)'
        ];
    } elseif (!file_exists($caminhoCompleto)) {
        $caminhosInvalidos[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'caminho' => $caminho
        ];
    } else {
        $comImagem[] = $row['nome'];
    }
}

echo "JOGOS COM IMAGEM (" . count($comImagem) . "):\n";
foreach ($comImagem as $nome) {
    echo "  ✅ $nome\n";
}

echo "\nJOGOS SEM IMAGEM (" . count($semImagem) . "):\n";
foreach ($semImagem as $jogo) {
    echo "  ❌ {$jogo['nome']} (ID: {$jogo['id']}) - Caminho: {$jogo['caminho']}\n";
}

echo "\nJOGOS COM CAMINHO INVÁLIDO (" . count($caminhosInvalidos) . "):\n";
foreach ($caminhosInvalidos as $jogo) {
    echo "  ⚠️ {$jogo['nome']} (ID: {$jogo['id']}) - Caminho atual: {$jogo['caminho']}\n";
}

echo "\nSUGESTÃO: Para os jogos sem imagem, baixe a capa e coloque em ../games/\n";
echo "com o nome: ../games/" . strtolower(str_replace(' ', '-', $jogo['nome'])) . ".webp\n";

$conexao->close();
?>