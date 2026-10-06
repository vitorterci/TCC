<?php
/**
 * API para gerenciamento de feedbacks (Admin)
 * Game Search - TCC
 */

require_once __DIR__ . '/../../admin/auth_admin.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_GET['acao'] ?? '';

exigirMetodoPorAcao($acao, [
    'get' => 'GET',
    'update_status' => 'POST',
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
            "SELECT * FROM feedback WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($feedback = $result->fetch_assoc()) {

            echo json_encode([
                'success' => true,
                'feedback' => $feedback
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Feedback não encontrado'
            ]);
        }

        break;


    case 'update_status':

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

        if (!is_array($dados) || !isset($dados['status'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos'
            ]);
            exit;
        }

        $status = $dados['status'];

        $statusPermitidos = [
            'pendente',
            'lido',
            'respondido'
        ];

        if (!in_array($status, $statusPermitidos, true)) {

            echo json_encode([
                'success' => false,
                'message' => 'Status inválido'
            ]);

            exit;
        }

        $stmt = $conexao->prepare(
            "UPDATE feedback
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $id
        );

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $stmt->error
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
            "DELETE FROM feedback WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            echo json_encode([
                'success' => true,
                'message' => 'Feedback excluído com sucesso'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir feedback: ' . $stmt->error
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
