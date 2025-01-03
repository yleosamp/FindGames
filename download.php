<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['uri']) || !isset($data['title'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

require_once 'includes/functions.php';

try {
    $success = downloadGame($data['uri'], $data['title']);
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Download iniciado com sucesso' : 'Erro ao iniciar download'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 