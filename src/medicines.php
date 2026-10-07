<?php
require_once __DIR__ . '/db.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// លុប
if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM medicines WHERE medicine_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'លុបឱសថបានជោគជ័យ');
    redirect('medicines.php');
}

// បន្ថែម / កែ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $generic_name = trim($_POST['generic_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $supplier_id = (int)($_POST['supplier_id'] ?? 0) ?: null;
    $batch_number = trim($_POST['batch_number'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'tablet');
    $cost_price = (float)($_POST['cost_price'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $expiry_date = $_POST['expiry_date'] ?? date('Y-m-d');
    $reorder_level = (int)($_POST['reorder_level'] ?? 10);
    $description = trim($_POST['description'] ?? '');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if ($name === '') {
        flash('error', 'សូមបំពេញឈ្មោះឱសថ');
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE medicines SET name=?, generic_name=?, category_id=?, supplier_id=?, batch_number=?, quantity=?, unit=?, cost_price=?, selling_price=?, expiry_date=?, reorder_level=?, description=? WHERE medicine_id=?");
            $stmt->bind_param('ssiisisdssssi', $name, $generic_name, $category_id, $supplier_id, $batch_number, $quantity, $unit, $cost_price, $selling_price, $expiry_date, $reorder_level, $description, $edit_id);
            $stmt->execute();
            flash('success', 'កែប្រែឱសថបានជោគជ័យ');
        } else {
            $stmt = $db->prepare("INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, quantity, unit, cost_price, selling_price, expiry_date, reorder_level, description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssiisisdssss', $name, $generic_name, $category_id, $supplier_id, $batch_number, $quantity, $unit, $cost_price, $selling_price, $expiry_date, $reorder_level, $description);
            $stmt->execute();
            flash('success', 'បន្ថែមឱសថបានជោគជ័យ');
        }
    }
    redirect('medicines.php');
}

// ទាញយកទិន្នន័យ
$categories = $db->query("SELECT * FROM categories ORDER BY name");
$suppliers = $db->query("SELECT * FROM suppliers ORDER BY name");

$editItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $db->prepare("
        SELECT m.*, c.name AS category_name, s.name AS supplier_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.category_id
        LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
        WHERE m.name LIKE ? OR m.generic_name LIKE ?
        ORDER BY m.name
    ");
    $like = "%$search%";
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $medicines = $stmt->get_result();
} else {
    $medicines = $db->query("
        SELECT m.*, c.name AS category_name, s.name AS supplier_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.category_id
        LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
        ORDER BY m.name
    ");
}

$pageTitle = 'Products';
include __DIR__ . '/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">Manage your pharmacy inventory</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="bi bi-plus-lg"></i> Add Product
    </button>
</div>

<form method="GET" style="margin-bottom:20px;">
    <input type="text" name="search" class="form-control" placeholder="Search products..."
           value="<?= htmlspecialchars($search) ?>" style="max-width:400px;">
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($medicines->num_rows === 0): ?>
                <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-secondary);">No products found</td></tr>
            <?php else: $i = 1; while ($m = $medicines->fetch_assoc()):
                $days_left = (strtotime($m['expiry_date']) - time()) / 86400;
                $isLow = $m['quantity'] <= $m['reorder_level'] && $m['quantity'] > 0;
                $isOut = $m['quantity'] == 0;
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($m['name']) ?></strong>
                        <?php if ($m['generic_name']): ?>
                            <div style="font-size:0.75rem;color:var(--text-secondary);"><?= htmlspecialchars($m['generic_name']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['category_name'] ?? '-') ?></td>
                    <td><strong><?= $m['quantity'] ?></strong> <?= htmlspecialchars($m['unit']) ?></td>
                    <td><?= formatMoney($m['cost_price']) ?></td>
                    <td><?= formatMoney($m['selling_price']) ?></td>
                    <td><?= date('d/m/Y', strtotime($m['expiry_date'])) ?></td>
                    <td>
                        <?php if ($isOut): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php elseif ($isLow): ?>
                            <span class="badge badge-warning">Low Stock</span>
                        <?php elseif ($days_left < 30): ?>
                            <span class="badge badge-warning">Expiring</span>
                        <?php else: ?>
                            <span class="badge badge-success">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=edit&id=<?= $m['medicine_id'] ?>" class="btn btn-secondary btn-sm" onclick="openModal(event, <?= htmlspecialchars(json_encode($m)) ?>)">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="?action=delete&id=<?= $m['medicine_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('លុបឱសថនេះ?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="productModal">
    <div class="modal">
        <h2 id="modalTitle">Add Product</h2>
        <form method="POST" id="productForm">
            <input type="hidden" name="edit_id" id="edit_id" value="0">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" id="f_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Generic Name</label>
                    <input type="text" name="generic_name" id="f_generic" class="form-control">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="f_category" class="form-control">
                        <option value="">-- Select --</option>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier_id" id="f_supplier" class="form-control">
                        <option value="">-- Select --</option>
                        <?php while ($s = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Batch Number</label>
                    <input type="text" name="batch_number" id="f_batch" class="form-control">
                </div>
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" id="f_qty" class="form-control" required value="0">
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" id="f_unit" class="form-control" value="tablet">
                </div>
                <div class="form-group">
                    <label>Cost Price *</label>
                    <input type="number" step="0.01" name="cost_price" id="f_cost" class="form-control" required value="0.00">
                </div>
                <div class="form-group">
                    <label>Selling Price *</label>
                    <input type="number" step="0.01" name="selling_price" id="f_price" class="form-control" required value="0.00">
                </div>
                <div class="form-group">
                    <label>Expiry Date *</label>
                    <input type="date" name="expiry_date" id="f_expiry" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" id="f_reorder" class="form-control" value="10">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="f_desc" class="form-control" rows="2"></textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(e, data) {
    if (e) e.preventDefault();
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');

    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Product';
        document.getElementById('edit_id').value = data.medicine_id;
        document.getElementById('f_name').value = data.name || '';
        document.getElementById('f_generic').value = data.generic_name || '';
        document.getElementById('f_category').value = data.category_id || '';
        document.getElementById('f_supplier').value = data.supplier_id || '';
        document.getElementById('f_batch').value = data.batch_number || '';
        document.getElementById('f_qty').value = data.quantity || 0;
        document.getElementById('f_unit').value = data.unit || 'tablet';
        document.getElementById('f_cost').value = data.cost_price || 0;
        document.getElementById('f_price').value = data.selling_price || 0;
        document.getElementById('f_expiry').value = data.expiry_date || '';
        document.getElementById('f_reorder').value = data.reorder_level || 10;
        document.getElementById('f_desc').value = data.description || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Product';
        form.reset();
        document.getElementById('edit_id').value = 0;
    }
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
}

document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include __DIR__ . '/footer.php'; ?>