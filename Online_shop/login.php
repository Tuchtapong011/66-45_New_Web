<?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: '🎉 เข้าสู่กิลด์สำเร็จ!',
    text: 'ยินดีต้อนรับกลับสู่กิลด์ของเรา, <?= htmlspecialchars($_SESSION['full_name']) ?>!',
    confirmButtonColor: '#6f42c1',
    confirmButtonText: 'เริ่มผจญภัย!',
    background: 'rgba(255,255,255,0.95)',
    backdrop: `rgba(0,0,0,0.4) url("https://i.gifer.com/7efs.gif") center left no-repeat`
});
</script>
<?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<?php
session_start();
require 'session_timeout.php';
require_once "Config.php";

// รับค่าจาก cookie
$savedUsername = $_COOKIE['remember_username'] ?? '';
$error = '';

try {
    // ตรวจสอบว่ามีสมาชิกในระบบไหม
    $checkUsers = $conn->query("SELECT COUNT(*) FROM users");
    $userCount = $checkUsers->fetchColumn();

    if ($userCount == 0) {
        $error = "ระบบยังไม่มีสมาชิก กรุณาสมัครสมาชิกก่อนเข้าสู่ระบบ";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userCount > 0) {
        $usernameOrEmail = trim($_POST['username_or_email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "ไม่พบชื่อผู้ใช้หรืออีเมลนี้ในระบบ";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "รหัสผ่านไม่ถูกต้อง";
        } else {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['LAST_ACTIVITY'] = time();

            if (isset($_POST['remember'])) {
                setcookie('remember_username', $usernameOrEmail, time() + 2592000);
            } else {
                setcookie('remember_username', '', time() - 3600);
            }
            $_SESSION['login_success'] = true;
            header("Location: " . ($user['role'] === 'Guild Master' ? 'admin/index.php' : 'index.php'));
            exit();
            $_SESSION['login_success'] = true;
            header("Location: " . ($user['role'] === 'Guild Master' ? 'admin/index.php' : 'index.php'));
            exit();
        }
    }
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดภายในระบบ: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบนักผจญภัย</title>
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
.login-container {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    width: 100%; max-width: 450px;
    box-shadow:0 0 25px rgba(0,0,0,0.6);
    color:#fff;
    position:relative;
}
.login-container h2 {
    font-family:'MedievalSharp',cursive;
    text-align:center;
    font-size:2.5rem;
    color:#ffd700;
    margin-bottom:30px;
    text-shadow:0 0 10px #000;
}
.form-label { font-weight:bold; color:#ffeaa7; }
.form-control { border-radius:10px; }
.btn-primary { background: linear-gradient(135deg,#ff6ec4,#7873f5); border:none; color:#fff; }
.btn-primary:hover { background: linear-gradient(135deg,#ff9a9e,#fad0c4); color:#000; }
canvas {position:absolute; top:0; left:0; width:100%; height:100%; z-index:-1;}
</style>
</head>
<body>

<canvas id="magicCanvas"></canvas>

<div class="login-container">
    <h2>เข้าสู่กิลด์นักผจญภัย</h2>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email"
                class="form-control"
                value="<?= htmlspecialchars($savedUsername) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="togglePassword">
                <label class="form-check-label" for="togglePassword">แสดงรหัสผ่าน</label>
            </div>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" <?= $savedUsername?'checked':'' ?>>
            <label class="form-check-label" for="remember">จำชื่อผู้ใช้</label>
        </div>
        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            <a href="register.php" class="btn btn-primary">สมัครนักผจญภัย</a>
        </div>
    </form>
</div>

<?php if (!empty($error)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'เกิดข้อผิดพลาด',
    text: '<?= htmlspecialchars($error) ?>',
    confirmButtonColor: '#ff6ec4'
});
</script>
<?php endif; ?>

<script>
document.getElementById("togglePassword").addEventListener("change",function(){
    document.getElementById("password").type = this.checked?"text":"password";
});

// พื้นหลังเวทมนตร์
const canvas=document.getElementById('magicCanvas');
const ctx=canvas.getContext('2d');
canvas.width=window.innerWidth;
canvas.height=window.innerHeight;

class Particle{constructor(x,y,r,color){this.x=x;this.y=y;this.r=r;this.color=color;this.vx=(Math.random()-0.5)*1.5;this.vy=(Math.random()-0.5)*1.5;}
update(){this.x+=this.vx;this.y+=this.vy;if(this.x<0||this.x>canvas.width)this.vx*=-1;if(this.y<0||this.y>canvas.height)this.vy*=-1;}
draw(){ctx.beginPath();ctx.arc(this.x,this.y,this.r,0,2*Math.PI);ctx.fillStyle=this.color;ctx.fill();}}
let particles=[];
for(let i=0;i<80;i++){particles.push(new Particle(Math.random()*canvas.width,Math.random()*canvas.height,Math.random()*2+1,['#ffd700','#ff6ec4','#00ffff','#ffffff'][Math.floor(Math.random()*4)]));}
function animate(){ctx.clearRect(0,0,canvas.width,canvas.height);particles.forEach(p=>{p.update();p.draw();});requestAnimationFrame(animate);}
animate();
</script>

</body>
</html>