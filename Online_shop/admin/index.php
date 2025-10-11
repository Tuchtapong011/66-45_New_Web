<?php
require '../session_timeout.php';
require_once '../Config.php';
require_once 'authadmin.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ห้องหัวหน้ากิลด์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body {
    background: radial-gradient(circle at top left, #4a0094ff, #c8ff00ff);
    min-height: 100vh;
    color: #fff;
    font-family: 'Kanit', sans-serif;
    overflow-x: hidden;
}

h2, p { text-shadow: 0 0 10px rgba(0,0,0,0.5); }

.card {
    border: none;
    border-radius: 20px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.dashboard-card {
    position: relative;
    overflow: hidden;
}

.dashboard-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,215,0,0.2));
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s;
    border-radius: 20px;
}

.dashboard-card:hover::after { opacity: 1; }

.dashboard-card:hover {
    transform: scale(1.08);
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
}

.card-body i {
    font-size: 3rem;
    text-shadow: 0 0 15px gold;
}

.btn-light {
    font-weight: bold;
    background: linear-gradient(135deg,#ffd700,#ffb347);
    color: #333;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255,215,0,0.6);
    transition: all 0.2s;
}

.btn-light:hover {
    transform: scale(1.05);
    box-shadow: 0 0 20px rgba(255,215,0,0.8);
}

.bg-warning { background: linear-gradient(135deg,#ff9a00,#ffcd38); }
.bg-dark { background: linear-gradient(135deg,#2c3e50,#34495e); }
.bg-primary { background: linear-gradient(135deg,#6f42c1,#9d50bb); }
.bg-success { background: linear-gradient(135deg,#28a745,#85e085); }

a.btn-danger {
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(255,0,0,0.7);
    transition: all 0.3s;
}

a.btn-danger:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(255,0,0,0.9); }

</style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">🏰 ห้องหัวหน้ากิลด์</h2>
        <p class="text-warning">ยินดีต้อนรับหัวหน้ากิลด์:<strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
    </div>

    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-warning dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill"></i>
                    <h5 class="card-title mt-3">สมาชิกกิลด์</h5>
                    <a href="user.php" class="btn btn-light mt-3 w-100">จัดการสมาชิก</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-dark dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder-fill"></i>
                    <h5 class="card-title mt-3">หมวดหมู่สินค้า</h5>
                    <a href="category.php" class="btn btn-light mt-3 w-100">จัดการหมวดหมู่</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-primary dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam"></i>
                    <h5 class="card-title mt-3">คลังสินค้า</h5>
                    <a href="products.php" class="btn btn-light mt-3 w-100">จัดการสินค้า</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-success dashboard-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart-check-fill"></i>
                    <h5 class="card-title mt-3">คำสั่งซื้อ</h5>
                    <a href="orders.php" class="btn btn-light mt-3 w-100">จัดการคำสั่งซื้อ</a>
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
