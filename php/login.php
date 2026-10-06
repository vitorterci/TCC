<?php
require_once __DIR__ . '/config.php';

$dados = json_decode(file_get_contents('php://input'), true);
if (!is_array($dados)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$identificador = trim((string)($dados['email'] ?? $dados['usuario'] ?? ''));
$senha = (string)($dados['senha'] ?? '');

if ($identificador === '' || $senha === '') {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
    exit;
}

$stmt = $conexao->prepare(
    'SELECT id, nome, usuario, email, senha, role, ativo, data_cadastro, data_atualizacao
     FROM usuarios
     WHERE (email = ? OR usuario = ?) AND ativo = 1
     LIMIT 1'
);
$stmt->bind_param('ss', $identificador, $identificador);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário ou senha incorretos']);
    $stmt->close();
    exit;
}

$duracaoSessao = 60 * 60 * 24 * 30;
ini_set('session.gc_maxlifetime', (string)$duracaoSessao);
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $duracaoSessao,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int)$usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_usuario'] = $usuario['usuario'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_role'] = $usuario['role'];
$_SESSION['usuario_ativo'] = (int)$usuario['ativo'];

unset($usuario['senha']);
$usuario['id'] = (int)$usuario['id'];
$usuario['ativo'] = (int)$usuario['ativo'];

echo json_encode([
    'success' => true,
    'message' => 'Login realizado com sucesso!',
    'user' => $usuario
]);

$stmt->close();
$conexao->close();
?>
