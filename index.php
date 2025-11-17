<?php
session_start();
require_once "./src/abstract.php";

$admin = isset($_SESSION["user_id"]);
$page = (int)($_GET['page'] ?? 1);
$limit = 6;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$musics = [];
$totalPages = 1;
$error = null;

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $countSql = "SELECT COUNT(*) FROM musics m LEFT JOIN albums a ON m.album_id = a.id";
    $params = [];
    
    if ($search) {
        $countSql .= " WHERE m.name LIKE ? OR m.artist LIKE ? OR a.name LIKE ?";
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
    
    $sql = "SELECT m.*, a.name as album_name FROM musics m LEFT JOIN albums a ON m.album_id = a.id";
    
    if ($search) {
        $sql .= " WHERE m.name LIKE ? OR m.artist LIKE ? OR a.name LIKE ?";
    }
    
    $sql .= " ORDER BY m.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $musics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Unable to load music. Please try again later.";
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
    <title>Music Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-3xl font-bold text-gray-900">🎵 Music Library</h1>
                    <a href="albums.php" class="text-blue-600 hover:text-blue-800">📀 View Albums</a>
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

        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4">
                <div class="flex gap-4">
                    <input type="text" id="searchInput" placeholder="Search music, artist, or album..." 
                           value="<?= h($search) ?>" class="flex-1 border rounded-lg px-4 py-2">
                    <button onclick="searchMusic()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                        🔍 Search
                    </button>
                    <?php if ($search): ?>
                        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-8"><?= h($error) ?></div>
        <?php elseif (empty($musics)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🎵</div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No music found</h3>
                    <p class="text-gray-500">Try adjusting your search or check back later.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($musics as $music): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                                    🎵
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= h($music['name']) ?></h3>
                                    <p class="text-gray-600"><?= h($music['artist']) ?></p>
                                </div>
                            </div>
                            
                            <?php if ($music['album_name']): ?>
                                <div class="mb-4">
                                    <span class="inline-block bg-purple-100 text-purple-800 text-sm px-3 py-1 rounded-full">
                                        💿 <?= h($music['album_name']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-2">
                                    Added <?= h(date('M j, Y', strtotime($music['created_at']))) ?>
                                </p>
                                <audio controls class="w-full" preload="none">
                                    <source src="<?= h($music['address']) ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            
                            <?php if ($admin): ?>
                                <div class="border-t pt-4">
                                    <a href="admin.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        🔧 Manage in Admin Panel
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-700">
                                Showing page <?= $page ?> of <?= $totalPages ?>
                            </p>
                            <div class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-2 rounded-lg transition duration-200">
                                        ← Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                                       class="<?= $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-300 hover:bg-gray-400 text-gray-800' ?> px-3 py-2 rounded-lg transition duration-200">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-2 rounded-lg transition duration-200">
                                        Next →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function searchMusic() {
            const query = document.getElementById('searchInput').value;
            window.location.href = query ? `?search=${encodeURIComponent(query)}` : 'index.php';
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchMusic();
            }
        });
    </script>
</body>
</html>