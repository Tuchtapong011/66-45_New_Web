<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "online_shop";

$dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4"; // แนะนำเพิ่ม charset ด้วย

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connected!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
