<?php
// Previne qualquer saída antes do JSON
ob_start();

// Define o tipo de conteúdo como JSON e charset
header('Content-Type: application/json; charset=utf-8');

// Desativa a exibição de erros no output
ini_set('display_errors', 0);
error_reporting(0);

require_once 'includes/functions.php';

try {
    $query = $_GET['q'] ?? '';
    $results = [];

    if (strlen($query) >= 2) {
        $results = searchSteamGames($query);
    }

    // Limpa qualquer saída anterior
    ob_clean();
    
    // Garante que o JSON está bem formatado
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'error' => true,
        'message' => 'Erro na pesquisa'
    ]);
} 