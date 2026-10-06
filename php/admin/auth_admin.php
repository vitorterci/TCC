<?php
/** Proteção para páginas e APIs administrativas. */
require_once __DIR__ . '/../auth.php';

if (estaAutenticado()) {
    atualizarRoleSessao((int)$_SESSION['usuario_id']);
}

exigirAdmin();
?>
