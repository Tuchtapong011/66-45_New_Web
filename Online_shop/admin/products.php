<?php
require '../Config.php'; // เชื่อมต่อฐานข้อมูล PDO
require 'authadmin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ลบสินค้า
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);

    // ลบสินค้า
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);

    $_SESSION['success'] = "ลบสินค้าสำเร็จ";
    header("Location: products.php");
    exit;
}

// เพิ่มสินค้า
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if ($name !== '' && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id]);

        $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ถูกต้อง";
        header("Location: products.php");
        exit;
    }
}

// ดึงข้อมูลสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
<style>
        body {
            background: radial-gradient(circle, rgba(140, 35, 232, 1) 0%, rgba(252, 70, 107, 1) 100%);
            min-height: 100vh;
            color: white;
        }
    </style>
</head>
<body class="container mt-4">

<h2>จัดการสินค้า</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<!-- ฟอร์มเพิ่มสินค้าใหม่ -->
<form method="post" class="row g-3 mb-4">
    <h5>เพิ่มสินค้ใหม่</h5>
    <div class="col-md-4">
        <input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required>
    </div>
    <div class="col-md-2">
        <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา" required>
    </div>
    <div class="col-md-2">
        <input type="number" name="stock" class="form-control" placeholder="จำนวน" required>
    </div>
    <div class="col-md-2">
        <select name="category_id" class="form-select" required>
            <option value="">เลือกหมวดหมู่</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
    </div>
    <div class="col-12">
        <button type="submit" name="add_product" class="btn btn-primary">เพิ่มสินค้า</button>
    </div>
</form>

<!-- ตารางแสดงรายการสินค้า -->
<h5>รายการสินค้า</h5>
<table class="table table-bordered">
<thead>
<tr>
    <th>ชื่อสินค้า</th>
    <th>หมวดหมู่</th>
    <th>ราคา</th>
    <th>คงเหลือ</th>
    <th>จัดการ</th>
</tr>
</thead>
<tbody>
<?php foreach ($products as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['product_name']) ?></td>
    <td><?= htmlspecialchars($p['category_name']) ?></td>
    <td><?= number_format($p['price'], 2) ?> บาท</td>
    <td><?= $p['stock'] ?></td>
    <td>
        <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete(event)">ลบ</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(event) {
    event.preventDefault(); // หยุดลิงก์ปกติ
    const url = event.currentTarget.href;

    Swal.fire({
        title: 'ยืนยันการลบสินค้า?',
        text: "เมื่อลบแล้วจะไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });

    return false;
}

// แสดง SweetAlert2 แจ้งเตือน success หรือ error จาก session
<?php if (isset($_SESSION['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'สำเร็จ',
    text: '<?= addslashes($_SESSION['success']) ?>',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false
});
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'ผิดพลาด',
    text: '<?= addslashes($_SESSION['error']) ?>',
});
<?php unset($_SESSION['error']); endif; ?>
</script>

</body>
</html>
