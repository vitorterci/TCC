<?php
/**
 * Diagnóstico de conexão e estrutura do banco de dados.
 * Acesse http://localhost/tcc_/php/diagnostico.php em ambiente local.
 * Remova este arquivo após concluir os testes.
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

function esc($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function buscarTabelas($conexao) {
    $sql = $conexao instanceof mysqli
        ? 'SHOW TABLES'
        : "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'";

    $resultado = $conexao->query($sql);
    if (!$resultado) {
        return [];
    }

    $tabelas = [];
    while ($linha = $resultado->fetch_assoc()) {
        $valores = array_values($linha);
        if (isset($valores[0])) {
            $tabelas[] = $valores[0];
        }
    }

    return $tabelas;
}

function buscarColunas($conexao, $tabela) {
    if ($conexao instanceof mysqli) {
        $resultado = $conexao->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $tabela) . '`');
        if (!$resultado) {
            return [];
        }

        $colunas = [];
        while ($linha = $resultado->fetch_assoc()) {
            if (isset($linha['Field'])) {
                $colunas[] = $linha['Field'];
            }
        }
        return $colunas;
    }

    $resultado = $conexao->query("PRAGMA table_info('" . str_replace("'", "''", $tabela) . "')");
    if (!$resultado) {
        return [];
    }

    $colunas = [];
    while ($linha = $resultado->fetch_assoc()) {
        if (isset($linha['name'])) {
            $colunas[] = $linha['name'];
        }
    }
    return $colunas;
}

$tabelasEsperadas = [
    'feedback' => ['nome', 'email', 'tipo_feedback', 'mensagem', 'avaliacao'],
    'jogos' => ['id', 'nome', 'descricao', 'preco', 'avaliacao_gamplay'],
    'precos' => ['jogo_id', 'platforma', 'precos', 'data_coleta'],
    'usuarios' => ['id', 'nome', 'usuario', 'email', 'senha']
];

$erroConexao = $conexao->connect_error ?? null;
$tabelasExistentes = $erroConexao ? [] : buscarTabelas($conexao);
$origem = $conexao instanceof mysqli ? 'MySQL via mysqli' : 'fallback SQLite';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Diagnóstico do banco - Game Search</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 960px; margin: 32px auto; padding: 0 16px; color: #222; }
        .ok { color: #176b2c; }
        .erro { color: #a51d2d; }
        .aviso { color: #8a4b00; }
        table { border-collapse: collapse; width: 100%; margin: 16px 0 24px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; }
        code { background: #f3f3f3; padding: 2px 4px; }
    </style>
</head>
<body>
    <h1>Relatório de diagnóstico do sistema</h1>

    <?php if ($erroConexao): ?>
        <p class="erro"><strong>Erro de conexão:</strong> <?= esc($erroConexao) ?></p>
        <p>Confirme se o MySQL está em execução e se os dados de <code>php/config.php</code> correspondem ao ambiente XAMPP.</p>
    <?php else: ?>
        <p class="ok"><strong>Conexão estabelecida.</strong> Origem: <?= esc($origem) ?>.</p>
    <?php endif; ?>

    <h2>Tabelas esperadas</h2>
    <table>
        <thead>
            <tr><th>Tabela</th><th>Status</th><th>Colunas verificadas</th><th>Colunas ausentes</th></tr>
        </thead>
        <tbody>
        <?php foreach ($tabelasEsperadas as $tabela => $colunasEsperadas): ?>
            <?php
                $existe = in_array($tabela, $tabelasExistentes, true);
                $colunas = $existe ? buscarColunas($conexao, $tabela) : [];
                $ausentes = array_values(array_diff($colunasEsperadas, $colunas));
            ?>
            <tr>
                <td><code><?= esc($tabela) ?></code></td>
                <td class="<?= $existe ? 'ok' : 'erro' ?>">
                    <?= $existe ? 'Encontrada' : 'Não encontrada' ?>
                </td>
                <td><?= $existe ? esc(implode(', ', $colunas)) : '—' ?></td>
                <td class="<?= empty($ausentes) ? 'ok' : 'aviso' ?>">
                    <?= empty($ausentes) ? 'Nenhuma' : esc(implode(', ', $ausentes)) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Configuração carregada</h2>
    <ul>
        <li>Host: <code><?= esc($dbHost ?? '') ?></code></li>
        <li>Usuário: <code><?= esc($dbUsername ?? '') ?></code></li>
        <li>Banco: <code><?= esc($dbName ?? '') ?></code></li>
    </ul>

    <p class="aviso">Este diagnóstico não exibe a senha do banco. Remova o arquivo após os testes para não deixar informações de estrutura expostas.</p>

</body>
</html>
<?php
if ($conexao && method_exists($conexao, 'close')) {
    $conexao->close();
}
?>

