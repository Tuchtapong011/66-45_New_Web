<?php
require '../session_timeout.php';
require '../Config.php';
require 'authadmin.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
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
    $_SESSION['error'] = "ไม่พบข้อมูลสินค้า";
    header("Location: products.php");
    exit;
}

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ถ้ามีการส่งแบบ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    $oldImage = $_POST['old_image'] ?? null;
    $removeImage = isset($_POST['remove_image']);
    $newImageName = $oldImage;

    // ลบรูปเดิมถ้ามีการติ๊ก
    if ($removeImage) {
        $newImageName = null;
    }

    // ถ้ามีอัปโหลดใหม่
    if (!empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $allowed = ['image/jpeg', 'image/png'];

        if (in_array($file['type'], $allowed) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newImageName = 'product_' . time() . '.' . $ext;

            $uploadDir = realpath(__DIR__ . '/../product_images');
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $newImageName = $oldImage; // ถ้าอัปโหลดล้มเหลว
            }
        }
    }

    // อัปเดตฐานข้อมูล
    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE product_id = ?");
    $stmt->execute([$name, $description, $price, $stock, $category_id, $newImageName, $product_id]);

    // ลบรูปเก่า ถ้ามีการเปลี่ยนรูป
    if (!empty($oldImage) && $oldImage !== $newImageName) {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $oldImage);
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }

    $_SESSION['success'] = "แก้ไขสินค้าสำเร็จ";
    header("Location: products.php");
    exit;
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
            background: radial-gradient(circle, rgba(0, 32, 53, 1) 0%, rgba(0, 69, 98, 1) 100%);
            min-height: 100vh;
            color: white;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
        }

        .form-control, .form-select, .btn {
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #1a73e8;
            border-color: #1a73e8;
        }

        .btn-primary:hover {
            background-color: #155a8a;
            border-color: #155a8a;
        }

        .alert {
            border-radius: 10px;
        }

        .form-label {
            font-weight: bold;
        }
    </style>
</head>

<body class="container mt-4">

<h2 class="text-center">แก้ไขสินค้า</h2>
<a href="products.php" class="btn btn-secondary mb-3">← กลับหน้าจัดการสินค้า</a>

<form method="post" enctype="multipart/form-data" class="row g-3">
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
        <label for="stock" class="form-label">จำนวนในคลัง</label>
        <input type="number" name="stock" id="stock" class="form-control" required value="<?= htmlspecialchars($product['stock']) ?>">
    </div>
    <div class="col-12">
        <label for="description" class="form-label">รายละเอียดสินค้า</label>
        <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label d-block">รูปปัจจุบัน</label>
        <?php if (!empty($product['image'])): ?>
            <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" width="120" height="120" class="rounded mb-2">
        <?php else: ?>
            <span class="text-muted d-block mb-2">ไม่มีรูป</span>
        <?php endif; ?>
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">อัปโหลดรูปใหม่ (jpg, png)</label>
        <input type="file" name="product_image" class="form-control">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
            <label class="form-check-label" for="remove_image">ลบรูปเดิม</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary w-100">บันทึกการแก้ไข</button>
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
