<?php
require_once __DIR__ . '/../../admin/auth_admin.php';
header('Content-Type: application/json; charset=utf-8');

$acao = $_GET['acao'] ?? '';
exigirMetodoPorAcao($acao, ['get' => 'GET', 'update' => 'POST', 'delete' => 'DELETE']);

if ($acao === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }
    $stmt = $conexao->prepare('SELECT id, nome, usuario, email, role, ativo, data_cadastro, data_atualizacao FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    echo json_encode($usuario
        ? ['success' => true, 'usuario' => $usuario]
        : ['success' => false, 'message' => 'Usuário não encontrado']);
    $stmt->close();
    exit;
}

if ($acao === 'update') {
    $id = (int)($_GET['id'] ?? 0);
    $dados = json_decode(file_get_contents('php://input'), true);
    if ($id <= 0 || !is_array($dados)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $nome = trim((string)($dados['nome'] ?? ''));
    $usuario = trim((string)($dados['usuario'] ?? ''));
    $email = trim((string)($dados['email'] ?? ''));
    $role = (string)($dados['role'] ?? 'usuario');
    $ativo = isset($dados['ativo']) ? (int)(bool)$dados['ativo'] : 1;
    if ($nome === '' || $usuario === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['usuario', 'admin'], true)) {
        echo json_encode(['success' => false, 'message' => 'Dados de usuário inválidos']);
        exit;
    }

    $stmtAtual = $conexao->prepare('SELECT role, ativo FROM usuarios WHERE id = ?');
    $stmtAtual->bind_param('i', $id);
    $stmtAtual->execute();
    $atual = $stmtAtual->get_result()->fetch_assoc();
    $stmtAtual->close();
    if (!$atual) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    if ($id === (int)$_SESSION['usuario_id'] && ($role !== 'admin' || $ativo !== 1)) {
        echo json_encode(['success' => false, 'message' => 'Não é permitido remover sua própria permissão de administrador ou desativar sua conta']);
        exit;
    }
    if ($atual['role'] === 'admin' && $role !== 'admin' && $id !== (int)$_SESSION['usuario_id']) {
        echo json_encode(['success' => false, 'message' => 'Não é permitido rebaixar outro administrador']);
        exit;
    }

    $check = $conexao->prepare('SELECT id FROM usuarios WHERE (usuario = ? OR email = ?) AND id <> ? LIMIT 1');
    $check->bind_param('ssi', $usuario, $email, $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Usuário ou e-mail já cadastrado por outro usuário']);
        $check->close();
        exit;
    }
    $check->close();

    $stmt = $conexao->prepare('UPDATE usuarios SET nome = ?, usuario = ?, email = ?, role = ?, ativo = ? WHERE id = ?');
    $stmt->bind_param('ssssii', $nome, $usuario, $email, $role, $ativo, $id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário.']);
        $stmt->close();
        exit;
    }

    if ($id === (int)$_SESSION['usuario_id']) {
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_usuario'] = $usuario;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_role'] = $role;
        $_SESSION['usuario_ativo'] = $ativo;
    }
    echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso']);
    $stmt->close();
    exit;
}

if ($acao === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }
    if ($id === (int)$_SESSION['usuario_id']) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir sua própria conta']);
        exit;
    }

    $stmtRole = $conexao->prepare('SELECT role FROM usuarios WHERE id = ?');
    $stmtRole->bind_param('i', $id);
    $stmtRole->execute();
    $alvo = $stmtRole->get_result()->fetch_assoc();
    $stmtRole->close();
    if (!$alvo) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }
    if ($alvo['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Não é permitido excluir outro administrador']);
        exit;
    }

    $stmt = $conexao->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    echo json_encode($stmt->execute()
        ? ['success' => true, 'message' => 'Usuário excluído com sucesso']
        : ['success' => false, 'message' => 'Erro ao excluir usuário.']);
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida']);
?>
