<?php
/**
 * Sistema centralizado de autenticação e autorização.
 */

if (session_status() === PHP_SESSION_NONE) {
    $duracaoSessao = 60 * 60 * 24 * 30;
    ini_set('session.gc_maxlifetime', (string)$duracaoSessao);
    session_set_cookie_params([
        'lifetime' => $duracaoSessao,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/config.php';

function estaAutenticado() {
    return !empty($_SESSION['usuario_id']) && (int)($_SESSION['usuario_ativo'] ?? 0) === 1;
}

function isAdmin() {
    if (!estaAutenticado()) {
        return false;
    }

    global $conexao;
    $id = (int)$_SESSION['usuario_id'];
    $stmt = $conexao->prepare('SELECT role, ativo FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || (int)$usuario['ativo'] !== 1) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_ativo'], $_SESSION['usuario_role']);
        return false;
    }

    $_SESSION['usuario_role'] = $usuario['role'];
    $_SESSION['usuario_ativo'] = (int)$usuario['ativo'];
    return $usuario['role'] === 'admin';
}

function getUsuarioAtual() {
    if (!estaAutenticado()) {
        return null;
    }

    return [
        'id' => (int)$_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'usuario' => $_SESSION['usuario_usuario'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'role' => $_SESSION['usuario_role'] ?? 'usuario',
        'ativo' => 1
    ];
}

function exigirAutenticacao() {
    if (!estaAutenticado()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/index.html';
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if (substr($base, -6) === '/pages') {
            $base = substr($base, 0, -6);
        }
        header('Location: ' . $base . '/pages/login.html');
        exit;
    }
}

function exigirAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Acesso negado. Você não tem permissão de administrador.'
        ]);
        exit;
    }
}

function exigirMetodoPorAcao($acao, array $metodosPorAcao) {
    $metodoEsperado = $metodosPorAcao[$acao] ?? null;
    if ($metodoEsperado === null) {
        return;
    }

    $metodoAtual = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($metodoAtual !== $metodoEsperado) {
        http_response_code(405);
        header('Allow: ' . $metodoEsperado);
        echo json_encode(['success' => false, 'message' => 'Método HTTP não permitido para esta ação.']);
        exit;
    }
}

function podeAcessar($pagina) {
    $publicas = ['index', 'login', 'register', 'sobre', 'ajuda', 'feedback'];
    $autenticadas = ['perfil', 'configuracao', 'biblioteca'];
    $pagina = basename(basename($pagina, '.php'), '.html');

    if (in_array($pagina, $publicas, true) || $pagina === '') {
        return true;
    }
    if (in_array($pagina, $autenticadas, true)) {
        return estaAutenticado();
    }
    if (strpos($pagina, 'admin') !== false) {
        return isAdmin();
    }

    return estaAutenticado();
}

function atualizarRoleSessao($usuarioId) {
    global $conexao;
    $stmt = $conexao->prepare('SELECT role, ativo FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || (int)$usuario['ativo'] !== 1) {
        unset($_SESSION['usuario_role'], $_SESSION['usuario_ativo']);
        return null;
    }

    $_SESSION['usuario_role'] = $usuario['role'];
    $_SESSION['usuario_ativo'] = (int)$usuario['ativo'];
    return $usuario['role'];
}

function fazerLogin($usuario) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int)$usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuário';
    $_SESSION['usuario_usuario'] = $usuario['usuario'] ?? '';
    $_SESSION['usuario_email'] = $usuario['email'] ?? '';
    $_SESSION['usuario_role'] = $usuario['role'] ?? 'usuario';
    $_SESSION['usuario_ativo'] = (int)($usuario['ativo'] ?? 1);
    return true;
}

function fazerLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    return true;
}

function getRedirectAfterLogin() {
    $redirect = $_SESSION['redirect_after_login'] ?? '/index.html';
    unset($_SESSION['redirect_after_login']);
    return $redirect;
}
?>