<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Redirect to login if not authorized
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin','asst_admin','staff'], true)) {
    header('Location: ' . $baseUrl . '/user/login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid item.';
    header('Location: index.php');
    exit;
}

$prodSql = "SELECT * FROM products WHERE product_id = $id";
$prodRes = mysqli_query($conn, $prodSql);
$product = $prodRes ? mysqli_fetch_assoc($prodRes) : null;

$invSql = "SELECT * FROM inventory WHERE product_id = $id";
$invRes = mysqli_query($conn, $invSql);
$inventory = $invRes ? mysqli_fetch_assoc($invRes) : null;

if (!$product) {
    $_SESSION['error'] = 'Item not found.';
    header('Location: index.php');
    exit;
}

$cats = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name");

$pageCss = '';
include __DIR__ . '/../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="edit-page">
    <div class="edit-container">
        <?php include __DIR__ . '/../includes/alert.php'; ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="page-title">Edit Item</h1>
                    <p class="page-subtitle">Item #<?= (int)$id ?> — <?= htmlspecialchars($product['product_name']) ?></p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn-secondary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to Items
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
       <form action="update.php?id=<?= (int)$id ?>" method="post" enctype="multipart/form-data" class="edit-form">
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Basic Information</h2>
                </div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Category *</label>
                        <select name="category_id" required class="form-select">
                            <option value="">Select category</option>
                            <?php mysqli_data_seek($cats, 0); while ($c = mysqli_fetch_assoc($cats)): ?>
                                <option value="<?= (int)$c['category_id'] ?>" <?= ((int)$c['category_id'] === (int)$product['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['category_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required class="form-input" />
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-textarea"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price *</label>
                        <div class="input-with-icon">
                            <span class="input-icon">₱</span>
                            <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required class="form-input with-icon" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Product Images</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Main Image Name</label>
                        <input type="text" name="main_img_name" value="<?= htmlspecialchars($product['main_img_name'] ?? '') ?>" class="form-input" />
                        <small class="form-hint">Base name without extension (e.g., "product_1")</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Upload New Main Image</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="main_image" accept="image/*" class="file-input" id="mainImage" />
                            <label for="mainImage" class="file-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                Choose Image
                            </label>
                            <span class="file-name">No file chosen</span>
                        </div>
                    </div>

                    <?php
                    $mainImgPath = null;
                    $mainBase = trim($product['main_img_name'] ?? '');
                    if ($mainBase !== '') {
                        foreach (['.jpg','.png','.webp','.jpeg'] as $ext) {
                            $path = 'products/' . $mainBase . $ext;
                            if (file_exists(__DIR__ . '/' . $path)) { 
                                $mainImgPath = '/GlamEssentials/item/' . $path; 
                                break; 
                            }
                        }
                    }
                    ?>

                    <?php if ($mainImgPath): ?>
                    <div class="form-group full-width">
                        <label class="form-label">Current Main Image</label>
                        <div class="image-preview">
                            <img src="<?= htmlspecialchars($mainImgPath) ?>" alt="Main Image" class="preview-img" />
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inventory Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Inventory Management</h2>
                </div>

                <div class="form-grid three-col">
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($inventory['quantity'] ?? 0) ?>" required class="form-input" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" value="<?= htmlspecialchars($inventory['unit'] ?? 'pcs') ?>" class="form-input" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" min="0" value="<?= htmlspecialchars($inventory['reorder_level'] ?? 10) ?>" class="form-input" />
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Product Status</h2>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?> class="checkbox-input" />
                        <span class="checkbox-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Featured Product
                        </span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="is_available" value="1" <?= !empty($product['is_available']) ? 'checked' : '' ?> class="checkbox-input" />
                        <span class="checkbox-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Available for Purchase
                        </span>
                    </label>
                </div>
            </div>

            <!-- Gallery Images Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Gallery Images</h2>
                </div>

                <?php
                $imgs = [];
                $q = mysqli_prepare($conn, 'SELECT image_id, img_name FROM product_images WHERE product_id = ? ORDER BY image_id DESC');
                mysqli_stmt_bind_param($q, 'i', $id);
                mysqli_stmt_execute($q);
                $r = mysqli_stmt_get_result($q);
                if ($r) { $imgs = mysqli_fetch_all($r, MYSQLI_ASSOC); }
                mysqli_stmt_close($q);
                ?>

                <?php if ($imgs): ?>
                    <div class="gallery-grid">
                        <?php foreach ($imgs as $gi):
                            $full = null;
                            $base = $gi['img_name'];
                            foreach (['.jpg','.png','.webp','.jpeg'] as $ext) {
                                $path = 'product_images/' . $base . $ext;
                                if (file_exists(__DIR__ . '/' . $path)) { $full = $path; break; }
                            }
                        ?>
                            <div class="gallery-item">
                                <?php if ($full): ?>
                                    <img src="<?= htmlspecialchars($full) ?>" alt="Gallery Image" class="gallery-img" />
                                <?php else: ?>
                                    <div class="gallery-placeholder">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <a href="gallery_delete.php?pid=<?= (int)$id ?>&img_id=<?= (int)$gi['image_id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this image?');" 
                                   class="gallery-delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                    Delete
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <p>No gallery images yet</p>
                    </div>
                <?php endif; ?>

                <div class="form-group full-width">
                    <label class="form-label">Add Gallery Images</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="gallery_images[]" accept="image/*" multiple class="file-input" id="galleryImages" />
                        <label for="galleryImages" class="file-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            Choose Images
                        </label>
                        <span class="file-name">No files chosen</span>
                    </div>
                    <div class="gallery-preview"></div>
                    <small class="form-hint">You can select multiple images at once</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Update Item
                </button>
                <a href="index.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Montserrat', sans-serif;
    background: #ffffff;
    color: #1a1a1a;
    line-height: 1.6;
}

.edit-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.edit-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
    transition: all 0.3s ease;
}

