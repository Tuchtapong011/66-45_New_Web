<?php
session_start();
require 'config.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้ว
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจสอบชื่อและอีเมล
    if (empty($full_name) || empty($email)) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุล และอีเมล";
    }

    // ตรวจสอบอีเมลซ้ำ (ยกเว้นอีเมลเดิมของตัวเอง)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "อีเมลนี้ถูกใช้งานแล้ว";
    }

    // ตรวจสอบการเปลี่ยนรหัสผ่าน (ถ้ามี)
    $new_hashed = null;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "รหัสผ่านเดิมไม่ถูกต้อง";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่านใหม่และยืนยันไม่ตรงกัน";
        } else {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // หากไม่มีข้อผิดพลาด -> ทำการอัปเดต
    if (empty($errors)) {
        if ($new_hashed) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $new_hashed, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $user_id]);
        }

        // อัปเดตข้อมูล session และตัวแปร $user
        $_SESSION['username'] = $user['username']; // สมมุติว่า session นี้มีอยู่
        $user['full_name'] = $full_name;
        $user['email'] = $email;

        $success = "✅ บันทึกข้อมูลเรียบร้อยแล้ว";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์สมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php require_once 'Profile.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
        
            <div class="card p-4">
                <h3 class="mb-4 text-center text-primary">👤 โปรไฟล์ของคุณ</h3>
                <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">← กลับหน้าหลัก</a>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="post" class="row g-3">

                    <!-- Full Name -->
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">ชื่อ - นามสกุล</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <div class="col-12">
                        <hr>
                        <h5 class="text-muted">🔒 เปลี่ยนรหัสผ่าน (ไม่จำเป็น)</h5>
                    </div>

                    <!-- Current Password -->
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">รหัสผ่านเดิม</label>
                        <input type="password" name="current_password" id="current_password" class="form-control">
                    </div>

                    <!-- New Password -->
                    <div class="col-md-6">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่ (ไม่ต่ำกว่า 6 ตัวอักษร)</label>
                        <input type="password" name="new_password" id="new_password" class="form-control">
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100 mt-3">💾 บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>