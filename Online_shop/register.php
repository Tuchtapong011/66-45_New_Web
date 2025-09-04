<?php
require_once 'Config.php';

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $Confirm_Password = $_POST['Confirm_Password'];

    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($Confirm_Password)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif ($password !== $Confirm_Password) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว";
        }
    }

    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username, full_name, email, password, role) VALUES (?,?,?,?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        header("Location: login.php?register=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #00bcd4, #8e44ad);
            font-family: 'Kanit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .register-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 26px;
            font-weight: bold;
            color: #6f42c1;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #6f42c1;
            border: none;
        }

        .btn-primary:hover {
            background-color: #59329b;
        }

        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2 class="form-title">สมัครสมาชิก</h2>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="text" name="username" id="username" class="form-control"
                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" name="fullname" id="fullname" class="form-control"
                value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" name="email" id="email" class="form-control"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="Confirm_Password" class="form-label">ยืนยันรหัสผ่าน</label>
            <input type="password" name="Confirm_Password" id="Confirm_Password" class="form-control" required>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="togglePassword">
            <label class="form-check-label" for="togglePassword">แสดงรหัสผ่าน</label>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
            <a href="login.php" class="btn btn-outline-secondary">เข้าสู่ระบบ</a>
        </div>
    </form>
</div>

<?php if (!empty($error)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        html: `<ul style="text-align:left;">
            <?php foreach ($error as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>`,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#6f42c1'
    });
</script>
<?php endif; ?>

<script>
    document.getElementById("togglePassword").addEventListener("change", function () {
        const pw1 = document.getElementById("password");
        const pw2 = document.getElementById("Confirm_Password");
        const show = this.checked ? "text" : "password";
        pw1.type = show;
        pw2.type = show;
    });
</script>

</body>
</html>
