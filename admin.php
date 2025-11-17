<?php 

require_once "src/abstract.php";

if (!$_SESSION["user_id"]) {
    header("Location: src/auth/login.php");
}

$db = new Database();
$pdo = $db->connect();

try {
    $stmt = $pdo->prepare("SELECT m.*, a.name as album_name FROM musics m LEFT JOIN albums a ON m.album_id = a.id");
    $stmt->execute();
    $musics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $musics = [];
    $error = "Error loading music data";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">🎵 Music Library</h1>
                <a href="src/auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    Logout
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4">
                <h2 class="text-xl font-bold text-gray-900 mb-4">📤 Upload Music</h2>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <input type="text" id="musicName" placeholder="Song Name" required class="border rounded-lg px-3 py-2">
                        <input type="text" id="artistName" placeholder="Artist" required class="border rounded-lg px-3 py-2">
                        <select id="albumSelect" class="border rounded-lg px-3 py-2">
                            <option value="">No Album</option>
                        </select>
                        <input type="file" id="musicFile" accept="audio/*" required class="border rounded-lg px-3 py-2">
                    </div>
                    <div class="flex gap-4 mb-4">
                        <input type="text" id="newAlbum" placeholder="Or create new album" class="border rounded-lg px-3 py-2 flex-1">
                        <button type="button" id="createAlbum" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            📀 Create Album
                        </button>
                    </div>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200">
                        🎵 Upload Music
                    </button>
                </form>
                <div id="uploadStatus" class="mt-4 hidden"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($musics)): ?>
                <?php foreach ($musics as $music): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    🎵
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($music["name"]) ?></h3>
                                    <p class="text-gray-600"><?= htmlspecialchars($music["artist"]) ?></p>
                                </div>
                            </div>
                            
                            <?php if ($music["album_name"]): ?>
                                <div class="mb-4">
                                    <span class="inline-block bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full">
                                        📀 <?= htmlspecialchars($music["album_name"]) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <audio controls class="w-full">
                                    <source src="<?= htmlspecialchars($music["address"]) ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="deleteMusic(<?= $music['id'] ?>)" 
                                   class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition duration-200">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🎵</div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No music found</h3>
                    <p class="text-gray-500">Start by adding some music to your library.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        loadAlbums();
        
        document.getElementById('createAlbum').addEventListener('click', function() {
            const albumName = document.getElementById('newAlbum').value;
            if (!albumName) {
                showStatus('Please enter album name', 'error');
                return;
            }
            
            fetch('src/album/create.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({name: albumName})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newAlbum').value = '';
                    loadAlbums();
                    showStatus('Album created successfully!', 'success');
                } else {
                    showStatus(data.message, 'error');
                }
            });
        });
        
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const musicFile = document.getElementById('musicFile').files[0];
            const musicName = document.getElementById('musicName').value;
            const artistName = document.getElementById('artistName').value;
            const albumId = document.getElementById('albumSelect').value;
            
            if (!musicFile || !musicName || !artistName) {
                showStatus('Please fill all fields', 'error');
                return;
            }
            
            formData.append('musicFile', musicFile);
            formData.append('musicName', musicName);
            formData.append('artistName', artistName);
            formData.append('albumId', albumId);
            
            showStatus('Uploading...', 'loading');
            
            fetch('src/music/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('Music uploaded successfully!', 'success');
                    document.getElementById('uploadForm').reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showStatus(data.message || 'Upload failed', 'error');
                }
            })
            .catch(error => {
                showStatus('Upload failed: ' + error.message, 'error');
            });
        });
        
        function showStatus(message, type) {
            const status = document.getElementById('uploadStatus');
            status.className = 'mt-4 p-3 rounded-lg ' + 
                (type === 'success' ? 'bg-green-100 text-green-800' : 
                 type === 'error' ? 'bg-red-100 text-red-800' : 
                 'bg-blue-100 text-blue-800');
            status.textContent = message;
            status.classList.remove('hidden');
        }
        
        function loadAlbums() {
            fetch('src/album/list.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('albumSelect');
                select.innerHTML = '<option value="">No Album</option>';
                data.forEach(album => {
                    select.innerHTML += `<option value="${album.id}">${album.name}</option>`;
                });
            });
        }
        
        function deleteMusic(id) {
            if (!confirm('Are you sure you want to delete this music?')) return;
            
            fetch('src/music/delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Delete failed');
                }
            });
        }
    </script>
</body>
</html>