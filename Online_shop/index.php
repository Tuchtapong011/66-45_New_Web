<?php
    session_start(); // เร็มต้น
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <title>หน้าหลัก</title>
    <style>
        a{

        }
    </style>
</head>
<body>
    <h1>Welcome to Sekai</h1>
    <p>ผู้ใช้: <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)</p>

    <div>
        <a href="logout.php" class="btn btn-success">ออกจากระบบ</a>
    </div>
</body>
</html>