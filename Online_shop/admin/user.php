<?php
require_once '../Config.php';
require_once 'authadmin.php';

// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = (int) $_GET['delete'];

    // ป้องกันไม่ให้ลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }

    header("Location: user.php");
    exit;
}

// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-actions a {
            margin-right: 5px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

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
        <div class="dashboard-header mb-4">
            <h2 class="fw-bold"><i class="bi bi-people-fill me-2"></i>จัดการสมาชิก</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้าผู้ดูแล
            </a>
        </div>

        <?php if (count($users) === 0): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> ยังไม่มีสมาชิกในระบบ
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ลำดับ</th>
                            <th scope="col">ชื่อผู้ใช้</th>
                            <th scope="col">ชื่อ - นามสกุล</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col">วันที่สมัคร</th>
                            <th scope="col">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                <td class="table-actions">
    <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">
        <i class="bi bi-pencil-fill"></i> แก้ไข
    </a>
    <button type="button"
        class="delete-button btn btn-danger btn-sm"
        data-user-id="<?= $user['user_id'] ?>">
        <i class="bi bi-trash-fill"></i> ลบ
    </button>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showDeleteConfirmation(userId) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: 'คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // สร้างและส่งฟอร์ม POST ไปที่ delUser_Sweet.php
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delUser_Sweet.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'u_id';
                input.value = userId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // แนบ event listener กับปุ่มลบ
    document.querySelectorAll('.delete-button').forEach((button) => {
        button.addEventListener('click', () => {
            const userId = button.getAttribute('data-user-id');
            showDeleteConfirmation(userId);
        });
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'ลบสมาชิกสำเร็จ',
        text: 'ข้อมูลถูกลบเรียบร้อยแล้ว!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'ตกลง'
    });
</script>
<?php endif; ?>
<script>
    // ลบ query string ออกจาก URL หลังแสดง alert
    if (window.location.search.includes('deleted=1')) {
        const newURL = window.location.origin + window.location.pathname;
        window.history.replaceState({}, document.title, newURL);
    }
</script>

</body>

</html>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'อัปเดตข้อมูลสำเร็จ',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
<?php endif; ?>