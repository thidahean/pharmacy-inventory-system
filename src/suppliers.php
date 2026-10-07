<?php
require_once __DIR__ . '/db.php';
requireLogin();

$db = getDB();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM suppliers WHERE supplier_id = $id");
    flash('success', 'លុបអ្នកផ្គត់ផ្គង់បានជោគជ័យ');
    redirect('suppliers.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if ($name !== '') {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE supplier_id=?");
            $stmt->bind_param('sssssi', $name, $contact, $phone, $email, $address, $edit_id);
            $stmt->execute();
            flash('success', 'កែប្រែអ្នកផ្គត់ផ្គង់បានជោគជ័យ');
        } else {
            $stmt = $db->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss', $name, $contact, $phone, $email, $address);
            $stmt->execute();
            flash('success', 'បន្ថែមអ្នកផ្គត់ផ្គង់បានជោគជ័យ');
        }
    }
    redirect('suppliers.php');
}

$suppliers = $db->query("SELECT * FROM suppliers ORDER BY name");

$pageTitle = 'Suppliers';
include __DIR__ . '/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h1 class="page-title">Suppliers</h1>
        <p class="page-subtitle">Manage your medicine suppliers</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><i class="bi bi-plus-lg"></i> Add Supplier</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th><th>Address</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if ($suppliers->num_rows === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-secondary);">No suppliers</td></tr>
            <?php else: $i = 1; while ($s = $suppliers->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><?= htmlspecialchars($s['contact_person'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['address'] ?? '-') ?></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='openModal(<?= json_encode($s) ?>)'><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $s['supplier_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('លុប?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="supModal">
    <div class="modal" style="max-width:550px;">
        <h2 id="supTitle">Add Supplier</h2>
        <form method="POST">
            <input type="hidden" name="edit_id" id="s_id" value="0">
            <div class="form-group">
                <label>Company Name *</label>
                <input type="text" name="name" id="s_name" class="form-control" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" id="s_contact" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="s_phone" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="s_email" class="form-control">
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" id="s_address" class="form-control" rows="2"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(data) {
    const modal = document.getElementById('supModal');
    if (data) {
        document.getElementById('supTitle').textContent = 'Edit Supplier';
        document.getElementById('s_id').value = data.supplier_id;
        document.getElementById('s_name').value = data.name;
        document.getElementById('s_contact').value = data.contact_person || '';
        document.getElementById('s_phone').value = data.phone || '';
        document.getElementById('s_email').value = data.email || '';
        document.getElementById('s_address').value = data.address || '';
    } else {
        document.getElementById('supTitle').textContent = 'Add Supplier';
        document.getElementById('s_id').value = 0;
        document.querySelectorAll('#supModal input, #supModal textarea').forEach(el => el.value = '');
    }
    modal.classList.add('active');
}
function closeModal() { document.getElementById('supModal').classList.remove('active'); }
document.getElementById('supModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>

<?php include __DIR__ . '/footer.php'; ?>