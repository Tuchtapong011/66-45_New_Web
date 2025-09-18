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

// เตรียมรูป
$img = !empty($product['image']) 
    ? 'product_images/' . rawurlencode($product['image']) 
    : 'product_images/no-image.png';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า | ISEKAI SHOP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: radial-gradient(circle at top left, #ffdee9, #b5fffc);
            min-height: 100vh;
        }

        .product-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            max-width: 500px; /* ไม่เกิน 500px */
            height: auto;
            max-height: 350px; /* จำกัดความสูง */
            object-fit: cover;
            border-radius: 10px;
            background-color: #f8f8f8;
            display: block;
            margin: 0 auto;
        }


        .back-link {
            color: #6f42c1;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .product-title {
            color: #6f42c1;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .product-meta {
            font-size: 0.95rem;
            color: #777;
            margin-bottom: 1rem;
        }

        .price {
            font-size: 1.3rem;
            color: #28a745;
            font-weight: bold;
        }

        .btn-success {
            font-weight: bold;
        }

        .alert {
            font-size: 0.95rem;
        }
    </style>
</head>

<body class="container py-4">

    <!-- Back to Shop -->
    <a href="index.php" class="back-link mb-3 d-inline-block">
        <i class="bi bi-arrow-left-circle"></i> กลับหน้ารายการสินค้า
    </a>

    <!-- Product Detail Card -->
    <div class="product-card mx-auto" style="max-width: 800px;">

        <!-- Product Image -->
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image mb-4">

        <!-- Product Name -->
        <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>

        <!-- Category -->
        <div class="product-meta">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></div>

        <!-- Description -->
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <!-- Price & Stock -->
        <p class="price">ราคา: <?= number_format((float)$product['price'], 2) ?> บาท</p>
        <p><strong>คงเหลือ:</strong> <?= (int)$product['stock'] ?> ชิ้น</p>

        <!-- Add to Cart or Login Prompt -->
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
