<?php
ob_start();
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to access this page.';
    header('Location: ' . $baseUrl . '/user/login.php');
    exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['user_role'] ?? '';

if (!in_array($role, ['admin','staff'], true)) {
    $_SESSION['error_message'] = 'You do not have permission to access this page.';
    header('Location: ' . $baseUrl . '/index.php');
    exit;
}

$success_message = '';
$error_message = '';

function sanitize_text($s) { return trim(filter_var($s, FILTER_SANITIZE_STRING)); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = sanitize_text($_POST['category_name'] ?? '');
        $img  = sanitize_text($_POST['img_name'] ?? '');

        if ($name === '' || mb_strlen($name) > 64) {
            $error_message = 'Category name is required and must be at most 64 characters.';
        } else {
            $stmt = $conn->prepare('SELECT category_id FROM categories WHERE category_name = ?');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $dup = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($dup) {
                $error_message = 'Category name already exists.';
            } else {
                $stmt = $conn->prepare('INSERT INTO categories (category_name, img_name) VALUES (?, ?)');
                $stmt->bind_param('ss', $name, $img);
                if ($stmt->execute()) { $success_message = 'Category created.'; } else { $error_message = 'Failed to create category.'; }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $id   = (int)($_POST['category_id'] ?? 0);
        $name = sanitize_text($_POST['category_name'] ?? '');
        $img  = sanitize_text($_POST['img_name'] ?? '');

        if ($id <= 0) {
            $error_message = 'Invalid category.';
        } elseif ($name === '' || mb_strlen($name) > 64) {
            $error_message = 'Category name is required and must be at most 64 characters.';
        } else {
            $stmt = $conn->prepare('SELECT category_id FROM categories WHERE category_name = ? AND category_id <> ?');
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $dup = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($dup) {
                $error_message = 'Another category with the same name exists.';
            } else {
                $stmt = $conn->prepare('UPDATE categories SET category_name = ?, img_name = ? WHERE category_id = ?');
                $stmt->bind_param('ssi', $name, $img, $id);
                if ($stmt->execute()) { $success_message = 'Category updated.'; } else { $error_message = 'Failed to update category.'; }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id <= 0) {
            $error_message = 'Invalid category.';
        } else {
            $check = $conn->prepare('SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?');
            $check->bind_param('i', $id);
            $check->execute();
            $cnt = $check->get_result()->fetch_assoc();
            $check->close();
            if (!empty($cnt['cnt'])) {
                $error_message = 'Cannot delete: category is in use by products.';
            } else {
                $stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?');
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) { $success_message = 'Category deleted.'; } else { $error_message = 'Failed to delete category.'; }
                $stmt->close();
            }
        }
    }
}

$pageCss = 'admin.css';
include __DIR__ . '/../includes/header.php';

$list = $conn->query('SELECT c.category_id, c.category_name, c.img_name, COUNT(p.product_id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.category_id GROUP BY c.category_id, c.category_name, c.img_name ORDER BY c.category_name');
$categories = $list ? $list->fetch_all(MYSQLI_ASSOC) : [];
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="admin-page">
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/alert.php'; ?>

        <div class="page-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="page-title">Manage Categories</h1>
                    <p class="page-subtitle">Create, edit, and delete product categories</p>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?><div class="alert success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="alert error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <div class="grid-2">
            <section class="card">
                <h2 class="section-title">Add Category</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="create" />
                    <div class="form-group full-width">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="category_name" required maxlength="64" class="form-input" placeholder="e.g. Hair Care" />
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Image Name (optional)</label>
                        <input type="text" name="img_name" class="form-input" placeholder="e.g. hair_care" />
                        <small class="muted">Used to find image in item/product_category/{img_name}.{ext}</small>
                    </div>
                    <div>
                        <button class="btn" type="submit">Create</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <h2 class="section-title">All Categories</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:70px;">ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th style="width:140px;">Products</th>
                                <th style="width:240px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$categories): ?>
                            <tr><td colspan="5" class="muted">No categories found.</td></tr>
                        <?php else: foreach ($categories as $c): ?>
                            <tr>
                                <td><?php echo (int)$c['category_id']; ?></td>
                                <td>
                                    <form method="post" style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="action" value="update" />
                                        <input type="hidden" name="category_id" value="<?php echo (int)$c['category_id']; ?>" />
                                        <input type="text" name="category_name" value="<?php echo htmlspecialchars($c['category_name']); ?>" maxlength="64" class="form-input" />
                                </td>
                                <td>
                                        <input type="text" name="img_name" value="<?php echo htmlspecialchars((string)$c['img_name']); ?>" class="form-input" placeholder="img key" />
                                </td>
                                <td class="muted"><?php echo (int)$c['product_count']; ?></td>
                                <td>
                                        <button class="btn" type="submit">Save</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Delete this category?');" style="display:inline-block; margin-left:8px;">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="category_id" value="<?php echo (int)$c['category_id']; ?>" />
                                        <button class="btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
