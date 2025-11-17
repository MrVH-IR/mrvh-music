<?php
require_once "../abstract.php";

header('Content-Type: application/json');

$albumId = (int)($_GET['id'] ?? 0);

if (!$albumId) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $stmt = $pdo->prepare("SELECT id, name, artist, address, created_at FROM musics WHERE album_id = ? ORDER BY created_at DESC");
    $stmt->execute([$albumId]);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($songs);
} catch (Exception $e) {
    echo json_encode([]);
}