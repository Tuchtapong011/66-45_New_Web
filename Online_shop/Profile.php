<?php
session_start();
require 'Config.php';
if(!isset($_SESSION['user_id'])){header("Location: login.php"); exit;}
$user_id=$_SESSION['user_id'];

// ✅ ถ้ามีการส่งชื่อใหม่เข้ามา (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $new_name = trim($_POST['full_name']);
    if ($new_name !== '') {
        $stmt = $conn->prepare("UPDATE users SET full_name=? WHERE user_id=?");
        $stmt->execute([$new_name, $user_id]);
        $_SESSION['full_name'] = $new_name;
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error','message'=>'ชื่อไม่สามารถเว้นว่างได้']);
    }
    exit;
}

$stmt=$conn->prepare("SELECT username, full_name, email, class, adventure_rank, created_at FROM users WHERE user_id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>โปรไฟล์นักผจญภัย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body{
    background:linear-gradient(135deg,#2b1055,#7597de);
    font-family:'Kanit',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    color:#fff;
}
.profile-card{
    background:rgba(255,255,255,0.1);
    backdrop-filter:blur(10px);
    border-radius:20px;
    padding:30px;
    max-width:600px;
    width:100%;
    box-shadow:0 0 20px rgba(0,0,0,0.6);
    text-align:left;
}
.profile-header h2{
    font-size:2.2rem;
    color:#ffd700;
    text-shadow:0 0 10px #000;
    margin-bottom:25px;
    text-align:center;
}
.value{color:#fff;font-weight:bold;font-size:1.1rem;}
.class{
    display:inline-block;
    padding:5px 15px;
    border-radius:50px;
    background:linear-gradient(45deg,#ff6ec4,#7873f5);
    font-weight:bold;
    font-size:1rem;
    animation:glow 2s infinite alternate;
    margin-top:5px;
}
@keyframes glow{
    0%{box-shadow:0 0 5px #ff6ec4,0 0 10px #7873f5;}
    100%{box-shadow:0 0 20px #ff6ec4,0 0 30px #7873f5;}
}
.edit-btn{
    background: linear-gradient(45deg,#34e89e,#0f3443);
    border:none;
    border-radius:12px;
    color:white;
    margin-top:15px;
}
.edit-btn:hover{
    background: linear-gradient(45deg,#0f3443,#34e89e);
    color:white;
}
</style>
</head>
<body>
<div class="profile-card">
    <div class="profile-header">
        <h2>✨ โปรไฟล์นักผจญภัย ✨</h2>
    </div>
    <div class="mb-3"><span>ชื่อผู้ใช้: </span><span class="value"><?=htmlspecialchars($user['username'])?></span></div>
    <div class="mb-2"><span>ชื่อเต็ม: </span><span id="fullNameDisplay" class="value"><?=htmlspecialchars($user['full_name'])?></span></div>
    <div class="mb-3"><span>สายอาชีพ: </span><span class="class"><?=htmlspecialchars($user['class'])?></span></div>
    <div class="mb-3"><span>แรงค์นักผจญภัย: </span><span class="value"><?=htmlspecialchars($user['adventure_rank'])?></span></div>
    <div class="mb-3"><span>อีเมล: </span><span class="value"><?=htmlspecialchars($user['email'])?></span></div>
    <div class="mb-3"><span>วันที่เข้าร่วม: </span><span class="value"><?=date("d M Y",strtotime($user['created_at']))?></span></div>

    <button class="btn edit-btn" id="editNameBtn">แก้ไขชื่อเต็มนักผจญภัย</button>
    <a href="index.php" class="btn btn-light ms-2 mt-3">กลับสู่ร้านค้า</a>
</div>

<script>
document.getElementById('editNameBtn').addEventListener('click', function(){
    Swal.fire({
        title: 'แก้ไขชื่อเต็มนักผจญภัย',
        input: 'text',
        inputLabel: 'ชื่อเต็ม',
        inputValue: document.getElementById('fullNameDisplay').textContent,
        showCancelButton: true,
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก',
        preConfirm: (newName) => {
            if(!newName) Swal.showValidationMessage('กรุณากรอกชื่อเต็ม');
            return newName;
        }
    }).then((result)=>{
        if(result.isConfirmed){
            fetch('', { // 🔹 ส่งกลับมาที่ไฟล์นี้เอง
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'full_name=' + encodeURIComponent(result.value)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('fullNameDisplay').textContent = result.value;
                    Swal.fire({
                        icon:'success',
                        title:'อัปเดตเรียบร้อย!',
                        text:'ชื่อของคุณได้รับการเปลี่ยนแปลงแล้ว',
                        timer:1500,
                        showConfirmButton:false
                    });
                } else {
                    Swal.fire('ผิดพลาด', data.message, 'error');
                }
            })
            .catch(err => Swal.fire('ผิดพลาด', err.message, 'error'));
        }
    });
});
</script>
</body>
</html>