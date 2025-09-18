<?php
require '../Config.php'; // เชื่อมต่อฐานข้อมูล PDO
require 'authadmin.php'; // ตรวจสอบ admin

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
        // หมวดหมู่นี้ยังมีสินค้าที่ใช้อยู่ ห้ามลบ
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าที่ใช้งานอยู่ในหมวดหมู่นี้";
    } else {
        // ไม่มีสินค้าในหมวดนี้ ลบได้
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
<title>จัดการหมวดหมู่</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
        body {
            background: radial-gradient(circle, rgba(140, 35, 232, 1) 0%, rgba(252, 70, 107, 1) 100%);
            min-height: 100vh;
            color: white;
        }
    </style>

</head>
<body class="container mt-4">

<h2>จัดการหมวดหมู่สินค้า</h2>

<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" required>
    </div>
    <div class="col-md-2">
        <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
    </div>
</form>

<h5>รายการหมวดหมู่</h5>
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
        <form method="post" class="d-flex">
            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
            <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม่" required>
            <button type="submit" name="update_category" class="btn btn-sm btn-warning">แก้ไข</button>
        </form>
    </td>
    <td>
        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $cat['category_id'] ?>)">ลบ</button>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

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
