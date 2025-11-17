<?php
session_start();
require_once "../abstract.php";

header('Content-Type: application/json');

if (!$_SESSION["user_id"]) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$musicName = $_POST['musicName'] ?? '';
$artistName = $_POST['artistName'] ?? '';
$albumId = $_POST['albumId'] ?? null;

if ($albumId === '') $albumId = null;

if (empty($musicName) || empty($artistName) || !isset($_FILES['musicFile'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$file = $_FILES['musicFile'];
$uploadDir = '../../musics/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only audio files allowed.']);
    exit;
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    try {
        $db = new Database();
        $pdo = $db->connect();
        
        $stmt = $pdo->prepare("INSERT INTO musics (name, artist, album_id, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$musicName, $artistName, $albumId, 'musics/' . $filename]);
        
        echo json_encode(['success' => true, 'message' => 'Music uploaded successfully']);
    } catch (Exception $e) {
        unlink($filepath);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}