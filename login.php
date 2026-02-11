<?php
error_reporting(0);
include 'auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (login($user, $pass)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة!";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام المكتبة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #1f4bd8 0%, #0b2d84 70%, #43b3e8 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 430px;
            text-align: center;
        }
        .logo {
            margin-bottom: 16px;
        }
        .logo img {
            width: 140px;
            height: auto;
            object-fit: contain;
        }
        h1 {
            color: #0b2d84;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            direction: ltr;
            text-align: left;
        }
        input:focus {
            outline: none;
            border-color: #0b2d84;
            box-shadow: 0 0 0 3px rgba(31,75,216,0.12);
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1f4bd8 0%, #0b2d84 70%, #43b3e8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(11,45,132,0.35);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .footer {
            margin-top: 20px;
            color: #999;
            font-size: 12px;
        }
        .pass-toggle {
            position: relative;
        }
        .eye {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #0b2d84;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo"><img src="college.png" alt="شعار الهيئة الملكية"></div>
        <h1>نظام المكتبة</h1>
        <p class="subtitle">كلية ينبع الصناعية - المكتبة الرئيسية</p>
        
        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>👤 اسم المستخدم</label>
                <input type="text" name="username" required autofocus placeholder="أدخل اسم المستخدم">
            </div>
            
            <div class="form-group">
                <label>🔒 كلمة المرور</label>
                <div class="pass-toggle">
                    <input type="password" id="pass" name="password" required placeholder="أدخل كلمة المرور">
                    <span class="eye" onclick="togglePass()">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">🔓 تسجيل الدخول</button>
        </form>
        
        <div class="footer">© <?= date('Y') ?> جميع الحقوق محفوظة</div>
    </div>
    
    <script>
        function togglePass() {
            var p = document.getElementById('pass');
            var e = document.querySelector('.eye');
            if (p.type === 'password') {
                p.type = 'text';
                e.textContent = '🙈';
            } else {
                p.type = 'password';
                e.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
