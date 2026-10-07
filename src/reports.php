<?php
require_once __DIR__ . '/db.php';
requireLogin();

$db = getDB();

$totalSales = $db->query("SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS total FROM sales")->fetch_assoc();
$todaySales = $db->query("SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc();
$monthSales = $db->query("SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())")->fetch_assoc();

$topProducts = $db->query("
    SELECT m.name, SUM(si.quantity) AS sold, SUM(si.subtotal) AS revenue
    FROM sale_items si
    JOIN medicines m ON si.medicine_id = m.medicine_id
    GROUP BY si.medicine_id
    ORDER BY sold DESC LIMIT 10
");

$pageTitle = 'Reports';
include __DIR__ . '/header.php';
?>

<h1 class="page-title">Reports</h1>
<p class="page-subtitle">Sales and inventory analytics</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-receipt"></i></div>
        <div class="stat-content">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value"><?= $totalSales['c'] ?></div>
            <div class="stat-change"><?= formatMoney($totalSales['total']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-calendar-day"></i></div>
        <div class="stat-content">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value"><?= $todaySales['c'] ?></div>
            <div class="stat-change up"><?= formatMoney($todaySales['total']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-calendar-month"></i></div>
        <div class="stat-content">
            <div class="stat-label">This Month</div>
            <div class="stat-value"><?= $monthSales['c'] ?></div>
            <div class="stat-change up"><?= formatMoney($monthSales['total']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pink"><i class="bi bi-graph-up"></i></div>
        <div class="stat-content">
            <div class="stat-label">Avg Sale</div>
            <div class="stat-value"><?= $totalSales['c'] > 0 ? formatMoney($totalSales['total'] / $totalSales['c']) : '$0.00' ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">🏆 Top Selling Products</div></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>#</th><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php if ($topProducts->num_rows === 0): ?>
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-secondary);">No sales data yet</td></tr>
            <?php else: $i = 1; while ($p = $topProducts->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= $p['sold'] ?> units</span></td>
                    <td><strong><?= formatMoney($p['revenue']) ?></strong></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>