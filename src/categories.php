<?php
require_once __DIR__ . '/db.php';
requireLogin();

$db = getDB();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM categories WHERE category_id = $id");
    flash('success', 'លុបប្រភេទបានជោគជ័យ');
    redirect('categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if ($name !== '') {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE categories SET name=?, description=? WHERE category_id=?");
            $stmt->bind_param('ssi', $name, $description, $edit_id);
            $stmt->execute();
            flash('success', 'កែប្រែប្រភេទបានជោគជ័យ');
        } else {
            $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $description);
            $stmt->execute();
            flash('success', 'បន្ថែមប្រភេទបានជោគជ័យ');
        }
    }
    redirect('categories.php');
}

$categories = $db->query("
    SELECT c.*, COUNT(m.medicine_id) AS product_count
    FROM categories c
    LEFT JOIN medicines m ON c.category_id = m.category_id
    GROUP BY c.category_id
    ORDER BY c.name
");

$pageTitle = 'Categories';
include __DIR__ . '/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Manage medicine categories</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><i class="bi bi-plus-lg"></i> Add Category</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Description</th><th>Products</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if ($categories->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-secondary);">No categories</td></tr>
            <?php else: $i = 1; while ($c = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                    <td><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                    <td><span class="badge badge-info"><?= $c['product_count'] ?></span></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='openModal(<?= json_encode($c) ?>)'><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $c['category_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('លុប?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="catModal">
    <div class="modal" style="max-width:500px;">
        <h2 id="catTitle">Add Category</h2>
        <form method="POST">
            <input type="hidden" name="edit_id" id="c_id" value="0">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" id="c_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="c_desc" class="form-control" rows="3"></textarea>
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
    const modal = document.getElementById('catModal');
    if (data) {
        document.getElementById('catTitle').textContent = 'Edit Category';
        document.getElementById('c_id').value = data.category_id;
        document.getElementById('c_name').value = data.name;
        document.getElementById('c_desc').value = data.description || '';
    } else {
        document.getElementById('catTitle').textContent = 'Add Category';
        document.getElementById('c_id').value = 0;
        document.getElementById('c_name').value = '';
        document.getElementById('c_desc').value = '';
    }
    modal.classList.add('active');
}
function closeModal() { document.getElementById('catModal').classList.remove('active'); }
document.getElementById('catModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>

<?php include __DIR__ . '/footer.php'; ?>