<?php
require_once 'config.php';

function searchSteamGames($query) {
    $steamApiUrl = "https://store.steampowered.com/api/storesearch/?term=" . urlencode($query) . "&l=portuguese&cc=BR";
    
    try {
        $response = file_get_contents($steamApiUrl);
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['items'])) {
            return [];
        }

        $results = [];
        foreach ($data['items'] as $game) {
            $detailsUrl = "https://store.steampowered.com/api/appdetails?appids={$game['id']}&l=portuguese&cc=BR";
            $detailsResponse = file_get_contents($detailsUrl);
            $detailsData = json_decode($detailsResponse, true);
            
            if ($detailsData && $detailsData[$game['id']]['success']) {
                $gameData = $detailsData[$game['id']]['data'];
                
                // Pula se for DLC
                if (isset($gameData['type']) && $gameData['type'] === 'dlc') {
                    continue;
                }
                
                $game['header_image'] = $gameData['header_image'];
                $game['publishers'] = $gameData['publishers'] ?? [];
                $game['release_date'] = $gameData['release_date'] ?? null;
                
                // Verifica downloads disponíveis
                $downloads = getGameDownloads($game['id']);
                $game['has_downloads'] = !empty($downloads);
                
                $results[] = $game;
            }
        }

        return $results;
    } catch (Exception $e) {
        error_log("Erro na busca Steam: " . $e->getMessage());
        return [];
    }
}

function getGameDetails($appId) {
    $steamApiUrl = "https://store.steampowered.com/api/appdetails?appids={$appId}&l=portuguese&cc=BR";
    
    try {
        $response = file_get_contents($steamApiUrl);
        $data = json_decode($response, true);
        
        if ($data && $data[$appId]['success']) {
            $gameData = $data[$appId]['data'];
            $gameData['downloads'] = getGameDownloads($appId);
            return $gameData;
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar detalhes do jogo: " . $e->getMessage());
    }
    return null;
}

function cleanGameTitle($title, $gameName) {
    // Remove o nome do jogo e palavras comuns em inglês
    $cleanTitle = str_ireplace($gameName, '', $title);
    $cleanTitle = str_ireplace([
        'free download', 
        'download', 
        'pc', 
        'repack',
        'multi',
        'by',
        'от',  // russo: "por"
        'для', // russo: "para"
        'игры', // russo: "jogos"
        'торрент', // russo: "torrent"
        'механики', // russo: "mechanics"
        'хатаб', // russo: "xatab"
        'репак', // russo: "repack"
        'fitgirl',
        'codex',
        'skidrow',
        'plaza',
        'gog',
        'rip',
        'proper',
        'dodi',
        'Decepticon',
        'palyer'
    ], '', $cleanTitle);
    
    // Remove caracteres não latinos (cirílico, etc)
    $cleanTitle = preg_replace('/[^\p{Latin}\d\s\-\[\]\(\)\._]/u', '', $cleanTitle);
    
    // Remove dois pontos do início do título
    $cleanTitle = preg_replace('/^:+\s*/', '', $cleanTitle);
    
    // Remove colchetes e parênteses vazios ou com espaços
    $cleanTitle = preg_replace('/\[\s*\]|\(\s*\)/', '', $cleanTitle);
    
    // Remove espaços múltiplos
    $cleanTitle = preg_replace('/\s+/', ' ', $cleanTitle);
    
    // Remove espaços do início e fim
    $cleanTitle = trim($cleanTitle);
    
    // Se não sobrou nada, é a versão padrão
    return empty($cleanTitle) ? 'Standard Edition' : $cleanTitle;
}

function getGameDownloads($appId) {
    $downloads = [];
    $sourceFiles = glob(GAME_SOURCES_PATH . '/*.json');
    
    $steamApiUrl = "https://store.steampowered.com/api/appdetails?appids={$appId}&l=portuguese&cc=BR";
    $response = file_get_contents($steamApiUrl);
    $data = json_decode($response, true);
    
    if (!$data || !$data[$appId]['success']) {
        return $downloads;
    }
    
    $gameName = strtolower($data[$appId]['data']['name']);
    
    foreach ($sourceFiles as $file) {
        if (basename($file) === 'catalogue.json') continue;
        
        $sourceData = json_decode(file_get_contents($file), true);
        
        // Usa o nome definido no JSON em vez do nome do arquivo
        $sourceName = $sourceData['name'] ?? basename($file, '.json');
        
        if (isset($sourceData['downloads'])) {
            foreach ($sourceData['downloads'] as $download) {
                if (isset($download['title']) && 
                    stripos(strtolower($download['title']), $gameName) !== false) {
                    
                    // Limpa o título e extrai a versão
                    $version = cleanGameTitle($download['title'], $gameName);
                    
                    if (!isset($downloads[$version])) {
                        $downloads[$version] = [];
                    }
                    
                    $downloads[$version][] = [
                        'source' => $sourceName, // Agora usa o nome original do JSON
                        'uris' => $download['uris'],
                        'fileSize' => $download['fileSize'] ?? null,
                        'version' => $download['version'] ?? null
                    ];
                }
            }
        }
    }
    
    return $downloads;
}

function formatFileSize($bytes) {
    $units = ['GB', 'TB', 'PB', 'EB', 'ZB'];
    $bytes = max((float)$bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    $formattedBytes = number_format($bytes, 2, '.', '');
    return $formattedBytes . ' ' . $units[$pow];
}