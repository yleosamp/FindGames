document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('game-search');
    const searchResults = document.getElementById('search-results');
    let timeoutId;

    // Criar modal para sources
    const modal = document.createElement('div');
    modal.className = 'sources-modal';
    modal.style.display = 'none';
    document.body.appendChild(modal);

    const showSources = async (gameId, gameTitle) => {
        try {
            const response = await fetch(`sources.php?game_id=${gameId}`);
            if (!response.ok) throw new Error('Erro na requisição');
            
            const sources = await response.json();
            
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>${gameTitle}</h2>
                    <div class="sources-list">
                        ${sources.length ? sources.map(source => `
                            <div class="source-item">
                                <div class="source-info">
                                    <span class="source-name">${source.name}</span>
                                    ${source.version ? `<span class="version">v${source.version}</span>` : ''}
                                    <span class="source-size">${formatFileSize(source.fileSize)}</span>
                                </div>
                                <a href="${source.uri}" class="download-button" 
                                   data-type="${source.uri.startsWith('magnet:') ? 'magnet' : 'direct'}">
                                    Download
                                </a>
                            </div>
                        `).join('') : '<div class="no-sources">Nenhuma fonte disponível</div>'}
                    </div>
                    <button class="close-modal">Fechar</button>
                </div>
            `;
            
            modal.style.display = 'flex';
            
            modal.querySelector('.close-modal').onclick = () => {
                modal.style.display = 'none';
            };
            
        } catch (error) {
            console.error('Erro ao carregar fontes:', error);
            modal.innerHTML = `
                <div class="modal-content error">
                    <h2>Erro</h2>
                    <p>Não foi possível carregar as fontes de download.</p>
                    <button class="close-modal">Fechar</button>
                </div>
            `;
            modal.style.display = 'flex';
        }
    };

    const formatFileSize = (bytes) => {
        if (!bytes) return 'Tamanho desconhecido';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        return `${size.toFixed(2)} ${units[unitIndex]}`;
    };

    const showResults = (games) => {
        searchResults.innerHTML = '';
        
        if (games && games.length > 0) {
            games.forEach(game => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                
                // Primeiro renderiza sem a imagem
                resultItem.innerHTML = `
                    <div class="game-thumbnail-placeholder"></div>
                    <div class="result-info">
                        <div class="title">${game.name}</div>
                        <div class="details">
                            ${game.publishers ? `<span class="publisher">${game.publishers[0]}</span>` : ''}
                            ${game.release_date ? `<span class="release-date">${game.release_date.date}</span>` : ''}
                        </div>
                    </div>
                    <span class="download-indicator ${game.has_downloads ? 'available' : 'unavailable'}">
                        ${game.has_downloads ? '✓' : '✗'}
                    </span>
                `;
                
                // Carrega a imagem de forma assíncrona
                if (game.header_image) {
                    const img = new Image();
                    img.onload = () => {
                        const placeholder = resultItem.querySelector('.game-thumbnail-placeholder');
                        if (placeholder) {
                            placeholder.replaceWith(img);
                        }
                    };
                    img.className = 'game-thumbnail';
                    img.src = game.header_image;
                    img.alt = game.name;
                }
                
                resultItem.addEventListener('click', () => {
                    window.location.href = `game.php?id=${game.id}`;
                });
                
                searchResults.appendChild(resultItem);
            });
            searchResults.style.display = 'block';
        } else {
            searchResults.style.display = 'none';
        }
    };

    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeoutId);
        const loadingIndicator = document.getElementById('search-loading');
        
        if (e.target.value.length < 2) {
            searchResults.style.display = 'none';
            loadingIndicator.style.display = 'none';
            return;
        }

        loadingIndicator.style.display = 'block';

        timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(`search.php?q=${encodeURIComponent(e.target.value)}`);
                if (!response.ok) throw new Error('Erro na requisição');
                
                const text = await response.text();
                try {
                    const games = JSON.parse(text);
                    showResults(games);
                } catch (parseError) {
                    console.error('Erro ao parsear JSON:', text);
                    throw parseError;
                }
            } catch (error) {
                console.error('Erro na pesquisa:', error);
                searchResults.innerHTML = `
                    <div class="error-message">
                        Erro ao buscar jogos. Tente novamente.
                    </div>
                `;
                searchResults.style.display = 'block';
            } finally {
                loadingIndicator.style.display = 'none';
            }
        }, 300);
    });

    // Fechar modal ao clicar fora
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}); 