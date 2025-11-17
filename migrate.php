<?php

require_once './database/database.php';

$db = new Database();

echo "Running migrations...\n";
$db->migrate();

echo "Running seeds...\n";
$db->seed();

echo "Done!\n";