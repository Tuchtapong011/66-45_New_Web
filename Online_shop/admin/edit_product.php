<?php
require '../Config.php';
require 'authadmin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// ดึงข้อมูลสินค้าที่จะแก้ไข
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = "ไม่พบสินค้าที่ต้องการแก้ไข";
    header("Location: products.php");
    exit;
}

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ถ้ามีการ submit แบบ POST เพื่อแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if ($name !== '' && $price > 0) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, stock=?, category_id=? WHERE product_id=?");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $product_id]);

        $_SESSION['success'] = "แก้ไขสินค้าสำเร็จ";
        header("Location: products.php");
        exit;
    } else {
        $error = "กรุณากรอกข้อมูลให้ถูกต้อง";
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขสินค้า</title>
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

<h2>แก้ไขสินค้า</h2>
<a href="products.php" class="btn btn-secondary mb-3">← กลับหน้าจัดการสินค้า</a>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
    <div class="col-md-6">
        <label for="product_name" class="form-label">ชื่อสินค้า</label>
        <input type="text" name="product_name" id="product_name" class="form-control" required value="<?= htmlspecialchars($product['product_name']) ?>">
    </div>
    <div class="col-md-6">
        <label for="category_id" class="form-label">หมวดหมู่</label>
        <select name="category_id" id="category_id" class="form-select" required>
            <option value="">เลือกหมวดหมู่</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="price" class="form-label">ราคา</label>
        <input type="number" step="0.01" name="price" id="price" class="form-control" required value="<?= htmlspecialchars($product['price']) ?>">
    </div>
    <div class="col-md-6">
        <label for="stock" class="form-label">จำนวน</label>
        <input type="number" name="stock" id="stock" class="form-control" required value="<?= htmlspecialchars($product['stock']) ?>">
    </div>
    <div class="col-12">
        <label for="description" class="form-label">รายละเอียด</label>
        <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    <div class="col-12">
        <button type="submit" name="update_product" class="btn btn-primary">บันทึกการแก้ไข</button>
        <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
    </div>
</form>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'เกิดข้อผิดพลาด',
    text: '<?= addslashes($_SESSION['error']) ?>',
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'สำเร็จ',
    text: '<?= addslashes($_SESSION['success']) ?>',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
});
</script>
<?php unset($_SESSION['success']); endif; ?>

</body>
</html>
