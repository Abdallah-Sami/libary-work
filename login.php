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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #0f1b3e;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(123, 77, 186, 0.25) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 50%, rgba(91, 197, 232, 0.2) 0%, transparent 60%),
                radial-gradient(ellipse at 50% 100%, rgba(46, 189, 112, 0.15) 0%, transparent 50%);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.97);
            padding: 44px 40px 36px;
            border-radius: 24px;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.35),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2ebd70, #5bc5e8, #7b4dba);
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            width: 120px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));
        }

        h1 {
            color: #1b2d6b;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #7b8ab5;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 22px;
            text-align: right;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1b2d6b;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b8ab5;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .input-wrapper .input-icon svg {
            width: 20px;
            height: 20px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 13px 14px 13px 48px;
            padding-right: 44px;
            border: 2px solid #e2e8f4;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            direction: ltr;
            text-align: left;
            background: #f8faff;
            color: #1b2d6b;
        }

        .input-wrapper input::placeholder {
            color: #a0aec0;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #7b4dba;
            box-shadow: 0 0 0 4px rgba(123, 77, 186, 0.12);
            background: #fff;
        }

        .toggle-pass {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7b8ab5;
            background: none;
            border: none;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            border-radius: 6px;
        }

        .toggle-pass:hover {
            color: #7b4dba;
            background: rgba(123, 77, 186, 0.08);
        }

        .toggle-pass svg {
            width: 22px;
            height: 22px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1b2d6b 0%, #7b4dba 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(123, 77, 186, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            border: 1px solid #fecaca;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: right;
        }

        .error svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .footer {
            margin-top: 24px;
            color: #a0aec0;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px 28px;
            }
            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <img src="college.png" alt="شعار الهيئة الملكية">
        </div>
        <h1>نظام المكتبة</h1>
        <p class="subtitle">كلية ينبع الصناعية - المكتبة الرئيسية</p>

        <?php if ($error): ?>
            <div class="error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input type="text" name="username" required autofocus placeholder="أدخل اسم المستخدم">
                </div>
            </div>

            <div class="form-group">
                <label>كلمة المرور</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="pass" name="password" required placeholder="أدخل كلمة المرور">
                    <button type="button" class="toggle-pass" onclick="togglePass()" aria-label="إظهار كلمة المرور">
                        <svg id="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">تسجيل الدخول</button>
        </form>

        <div class="footer">&copy; <?= date('Y') ?> جميع الحقوق محفوظة</div>
    </div>

    <script>
        function togglePass() {
            var p = document.getElementById('pass');
            var eyeOpen = document.getElementById('eye-open');
            var eyeClosed = document.getElementById('eye-closed');
            if (p.type === 'password') {
                p.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                p.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
</body>
</html>
