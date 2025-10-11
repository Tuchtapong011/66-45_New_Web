<?php
require '../session_timeout.php';
require '../config.php';
require 'authadmin.php';


// ตรวจสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}

// ตรวจ param id
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ดึงข้อมูลสมาชิกเฉพาะ role=member
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h3>ไม่พบสมาชิก</h3>";
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบค่าที่จำเป็น
    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    // ตรวจ username/email ซ้ำ (ยกเว้น user_id ของตัวเอง)
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "duplicate";
        }
    }

    // ตรวจรหัสผ่าน (ถ้ามีการกรอก)
    if (!$error && $password !== '') {
        if (strlen($password) < 6) {
            $error = "password_short";
        } elseif ($password !== $confirm_password) {
            $error = "password_mismatch";
        }
    }

    // หากไม่มี error -> ทำการ UPDATE
    if (!$error) {
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$username, $full_name, $email, $hashed, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$username, $full_name, $email, $user_id]);
        }

        // Redirect หลังสำเร็จ
        header("Location: user.php?updated=1");
        exit;
    }

    // กรณีมี error ให้เก็บค่ากลับใน $user เพื่อเติมฟอร์ม
    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle, rgba(140, 35, 232, 1) 0%, rgba(252, 70, 107, 1) 100%);
            min-height: 100vh;
            color: white;
        }
    </style>
</head>
<body class="container mt-4">

    <h2>แก้ไขข้อมูลสมาชิก</h2>
    <a href="user.php" class="btn btn-secondary mb-3">← กลับหน้ารายชื่อสมาชิก</a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">ชื่อผู้ใช้</label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">ชื่อ - นามสกุล</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">อีเมล</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">รหัสผ่านใหม่ <small class="text-muted">(ถ้าไม่เปลี่ยน ให้เว้นว่าง)</small></label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="confirm_password" class="form-control">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
        </div>
    </form>

<?php if (isset($error)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($error === "duplicate"): ?>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ',
                confirmButtonText: 'ตกลง'
            });
        <?php elseif ($error === "password_short"): ?>
            Swal.fire({
                icon: 'warning',
                title: 'รหัสผ่านสั้นเกินไป',
                text: 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
                confirmButtonText: 'เข้าใจแล้ว'
            });
        <?php elseif ($error === "password_mismatch"): ?>
            Swal.fire({
                icon: 'error',
                title: 'รหัสผ่านไม่ตรงกัน',
                text: 'กรุณากรอกรหัสผ่านใหม่ให้ตรงกันทั้งสองช่อง',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>
    </script>
<?php endif; ?>



</body>
</html>