.page-header:hover {
    border-color: rgba(0,0,0,0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 400;
    margin-bottom: 5px;
    color: #0a0a0a;
}

.page-subtitle {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #fafafa;
    color: #0a0a0a;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    border: 1px solid rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #0a0a0a;
    color: #ffffff;
    border-color: #0a0a0a;
}

/* Form Sections */
.edit-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 30px;
}

.section-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 400;
    color: #0a0a0a;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-grid.three-col {
    grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    font-weight: 600;
}

.form-input,
.form-select,
.form-textarea {
    padding: 12px 15px;
    border: 1px solid rgba(0,0,0,0.15);
    background: #fafafa;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    color: #0a0a0a;
    transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-hint {
    font-size: 11px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

/* Input with Icon */
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    font-weight: 500;
    pointer-events: none;
}

.form-input.with-icon {
    padding-left: 35px;
}

/* File Upload */
.file-upload-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.file-input {
    display: none;
}

.file-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #0a0a0a;
    color: #ffffff;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-label:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.file-name {
    font-size: 12px;
    color: rgba(0,0,0,0.5);
    flex: 1;
}

/* Image Previews */
.gallery-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.gallery-preview-item {
    width: 100px;
    text-align: center;
}

.gallery-preview-item img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border: 1px solid #eee;
    border-radius: 4px;
}

.gallery-preview-item span {
    display: block;
    font-size: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 5px;
}

/* Main image preview */
.image-preview {
    max-width: 200px;
    margin-top: 10px;
    border: 1px solid #eee;
    border-radius: 4px;
    padding: 5px;
}

.preview-img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* Checkbox Group */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 15px;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fafafa;
    transition: all 0.3s ease;
}

.checkbox-label:hover {
    border-color: rgba(0,0,0,0.15);
    background: #ffffff;
}

.checkbox-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #0a0a0a;
    font-weight: 500;
}

.checkbox-text svg {
    opacity: 0.5;
}

.checkbox-input:checked + .checkbox-text svg {
    opacity: 1;
}

/* Gallery Grid */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.gallery-item {
    position: relative;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 4px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.gallery-item:hover {
    border-color: rgba(0,0,0,0.15);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.gallery-img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.gallery-placeholder {
    width: 100%;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    color: rgba(0,0,0,0.3);
}

.gallery-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    background: #fef2f2;
    color: #b91c1c;
    text-decoration: none;
    font-size: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-weight: 500;
    border-top: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.gallery-delete:hover {
    background: #b91c1c;
    color: #ffffff;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.empty-state svg {
    opacity: 0.2;
    margin-bottom: 15px;
}

.empty-state p {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 12px;
    padding: 30px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: #0a0a0a;
    color: #ffffff;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Montserrat', sans-serif;
}

.btn-primary:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: #fafafa;
    color: rgba(0,0,0,0.6);
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    border: 1px solid rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #ffffff;
    color: #0a0a0a;
    border-color: #0a0a0a;
}

/* Responsive Design */
@media (max-width: 768px) {
    .edit-page {
        padding: 80px 20px 50px;
    }

    .page-header {
        padding: 30px 25px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .page-title {
        font-size: 26px;
    }

    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .form-section {
        padding: 25px 20px;
    }

    .form-grid,
    .form-grid.three-col {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 18px;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .file-upload-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .file-label {
        justify-content: center;
    }

    .form-actions {
        flex-direction: column;
        padding: 25px 20px;
    }

    .btn-primary,
    .btn-cancel {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 22px;
    }

    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main image file input
    const mainImageInput = document.getElementById('mainImage');
    if (mainImageInput) {
        const mainFileLabel = mainImageInput.nextElementSibling;
        const mainFileName = mainFileLabel.nextElementSibling;
        
        mainImageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                mainFileName.textContent = this.files[0].name;
                
                // Show preview if it's an image
                const file = this.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.querySelector('.preview-img');
                        if (preview) {
                            preview.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                mainFileName.textContent = 'No file chosen';
            }
        });
    }

    // Gallery images file input
    const galleryInput = document.getElementById('galleryImages');
    if (galleryInput) {
        const galleryFileLabel = galleryInput.nextElementSibling;
        const galleryFileName = galleryFileLabel.nextElementSibling;
        
        galleryInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                if (this.files.length === 1) {
                    galleryFileName.textContent = this.files[0].name;
                } else {
                    galleryFileName.textContent = this.files.length + ' files selected';
                }
                
                // Show previews for selected images
                const galleryPreview = document.querySelector('.gallery-preview');
                if (galleryPreview) {
                    galleryPreview.innerHTML = ''; // Clear previous previews
                    
                    Array.from(this.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const preview = document.createElement('div');
                                preview.className = 'gallery-preview-item';
                                preview.innerHTML = `
                                    <img src="${e.target.result}" alt="Preview" />
                                    <span>${file.name}</span>
                                `;
                                galleryPreview.appendChild(preview);
                            }
                            reader.readAsDataURL(file);
                        }
                    });
                }
            } else {
                galleryFileName.textContent = 'No files chosen';
                const galleryPreview = document.querySelector('.gallery-preview');
                if (galleryPreview) {
                    galleryPreview.innerHTML = '';
                }
            }
        });
    }
});
</script>