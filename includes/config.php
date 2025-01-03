<?php
// Configurações do sistema
define('SITE_NAME', 'Game Finder');
define('MAX_SEARCH_RESULTS', 8);
define('DOWNLOADS_PATH', __DIR__ . '/../downloads');
define('GAME_SOURCES_PATH', __DIR__ . '/../game-sources');

// Configurações de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
session_start(); 