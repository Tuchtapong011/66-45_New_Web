<?php
// product_detail.php
session_start();
require 'session_timeout.php';
require_once 'Config.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch product
$stmt = $conn->prepare("SELECT p.*, c.category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // product not found
    header("Location: index.php");
    exit;
}

// Related products (same category, exclude current)
$relatedStmt = $conn->prepare("SELECT product_id, product_name, price, image FROM products WHERE category_id = ? AND product_id != ? ORDER BY created_at DESC LIMIT 4");
$relatedStmt->execute([$product['category_id'], $id]);
$related = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

// Cart count from session (if available)
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartCount += $item['quantity'];
}

// helper safe echo
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($product['product_name']) ?> — Guild Shop</title>

<!-- Bootstrap / Icons / Fonts / SweetAlert -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit&family=MedievalSharp&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Theme: Guild Marketplace — elegant fantasy */
:root{
  --gold:#ffd700;
  --accent1:#7c3aed;
  --accent2:#ff6ec4;
  --card-bg:linear-gradient(145deg,#2b1055,#3b2a6d);
}

body{
  font-family:'Kanit',sans-serif;
  background: radial-gradient(circle at top left, #0f172a, #1e2340 40%, #0b1220 100%);
  color:#e6e6e6;
  padding-bottom:60px;
}

/* Top nav */
.guild-nav {
  background: rgba(10,10,20,0.6);
  border: 1px solid rgba(200,180,255,0.06);
  backdrop-filter: blur(6px);
  padding: 12px 20px;
  border-radius: 12px;
  margin: 18px auto;
  max-width: 1200px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
}
.guild-title {
  font-family:'MedievalSharp',cursive;
  color:var(--gold);
  text-shadow:0 0 8px rgba(255,215,0,0.15);
  font-size:1.6rem;
}
.nav-actions { display:flex; gap:8px; align-items:center; }

/* Main layout */
.container-main{
  max-width: 1200px;
  margin: 18px auto;
}

/* Product card */
.product-panel{
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.04);
  border-radius: 14px;
  padding: 18px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.6);
}
.left-col .carousel-item img{ border-radius:10px; max-height:460px; object-fit:cover; width:100%; }
.badge-new { background: linear-gradient(135deg,#ff6ec4,#7c3aed); color:#fff; font-weight:600; }

/* Price and buy area */
.price-large { font-size:1.6rem; font-weight:800; color:var(--gold); }
.stock-badge { font-weight:700; padding:.35rem .6rem; border-radius:8px; }

/* Tabs / specs */
.specs { background: rgba(255,255,255,0.02); padding:12px; border-radius:8px; }

/* Related */
.related-card { background: linear-gradient(135deg,#1f1540, #2b0f3a); border-radius:12px; border:1px solid rgba(255,255,255,0.03); }

/* Buttons */
.btn-guild {
  background: linear-gradient(135deg,var(--accent2),var(--accent1));
  color:#000; font-weight:700; border:none;
  border-radius:10px;
}
.btn-guild:hover { filter:brightness(.98); transform:translateY(-2px); }

/* Footer */
.footer {
  margin-top:30px; text-align:center; color:#9aa0b4; font-size:.9rem;
}
</style>
</head>
<body>

<!-- Top navigation -->
<div class="guild-nav container-main">
  <div class="d-flex align-items-center gap-3">
    <a href="index.php" class="text-decoration-none guild-title">⚔️ Guild Shop</a>
    <div class="text-muted small">/ ห้องจัดแสดงไอเทม</div>
  </div>

  <div class="nav-actions">
    <?php if(isset($_SESSION['user_id'])): ?>
      <div class="me-2 text-end">
        <div style="font-size:.9rem; color:#ffd;">สวัสดี, <b><?= e($_SESSION['full_name']) ?></b></div>
        <div class="text-muted small"><?= e($_SESSION['profession'] ?? 'นักผจญภัย') ?></div>
      </div>
    <?php endif; ?>
    <a href="cart.php" class="btn btn-outline-light position-relative">
      <i class="bi bi-cart"></i> ตะกร้า
      <span id="nav-cart-count" class="badge bg-warning text-dark rounded-pill ms-1"><?= $cartCount ?></span>
    </a>
    <?php if(!isset($_SESSION['user_id'])): ?>
      <a href="login.php" class="btn btn-guild">เข้าสู่ระบบ</a>
    <?php else: ?>
      <a href="profile.php" class="btn btn-outline-light">โปรไฟล์</a>
    <?php endif; ?>
  </div>
</div>

<!-- Product detail -->
<div class="container-main">
  <div class="product-panel row g-4">
    <!-- Left: Images -->
    <div class="col-lg-6 left-col">
      <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
          // gather images: main image + potential others (if name pattern)
          $images = [];
          if (!empty($product['image'])) $images[] = 'product_images/'.rawurlencode($product['image']);
          // try to find similarly named extra images (optional)
          // (If you have multiple images naming convention, adapt here)
          if (count($images) === 0) $images[] = 'product_images/no-image.png';
          foreach($images as $i => $imgPath):
          ?>
          <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
            <img src="<?= e($imgPath) ?>" class="d-block w-100" alt="<?= e($product['product_name']) ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <?php if(count($images) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
        <?php endif; ?>
      </div>

      <!-- short description / category -->
      <div class="mt-3">
        <span class="badge badge-new">หมวด: <?= e($product['category_name'] ?? 'อื่นๆ') ?></span>
        <?php if (isset($product['created_at']) && (time() - strtotime($product['created_at']) <= 7*24*3600)): ?>
          <span class="badge bg-success ms-2">สินค้าใหม่</span>
        <?php endif; ?>
      </div>

    </div>

    <!-- Right: Info and buy -->
    <div class="col-lg-6 d-flex flex-column">
      <h2 style="font-family:'MedievalSharp',cursive; color:var(--gold)"><?= e($product['product_name']) ?></h2>
      <p class="text-muted small mb-2"><?= nl2br(e($product['description'])) ?></p>

      <div class="d-flex align-items-center gap-3 my-3">
        <div class="price-large"><?= number_format((float)$product['price'],2) ?> G</div>
        <div>
          <?php if((int)$product['stock'] > 10): ?>
            <span class="stock-badge bg-success">มีสินค้า (<?= (int)$product['stock'] ?> ชิ้น)</span>
          <?php elseif((int)$product['stock'] > 0): ?>
            <span class="stock-badge bg-warning text-dark">เหลือน้อย (<?= (int)$product['stock'] ?>)</span>
          <?php else: ?>
            <span class="stock-badge bg-danger">สินค้าหมด</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">จำนวน</label>
        <div class="input-group" style="max-width:180px;">
          <button class="btn btn-outline-light" id="decQty" type="button">-</button>
          <input type="number" id="qty" class="form-control text-center" value="1" min="1" max="<?= max(1,(int)$product['stock']) ?>">
          <button class="btn btn-outline-light" id="incQty" type="button">+</button>
        </div>
      </div>

      <div class="d-flex gap-2 mt-auto">
        <?php if((int)$product['stock'] > 0): ?>
          <button id="addToCartBtn" class="btn btn-guild btn-lg">
            <i class="bi bi-cart-plus"></i> เพิ่มในตะกร้า
          </button>
        <?php else: ?>
          <button class="btn btn-secondary btn-lg" disabled>สินค้าหมด</button>
        <?php endif; ?>

        <a href="product_detail.php?id=<?= (int)$product['product_id'] ?>&view=spec" class="btn btn-outline-light">ดูสเปค</a>
      </div>

      <!-- Tabs: description / specs / reviews -->
      <div class="mt-4">
        <ul class="nav nav-tabs" id="pdTabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">คำอธิบาย</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">สเปค</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">รีวิว</button></li>
        </ul>
        <div class="tab-content p-3" style="background: rgba(0,0,0,0.25); border-radius:8px;">
          <div class="tab-pane fade show active" id="desc">
            <div class="specs">
              <?= nl2br(e($product['description'])) ?>
            </div>
          </div>
          <div class="tab-pane fade" id="specs">
            <div class="specs">
              <table class="table table-borderless table-sm text-light mb-0">
                <tr><th class="text-muted">รหัสสินค้า</th><td><?= (int)$product['product_id'] ?></td></tr>
                <tr><th class="text-muted">ราคา</th><td><?= number_format((float)$product['price'],2) ?> G</td></tr>
                <tr><th class="text-muted">สต็อก</th><td><?= (int)$product['stock'] ?></td></tr>
                <tr><th class="text-muted">หมวด</th><td><?= e($product['category_name'] ?? '-') ?></td></tr>
                <tr><th class="text-muted">วันที่ขึ้นบัญชี</th><td><?= e(date('d M Y', strtotime($product['created_at']))) ?></td></tr>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="reviews">
            <div class="specs">
              <!-- example reviews (static) -->
              <div class="mb-3">
                <strong>ผู้เล่น: ArcherQueen</strong> <span class="text-warning">★★★★☆</span>
                <p class="small text-muted mb-0">ดาบแรงดี เหมาะกับการลุยดันเจี้ยนเลเวลกลาง</p>
              </div>
              <div class="mb-3">
                <strong>ผู้เล่น: MageLord</strong> <span class="text-warning">★★★★★</span>
                <p class="small text-muted mb-0">ของหายาก คุณภาพเกินราคา!</p>
              </div>
              <hr>
              <small class="text-muted">คุณต้องเข้าสู่ระบบเพื่อเขียนรีวิว</small>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Related products -->
  <?php if(!empty($related)): ?>
  <div class="mt-4">
    <h5 style="color:var(--gold)">สินค้าแนะนำจากกิลด์</h5>
    <div class="row g-3 mt-2">
      <?php foreach($related as $r): 
        $rimg = !empty($r['image']) ? 'product_images/'.rawurlencode($r['image']) : 'product_images/no-image.png';
      ?>
      <div class="col-6 col-md-3">
        <a href="product_detail.php?id=<?= (int)$r['product_id'] ?>" class="text-decoration-none">
          <div class="p-2 related-card text-center">
            <img src="<?= e($rimg) ?>" alt="<?= e($r['product_name']) ?>" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
            <div class="mt-2 text-truncate"><?= e($r['product_name']) ?></div>
            <div class="mt-1 text-warning fw-bold"><?= number_format((float)$r['price'],2) ?> G</div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="footer mt-4">Guild Shop — ร้านค้าจากต่างโลก | สนับสนุนโดยคณะนักรบแห่งป่า</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Quantity controls */
const qtyInput = document.getElementById('qty');
document.getElementById('incQty').addEventListener('click', ()=> {
  let v = parseInt(qtyInput.value) || 1;
  const max = parseInt(qtyInput.max) || 999;
  if (v < max) qtyInput.value = v+1;
});
document.getElementById('decQty').addEventListener('click', ()=> {
  let v = parseInt(qtyInput.value) || 1;
  if (v > 1) qtyInput.value = v-1;
});

/* Add to cart via AJAX */
document.getElementById('addToCartBtn')?.addEventListener('click', function(){
  const productId = <?= (int)$product['product_id'] ?>;
  let qty = parseInt(qtyInput.value) || 1;
  const max = parseInt(qtyInput.max) || 999;
  if (qty < 1) qty = 1;
  if (qty > max) qty = max;

  // send to cart.php (expects POST product_id & quantity)
  fetch('cart.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `product_id=${productId}&quantity=${qty}`
  })
  .then(async res => {
    if (!res.ok) throw new Error('การเชื่อมต่อล้มเหลว');
    const text = await res.text();
    // optional: cart.php can echo new cart count or "OK"
    // We'll increment nav badge locally for immediate feedback
    const navBadge = document.getElementById('nav-cart-count');
    if (navBadge) navBadge.textContent = parseInt(navBadge.textContent || 0) + qty;
    Swal.fire({
      icon: 'success',
      title: 'เพิ่มลงตะกร้าแล้ว',
      html: `คุณได้เพิ่ม <b>${qty}</b> x <?= e($product['product_name']) ?> ลงในตะกร้า`,
      showConfirmButton: true,
      confirmButtonText: 'ไปที่ตะกร้า',
      showCancelButton: true,
      cancelButtonText: 'ดูสินค้าต่อ'
    }).then((r)=>{
      if (r.isConfirmed) window.location.href = 'cart.php';
    });
  })
  .catch(err => {
    Swal.fire('ผิดพลาด', err.message, 'error');
  });
});
</script>

</body>
</html>