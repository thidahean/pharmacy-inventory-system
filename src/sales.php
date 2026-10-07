<?php
require_once __DIR__ . '/db.php';
requireLogin();

$db = getDB();

// បង្កើតការលក់ថ្មី
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $items = json_decode($_POST['items'] ?? '[]', true);
    $user = currentUser();

    if (empty($items)) {
        flash('error', 'សូមបន្ថែមឱសថយ៉ាងហោចណាស់មួយ');
    } else {
        $db->begin_transaction();
        try {
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['qty'];
            }

            $stmt = $db->prepare("INSERT INTO sales (invoice_number, customer_name, customer_phone, user_id, total_amount, payment_method) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('sssids', $invoice, $customer_name, $customer_phone, $user['id'], $total, $payment_method);
            $stmt->execute();
            $sale_id = $db->insert_id;

            $stmt2 = $db->prepare("INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, subtotal) VALUES (?,?,?,?,?)");
            $stmt3 = $db->prepare("UPDATE medicines SET quantity = quantity - ? WHERE medicine_id = ?");

            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['qty'];
                $stmt2->bind_param('iiidd', $sale_id, $item['id'], $item['qty'], $item['price'], $subtotal);
                $stmt2->execute();
                $stmt3->bind_param('ii', $item['qty'], $item['id']);
                $stmt3->execute();
            }

            $db->commit();
            flash('success', "ការលក់បានជោគជ័យ! លេខវិក្កយបត្រ: $invoice");
        } catch (Exception $e) {
            $db->rollback();
            flash('error', 'មានបញ្ហា: ' . $e->getMessage());
        }
    }
    redirect('sales.php');
}

$medicines = $db->query("SELECT medicine_id, name, selling_price, quantity FROM medicines WHERE quantity > 0 ORDER BY name");

$recentSales = $db->query("
    SELECT s.*, u.full_name AS cashier
    FROM sales s
    LEFT JOIN users u ON s.user_id = u.user_id
    ORDER BY s.sale_id DESC LIMIT 10
");

$pageTitle = 'Sales';
include __DIR__ . '/header.php';
?>

<h1 class="page-title">Sales / POS</h1>
<p class="page-subtitle">Create new sale and manage transactions</p>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header"><div class="card-title">🛒 New Sale</div></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" id="cust_name" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" id="cust_phone" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Add Medicine</label>
                <select id="med_select" class="form-control">
                    <option value="">-- Select medicine --</option>
                    <?php while ($m = $medicines->fetch_assoc()): ?>
                        <option value="<?= $m['medicine_id'] ?>" data-price="<?= $m['selling_price'] ?>" data-name="<?= htmlspecialchars($m['name']) ?>" data-stock="<?= $m['quantity'] ?>">
                            <?= htmlspecialchars($m['name']) ?> - <?= formatMoney($m['selling_price']) ?> (<?= $m['quantity'] ?> left)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="button" class="btn btn-primary" onclick="addItem()"><i class="bi bi-plus-lg"></i> Add to Cart</button>

            <table class="data-table" style="margin-top:20px;">
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="cart_body">
                    <tr><td colspan="5" style="text-align:center;color:var(--text-secondary);padding:30px;">Cart is empty</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><div class="card-title">Payment</div></div>
            <div class="card-body">
                <div style="font-size:0.85rem;color:var(--text-secondary);">Total</div>
                <div style="font-size:2.2rem;font-weight:800;color:var(--brand-medium);margin-bottom:20px;" id="total_display">$0.00</div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select id="payment_method" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>

                <button class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;" onclick="completeSale()">
                    <i class="bi bi-check-circle"></i> Complete Sale
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Recent Sales</div></div>
            <div class="card-body" style="padding:0;max-height:400px;overflow-y:auto;">
                <table class="data-table">
                    <thead><tr><th>Invoice</th><th>Amount</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php if ($recentSales->num_rows === 0): ?>
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-secondary);">No sales yet</td></tr>
                    <?php else: while ($s = $recentSales->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:0.8rem;"><?= htmlspecialchars($s['invoice_number']) ?></td>
                            <td><strong><?= formatMoney($s['total_amount']) ?></strong></td>
                            <td style="font-size:0.75rem;color:var(--text-secondary);"><?= date('d/m H:i', strtotime($s['sale_date'])) ?></td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form method="POST" id="saleForm" style="display:none;">
    <input type="hidden" name="customer_name" id="f_cust_name">
    <input type="hidden" name="customer_phone" id="f_cust_phone">
    <input type="hidden" name="payment_method" id="f_payment">
    <input type="hidden" name="items" id="f_items">
</form>

<script>
let cart = [];

function addItem() {
    const sel = document.getElementById('med_select');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return alert('Please select a medicine');

    const id = parseInt(opt.value);
    const name = opt.dataset.name;
    const price = parseFloat(opt.dataset.price);
    const stock = parseInt(opt.dataset.stock);

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty >= stock) return alert('Not enough stock');
        existing.qty++;
    } else {
        cart.push({ id, name, price, qty: 1, stock });
    }
    sel.value = '';
    renderCart();
}

function removeItem(idx) { cart.splice(idx, 1); renderCart(); }

function changeQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty < 1) cart[idx].qty = 1;
    if (cart[idx].qty > cart[idx].stock) cart[idx].qty = cart[idx].stock;
    renderCart();
}

function renderCart() {
    const body = document.getElementById('cart_body');
    if (cart.length === 0) {
        body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-secondary);padding:30px;">Cart is empty</td></tr>';
        document.getElementById('total_display').textContent = '$0.00';
        return;
    }
    let total = 0, html = '';
    cart.forEach((item, i) => {
        const sub = item.price * item.qty;
        total += sub;
        html += `<tr>
            <td>${item.name}</td>
            <td>$${item.price.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-secondary btn-sm" onclick="changeQty(${i}, -1)">-</button>
                <strong style="margin:0 8px;">${item.qty}</strong>
                <button type="button" class="btn btn-secondary btn-sm" onclick="changeQty(${i}, 1)">+</button>
            </td>
            <td>$${sub.toFixed(2)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${i})"><i class="bi bi-x"></i></button></td>
        </tr>`;
    });
    body.innerHTML = html;
    document.getElementById('total_display').textContent = '$' + total.toFixed(2);
}

function completeSale() {
    if (cart.length === 0) return alert('Cart is empty');
    document.getElementById('f_cust_name').value = document.getElementById('cust_name').value;
    document.getElementById('f_cust_phone').value = document.getElementById('cust_phone').value;
    document.getElementById('f_payment').value = document.getElementById('payment_method').value;
    document.getElementById('f_items').value = JSON.stringify(cart);
    document.getElementById('saleForm').submit();
}
</script>

<?php include __DIR__ . '/footer.php'; ?>