<?php
session_start();
require_once 'Config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<h3 class='text-center text-danger mt-5'>ไม่พบสินค้าที่คุณต้องการ</h3>";
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: radial-gradient(circle at top left, #ffdee9, #b5fffc);
            min-height: 100vh;
        }

        .card {
            border-radius: 15px;
            padding: 30px;
            background-color: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .back-link {
            color: #6f42c1;
            font-weight: bold;
        }

        .btn-success {
            font-weight: bold;
        }
    </style>
</head>
<body class="container py-4">

    <a href="index.php" class="back-link mb-3 d-inline-block">
        <i class="bi bi-arrow-left-circle"></i> กลับหน้ารายการสินค้า
    </a>

    <div class="card mx-auto" style="max-width: 700px;">
        <h2 class="mb-3"><?= htmlspecialchars($product['product_name']) ?></h2>
        <h6 class="text-muted mb-3">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>

        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p><strong>ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>
        <p><strong>คงเหลือ:</strong> <?= $product['stock'] ?> ชิ้น</p>

        <?php if ($isLoggedIn): ?>
            <form action="cart.php" method="post" class="mt-4">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                <div class="mb-3 d-flex align-items-center">
    <label for="quantity" class="form-label me-2 mb-0">จำนวน:</label>
    <input type="number" name="quantity" id="quantity"
        class="form-control form-control-sm"
        style="width: 100px;"
        value="1" min="1" max="<?= $product['stock'] ?>" required>
</div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-cart-plus"></i> เพิ่มในตะกร้า
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
