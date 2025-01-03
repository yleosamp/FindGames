<?php
require_once 'includes/functions.php';

$appId = $_GET['id'] ?? null;
if (!$appId) {
    header('Location: index.php');
    exit;
}

$game = getGameDetails($appId);
if (!$game) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> - Game Finder</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/game.css">
    <style>
        .version-accordion {
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }
        
        .version-header {
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .version-content {
            display: none;
            padding: 15px;
        }
        
        .version-content.active {
            display: block;
        }
        
        .version-header:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .version-toggle {
            font-size: 20px;
            transition: transform 0.3s;
        }
        
        .version-header.active .version-toggle {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="game-container">
        <a href="index.php" class="back-button">← Voltar</a>
        
        <header class="game-header">
            <div class="game-banner">
                <img src="<?php echo htmlspecialchars($game['header_image']); ?>" 
                     alt="<?php echo htmlspecialchars($game['name']); ?>">
            </div>
            <div class="game-info">
                <h1><?php echo htmlspecialchars($game['name']); ?></h1>
                <div class="game-meta">
                    <span class="publisher"><?php echo htmlspecialchars($game['publishers'][0] ?? ''); ?></span>
                    <span class="release-date"><?php echo $game['release_date']['date'] ?? ''; ?></span>
                </div>
            </div>
        </header>

        <main class="game-content">
            <div class="game-description">
                <?php echo $game['detailed_description']; ?>
            </div>

            <?php if (!empty($game['downloads'])): ?>
            <div class="download-section">
                <h2>Downloads Disponíveis</h2>
                <?php foreach ($game['downloads'] as $version => $versionDownloads): ?>
                    <div class="version-accordion">
                        <div class="version-header" onclick="toggleVersion(this)">
                            <h3><?php echo htmlspecialchars($version); ?></h3>
                            <span class="version-toggle">▼</span>
                        </div>
                        <div class="version-content">
                            <?php foreach ($versionDownloads as $download): ?>
                                <?php foreach ($download['uris'] as $uri): ?>
                                    <div class="download-item">
                                        <div class="download-info">
                                            <span class="source"><?php echo htmlspecialchars($download['source']); ?></span>
                                            <?php if (isset($download['version'])): ?>
                                                <span class="version">v<?php echo htmlspecialchars($download['version']); ?></span>
                                            <?php endif; ?>
                                            <span class="size"><?php echo formatFileSize($download['fileSize']); ?></span>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($uri); ?>" 
                                           class="download-button"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            Download
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function toggleVersion(header) {
        const content = header.nextElementSibling;
        const isActive = header.classList.contains('active');
        
        // Fecha todas as outras abas
        document.querySelectorAll('.version-header.active').forEach(el => {
            if (el !== header) {
                el.classList.remove('active');
                el.nextElementSibling.classList.remove('active');
            }
        });
        
        // Toggle da aba atual
        header.classList.toggle('active');
        content.classList.toggle('active');
    }
    </script>
</body>
</html> 