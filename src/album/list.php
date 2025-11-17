<?php
session_start();
require_once "../abstract.php";

header('Content-Type: application/json');

if (!$_SESSION["user_id"]) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $stmt = $pdo->prepare("SELECT id, name FROM albums ORDER BY name");
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($albums);
} catch (Exception $e) {
    echo json_encode([]);
}