<?php
session_start();
require '../session_timeout.php';
require_once '../Config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    http_response_code(403);
    exit("Forbidden");
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("Missing user id");
}

$userId = intval($_GET['id']);

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo "success";
} catch (PDOException $e) {
    http_response_code(500);
    echo "error: " . $e->getMessage();
}
?>