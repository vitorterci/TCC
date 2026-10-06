<?php
/**
 * Processamento do Formulário de Feedback
 * Projeto: TCC (Game Search)
 * Conecta-se à tabela feedback no banco MySQL tcc utilizando prepared statements.
 */

require_once 'config.php';

// Suportar tanto JSON via Fetch API quanto formulário tradicional POST
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados) {
    $dados = $_POST;
}

if (!$dados) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado foi enviado.']);
    exit;
}

$nome = trim($dados['nome'] ?? '');
$email = trim($dados['email'] ?? '');
$tipo_feedback = trim($dados['tipo'] ?? 'sugestao');
$mensagem = trim($dados['mensagem'] ?? '');
$avaliacao = intval($dados['avaliacao'] ?? 5);

// Validações
if (empty($mensagem)) {
    echo json_encode(['success' => false, 'message' => 'O campo de mensagem é obrigatório.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'O e-mail informado é inválido.']);
    exit;
}

// Validar ENUM tipo_feedback conforme estrutura real do banco: 'sugestao','reclamacao','elogio','duvida'
$tipos_permitidos = ['sugestao', 'reclamacao', 'elogio', 'duvida'];
if (!in_array($tipo_feedback, $tipos_permitidos)) {
    $tipo_feedback = 'sugestao';
}

// Inserção segura com prepared statement utilizando exatamente as colunas existentes na tabela feedback
$stmt = $conexao->prepare("INSERT INTO feedback (nome, email, tipo_feedback, mensagem, avaliacao) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conexao->error]);
    exit;
}

$stmt->bind_param("ssssi", $nome, $email, $tipo_feedback, $mensagem, $avaliacao);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Feedback enviado com sucesso! Agradecemos sua colaboração.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar o feedback no banco de dados: ' . $stmt->error
    ]);
}

$stmt->close();
$conexao->close();
?>
