<?php
session_start();
require_once "../abstract.php";

header('Content-Type: application/json');

if (!$_SESSION["user_id"]) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Album name required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $stmt = $pdo->prepare("INSERT INTO albums (name) VALUES (?)");
    $stmt->execute([$name]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}