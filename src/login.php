<?php
require_once __DIR__ . '/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'សូមបំពេញឈ្មោះអ្នកប្រើ និងពាក្យសម្ងាត់';
    } else {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            redirect('dashboard.php');
        } else {
            $error = 'ឈ្មោះអ្នកប្រើ ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #3C096C 0%, #7B2CBF 50%, #9D4EDD 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            padding: 45px 40px;
            width: 100%;
            max-width: 430px;
        }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #7B2CBF, #5A189A);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(123, 44, 191, 0.4);
        }
        .logo-area h1 { font-size: 1.8rem; color: #1e1b4b; margin-bottom: 3px; }
        .logo-area p { color: #6b7280; font-size: 0.85rem; letter-spacing: 2px; font-weight: 600; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.85rem; color: #1e1b4b; }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e9d5ff;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        .form-control:focus {
            border-color: #7B2CBF;
            box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.15);
        }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #7B2CBF, #5A189A);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 6px 18px rgba(123, 44, 191, 0.4);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(123, 44, 191, 0.5); }
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 0.85rem;
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .demo-info {
            background: #f5f3ff;
            border-radius: 10px;
            padding: 15px;
            margin-top: 25px;
            font-size: 0.8rem;
            color: #5A189A;
            border: 1px dashed #C77DFF;
        }
        .demo-info strong { display: block; margin-bottom: 8px; color: #3C096C; }
        .demo-info code {
            background: #fff;
            padding: 2px 7px;
            border-radius: 5px;
            color: #7B2CBF;
            font-weight: 600;
        }
        .demo-info div { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">NR</div>
            <h1>NR1 Pharmacy</h1>
            <p>INVENTORY SYSTEM</p>
        </div>

        <?php if ($error): ?>
            <div class="alert"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>ឈ្មោះអ្នកប្រើ</label>
                <input type="text" name="username" class="form-control" required autofocus
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label>ពាក្យសម្ងាត់</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <div class="demo-info">
            <strong>🔑 Demo Accounts:</strong>
            <div>Admin: <code>admin</code> / <code>admin123</code></div>
            <div>Pharmacist: <code>pharmacist</code> / <code>pharma123</code></div>
            <div>Cashier: <code>cashier</code> / <code>cash123</code></div>
        </div>
    </div>
</body>
</html>