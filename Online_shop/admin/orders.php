<?php
require '../session_timeout.php';
require '../config.php';
require 'authadmin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        header("Location: orders.php");
        exit;
    }
}

// ฟังก์ชันกำหนดสี badge ตาม status
function statusColor($status) {
    return match($status) {
        'pending' => '#FFA500',
        'processing' => '#1E90FF',
        'shipped' => '#32CD32',
        'completed' => '#FFD700',
        'cancelled' => '#FF4500',
        default => '#ccc',
    };
}

function shippingColor($status) {
    return match($status) {
        'not_shipped' => '#FF6347',
        'shipped' => '#1E90FF',
        'delivered' => '#32CD32',
        default => '#ccc',
    };
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📜 จัดการคำสั่งซื้อ - ห้องหัวหน้ากิลด์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
<style>
body {
    background: radial-gradient(circle at top left, #2c003e, #5c2a9d, #ff6f61);
    min-height: 100vh;
    color: #fff;
    font-family: 'Kanit', sans-serif;
}

.container { padding: 40px 20px; }

h2 { text-align: center; font-weight: bold; color: #ffd700; text-shadow: 0 0 10px #fffa9e; margin-bottom: 30px; }

.btn-back { margin-bottom: 20px; font-weight: bold; }

.accordion-button {
    background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(255,140,0,0.2));
    color: #6fff00ff;
    font-weight: bold;
    text-shadow: 0 0 5px #000000ff;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, rgba(255,215,0,0.4), rgba(255,140,0,0.4));
    color: #fff;
}

.accordion-body {
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
    margin-top: 5px;
    padding: 20px;
}

.list-group-item { background: rgba(0,0,0,0.3); border: none; color: #fff; }

.list-group-item span { font-weight: bold; color: #ffd700; }

form .form-select { background: rgba(255,255,255,0.2); color: #09ff00ff; border-radius: 8px; }

.btn-primary { background: linear-gradient(135deg,#ffd700,#f6c90e); border: none; font-weight: bold; box-shadow: 0 0 10px rgba(255,215,0,0.5); }
.btn-success { background: linear-gradient(135deg,#32cd32,#228b22); border: none; font-weight: bold; box-shadow: 0 0 10px rgba(50,205,50,0.5); }

.badge-status {
    color: #ffffffff;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 10px;
    text-transform: capitalize;
}
</style>
</head>
<body>

<div class="container">
    <h2>📜 คำสั่งซื้อทั้งหมด - ห้องหัวหน้ากิลด์</h2>
    <a href="index.php" class="btn btn-secondary btn-back">← กลับหน้าผู้ดูแล</a>

    <div class="accordion" id="ordersAccordion">
    <?php foreach ($orders as $index => $order): ?>
        <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $index ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                    คำสั่งซื้อ #<?= $order['order_id'] ?> | <?= htmlspecialchars($order['username']) ?> | <?= $order['order_date'] ?> | 
                    <span class="badge-status" style="background-color: <?= statusColor($order['status']) ?>;">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </button>
            </h2>
            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#ordersAccordion">
                <div class="accordion-body">

                    <h5>📦 รายการสินค้า</h5>
                    <ul class="list-group mb-3">
                        <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                                <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>ยอดรวม:</strong> <?= number_format($order['total_amount'], 2) ?> บาท</p>

                    <form method="post" class="row g-2 mb-3">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <?php
                                $statuses = ['pending','processing','shipped','completed','cancelled'];
                                foreach ($statuses as $status) {
                                    $selected = ($order['status']===$status)?'selected':'';
                                    echo "<option value=\"$status\" $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="update_status" class="btn btn-primary">อัปเดตสถานะ</button>
                        </div>
                    </form>

                    <?php if ($shipping): ?>
                        <h5>🚚 ข้อมูลจัดส่ง</h5>
                        <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                        <form method="post" class="row g-2">
                            <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                            <div class="col-md-4">
                                <select name="shipping_status" class="form-select">
                                    <?php
                                    $s_statuses = ['not_shipped','shipped','delivered'];
                                    foreach ($s_statuses as $s) {
                                        $selected = ($shipping['shipping_status']===$s)?'selected':'';
                                        echo "<option value=\"$s\" $selected>$s</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="update_shipping" class="btn btn-success">อัปเดตการจัดส่ง</button>
                            </div>
                        </form>
                        <p>สถานะจัดส่ง: 
                            <span class="badge-status" style="background-color: <?= shippingColor($shipping['shipping_status']) ?>;">
                                <?= str_replace('_',' ',ucfirst($shipping['shipping_status'])) ?>
                            </span>
                        </p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
