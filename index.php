<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Finder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="spotlight-container">
        <div class="search-wrapper">
            <input type="text" 
                   id="game-search" 
                   placeholder="Digite o nome do jogo..." 
                   autocomplete="off"
                   autofocus>
            <div class="loading-indicator" id="search-loading"></div>
            <div id="search-results" class="search-results"></div>
        </div>
    </div>
    <script src="assets/js/search.js"></script>
</body>
</html> 