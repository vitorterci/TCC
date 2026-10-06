<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getUsuarioLogado() {
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    global $conexao;
    $id = (int)$_SESSION['usuario_id'];
    $stmt = $conexao->prepare(
        'SELECT id, nome, usuario, email, role, ativo, data_cadastro, data_atualizacao
         FROM usuarios
         WHERE id = ? AND ativo = 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        return null;
    }

    $usuario['id'] = (int)$usuario['id'];
    $usuario['ativo'] = (int)$usuario['ativo'];
    return $usuario;
}

function isLogado() {
    return getUsuarioLogado() !== null;
}

function getUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

function getUsuarioNome() {
    return $_SESSION['usuario_nome'] ?? null;
}
?>
