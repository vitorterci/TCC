<?php
require_once __DIR__ . '/config.php';

$dados = json_decode(file_get_contents('php://input'), true);
if (!is_array($dados)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$nome = trim((string)($dados['nome'] ?? ''));
$usuario = trim((string)($dados['usuario'] ?? ''));
$email = trim((string)($dados['email'] ?? ''));
$senha = (string)($dados['senha'] ?? '');

if ($nome === '' || $usuario === '' || $email === '' || $senha === '') {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
    exit;
}
if (strlen($senha) < 6) {
    echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres']);
    exit;
}

$check = $conexao->prepare('SELECT id FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1');
$check->bind_param('ss', $usuario, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário ou e-mail já cadastrado']);
    $check->close();
    exit;
}
$check->close();

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$role = 'usuario';
$ativo = 1;
$stmt = $conexao->prepare(
    'INSERT INTO usuarios (nome, usuario, email, senha, role, ativo)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssssi', $nome, $usuario, $email, $senhaHash, $role, $ativo);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário.']);
    $stmt->close();
    $conexao->close();
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Cadastro realizado com sucesso!',
    'user' => [
        'id' => (int)$conexao->insert_id,
        'nome' => $nome,
        'usuario' => $usuario,
        'email' => $email,
        'role' => $role,
        'ativo' => $ativo
    ]
]);

$stmt->close();
$conexao->close();
?>
