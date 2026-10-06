<?php
/**
 * API para gerenciamento de preços (Admin)
 * Game Search - TCC
 */

require_once __DIR__ . '/../../admin/auth_admin.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_GET['acao'] ?? '';

exigirMetodoPorAcao($acao, [
    'get' => 'GET',
    'create' => 'POST',
    'update' => 'POST',
    'delete' => 'DELETE'
]);

switch ($acao) {

    case 'get':

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID inválido'
            ]);
            exit;
        }

        $stmt = $conexao->prepare(
            "SELECT * FROM precos WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($preco = $result->fetch_assoc()) {

            echo json_encode([
                'success' => true,
                'preco' => $preco
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Preço não encontrado'
            ]);
        }

        break;


    case 'create':

        $dados = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($dados)) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos'
            ]);
            exit;
        }

        $jogo_id = intval($dados['jogo_id'] ?? 0);
        $loja = trim($dados['loja'] ?? '');
        $plataforma = trim($dados['plataforma'] ?? '');
        $preco = floatval($dados['preco'] ?? 0);
        $preco_antigo = floatval($dados['preco_antigo'] ?? 0);
        $url_oferta = trim($dados['url_oferta'] ?? '');
        $disponivel = isset($dados['disponivel'])
            ? intval($dados['disponivel'])
            : 1;

        if ($jogo_id <= 0 || $loja === '' || $plataforma === '' || $preco < 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados obrigatórios inválidos'
            ]);
            exit;
        }

        $desconto = 0;

        if ($preco_antigo > 0 && $preco < $preco_antigo) {
            $desconto = round(
                (($preco_antigo - $preco) / $preco_antigo) * 100
            );
        }

        $sql = "
            INSERT INTO precos
            (
                jogo_id,
                loja,
                plataforma,
                preco,
                preco_antigo,
                desconto_percentual,
                moeda,
                url_oferta,
                disponivel
            )
            VALUES (?, ?, ?, ?, ?, ?, 'BRL', ?, ?)
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "issddisi",
            $jogo_id,
            $loja,
            $plataforma,
            $preco,
            $preco_antigo,
            $desconto,
            $url_oferta,
            $disponivel
        );

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Preço criado com sucesso',
                'id' => $conexao->insert_id
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar preço: ' . $stmt->error
            ]);
        }

        break;


    case 'update':

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID inválido'
            ]);
            exit;
        }

        $dados = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($dados)) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos'
            ]);
            exit;
        }

        $jogo_id = intval($dados['jogo_id'] ?? 0);
        $loja = trim($dados['loja'] ?? '');
        $plataforma = trim($dados['plataforma'] ?? '');
        $preco = floatval($dados['preco'] ?? 0);
        $preco_antigo = floatval($dados['preco_antigo'] ?? 0);
        $url_oferta = trim($dados['url_oferta'] ?? '');
        $disponivel = isset($dados['disponivel'])
            ? intval($dados['disponivel'])
            : 1;

        $desconto = 0;

        if ($preco_antigo > 0 && $preco < $preco_antigo) {
            $desconto = round(
                (($preco_antigo - $preco) / $preco_antigo) * 100
            );
        }

        $sql = "
            UPDATE precos SET
                jogo_id = ?,
                loja = ?,
                plataforma = ?,
                preco = ?,
                preco_antigo = ?,
                desconto_percentual = ?,
                url_oferta = ?,
                disponivel = ?
            WHERE id = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "issddisii",
            $jogo_id,
            $loja,
            $plataforma,
            $preco,
            $preco_antigo,
            $desconto,
            $url_oferta,
            $disponivel,
            $id
        );

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Preço atualizado com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar preço: ' . $stmt->error
            ]);
        }

        break;


    case 'delete':

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID inválido'
            ]);
            exit;
        }

        $stmt = $conexao->prepare(
            "DELETE FROM precos WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Preço excluído com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir preço: ' . $stmt->error
            ]);
        }

        break;


    default:

        echo json_encode([
            'success' => false,
            'message' => 'Ação inválida'
        ]);

        break;
}
?>
