<?php 

if (! $_SESSION) {
    session_start();
}

require_once __DIR__ . "/../database/database.php";

$db = new Database();
$db->provider();