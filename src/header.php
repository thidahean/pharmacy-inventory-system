<?php
require_once __DIR__ . '/db.php';
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="km" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php if (isLoggedIn()): ?>
<aside class="sidebar">
    <div class="brand">
        <div style="width:45px;height:45px;background:linear-gradient(135deg,#7B2CBF,#5A189A);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;">NR</div>
        <div class="brand-text">
            <div class="nri">NR1</div>
            <div class="pharmacy">PHARMACY</div>
        </div>
    </div>

    <a href="dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="medicines.php" class="<?= $currentPage === 'medicines' ? 'active' : '' ?>"><i class="bi bi-capsule"></i> Products</a>
    <a href="categories.php" class="<?= $currentPage === 'categories' ? 'active' : '' ?>"><i class="bi bi-tags"></i> Categories</a>
    <a href="suppliers.php" class="<?= $currentPage === 'suppliers' ? 'active' : '' ?>"><i class="bi bi-truck"></i> Suppliers</a>
    <a href="sales.php" class="<?= $currentPage === 'sales' ? 'active' : '' ?>"><i class="bi bi-cart"></i> Sales</a>
    <a href="reports.php" class="<?= $currentPage === 'reports' ? 'active' : '' ?>"><i class="bi bi-bar-chart"></i> Reports</a>

    <div style="padding: 20px 20px 0;">
        <div class="theme-toggle" title="Toggle theme">
            <i class="bi bi-moon-stars-fill"></i>
            <i class="bi bi-sun-fill"></i>
            <div class="toggle-ball"></div>
        </div>
    </div>

    <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
</aside>

<main class="main-content">
    <div class="topbar">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search product, medicine, or barcode...">
        </div>
        <div class="user-section">
            <i class="bi bi-bell" style="font-size:1.3rem;color:var(--text-secondary);cursor:pointer;"></i>
            <div class="user-info">
                <div class="avatar"><?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?></div>
                <div>
                    <div class="name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></div>
                    <div class="role"><?= ucfirst($user['role'] ?? '') ?></div>
                </div>
                <i class="bi bi-chevron-down" style="color:var(--text-secondary);font-size:0.8rem;"></i>
            </div>
        </div>
    </div>

    <div class="content">
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
<?php endif; ?>