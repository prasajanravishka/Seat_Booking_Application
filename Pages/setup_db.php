<?php
// ─────────────────────────────────────────
//  SeatBook — Database Setup Script
//  Run this ONCE to create your tables:
//  php setup_db.php
// ─────────────────────────────────────────

require_once 'config/db.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `u_id`         INT(10)      NOT NULL AUTO_INCREMENT,
    `first_name`   VARCHAR(100) NOT NULL,
    `last_name`    VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(20)  NOT NULL,
    `email`        VARCHAR(100) NOT NULL UNIQUE,
    `address`      VARCHAR(200) NOT NULL,
    `province`     VARCHAR(50)  NOT NULL,
    `country`      VARCHAR(50)  NOT NULL,
    `password`     VARCHAR(250) NOT NULL,
    `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "✅ Table `users` created successfully.\n";
} else {
    echo "❌ Error: " . $conn->error . "\n";
}

$conn->close();
echo "✅ Database setup complete. You can delete this file now.\n";
?>
