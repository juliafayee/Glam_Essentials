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
$pageCss = 'admin.css';
include __DIR__ . '/../includes/header.php';

$cats = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name");
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="create-page">
    <div class="create-container">
        <?php include __DIR__ . '/../includes/alert.php'; ?>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="page-title">Add New Item</h1>
                    <p class="page-subtitle">Create a new product in your catalog</p>
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

        <!-- Create Form -->
        <form action="store.php" method="post" enctype="multipart/form-data" class="create-form">
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Basic Information</h2>
                    <p class="section-description">Essential product details</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Category *</label>
                        <select name="category_id" required class="form-select">
                            <option value="">Select category</option>
                            <?php while ($c = mysqli_fetch_assoc($cats)): ?>
                                <option value="<?= (int)$c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="product_name" required class="form-input" placeholder="Enter product name" />
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-textarea" placeholder="Describe your product..."></textarea>
                        <small class="form-hint">Optional but recommended for better product visibility</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price *</label>
                        <div class="input-with-icon">
                            <span class="input-icon">₱</span>
                            <input type="number" name="price" step="0.01" min="0" required class="form-input with-icon" placeholder="0.00" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Product Images</h2>
                    <p class="section-description">Upload high-quality images to showcase your product</p>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Main Image Name</label>
                        <input type="text" name="main_img_name" class="form-input" placeholder="e.g. keratin_treatment" />
                        <small class="form-hint">Base name without extension (e.g., "product_1"). Leave empty to auto-generate.</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Upload Main Image</label>
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
                        <small class="form-hint">Recommended: Square image, at least 800x800px</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Upload Gallery Images</label>
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
                        <small class="form-hint">You can select multiple images at once</small>
                    </div>
                </div>
            </div>

            <!-- Inventory Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Inventory Management</h2>
                    <p class="section-description">Set stock levels and tracking</p>
                </div>

                <div class="form-grid three-col">
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" min="0" value="0" required class="form-input" placeholder="0" />
                        <small class="form-hint">Initial stock</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" value="pcs" class="form-input" placeholder="pcs" />
                        <small class="form-hint">e.g., pcs, box, set</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" min="0" value="10" class="form-input" placeholder="10" />
                        <small class="form-hint">Alert threshold</small>
                    </div>
                </div>
            </div>

            <!-- Product Status Section -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Product Status</h2>
                    <p class="section-description">Configure visibility and promotion settings</p>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" class="checkbox-input" />
                        <span class="checkbox-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Featured Product
                        </span>
                        <small class="checkbox-hint">Display this product prominently on the homepage</small>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="is_available" value="1" checked class="checkbox-input" />
                        <span class="checkbox-text">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Available for Purchase
                        </span>
                        <small class="checkbox-hint">Allow customers to add this product to their cart</small>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Create Product
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

.create-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.create-container {
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
.create-form {
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
    margin-bottom: 5px;
}

.section-description {
    font-size: 12px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
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

.form-input::placeholder,
.form-textarea::placeholder {
    color: rgba(0,0,0,0.3);
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
    margin-top: 4px;
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
    flex-wrap: wrap;
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

/* Checkbox Group */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    cursor: pointer;
    padding: 20px;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fafafa;
    transition: all 0.3s ease;
}

.checkbox-label:hover {
    border-color: rgba(0,0,0,0.15);
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.checkbox-label:has(.checkbox-input:checked) {
    border-color: #0a0a0a;
    background: #ffffff;
}

.checkbox-input {
    display: none;
}

.checkbox-text {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #0a0a0a;
    font-weight: 500;
}

.checkbox-text::before {
    content: '';
    width: 20px;
    height: 20px;
    border: 2px solid rgba(0,0,0,0.2);
    border-radius: 2px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.checkbox-input:checked + .checkbox-text::before {
    background: #0a0a0a;
    border-color: #0a0a0a;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
}

.checkbox-text svg {
    opacity: 0.5;
}

.checkbox-input:checked + .checkbox-text svg {
    opacity: 1;
}

.checkbox-hint {
    font-size: 11px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
    margin-left: 30px;
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
    .create-page {
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

    .checkbox-hint {
        margin-left: 0;
    }
}
</style>

<script>
// File input handlers
document.addEventListener('DOMContentLoaded', function() {
    // Main image file input
    const mainImageInput = document.getElementById('mainImage');
    if (mainImageInput) {
        const mainFileLabel = mainImageInput.nextElementSibling;
        const mainFileName = mainFileLabel.nextElementSibling;
        
        mainImageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                mainFileName.textContent = this.files[0].name;
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
            } else {
                galleryFileName.textContent = 'No files chosen';
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>