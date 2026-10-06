<?php
/**
 * API para gerenciamento de jogos (Admin)
 * Game Search - TCC
 */

// Usar a mesma proteção da página administrativa, incluindo a consulta
// do tipo atual do usuário antes da autorização.
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
            "SELECT j.*, COALESCE((SELECT MIN(p.preco) FROM precos p WHERE p.jogo_id = j.id), 0) AS preco_oferta FROM jogos j WHERE j.id = ?"
        );

        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao preparar a consulta: ' . $conexao->error
            ]);
            exit;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($jogo = $result->fetch_assoc()) {
            $jogo['preco'] = $jogo['preco_oferta'];
            unset($jogo['preco_oferta']);

            echo json_encode([
                'success' => true,
                'jogo' => $jogo
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Jogo não encontrado'
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

        $slug = trim($dados['slug'] ?? '');
        $nome = trim($dados['nome'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');
        $categoria = trim($dados['categoria'] ?? '');
        $plataforma = trim($dados['plataforma'] ?? '');
        $genero = trim($dados['genero'] ?? '');
        $etaria = trim($dados['etaria'] ?? '');
        $ano = intval($dados['ano'] ?? 0);
        $status = trim($dados['status'] ?? 'ativo');

        if ($nome === '') {
            echo json_encode([
                'success' => false,
                'message' => 'O nome do jogo é obrigatório'
            ]);
            exit;
        }

        $sql = "
            INSERT INTO jogos
            (
                slug,
                nome,
                descricao,
                categoria,
                plataforma,
                genero,
                etaria,
                ano,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao preparar a consulta: ' . $conexao->error
            ]);
            exit;
        }

        $stmt->bind_param(
            "sssssssis",
            $slug,
            $nome,
            $descricao,
            $categoria,
            $plataforma,
            $genero,
            $etaria,
            $ano,
            $status
        );

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Jogo criado com sucesso',
                'id' => $conexao->insert_id
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar jogo: ' . $stmt->error
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

        $slug = trim($dados['slug'] ?? '');
        $nome = trim($dados['nome'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');
        $categoria = trim($dados['categoria'] ?? '');
        $plataforma = trim($dados['plataforma'] ?? '');
        $genero = trim($dados['genero'] ?? '');
        $etaria = trim($dados['etaria'] ?? '');
        $ano = intval($dados['ano'] ?? 0);
        $status = trim($dados['status'] ?? 'ativo');

        if ($nome === '') {
            echo json_encode([
                'success' => false,
                'message' => 'O nome do jogo é obrigatório'
            ]);
            exit;
        }

        $sql = "
            UPDATE jogos SET
                slug = ?,
                nome = ?,
                descricao = ?,
                categoria = ?,
                plataforma = ?,
                genero = ?,
                etaria = ?,
                ano = ?,
                status = ?
            WHERE id = ?
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao preparar a consulta: ' . $conexao->error
            ]);
            exit;
        }

        $stmt->bind_param(
            "sssssssisi",
            $slug,
            $nome,
            $descricao,
            $categoria,
            $plataforma,
            $genero,
            $etaria,
            $ano,
            $status,
            $id
        );

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Jogo atualizado com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar jogo: ' . $stmt->error
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
            "DELETE FROM jogos WHERE id = ?"
        );

        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao preparar a consulta: ' . $conexao->error
            ]);
            exit;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Jogo excluído com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir jogo: ' . $stmt->error
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
