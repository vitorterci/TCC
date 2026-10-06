<?php
// php/corrigir_imagens.php
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "CORRIGINDO CAMINHOS DE IMAGENS\n";
echo "==============================\n\n";

// 1. Buscar todos os jogos
$sql = "SELECT id, nome, img FROM jogos";
$result = $conexao->query($sql);

if (!$result) {
    echo "Erro ao buscar jogos.\n";
    exit;
}

$contador = 0;
$corrigidos = 0;
$pular = 0;

while ($row = $result->fetch_assoc()) {
    $caminho = $row['img'] ?? '';
    $novoCaminho = null;
    
    // Se estiver vazio ou null
    if (empty($caminho)) {
        $novoCaminho = '../games/naoencontrada.png';
    }
    // Se não começa com '../games/' nem com '' nem com 'http'
    elseif (!preg_match('/^(games\/|assets\/|http)/', $caminho)) {
        // Extrair o nome do arquivo
        $nomeArquivo = basename($caminho);
        if ($nomeArquivo) {
            // Verificar se existe em ../games/
            $caminhoTeste = '../games/' . $nomeArquivo;
            if (file_exists(__DIR__ . '/../' . $caminhoTeste)) {
                $novoCaminho = $caminhoTeste;
            } else {
                // Verificar se existe em img/
                $caminhoTesteAssets = 'img/' . $nomeArquivo;
                if (file_exists(__DIR__ . '/../' . $caminhoTesteAssets)) {
                    $novoCaminho = $caminhoTesteAssets;
                } else {
                    $novoCaminho = '../games/naoencontrada.png';
                }
            }
        } else {
            $novoCaminho = '../games/naoencontrada.png';
        }
    }
    // Se está em ../games/ mas o arquivo não existe
    elseif (strpos($caminho, '../games/') === 0 && $caminho !== '../games/naoencontrada.png') {
        $caminhoCompleto = __DIR__ . '/../' . $caminho;
        if (!file_exists($caminhoCompleto)) {
            $novoCaminho = '../games/naoencontrada.png';
        }
    }
    // Se está em  mas o arquivo não existe
    elseif (strpos($caminho, '') === 0) {
        $caminhoCompleto = __DIR__ . '/../' . $caminho;
        if (!file_exists($caminhoCompleto)) {
            // Tenta encontrar em ../games/
            $nomeArquivo = basename($caminho);
            if ($nomeArquivo) {
                $caminhoTeste = '../games/' . $nomeArquivo;
                if (file_exists(__DIR__ . '/../' . $caminhoTeste)) {
                    $novoCaminho = $caminhoTeste;
                } else {
                    $novoCaminho = '../games/naoencontrada.png';
                }
            } else {
                $novoCaminho = '../games/naoencontrada.png';
            }
        }
    }
    
    // Se encontrou um novo caminho, atualiza
    if ($novoCaminho !== null && $novoCaminho !== $caminho) {
        $stmt = $conexao->prepare("UPDATE jogos SET img = ? WHERE id = ?");
        $stmt->bind_param("si", $novoCaminho, $row['id']);
        if ($stmt->execute()) {
            echo "✅ {$row['nome']} (ID: {$row['id']}): '{$caminho}' → '{$novoCaminho}'\n";
            $corrigidos++;
        } else {
            echo "❌ Erro ao corrigir {$row['nome']}: " . $stmt->error . "\n";
        }
        $stmt->close();
    } elseif ($novoCaminho !== null && $novoCaminho === $caminho) {
        $pular++;
    }
    $contador++;
}

echo "\nRESUMO:\n";
echo "Total de jogos: $contador\n";
echo "Caminhos corrigidos: $corrigidos\n";
echo "Caminhos já corretos: $pular\n";

$conexao->close();
?>