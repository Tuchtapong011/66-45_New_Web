<?php
require_once '../Config.php';
require_once 'authadmin.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: radial-gradient(circle, rgba(140, 35, 232, 1) 0%, rgba(252, 70, 107, 1) 100%);
            min-height: 100vh;
            color: white;
        }

        .card {
            border: none;
        }

        .dashboard-card {
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }

        .btn-light {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">แผงควบคุมผู้ดูแลระบบ</h2>
        <p class="text-muted">ยินดีต้อนรับผู้ดูแล: <strong><?= htmlspecialchars($_SESSION['user_id']) ?></strong></p>
    </div>

    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-warning dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1 mb-3"></i>
                    <h5 class="card-title">จัดการสมาชิก</h5>
                    <a href="user.php" class="btn btn-light mt-3 w-100">ไปยังหน้าจัดการ</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-dark dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder-fill fs-1 mb-3"></i>
                    <h5 class="card-title">จัดการหมวดหมู่</h5>
                    <a href="category.php" class="btn btn-light mt-3 w-100">ไปยังหน้าจัดการ</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-primary dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-1 mb-3"></i>
                    <h5 class="card-title">จัดการสินค้า</h5>
                    <a href="products.php" class="btn btn-light mt-3 w-100">ไปยังหน้าจัดการ</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-success dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart-check-fill fs-1 mb-3"></i>
                    <h5 class="card-title">จัดการคำสั่งซื้อ</h5>
                    <a href="orders.php" class="btn btn-light mt-3 w-100">ไปยังหน้าจัดการ</a>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">
        <a href="../logout.php" class="btn btn-danger">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>
</div>

</body>
</html>
