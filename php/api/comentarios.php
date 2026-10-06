<?php
/**
 * API DE COMENTÁRIOS POR JOGO - GAME SEARCH
 *
 * GET    ?jogo_id=X                 → lista comentários do jogo
 * POST   { jogo_id, comentario }    → cria (autenticado)
 * PUT    ?id=X { comentario }       → edita (dono)
 * DELETE ?id=X                      → exclui (dono ou admin)
 *
 * Regras:
 * - usuario_id SEMPRE da sessão (nunca do body)
 * - Prepared statements
 * - Limite de 1000 caracteres
 * - Comentário vazio rejeitado
 */

require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

const COMENTARIO_MAX = 1000;

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

function usuarioAutenticado(): ?array {
    $id = (int)($_SESSION['usuario_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    return [
        'id'   => $id,
        'role' => (string)($_SESSION['usuario_role'] ?? 'usuario'),
    ];
}

function validarComentario($texto): string {
    $texto = trim((string)$texto);

    if ($texto === '') {
        responder(['success' => false, 'message' => 'O comentário não pode estar vazio.'], 422);
    }

    if (function_exists('mb_strlen')) {
        if (mb_strlen($texto, 'UTF-8') > COMENTARIO_MAX) {
            responder([
                'success' => false,
                'message' => 'Comentário excede o limite de ' . COMENTARIO_MAX . ' caracteres.'
            ], 422);
        }
    } elseif (strlen($texto) > COMENTARIO_MAX) {
        responder([
            'success' => false,
            'message' => 'Comentário excede o limite de ' . COMENTARIO_MAX . ' caracteres.'
        ], 422);
    }

    return $texto;
}

function formatarComentario(array $row, ?array $usuarioAtual): array {
    $row['id']         = (int)$row['id'];
    $row['usuario_id'] = (int)$row['usuario_id'];
    $row['jogo_id']    = (int)$row['jogo_id'];

    $row['pode_editar'] = $usuarioAtual !== null
        && $usuarioAtual['id'] === $row['usuario_id'];

    $row['pode_excluir'] = $usuarioAtual !== null
        && ($usuarioAtual['id'] === $row['usuario_id']
            || ($usuarioAtual['role'] ?? '') === 'admin');

    return $row;
}

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$usuarioAtual = usuarioAutenticado();

/* ------------------------------------------------------------------ GET */
if ($metodo === 'GET') {
    $jogoId = (int)($_GET['jogo_id'] ?? 0);

    if ($jogoId <= 0) {
        responder(['success' => false, 'message' => 'Informe um jogo válido.'], 422);
    }

    $stmt = $conexao->prepare(
        "SELECT c.id, c.usuario_id, c.jogo_id, c.comentario,
                c.data_criacao, c.data_atualizacao,
                u.nome AS usuario_nome, u.foto_perfil AS usuario_foto
         FROM comentarios c
         INNER JOIN usuarios u ON u.id = c.usuario_id
         WHERE c.jogo_id = ?
         ORDER BY c.data_criacao DESC, c.id DESC"
    );

    if (!$stmt) {
        responder(['success' => false, 'message' => 'Não foi possível consultar os comentários.'], 500);
    }

    $stmt->bind_param('i', $jogoId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $comentarios = [];
    while ($row = $resultado->fetch_assoc()) {
        $comentarios[] = formatarComentario($row, $usuarioAtual);
    }
    $stmt->close();

    responder([
        'success'      => true,
        'comentarios'  => $comentarios,
        'total'        => count($comentarios),
        'autenticado'  => $usuarioAtual !== null,
    ]);
}

/* ----------------------------------------------------------------- POST */
if ($metodo === 'POST') {
    if (!$usuarioAtual) {
        responder(['success' => false, 'message' => 'Faça login para comentar.'], 401);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    }

    $jogoId = (int)($dados['jogo_id'] ?? 0);
    if ($jogoId <= 0) {
        responder(['success' => false, 'message' => 'Jogo inválido.'], 422);
    }

    $comentario = validarComentario($dados['comentario'] ?? '');

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
        'INSERT INTO comentarios (usuario_id, jogo_id, comentario) VALUES (?, ?, ?)'
    );
    $stmt->bind_param('iis', $usuarioAtual['id'], $jogoId, $comentario);
    $ok = $stmt->execute();
    $novoId = $conexao->insert_id;
    $stmt->close();

    if (!$ok) {
        responder(['success' => false, 'message' => 'Não foi possível publicar o comentário.'], 500);
    }

    responder([
        'success' => true,
        'message' => 'Comentário publicado.',
        'id'      => $novoId,
    ], 201);
}

/* ----------------------------------------------------- PUT/PATCH/DELETE */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    responder(['success' => false, 'message' => 'ID inválido.'], 422);
}

$stmt = $conexao->prepare('SELECT usuario_id FROM comentarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$dono = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dono) {
    responder(['success' => false, 'message' => 'Comentário não encontrado.'], 404);
}

$ehDono  = $usuarioAtual !== null && $usuarioAtual['id'] === (int)$dono['usuario_id'];
$ehAdmin = $usuarioAtual !== null && ($usuarioAtual['role'] ?? '') === 'admin';

if ($metodo === 'PUT' || $metodo === 'PATCH') {
    if (!$usuarioAtual) {
        responder(['success' => false, 'message' => 'Faça login para editar.'], 401);
    }
    if (!$ehDono) {
        responder(['success' => false, 'message' => 'Você só pode editar seus próprios comentários.'], 403);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder(['success' => false, 'message' => 'Dados inválidos.'], 400);
    }

    $comentario = validarComentario($dados['comentario'] ?? '');

    $stmt = $conexao->prepare(
        'UPDATE comentarios SET comentario = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?'
    );
    $stmt->bind_param('si', $comentario, $id);
    $ok = $stmt->execute();
    $stmt->close();

    responder(
        $ok
            ? ['success' => true, 'message' => 'Comentário atualizado.']
            : ['success' => false, 'message' => 'Não foi possível atualizar.'],
        $ok ? 200 : 500
    );
}

if ($metodo === 'DELETE') {
    if (!$usuarioAtual) {
        responder(['success' => false, 'message' => 'Faça login para excluir.'], 401);
    }
    if (!$ehDono && !$ehAdmin) {
        responder(['success' => false, 'message' => 'Sem permissão para excluir este comentário.'], 403);
    }

    $stmt = $conexao->prepare('DELETE FROM comentarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    responder(
        $ok
            ? ['success' => true, 'message' => 'Comentário excluído.']
            : ['success' => false, 'message' => 'Não foi possível excluir.'],
        $ok ? 200 : 500
    );
}

responder(['success' => false, 'message' => 'Método não suportado.'], 405);