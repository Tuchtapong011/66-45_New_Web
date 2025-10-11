<?php
session_start();
require_once "Config.php";
require 'session_timeout.php';

// ✅ ตรวจสอบว่ามีตะกร้าไหม ถ้าไม่มีให้สร้างเป็น array ว่าง
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ ถ้าไม่มีสินค้าในตะกร้า ให้แจ้งเตือนแล้วกลับหน้าตะกร้า
if (empty($_SESSION['cart'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'ไม่มีสินค้าในตะกร้า!',
                text: 'กรุณาเลือกสินค้าก่อนทำการชำระเงิน',
                confirmButtonText: 'กลับไปเลือกสินค้า',
                confirmButtonColor: '#f6c90e'
            }).then(() => { window.location.href='cart.php'; });
        }, 300);
    </script>";
    exit;
}

// ✅ ดึงข้อมูลผู้ใช้
$user = ['username'=>'ไม่พบข้อมูล', 'email'=>'-'];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $fetchedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetchedUser) $user = $fetchedUser;
}

// ✅ คำนวณราคารวม
$total = 0;
$cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
foreach ($cartItems as $item) {
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    $price = isset($item['price']) ? (float)$item['price'] : 0;
    $total += $quantity * $price;
}

// ✅ เมื่อกดชำระเงิน
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize input
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($address === '' || $city === '' || $zipcode === '' || $phone === '') {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        try {
            $conn->beginTransaction();

            // บันทึก orders
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, order_date) VALUES (?, ?, 'pending', NOW())");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $order_id = $conn->lastInsertId();

            // บันทึกรายการสินค้า
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $product_id = $item['product_id'] ?? null;
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : null;
                $price = isset($item['price']) ? (float)$item['price'] : null;
                if ($product_id !== null && $quantity !== null && $price !== null) {
                    $stmtItem->execute([$order_id, $product_id, $quantity, $price]);
                }
            }

            // บันทึกข้อมูลจัดส่ง
            $stmtShip = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone, shipping_status) VALUES (?, ?, ?, ?, ?, 'not_shipped')");
            $stmtShip->execute([$order_id, $address, $city, $zipcode, $phone]);

            $conn->commit();
            unset($_SESSION['cart']);
            $success = true;

        } catch (Exception $e) {
            $conn->rollBack();
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>💰 ชำระเงิน | Guild Treasury</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    background: radial-gradient(circle at top,#fceabb,#f8b500,#fceabb);
    font-family: 'Kanit', sans-serif;
    min-height: 100vh;
    padding: 50px 0;
}
.checkout-container {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    box-shadow: 0 0 30px rgba(255,215,0,0.4);
    padding: 40px 60px;
    max-width: 900px;
    margin: 0 auto;
}
h2 {
    text-align: center;
    font-weight: bold;
    color: #b8860b;
    text-shadow: 0 0 10px #fff5cc;
    margin-bottom: 35px;
}
.section-title {
    font-size: 1.2rem;
    color: #6b4f00;
    font-weight: bold;
    margin-top: 25px;
    margin-bottom: 15px;
    text-shadow: 0 0 6px #fff;
}
.cart-summary {
    background: rgba(255, 250, 230, 0.8);
    padding: 15px;
    border-radius: 12px;
    box-shadow: inset 0 0 10px rgba(255,215,0,0.2);
}
.item-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px dashed #e0c068;
}
.total {
    text-align: right;
    font-size: 1.2rem;
    font-weight: bold;
    color: #d4af37;
    margin-top: 15px;
}
.btn-pay {
    background: linear-gradient(135deg,#ffd700,#f6c90e);
    border: none;
    color: #fff;
    font-weight: bold;
    border-radius: 12px;
    width: 100%;
    padding: 14px;
    margin-top: 30px;
    box-shadow: 0 0 20px rgba(255,215,0,0.5);
    transition: all 0.2s;
}
.btn-pay:hover {
    transform: scale(1.03);
    box-shadow: 0 0 30px rgba(255,215,0,0.8);
}
.btn-back {
    background: #eee;
    color: #333;
    border-radius: 10px;
    padding: 10px;
    text-decoration: none;
    display: inline-block;
    margin-top: 10px;
}
</style>
</head>
<body>

<div class="checkout-container">
    <h2>💰 ยืนยันการสั่งซื้อสินค้ากิลด์ 💰</h2>

    <?php if($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="checkoutForm" method="post">
        <div class="section-title">📜 ข้อมูลผู้สั่งซื้อ</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">อีเมล</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">ที่อยู่</label>
                <input type="text" class="form-control" name="address" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">เมือง</label>
                <input type="text" class="form-control" name="city" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">รหัสไปรษณีย์</label>
                <input type="text" class="form-control" name="zipcode" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">เบอร์ติดต่อ</label>
                <input type="text" class="form-control" name="phone" required>
            </div>
        </div>

        <div class="section-title">🧾 รายการสินค้า</div>
        <div class="cart-summary">
            <?php foreach ($cartItems as $item):
                $name = $item['name'] ?? null;
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : null;
                $price = isset($item['price']) ? (float)$item['price'] : null;
                if ($name !== null && $quantity !== null && $price !== null):
            ?>
                <div class="item-row">
                    <span><?= htmlspecialchars($name) ?> × <?= $quantity ?></span>
                    <strong><?= number_format($price * $quantity, 2) ?> ฿</strong>
                </div>
            <?php endif; endforeach; ?>
            <div class="total">รวมทั้งหมด: <?= number_format($total, 2) ?> บาท</div>
        </div>

        <button type="submit" class="btn-pay">✨ ยืนยันและชำระเงิน ✨</button>
        <a href="cart.php" class="btn-back">← กลับไปตะกร้า</a>
    </form>
</div>

<?php if($success): ?>
<script>
Swal.fire({
    icon: 'success',
    title: '✨ ชำระเงินสำเร็จ! ✨',
    text: 'คำสั่งซื้อถูกบันทึกแล้ว!',
    showConfirmButton: false,
    timer: 2000
}).then(() => window.location.href = 'orders.php');
</script>
<?php endif; ?>

</body>
</html>
