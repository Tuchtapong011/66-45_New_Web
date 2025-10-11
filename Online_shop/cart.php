<?php
session_start();
require_once "Config.php";
require 'session_timeout.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// ✅ เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'name' => $product['product_name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        header("Location: cart.php?added=1");
        exit;
    }
}

// ✅ อัปเดตจำนวน (AJAX)
if (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $id = (int)$_POST['id'];
    $quantity = max(1, (int)$_POST['quantity']);
    if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]['quantity'] = $quantity;

    $total = 0;
    foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['quantity'];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'item_total' => number_format($_SESSION['cart'][$id]['price'] * $_SESSION['cart'][$id]['quantity'], 2),
        'cart_total' => number_format($total, 2)
    ]);
    exit;
}

// ✅ ลบสินค้า
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
    header("Location: cart.php?removed=1");
    exit;
}

// ✅ ลบทั้งหมด
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    $_SESSION['cart'] = [];
    header("Location: cart.php?cleared=1");
    exit;
}

// ✅ คำนวณราคารวม
$total = 0;
foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['quantity'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>💎 Guild Cart - ตะกร้าแห่งกิลด์ทองคำ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    background: radial-gradient(circle at top,#3a1c71,#d76d77,#ffaf7b);
    font-family: 'Kanit', sans-serif;
    color: #fff;
    overflow-x: hidden;
    position: relative;
}
.container {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 15px;
    padding: 30px;
    margin-top: 50px;
    box-shadow: 0 0 40px rgba(255,215,0,0.5);
    border: 2px solid rgba(255,215,0,0.3);
}
.cart-item {
    background: rgba(255,215,0,0.08);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 0 10px rgba(255,215,0,0.3);
    transition: all 0.4s ease;
}
.cart-item:hover {
    transform: scale(1.02);
    background: rgba(255,215,0,0.18);
}
.cart-item img {
    width: 90px;
    height: 90px;
    border-radius: 10px;
    border: 2px solid rgba(255,215,0,0.5);
    object-fit: cover;
}
.quantity-control {
    display: flex;
    align-items: center;
    gap: 8px;
}
.quantity-control button {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg,#ffeb3b,#f57f17);
    border: none;
    color: #000;
    font-weight: bold;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(255,215,0,0.6);
}
.quantity-control input {
    width: 55px;
    text-align: center;
    border-radius: 6px;
    border: none;
    padding: 5px;
    background-color: rgba(255,255,255,0.9);
    color: #000;
}
.btn-remove {
    background: linear-gradient(135deg,#ff1744,#f57f17);
    border: none;
    color: white;
    border-radius: 10px;
    padding: 6px 12px;
    box-shadow: 0 0 8px rgba(255,50,50,0.7);
}
.btn-checkout {
    background: linear-gradient(135deg,#ffd700,#ffb347);
    border: none;
    color: #000;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: bold;
    box-shadow: 0 0 20px rgba(255,215,0,0.6);
}
.btn-clear {
    background: linear-gradient(135deg,#ff9a9e,#fad0c4);
    border: none;
    color: #333;
    border-radius: 12px;
    padding: 10px 20px;
}
.total-box {
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 15px;
    text-align: right;
    box-shadow: 0 0 20px rgba(255,215,0,0.4);
}
.flash {
    animation: flashGlow 0.8s ease;
}
@keyframes flashGlow {
    0% { transform: scale(1); text-shadow: 0 0 10px gold; }
    50% { transform: scale(1.2); text-shadow: 0 0 25px #fff176; }
    100% { transform: scale(1); text-shadow: 0 0 5px gold; }
}
#particleCanvas {
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
}
</style>
</head>
<body>

<canvas id="particleCanvas"></canvas>

<div class="container">
    <h2 class="text-center mb-4">💰 ตะกร้าสมบัติแห่งกิลด์ทองคำ</h2>

    <?php if (isset($_GET['added'])): ?>
    <script>Swal.fire({icon:'success',title:'เพิ่มสินค้าสำเร็จ!',showConfirmButton:false,timer:1200});</script>
    <?php endif; ?>

    <?php if (isset($_GET['removed'])): ?>
    <script>Swal.fire({icon:'info',title:'เตะสินค้าออกแล้ว!',showConfirmButton:false,timer:1200});</script>
    <?php endif; ?>

    <?php if (isset($_GET['cleared'])): ?>
    <script>Swal.fire({icon:'info',title:'ล้างตะกร้าเรียบร้อย!',showConfirmButton:false,timer:1200});</script>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="text-center">
            <p>🧺 ไม่มีสมบัติในตะกร้า</p>
            <a href="index.php" class="btn btn-light">กลับไปเลือกสินค้า</a>
        </div>
    <?php else: ?>
        <div id="cart-list">
        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
            <div class="cart-item d-flex justify-content-between align-items-center" id="item-<?= $id ?>">
                <div class="d-flex align-items-center gap-3">
                    <img src="product_images/<?= htmlspecialchars($item['image'] ?: 'no-image.png') ?>" alt="">
                    <div>
                        <h5><?= htmlspecialchars($item['name']) ?></h5>
                        <div class="price"><?= number_format($item['price'], 2) ?> บาท</div>
                        <div class="quantity-control mt-2">
                            <button onclick="updateQuantity(<?= $id ?>, -1)">-</button>
                            <input type="text" value="<?= $item['quantity'] ?>" id="qty-<?= $id ?>" readonly>
                            <button onclick="updateQuantity(<?= $id ?>, 1)">+</button>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div id="item-total-<?= $id ?>" class="fw-bold mb-2"><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</div>
                    <button class="btn-remove" onclick="removeItem(<?= $id ?>)">เตะออก</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="total-box mt-4">
            <h4>💎 รวมทั้งหมด: <span id="cart-total"><?= number_format($total, 2) ?></span> บาท</h4>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="#" class="btn btn-clear" onclick="confirmClear()">ลบสินค้าทั้งหมด</a>
                <a href="checkout.php" class="btn btn-checkout">💎 ไปชำระเงิน</a>
                <a href="index.php" class="btn btn-light">กลับสู่ร้านค้า</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// ✅ ปรับจำนวนสินค้าแบบเรียลไทม์
function updateQuantity(id, change){
    const input = document.getElementById('qty-'+id);
    let quantity = parseInt(input.value) + change;
    if(quantity < 1) quantity = 1;
    input.value = quantity;

    fetch('cart.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=update_quantity&id='+id+'&quantity='+quantity
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            const itemTotal = document.getElementById('item-total-'+id);
            const cartTotal = document.getElementById('cart-total');
            itemTotal.textContent = data.item_total + ' ฿';
            cartTotal.textContent = data.cart_total;
            itemTotal.classList.add('flash');
            cartTotal.classList.add('flash');
            setTimeout(()=>{ itemTotal.classList.remove('flash'); cartTotal.classList.remove('flash'); },800);
        }
    });
}

// ✅ ลบสินค้า
function removeItem(id){
    Swal.fire({
        title:'เตะสินค้าออก?',
        text:'สินค้านี้จะหายไปจากตะกร้า!',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#d33',
        confirmButtonText:'เตะออก',
        cancelButtonText:'ยกเลิก'
    }).then((result)=>{
        if(result.isConfirmed){
            createParticles();
            setTimeout(()=>{ window.location.href='cart.php?remove='+id; },600);
        }
    });
}

// ✅ ลบทั้งหมด
function confirmClear(){
    Swal.fire({
        title:'ล้างตะกร้าทั้งหมด?',
        text:'สมบัติทั้งหมดจะหายไป!',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'ยืนยัน',
        cancelButtonText:'ยกเลิก'
    }).then((result)=>{
        if(result.isConfirmed){
            createParticles();
            setTimeout(()=>{ window.location.href='cart.php?clear=1'; },800);
        }
    });
}

// ✅ เอฟเฟกต์ประกายดาว
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
let particles = [];
function createParticles(){
    for(let i=0;i<50;i++){
        particles.push({
            x: window.innerWidth/2,
            y: window.innerHeight/2,
            size: Math.random()*4+2,
            speedX: (Math.random()-0.5)*6,
            speedY: (Math.random()-0.5)*6,
            color: 'hsl('+(Math.random()*60+40)+',100%,70%)',
            life: 60
        });
    }
}
function animateParticles(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    particles.forEach((p,i)=>{
        p.x+=p.speedX;
        p.y+=p.speedY;
        p.life--;
        ctx.fillStyle=p.color;
        ctx.beginPath();
        ctx.arc(p.x,p.y,p.size,0,2*Math.PI);
        ctx.fill();
        if(p.life<=0) particles.splice(i,1);
    });
    requestAnimationFrame(animateParticles);
}
animateParticles();

// ✅ เอฟเฟกต์เวลาชำระเงิน
function checkoutEffect(){
    createParticles();
    Swal.fire({
        icon:'success',
        title:'✨ ชำระเงินสำเร็จ!',
        text:'พลังทองคำแห่งกิลด์กำลังสะสม...',
        showConfirmButton:false,
        timer:2000
    });
}
</script>
</body>
</html>