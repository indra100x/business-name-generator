<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_database";

try {
    $db = new PDO("mysql:host=$servername", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $db->exec($sql);

    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(50) NOT NULL,
        KEY1 VARCHAR(15) NOT NULL,
        used ENUM('0','1') DEFAULT '0',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );";
    
    $db->exec($sql);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
