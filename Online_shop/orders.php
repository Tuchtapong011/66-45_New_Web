<?php
session_start();
require 'session_timeout.php';
require_once "Config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ถ้ามี request AJAX ให้ส่ง JSON กลับ
if (isset($_GET['ajax'])) {
    if (isset($_GET['id'])) {
        // รายละเอียดคำสั่งซื้อเดียว
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=? AND order_id=?");
        $stmt->execute([$user_id, $_GET['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $itemStmt = $conn->prepare("
                SELECT p.product_name, oi.quantity, oi.price
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?
            ");
            $itemStmt->execute([$order['order_id']]);
            $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($order);
    } else {
        // ดึงรายการทั้งหมดของผู้ใช้ พร้อม alias total_amount เป็น total_price
        $stmt = $conn->prepare("
            SELECT order_id,
                   COALESCE(order_date, NOW()) AS order_date,
                   total_amount AS total_price,
                   status
            FROM orders
            WHERE user_id = ?
            ORDER BY order_id DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($orders);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📜 บันทึกคำสั่งซื้อของกิลด์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Kanit', sans-serif;
    background: radial-gradient(circle at top, #fff8dc, #ffe58f, #fffdf4);
    min-height: 100vh;
    padding: 40px 0;
    overflow-x: hidden;
}
.container-box {
    background: rgba(255, 255, 255, 0.93);
    border-radius: 20px;
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.4);
    padding: 40px 50px;
    max-width: 1000px;
    margin: auto;
    position: relative;
}
h2 {
    text-align: center;
    font-weight: bold;
    color: #b8860b;
    text-shadow: 0 0 10px #fff7cc;
    margin-bottom: 30px;
}
.table th {
    background: linear-gradient(135deg, #ffcc00, #ffb300);
    color: white;
    font-weight: bold;
}
tr.fade-out {
    animation: fadeOut 0.5s forwards;
}
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}
.btn-back {
    display: block;
    width: fit-content;
    margin: 20px auto 0;
    background: #ffcc00;
    color: #333;
    border-radius: 12px;
    padding: 10px 20px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.btn-back:hover {
    background: #ffb300;
    transform: scale(1.05);
}
.loading {
    text-align: center;
    color: #999;
    font-size: 1.2rem;
    margin-top: 30px;
}
</style>
</head>
<body>

<div class="container-box">
    <h2>📜 บันทึกคำสั่งซื้อของกิลด์</h2>

    <div id="orders-container" class="text-center">
        <div class="loading">⏳ กำลังโหลดคำสั่งซื้อ...</div>
    </div>

    <a href="index.php" class="btn-back">← กลับสู่ร้านค้ากิลด์</a>
</div>

<script>
function fetchOrders() {
    fetch('orders.php?ajax=1')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('orders-container');
        if (!data || data.length === 0) {
            container.innerHTML = `
                <div class="text-muted mt-4">
                    😢 ยังไม่มีคำสั่งซื้อในตอนนี้<br>
                    <a href="index.php" class="btn btn-warning mt-3">กลับไปเลือกสินค้า</a>
                </div>
            `;
            return;
        }

        let html = `
        <table class="table table-hover text-center align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ยอดรวม (G)</th>
                    <th>สถานะ</th>
                    <th>รายละเอียด</th>
                </tr>
            </thead>
            <tbody>
        `;
        data.forEach((order, i) => {
            html += `
            <tr id="row-${order.order_id}">
                <td>${i+1}</td>
                <td>${order.order_date}</td>
                <td>${parseFloat(order.total_price).toFixed(2)}</td>
                <td>${getStatusBadge(order.status)}</td>
                <td><button class="btn btn-sm btn-outline-info" onclick="showDetail(${order.order_id})">ดู</button></td>
            </tr>`;
        });
        html += `</tbody></table>`;
        container.innerHTML = html;
    });
}

function getStatusBadge(status) {
    switch(status) {
        case 'completed': return '<span class="badge bg-success">จัดส่งแล้ว</span>';
        case 'pending': return '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
        case 'processing': return '<span class="badge bg-info text-dark">กำลังดำเนินการ</span>';
        case 'shipped': return '<span class="badge bg-primary">กำลังจัดส่ง</span>';
        case 'cancelled': return '<span class="badge bg-danger">ยกเลิก</span>';
        default: return `<span class="badge bg-secondary">${status || '-'}</span>`;
    }
}

function showDetail(orderId) {
    fetch(`orders.php?ajax=1&id=${orderId}`)
    .then(res => res.json())
    .then(order => {
        let productHtml = "";
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                productHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td>${(item.quantity * item.price).toFixed(2)}</td>
                    </tr>`;
            });
        }

        Swal.fire({
            title: `🧾 รายละเอียดคำสั่งซื้อ #${order.order_id}`,
            html: `
                <b>วันที่:</b> ${order.order_date}<br>
                <b>ยอดรวม:</b> ${parseFloat(order.total_amount).toFixed(2)} G<br>
                <hr>
                <table class="table table-sm text-center">
                    <thead><tr><th>สินค้า</th><th>จำนวน</th><th>ราคา</th><th>รวม</th></tr></thead>
                    <tbody>${productHtml}</tbody>
                </table>
            `,
            confirmButtonText: 'ปิด',
            confirmButtonColor: '#ffcc00',
            background: 'rgba(255,255,255,0.95)',
            color: '#333'
        });
    });
}

// โหลดคำสั่งซื้อทุก 5 วิ (Real-time)
setInterval(fetchOrders, 5000);
fetchOrders();
</script>

</body>
</html>
