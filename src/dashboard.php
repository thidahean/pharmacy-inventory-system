<?php
require_once __DIR__ . '/db.php';
requireLogin();

$pageTitle = 'Dashboard';
$db = getDB();

$totalMedicines = $db->query("SELECT COUNT(*) AS c FROM medicines")->fetch_assoc()['c'];
$totalSuppliers = $db->query("SELECT COUNT(*) AS c FROM suppliers")->fetch_assoc()['c'];
$lowStock = $db->query("SELECT COUNT(*) AS c FROM medicines WHERE quantity <= reorder_level AND quantity > 0")->fetch_assoc()['c'];
$outOfStock = $db->query("SELECT COUNT(*) AS c FROM medicines WHERE quantity = 0")->fetch_assoc()['c'];
$totalStockValue = $db->query("SELECT COALESCE(SUM(quantity * selling_price), 0) AS v FROM medicines")->fetch_assoc()['v'];

$lowStockItems = $db->query("
    SELECT medicine_id, name, quantity, reorder_level
    FROM medicines
    WHERE quantity <= reorder_level
    ORDER BY quantity ASC LIMIT 5
");

$expiringItems = $db->query("
    SELECT medicine_id, name, expiry_date, quantity,
           DATEDIFF(expiry_date, CURDATE()) AS days_left
    FROM medicines
    WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
    ORDER BY expiry_date ASC LIMIT 5
");

include __DIR__ . '/header.php';
?>

<h1 class="page-title">Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'] ?? 'Admin')[0]) ?>! 👋</h1>
<p class="page-subtitle">Here's what's happening with your pharmacy today.</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-capsule"></i></div>
        <div class="stat-content">
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= number_format($totalMedicines) ?></div>
            <div class="stat-change up"><i class="bi bi-arrow-up"></i> 12% vs last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-content">
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-value"><?= $lowStock ?></div>
            <div class="stat-change down">need attention</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-content">
            <div class="stat-label">Stock Value</div>
            <div class="stat-value"><?= formatMoney($totalStockValue) ?></div>
            <div class="stat-change up"><i class="bi bi-arrow-up"></i> 18% vs last month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pink"><i class="bi bi-x-circle"></i></div>
        <div class="stat-content">
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value"><?= $outOfStock ?></div>
            <div class="stat-change down">need restock</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Sales Overview</div>
            <span style="color:var(--text-secondary);font-size:0.85rem;">This Week</span>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="90"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Stock Status</div>
        </div>
        <div class="card-body" style="display:flex;justify-content:center;align-items:center;">
            <canvas id="stockChart" height="220"></canvas>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-exclamation-triangle" style="color:var(--warning);"></i> Low Stock Alert</div>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($lowStockItems->num_rows === 0): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-secondary);padding:30px;">No low stock items</td></tr>
                    <?php else: while ($m = $lowStockItems->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><strong><?= $m['quantity'] ?></strong> / <?= $m['reorder_level'] ?></td>
                            <td>
                                <?php if ($m['quantity'] == 0): ?>
                                    <span class="badge badge-danger">Out</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Low</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-calendar-x" style="color:var(--danger);"></i> Expiring Soon</div>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Expiry</th><th>Days</th></tr></thead>
                    <tbody>
                    <?php if ($expiringItems->num_rows === 0): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--text-secondary);padding:30px;">No expiring items</td></tr>
                    <?php else: while ($m = $expiringItems->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($m['expiry_date'])) ?></td>
                            <td>
                                <?php if ($m['days_left'] < 0): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php elseif ($m['days_left'] <= 30): ?>
                                    <span class="badge badge-danger"><?= $m['days_left'] ?>d</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= $m['days_left'] ?>d</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function initCharts() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? '#2d1b4e' : '#e9d5ff';
    const textColor = isDark ? '#a78bfa' : '#6b7280';

    if (window.salesChartInstance) window.salesChartInstance.destroy();
    if (window.stockChartInstance) window.stockChartInstance.destroy();

    window.salesChartInstance = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales',
                data: [520, 680, 450, 900, 750, 1200, 1050],
                borderColor: '#7B2CBF',
                backgroundColor: 'rgba(123, 44, 191, 0.15)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#7B2CBF',
                pointRadius: 5,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
                x: { grid: { display: false }, ticks: { color: textColor } }
            }
        }
    });

    window.stockChartInstance = new Chart(document.getElementById('stockChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [<?= max(0, $totalMedicines - $lowStock - $outOfStock) ?>, <?= $lowStock ?>, <?= $outOfStock ?>],
                backgroundColor: ['#7B2CBF', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, padding: 15, font: { size: 12 } } }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', initCharts);
window.addEventListener('themeChanged', initCharts);
</script>

<?php include __DIR__ . '/footer.php'; ?>