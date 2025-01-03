<?php
header('Content-Type: application/json');
require_once 'includes/functions.php';

$steamId = $_GET['steam_id'] ?? null;

if (!$steamId) {
    echo json_encode(['error' => 'Steam ID não fornecido']);
    exit;
}

$sources = getGameSources($steamId);
echo json_encode($sources); 