<?php
session_start();
require_once "Config.php";

// รับค่าจาก cookie ถ้ามี
$savedUsername = $_COOKIE['remember_username'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // ถ้าเลือกจำชื่อผู้ใช้
        if (isset($_POST['remember'])) {
            setcookie('remember_username', $usernameOrEmail, time() + (30 * 24 * 60 * 60)); // 30 วัน
        } else {
            setcookie('remember_username', '', time() - 3600); // ลบ cookie
        }

        header("Location: " . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert -->
    <style>
        body {
            background: linear-gradient(135deg, #00bcd4, #8e44ad);
            font-family: 'Kanit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 28px;
            color: #6f42c1;
        }

        .btn-primary {
            background-color: #6f42c1;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5a329e;
        }

        .form-label {
            font-weight: bold;
        }

        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2 class="form-title">เข้าสู่ระบบ</h2>

    <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'สมัครสมาชิกสำเร็จ!',
        text: 'กรุณาเข้าสู่ระบบเพื่อใช้งาน',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'ตกลง'
    });
</script>
<?php endif; ?>


    <form method="post" novalidate>
        <div class="mb-3">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email"
                class="form-control"
                value="<?= htmlspecialchars($savedUsername) ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="togglePassword">
                <label class="form-check-label" for="togglePassword">แสดงรหัสผ่าน</label>
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                <?= $savedUsername ? 'checked' : '' ?>>
            <label class="form-check-label" for="remember">จำชื่อผู้ใช้</label>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            <a href="register.php" class="btn btn-outline-secondary">สมัครสมาชิก</a>
        </div>
    </form>
</div>

<?php if (!empty($error)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: '<?= htmlspecialchars($error) ?>',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#6f42c1'
    });
</script>
<?php endif; ?>

<script>
    // Toggle password visibility
    document.getElementById("togglePassword").addEventListener("change", function () {
        const passwordInput = document.getElementById("password");
        passwordInput.type = this.checked ? "text" : "password";
    });
</script>

</body>
</html>
