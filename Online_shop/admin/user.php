<?php
session_start();
require '../session_timeout.php';
require_once '../Config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->query("SELECT user_id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>🛡️ ห้องบัญชาการกิลด์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    background: radial-gradient(circle at top, #3a0ca3, #000);
    font-family: 'Kanit', sans-serif;
    color: #fff;
    min-height: 100vh;
    padding: 40px 20px;
}

.guild-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: #ffd700;
    text-shadow: 0 0 20px #a78bfa, 0 0 30px #5a189a;
    margin-bottom: 25px;
}

.card-panel {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 0 25px rgba(255,215,0,0.3);
}

.btn-guild {
    background: linear-gradient(135deg,#ffd700,#b07cf7);
    color: #000;
    border: none;
    font-weight: bold;
}
.btn-guild:hover {
    background: linear-gradient(135deg,#fff78a,#a06cd5);
    color: #000;
}

.fade-out {
    animation: fadeOut 0.6s forwards;
}
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.97); height: 0; margin: 0; padding: 0; }
}
.table thead {
    background: linear-gradient(90deg,#4c1d95,#7c3aed);
    color: #fff;
}
.table tbody tr {
    background: rgba(255,255,255,0.05);
    color: #fff;
}
.table tbody tr:hover {
    background: rgba(255,255,255,0.15);
}
</style>
</head>
<body>
<h2 class="guild-title">⚔️ ห้องบัญชาการกิลด์ ⚔️</h2>

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button id="deleteSelected" class="btn btn-danger btn-sm">
                <i class="bi bi-person-dash-fill"></i> เตะสมาชิกที่เลือก
            </button>
            <a href="index.php" class="btn btn-guild btn-sm ms-2">
                🏰 กลับสู่หน้าหลักกิลด์
            </a>
        </div>
        <span class="text-light small">หัวหน้ากิลด์สามารถเตะสมาชิกออกได้จากที่นี่</span>
    </div>

    <table class="table table-hover table-bordered align-middle text-center" id="userTable">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>ชื่อผู้ใช้</th>
                <th>ชื่อเต็ม</th>
                <th>อีเมล</th>
                <th>สิทธิ์</th>
                <th>วันที่เข้าร่วม</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr id="row-<?= $u['user_id'] ?>">
                <td><input type="checkbox" class="user-checkbox" value="<?= $u['user_id'] ?>"></td>
                <td><?= $u['user_id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-danger delete-user-btn" data-id="<?= $u['user_id'] ?>">
                        <i class="bi bi-person-x"></i> เตะสมาชิก
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// ✅ ปุ่มเลือกทั้งหมด
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.user-checkbox').forEach(chk => chk.checked = this.checked);
});

// ✅ ฟังก์ชันเตะสมาชิก
function kickUser(userId, rowEl) {
    return fetch(`delUser_Sweet.php?id=${userId}`)
        .then(res => res.text())
        .then(result => {
            if (result.includes("success")) {
                rowEl.classList.add('fade-out');
                setTimeout(() => rowEl.remove(), 600);
            } else {
                Swal.fire('ผิดพลาด', 'ไม่สามารถเตะสมาชิก ID ' + userId, 'error');
            }
        });
}

// ✅ เตะสมาชิกเดี่ยว
document.querySelectorAll('.delete-user-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.dataset.id;
        const row = document.getElementById('row-' + userId);

        Swal.fire({
            title: 'เตะสมาชิกออกจากกิลด์?',
            text: 'สมาชิกจะถูกลบออกจากระบบกิลด์ทันที!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'เตะเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((res) => {
            if (res.isConfirmed) {
                kickUser(userId, row).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'เตะสำเร็จ!',
                        text: 'สมาชิกถูกเตะออกจากกิลด์เรียบร้อยแล้ว',
                        timer: 1200,
                        showConfirmButton: false
                    });
                });
            }
        });
    });
});

// ✅ เตะสมาชิกหลายคน
document.getElementById('deleteSelected').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(chk => chk.value);
    if (selected.length === 0) {
        Swal.fire('กรุณาเลือกสมาชิกก่อน', '', 'info');
        return;
    }

    Swal.fire({
        title: `ยืนยันการเตะสมาชิก ${selected.length} คน?`,
        text: 'การเตะนี้ไม่สามารถย้อนกลับได้!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'เตะเลย!',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#d33'
    }).then(result => {
        if (result.isConfirmed) {
            Promise.all(selected.map(id => {
                const row = document.getElementById('row-' + id);
                return kickUser(id, row);
            }))
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'เตะสมาชิกสำเร็จ!',
                    text: `เตะสมาชิก ${selected.length} คนออกจากกิลด์เรียบร้อยแล้ว`,
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
    });
});
</script>
</body>
</html>