<?php
require '../config.php';
require 'authadmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u_id'])) {
    $user_id = (int) $_POST['u_id'];

    // ลบเฉพาะผู้ใช้ที่เป็น member
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
    $stmt->execute([$user_id]);

    // Redirect พร้อมส่งพารามิเตอร์ ?deleted=1
    header("Location: user.php?deleted=1");
    exit;
}
?>
