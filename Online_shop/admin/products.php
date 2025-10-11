<?php
require '../session_timeout.php';
require '../Config.php';
require 'authadmin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}

// ลบสินค้า + ลบรูป
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn();

    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบสินค้า";
        header("Location: products.php");
        exit;
    }

    if ($imageName) {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = realpath($baseDir . '/' . $imageName);

        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }

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
    $imageName = null;

    if ($name !== '' && $price > 0) {
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];
            
            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], __DIR__ . '/../product_images/' . $imageName);
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);

        $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ถูกต้อง";
        header("Location: products.php");
        exit;
    }
}

// ดึงสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>⚔️ จัดการสินค้า - ห้องหัวหน้ากิลด์</title>
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
    color: #00ff37ff;
}

.table thead th {
    background: rgba(255, 215, 0, 0.25);
    font-weight: bold;
    text-align: center;
}

.table tbody tr:hover {
    background: rgba(255, 215, 0, 0.15);
    transition: all 0.3s;
}

input.form-control, select.form-select, textarea.form-control {
    border-radius: 10px;
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

img.rounded {
    border-radius: 10px;
    object-fit: cover;
}

</style>
</head>
<body>

<div class="container">
    <h2>⚔️ จัดการสินค้า - ห้องหัวหน้ากิลด์</h2>

    <a href="index.php" class="btn btn-secondary btn-back">← กลับหน้าผู้ดูแล</a>

    <form method="post" enctype="multipart/form-data" class="row g-3 mb-4">
        <h5>เพิ่มสินค้าขใหม่</h5>
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
        <div class="col-md-4">
            <input type="file" name="product_image" class="form-control" accept="image/jpeg,image/png">
        </div>
        <div class="col-12">
            <button type="submit" name="add_product" class="btn btn-primary mt-2">เพิ่มสินค้า</button>
        </div>
    </form>

    <h5>📦 รายการสินค้า</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>รูป</th>
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
                <td>
                    <?php if ($p['image']): ?>
                    <img src="../product_images/<?= htmlspecialchars($p['image']) ?>" width="60" height="60" class="rounded">
                    <?php else: ?>
                    <span class="text-muted">ไม่มีรูป</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['product_name']) ?></td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><?= number_format($p['price'], 2) ?> บาท</td>
                <td><?= $p['stock'] ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                    <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event)">ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(event) {
    event.preventDefault();
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
