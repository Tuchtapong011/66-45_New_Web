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
            background: linear-gradient(to bottom, #fdf0ff, #d4ecff);
            background-attachment: fixed;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .shop-title {
            font-size: 2.8rem;
            font-weight: bold;
            color: #8e44ad;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .topbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .product-thumb {
            width: 100%;
            aspect-ratio: 4/3; /* อัตราส่วน 4:3 เหมาะกับรูปสินค้า */
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            background-color: #f8f8f8;
        }

        .product-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #e67e22;
        }

        .rating i {
            color: #f1c40f;
        }

        .badge-top-left {
            position: absolute;
            top: .5rem;
            left: .5rem;
            z-index: 2;
            font-size: 0.75rem;
            padding: .4em .6em;
            border-radius: .5rem;
        }

        .wishlist {
            color: #ccc;
        }

        .wishlist:hover {
            color: #ff5b5b;
        }

        .btn-outline-primary {
            border-radius: 8px;
        }

        .btn-success {
            border-radius: 8px;
        }

        .btn-outline-primary:hover {
            background-color: #8e44ad;
            border-color: #8e44ad;
            color: white;
        }
    </style>
</head>

<body class="container py-4">

    <!-- Topbar -->
    <div class="d-flex justify-content-between align-items-center topbar">
        <div class="shop-title">꧁༺ ISEKAI SHOP ༻꧂</div>
        <div>
            <?php if ($isLoggedIn): ?>
                <span class="me-3 text-dark">
                    สวัสดี, <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= $_SESSION['role'] ?>)
                </span>
                <a href="profile.php" class="btn btn-outline-info btn-sm">โปรไฟล์</a>
                <a href="cart.php" class="btn btn-outline-warning btn-sm">🛒 ตะกร้า</a>
                <a href="orders.php" class="btn btn-outline-info btn-sm">ดูประวัติการสั่งซื้อ</a>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success btn-sm">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary btn-sm">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-4">
    <?php foreach ($products as $p): ?>
        <?php
        $img = !empty($p['image']) 
            ? 'product_images/' . rawurlencode($p['image']) 
            : 'product_images/no-image.png';

        $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);
        $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
        $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card product-card h-100 position-relative shadow-sm">
                <?php if ($isNew): ?>
                    <span class="badge bg-success badge-top-left">ใหม่!</span>
                <?php elseif ($isHot): ?>
                    <span class="badge bg-danger badge-top-left">HOT</span>
                <?php endif; ?>

                <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="d-block">
                    <img src="<?= htmlspecialchars($img) ?>"
                        alt="<?= htmlspecialchars($p['product_name']) ?>"
                        class="img-fluid w-100 product-thumb">
                </a>

                <div class="p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="product-meta">
                            <?= htmlspecialchars($p['category_name'] ?? 'ไม่ระบุหมวดหมู่') ?>
                        </div>
                        <button class="btn btn-link p-0 wishlist" title="เพิ่มใน Wishlist" type="button">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>

                    <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="text-decoration-none">
                        <div class="product-title">
                            <?= htmlspecialchars($p['product_name']) ?>
                        </div>
                    </a>

                    <div class="rating mb-2">
                        <?php for ($i = 0; $i < $full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                        <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                        <?php for ($i = 0; $i < 5 - $full - $half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                    </div>

                    <div class="price mb-3">
                        <?= number_format((float)$p['price'], 2) ?> บาท
                    </div>

                    <div class="mt-auto d-flex gap-2">
                        <?php if ($isLoggedIn): ?>
                        <button type="button"
                            class="btn btn-sm btn-success add-to-cart-btn"
                            data-product-id="<?= (int)$p['product_id'] ?>">
                            ใส่ตะกร้า
                        </button>
                    <?php else: ?>
                        <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                    <?php endif; ?>

                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>"
                        class="btn btn-sm btn-outline-primary ms-auto">
                            ดูรายละเอียด
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');

            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => {
                if (response.ok) {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "เพิ่มสินค้าลงตะกร้าแล้ว!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "เกิดข้อผิดพลาด",
                        text: "ไม่สามารถเพิ่มสินค้าได้",
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "ผิดพลาด",
                    text: error.message,
                });
            });
        });
    });
</script>

</body>
</html>
