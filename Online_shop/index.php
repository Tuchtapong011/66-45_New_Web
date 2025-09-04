<?php
session_start();
require_once 'Config.php';

$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $conn->query("SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ISEKAI SHOP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: radial-gradient(circle at top left, #ffdee9, #b5fffc);
            min-height: 100vh;
        }

        .shop-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #6f42c1;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .product-card {
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .topbar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .btn-outline-primary {
            margin-left: 10px;
        }
    </style>
</head>

<body class="container py-4">

    <!-- Topbar -->
    <div class="d-flex justify-content-between align-items-center topbar shadow-sm">
        <div class="shop-title">꧁༺ ISEKAI SHOP ༻꧂</div>
        <div>
            <?php if ($isLoggedIn): ?>
                <span class="me-3 text-dark">
                    ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)
                </span>
                <a href="profile.php" class="btn btn-outline-info btn-sm">ข้อมูลส่วนตัว</a>
                <a href="cart.php" class="btn btn-outline-warning btn-sm">ดูตะกร้า</a>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success btn-sm">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary btn-sm">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- สินค้า -->
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($product['category_name']) ?></h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <p><strong>ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <?php if ($isLoggedIn): ?>
                                <form action="cart.php" method="post" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-cart-plus"></i> เพิ่มในตะกร้า
                                    </button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                            <?php endif; ?>

                            <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-primary">
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
