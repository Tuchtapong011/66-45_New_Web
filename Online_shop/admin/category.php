<?php
require '../session_timeout.php';
require '../Config.php'; // เชื่อมต่อฐานข้อมูล PDO
require 'authadmin.php'; // ตรวจสอบ admin

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        if ($stmt->execute([$category_name])) {
            $_SESSION['success'] = "เพิ่มหมวดหมู่เรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มหมวดหมู่";
        }
        header("Location: category.php");
        exit;
    }
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = (int)$_POST['category_id'];
    $new_name = trim($_POST['new_name']);
    if ($new_name !== '') {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        if ($stmt->execute([$new_name, $category_id])) {
            $_SESSION['success'] = "แก้ไขชื่อหมวดหมู่เรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการแก้ไขชื่อหมวดหมู่";
        }
        header("Location: category.php");
        exit;
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];

    // ตรวจสอบว่าหมวดหมู่นี้ยังถูกใช้อยู่ในตารางสินค้า 
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าที่ใช้งานอยู่ในหมวดหมู่นี้";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }

    header("Location: category.php");
    exit;
}

// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>⚔️ จัดการหมวดหมู่สินค้า - ห้องหัวหน้ากิลด์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    background: radial-gradient(circle at top left, #2c003e, #5c2a9d, #ff6f61);
    min-height: 100vh;
    color: #fff;
    font-family: 'Kanit', sans-serif;
}

.container {
    padding: 40px 20px;
}

h2 {
    text-align: center;
    font-weight: bold;
    color: #ffd700;
    text-shadow: 0 0 10px #fffa9e;
    margin-bottom: 30px;
}

.btn-back {
    margin-bottom: 20px;
    font-weight: bold;
}

form.row.g-3 {
    background: rgba(255, 215, 0, 0.1);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
}

.table {
    border-radius: 15px;
    background: linear-gradient(135deg, rgba(80, 0, 150, 0.6), rgba(0, 120, 200, 0.6));
    box-shadow: 0 0 20px rgba(255,215,0,0.4);
    border: none;
}

.table th, .table td {
    vertical-align: middle;
    color: #73ff00ff;
}

.table thead th {
    background: rgba(255, 215, 0, 0.25);
    font-weight: bold;
    text-align: center;
}

.table tbody tr {
    transition: all 0.3s;
}

.table tbody tr:hover {
    background: rgba(255, 215, 0, 0.15);
}

.table tbody td form {
    display: flex;
    gap: 5px;
}

.btn {
    font-weight: bold;
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    border-radius: 8px;
}

.btn-primary {
    background: linear-gradient(135deg,#ffd700,#f6c90e);
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg,#ffb347,#ffcc33);
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg,#ff3c3c,#ff7f50);
    border: none;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
}

input.form-control {
    border-radius: 10px;
}

</style>
</head>
<body>

<div class="container">
    <h2>⚔️ จัดการหมวดหมู่สินค้า - ห้องหัวหน้ากิลด์</h2>

    <a href="index.php" class="btn btn-secondary btn-back">← กลับหน้าผู้ดูแล</a>

    <form method="post" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add_category" class="btn btn-primary w-100">เพิ่มหมวดหมู่</button>
        </div>
    </form>

    <h5 class="mb-3">📜 รายการหมวดหมู่</h5>
    <table class="table table-bordered">
    <thead>
    <tr>
        <th>ชื่อหมวดหมู่</th>
        <th>แก้ไขชื่อ</th>
        <th>จัดการ</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <td><?= htmlspecialchars($cat['category_name']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                <input type="text" name="new_name" class="form-control" placeholder="ชื่อใหม่" required>
                <button type="submit" name="update_category" class="btn btn-warning btn-sm mt-1">แก้ไข</button>
            </form>
        </td>
        <td class="text-center">
            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $cat['category_id'] ?>)">ลบ</button>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
</div>

<script>
function confirmDelete(categoryId) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'category.php?delete=' + categoryId;
        }
    });
}

<?php if (isset($_SESSION['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'สำเร็จ',
    text: '<?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>',
    confirmButtonText: 'ตกลง'
});
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'ผิดพลาด',
    text: '<?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>',
    confirmButtonText: 'ตกลง'
});
<?php unset($_SESSION['error']); endif; ?>
</script>

</body>
</html>
