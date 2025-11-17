<?php
session_start();
require_once "../abstract.php";

header('Content-Type: application/json');

if (!$_SESSION["user_id"]) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid music ID']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $stmt = $pdo->prepare("SELECT address FROM musics WHERE id = ?");
    $stmt->execute([$id]);
    $music = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($music) {
        $stmt = $pdo->prepare("DELETE FROM musics WHERE id = ?");
        $stmt->execute([$id]);
        
        $filepath = '../../' . $music['address'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Music not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Delete failed']);
}