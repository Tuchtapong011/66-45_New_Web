<?php
require_once 'Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับจาก form
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $dConfirm_Password = $_POST['Confirm_Password'];

    // ตรวจสอบรหัสผ่านตรงกันหรือไม่
    if ($password !== $dConfirm_Password) {
        $errorMessage = "รหัสผ่านไม่ตรงกัน!";
    } else {
        // นำลงฐานข้อมูล
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username, full_name, email, password, role) VALUES (?,?,?,?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        $successMessage = "สมัครสมาชิกสำเร็จ!";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" />
    <style>
        body {
            background: linear-gradient(to right, #00bcd4, #8e44ad);
            font-family: 'Arial', sans-serif;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
            margin-top: 100px;
        }

        h2 {
            color: #333;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .btn-primary {
            width: 100%;
            background-color: #00bcd4;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0097a7;
        }

        .btn-link {
            text-decoration: none;
            font-size: 14px;
            color: #6c757d;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .btn-link:hover {
            color: #333;
        }

        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .input-group-text {
            background-color: #f1f1f1;
            border-radius: 8px 0 0 8px;
        }

        input.form-control {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>สมัครสมาชิก</h2>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= $errorMessage ?>
            </div>
        <?php elseif (isset($successMessage)): ?>
            <div class="alert alert-success">
                <?= $successMessage ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
            </div>
            <div class="mb-3">
                <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="ชื่อ-นามสกุล"
                    required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="อีเมล" required>
            </div>
            <div class="mb-3">
                <label for="Password" class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" id="Password" class="form-control" placeholder="รหัสผ่าน"
                    required>
            </div>
            <div class="mb-3">
                <label for="ConfirmPassword" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" name="Confirm_Password" id="ConfirmPassword" class="form-control"
                    placeholder="ยืนยันรหัสผ่าน" required>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>

</body>

</html>