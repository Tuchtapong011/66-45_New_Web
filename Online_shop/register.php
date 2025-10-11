<?php
session_start();
require 'Config.php';

$error = '';
$register_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $class = $_POST['class'];

    // ตรวจสอบ username / email ซ้ำ
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, class, adventure_rank) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$username, $full_name, $email, $hash, $class, 'F']); 
        $register_success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครนักผจญภัย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit&family=MedievalSharp&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    margin:0; padding:0; font-family:'Kanit', sans-serif;
    background: linear-gradient(135deg,#2b1055,#7597de);
    min-height:100vh; display:flex; justify-content:center; align-items:center;
    overflow:hidden;
}
.register-container {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    width: 100%; max-width: 450px;
    box-shadow:0 0 25px rgba(0,0,0,0.6);
    color:#fff;
    position:relative;
}
.register-container h2 {
    font-family:'MedievalSharp',cursive;
    text-align:center;
    font-size:2.5rem;
    color:#ffd700;
    margin-bottom:30px;
    text-shadow:0 0 10px #000;
}
.form-label { font-weight:bold; color:#ffeaa7; }
.form-control { border-radius:10px; background: rgba(255,255,255,0.15); color:#fff; border:none; }
.form-control:focus { background: rgba(255,255,255,0.2); color:#fff; box-shadow:none; border:none; }
.select-class {
    background: rgba(255,255,255,0.15);
    color: #fff; 
    border: none;
    border-radius: 10px;
}
.select-class option {
    color: #000; 
    background: #fff; 
}
.btn-primary { background: linear-gradient(135deg,#ff6ec4,#7873f5); border:none; color:#fff; }
.btn-primary:hover { background: linear-gradient(135deg,#ff9a9e,#fad0c4); color:#000; }
.btn-outline-secondary { border-radius:10px; color:#fff; border:1px solid #fff; }
canvas {position:absolute; top:0; left:0; width:100%; height:100%; z-index:-1;}
</style>
</head>
<body>

<canvas id="magicCanvas"></canvas>

<div class="register-container">
    <h2>สมัครเป็นนักผจญภัย</h2>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon:'error',
            title:'เกิดข้อผิดพลาด',
            text:'<?= htmlspecialchars($error) ?>',
            confirmButtonColor:'#ffd700'
        });
    </script>
    <?php endif; ?>

    <?php if ($register_success): ?>
    <script>
        Swal.fire({
            icon:'success',
            title:'สมัครสมาชิกสำเร็จ!',
            text:'ยินดีต้อนรับนักผจญภัยใหม่ 🎉',
            confirmButtonColor:'#ffd700'
        }).then(()=>{ window.location='login.php'; });
    </script>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label class="form-label">ชื่อผู้ใช้</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ชื่อเต็ม</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">อีเมล</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">เลือกสายอาชีพ</label>
            <select name="class" class="form-select select-class" required>
                <option value="นักดาบ">นักดาบ</option>
                <option value="นักเวทย์">นักเวทย์</option>
                <option value="นักธนู">นักธนู</option>
                <option value="โจร">โจร</option>
                <option value="นักบวช">นักบวช</option>
            </select>
        </div>
        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary">เริ่มการผจญภัย</button>
            <a href="login.php" class="btn btn-outline-secondary">กลับสู่ทางเข้า</a>
        </div>
    </form>
</div>

<script>
// Particle magic effect
const canvas=document.getElementById('magicCanvas');
const ctx=canvas.getContext('2d');
canvas.width=window.innerWidth;
canvas.height=window.innerHeight;

class Particle {
    constructor(x,y,r,color){
        this.x=x; this.y=y; this.r=r; this.color=color;
        this.vx=(Math.random()-0.5)*1.5; this.vy=(Math.random()-0.5)*1.5;
    }
    update(){
        this.x+=this.vx; this.y+=this.vy;
        if(this.x<0||this.x>canvas.width)this.vx*=-1;
        if(this.y<0||this.y>canvas.height)this.vy*=-1;
    }
    draw(){
        ctx.beginPath();
        ctx.arc(this.x,this.y,this.r,0,2*Math.PI);
        ctx.fillStyle=this.color;
        ctx.fill();
    }
}

let particles=[];
for(let i=0;i<80;i++){
    particles.push(new Particle(Math.random()*canvas.width,Math.random()*canvas.height,Math.random()*2+1,['#ffd700','#ff6ec4','#00ffff','#ffffff'][Math.floor(Math.random()*4)]));
}

function animate(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    particles.forEach(p=>{p.update(); p.draw();});
    requestAnimationFrame(animate);
}
animate();
</script>

</body>
</html>