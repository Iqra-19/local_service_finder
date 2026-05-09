<?php
require 'config/db.php';
try {
    $pdo->exec("ALTER TABLE services ADD COLUMN location VARCHAR(100) DEFAULT NULL AFTER category");
    $pdo->exec("ALTER TABLE services ADD COLUMN image VARCHAR(255) DEFAULT 'default_service.jpg' AFTER location");
    $pdo->exec("ALTER TABLE users ADD COLUMN location VARCHAR(100) DEFAULT NULL AFTER role");
    echo "Success";
} catch (Exception $e) {
    echo $e->getMessage();
}
