<?php
require_once '../Config.php';
require_once 'authadmin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u_id'])) {
    $user_id = (int) $_POST['u_id'];

    // ป้องกันไม่ให้ลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
        header("Location: user.php?deleted=1");
        exit;
    } else {
        // ป้องกันการลบตัวเอง
        header("Location: user.php?error=cannot_delete_self");
        exit;
    }
} else {
    // การเข้าถึงที่ไม่ถูกต้อง
    header("Location: user.php");
    exit;
}
