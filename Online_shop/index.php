<?php
session_start();
require 'session_timeout.php';
require_once 'Config.php';

$isLoggedIn = isset($_SESSION['user_id']);

// โหลดข้อมูลนักผจญภัย
if ($isLoggedIn && (!isset($_SESSION['full_name']) || !isset($_SESSION['class']) || !isset($_SESSION['adventure_rank']))) {
    $stmt = $conn->prepare("SELECT full_name, class, adventure_rank FROM users WHERE user_id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $userSession = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['full_name'] = $userSession['full_name'];
    $_SESSION['class'] = $userSession['class'];
    $_SESSION['adventure_rank'] = $userSession['adventure_rank'];
}

// โหลดสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนสินค้าในตะกร้า
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Guild Shop - ร้านค้ากิลนักผจญภัย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit&family=MedievalSharp&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  font-family:'Kanit',sans-serif;
  background: radial-gradient(circle at top, #2b1055, #7597de);
  color: #fff;
  overflow-x: hidden;
  position: relative;
}
.magic-glow {
  position: fixed;
  top:0; left:0;
  width:100%; height:100%;
  background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.15), transparent 70%);
  animation: pulse 6s infinite alternate;
  pointer-events:none;
  z-index:-1;
}
@keyframes pulse { from {opacity: 0.3;} to {opacity: 0.6;} }
.guild-header {
  background: rgba(0,0,0,0.6);
  border: 2px solid #ffd700;
  border-radius: 15px;
  padding: 15px 25px;
  margin: 25px 0;
  box-shadow: 0 0 15px rgba(255,215,0,0.6);
}
.guild-header h1 {
  font-family:'MedievalSharp',cursive;
  color: #ffd700;
  font-size: 2.5rem;
  text-shadow: 0 0 10px #ffea00;
}
.cart-btn {
  position: relative;
  background: linear-gradient(135deg,#ff6ec4,#7873f5);
  border: none;
  border-radius: 50%;
  width: 45px; height: 45px;
  color: white;
  font-size: 1.3rem;
}
.cart-btn .badge {
  position: absolute;
  top: -5px; right: -5px;
  background: #f9d71c;
  color: black;
  font-weight: bold;
}
.product-card {
  border: none;
  border-radius: 15px;
  background: linear-gradient(145deg,#3a1c71,#d76d77,#ffaf7b);
  color: #fff;
  transition: transform 0.3s, box-shadow 0.3s;
}
.product-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 0 30px rgba(255,215,0,0.4);
}
.product-thumb {
  border-radius: 15px 15px 0 0;
  width:100%;
  aspect-ratio:4/3;
  object-fit:cover;
}
.product-title { font-weight:bold; color:#fff; }
.price { font-size:1.2rem; font-weight:bold; color:#ffd700; }
.btn-buy {
  background: linear-gradient(135deg,#34e89e,#0f3443);
  border: none; border-radius: 10px; color: #fff;
}
.btn-buy:hover { background: linear-gradient(135deg,#0f3443,#34e89e); }
.btn-outline-light { border-radius:10px; }

.guild-header {
  background: rgba(0,0,0,0.6);
  border: 2px solid #ffd700;
  border-radius: 15px;
  padding: 15px 25px;
  margin: 25px 0;
  box-shadow: 0 0 15px rgba(255,215,0,0.6);
  text-align: center;
}

.guild-header h1 {
  font-family:'MedievalSharp',cursive;
  color: #ffd700;
  font-size: 2.5rem;
  text-shadow: 0 0 10px #ffea00;
}

</style>
</head>
<body class="container py-4">

<div class="magic-glow"></div>

<!-- Header -->
<div class="guild-header">
  <div class="d-flex justify-content-between align-items-center">
    <!-- ซ้าย: ว่างไว้สำหรับบาลานซ์ -->
    <div style="width:45px;"></div>

    <!-- กลาง: ชื่อร้าน -->
    <div class="text-center flex-grow-1">
      <h1 style="margin:0;">⚔️ Guild Shop ⚗️</h1>
    </div>

    <!-- ขวา: ปุ่มตะกร้า -->
    <?php if ($isLoggedIn): ?>
    <div class="d-flex align-items-center">
      <a href="cart.php" class="position-relative">
        <button class="cart-btn">
          <i class="bi bi-cart"></i>
          <span id="cart-count" class="badge rounded-pill"><?= $cartCount ?></span>
        </button>
      </a>
    </div>
    <?php else: ?>
      <div style="width:45px;"></div>
    <?php endif; ?>
  </div>

  <!-- แถบข้อมูลนักผจญภัย -->
  <?php if ($isLoggedIn): ?>
    <p class="mt-3 mb-1 text-center">
      ยินดีต้อนรับนักผจญภัย <b><?= htmlspecialchars($_SESSION['full_name']) ?></b>
      (<?= htmlspecialchars($_SESSION['class']) ?> | <?= htmlspecialchars($_SESSION['adventure_rank']) ?>)
    </p>
    <div class="text-center">
      <a href="profile.php" class="btn btn-outline-light btn-sm">โปรไฟล์</a>
      <a href="orders.php" class="btn btn-outline-warning btn-sm">📜 ประวัติการสั่งซื้อ</a>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
    </div>
  <?php else: ?>
    <div class="text-center mt-2">
      <a href="login.php" class="btn btn-success btn-sm">เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-primary btn-sm">สมัครสมาชิก</a>
    </div>
  <?php endif; ?>
</div>

<!-- Product Grid -->
<div class="row g-4">
<?php foreach($products as $p): 
$img = !empty($p['image']) ? 'product_images/'.rawurlencode($p['image']) : 'product_images/no-image.png';
?>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card product-card h-100">
      <img src="<?= htmlspecialchars($img) ?>" class="product-thumb" alt="<?= htmlspecialchars($p['product_name']) ?>">
      <div class="card-body">
        <h5 class="product-title"><?= htmlspecialchars($p['product_name']) ?></h5>
        <p class="small"><?= htmlspecialchars($p['category_name'] ?? 'ไม่ระบุหมวดหมู่') ?></p>
        <div class="price"><?= number_format((float)$p['price'],2) ?> G</div>
        <div class="mt-3 d-flex justify-content-between align-items-center">
          <?php if($isLoggedIn): ?>
            <button class="btn btn-buy btn-sm add-to-cart-btn" data-product-id="<?= (int)$p['product_id'] ?>">🛒 เพิ่มในตะกร้า</button>
          <?php else: ?>
            <small class="text-warning">เข้าสู่ระบบเพื่อซื้อ</small>
          <?php endif; ?>
          <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-outline-light btn-sm">รายละเอียด</a>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.add-to-cart-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const id = btn.dataset.productId;
    fetch('cart.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`product_id=${id}&quantity=1`
    })
    .then(r=>r.ok ? r.text() : Promise.reject('Error'))
    .then(()=>{
      const cartCount=document.getElementById('cart-count');
      cartCount.textContent=parseInt(cartCount.textContent)+1;
      Swal.fire({
        position:"top-end",
        icon:"success",
        title:"เพิ่มสินค้าลงตะกร้าแล้ว!",
        showConfirmButton:false,
        timer:1500
      });
    })
    .catch(err=>{
      Swal.fire('เกิดข้อผิดพลาด',err,'error');
    });
  });
});
</script>

</body>
</html>