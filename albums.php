<?php
session_start();
require_once "./src/abstract.php";

$admin = isset($_SESSION["user_id"]);
$albums = [];
$error = null;

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $stmt = $pdo->prepare("SELECT a.*, COUNT(m.id) as song_count FROM albums a LEFT JOIN musics m ON a.id = m.album_id GROUP BY a.id ORDER BY a.name");
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Unable to load albums. Please try again later.";
}

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums - Music Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-3xl font-bold text-gray-900">💿 Albums</h1>
                    <a href="index.php" class="text-blue-600 hover:text-blue-800">← Back to Music</a>
                </div>
                <?php if ($admin): ?>
                    <a href="admin.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        🔧 Admin Panel
                    </a>
                <?php else: ?>
                    <a href="src/auth/login.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        🔑 Login
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-8"><?= h($error) ?></div>
        <?php elseif (empty($albums)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">💿</div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No albums found</h3>
                    <p class="text-gray-500">Albums will appear here when songs are added to them.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Albums Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($albums as $album): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                                    💿
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= h($album['name']) ?></h3>
                                    <p class="text-gray-600"><?= $album['song_count'] ?> songs</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">
                                    Created <?= h(date('M j, Y', strtotime($album['created_at']))) ?>
                                </p>
                            </div>
                            
                            <button onclick="toggleAlbumSongs(<?= $album['id'] ?>)" 
                                   class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200">
                                🎵 View Songs
                            </button>
                            
                            <div id="songs-<?= $album['id'] ?>" class="hidden mt-4 border-t pt-4">
                                <div class="loading text-center text-gray-500">Loading songs...</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const openAlbums = new Set();
        
        function toggleAlbumSongs(albumId) {
            const container = document.getElementById(`songs-${albumId}`);
            
            if (openAlbums.has(albumId)) {
                container.classList.add('hidden');
                openAlbums.delete(albumId);
            } else {
                container.classList.remove('hidden');
                openAlbums.add(albumId);
                loadAlbumSongs(albumId);
            }
        }
        
        function loadAlbumSongs(albumId) {
            const container = document.getElementById(`songs-${albumId}`);
            
            fetch(`src/album/songs.php?id=${albumId}`)
            .then(response => response.json())
            .then(songs => {
                if (songs.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center">No songs in this album</p>';
                    return;
                }
                
                let html = '<div class="space-y-3">';
                songs.forEach(song => {
                    html += `
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900">${escapeHtml(song.name)}</h4>
                                    <p class="text-sm text-gray-600">${escapeHtml(song.artist)}</p>
                                </div>
                            </div>
                            <audio controls class="w-full" preload="none">
                                <source src="${escapeHtml(song.address)}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    `;
                });
                html += '</div>';
                
                container.innerHTML = html;
            })
            .catch(error => {
                container.innerHTML = '<p class="text-red-500 text-center">Error loading songs</p>';
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>