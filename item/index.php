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

$pageCss = $baseUrl . '/includes/style/admin.css';
include __DIR__ . '/../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="items-page">
    <div class="items-container">
        <?php include __DIR__ . '/../includes/alert.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="page-title">Manage Items</h1>
                    <p class="page-subtitle">View and manage your product catalog</p>
                </div>
                <div class="header-actions">
                    <a href="<?php echo $baseUrl; ?>/item/create.php" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add New Item
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section">
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
            </div>

            <div class="filter-controls">
                <div class="filter-group">
                    <label for="categoryFilter">Category</label>
                    <select id="categoryFilter" class="filter-select">
                        <option value="all">All Categories</option>
                        <?php
                        $cat_sql = "SELECT DISTINCT category_name FROM categories ORDER BY category_name";
                        $cat_result = mysqli_query($conn, $cat_sql);
                        while ($cat = mysqli_fetch_assoc($cat_result)) {
                            echo '<option value="' . htmlspecialchars($cat['category_name']) . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sortFilter">Sort By</label>
                    <select id="sortFilter" class="filter-select">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                    </select>
                </div>

                <button id="resetFilters" class="btn-reset">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="1 4 1 10 7 10"/><polyline points="23 20 23 14 17 14"/>
                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        <?php
        // Fetch products with categories, inventory qty, and main image name
        $sql = "SELECT p.product_id, p.product_name, p.price, p.is_available, p.is_featured, p.main_img_name, c.category_name, 
                       COALESCE(i.quantity, 0) AS quantity, COALESCE(i.unit, 'pcs') AS unit
                FROM products p
                JOIN categories c ON c.category_id = p.category_id
                LEFT JOIN inventory i ON i.product_id = p.product_id
                ORDER BY p.product_id DESC";
        $result = mysqli_query($conn, $sql);
        ?>

        <!-- Items Table Section -->
        <div class="table-section">
            <div class="table-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Inventory</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                              // Handle image display
                              $imgTag = '<div class="product-img-placeholder"></div>';
                              $imgBase = trim($row['main_img_name'] ?? '');
                              if ($imgBase !== '') {
                                $dir = __DIR__ . '/products/';
                                $webBase = '/GlamEssentials/item/products/';
                                $exts = ['.jpg','.png','.webp','.jpeg'];
                                foreach ($exts as $ext) {
                                  $fs = $dir . $imgBase . $ext;
                                  if (file_exists($fs)) { 
                                    $imgTag = '<img src="'.$webBase.$imgBase.$ext.'" alt="'.htmlspecialchars($row['product_name']).'" class="product-img" />'; 
                                    break; 
                                  }
                                }
                              }
                            ?>
                            <tr class="product-row" 
                                data-category="<?= htmlspecialchars($row['category_name']) ?>"
                                data-price="<?= (float)$row['price'] ?>"
                                data-name="<?= htmlspecialchars($row['product_name']) ?>"
                                data-id="<?= (int)$row['product_id'] ?>">
                                <td><?= $imgTag ?></td>
                                <td><span class="product-id">#<?= (int)$row['product_id'] ?></span></td>
                                <td><strong><?= htmlspecialchars($row['product_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><strong>₱<?= number_format((float)$row['price'], 2) ?></strong></td>
                                <td>
                                    <span class="inventory-badge <?= (int)$row['quantity'] < 10 ? 'low-stock' : '' ?>">
                                        <?= (int)$row['quantity'] . ' ' . htmlspecialchars($row['unit']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="status-badges">
                                        <?php if ($row['is_featured']): ?>
                                            <span class="badge badge-featured">Featured</span>
                                        <?php endif; ?>
                                        <span class="badge badge-<?= $row['is_available'] ? 'available' : 'unavailable' ?>">
                                            <?= $row['is_available'] ? 'Available' : 'Unavailable' ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= $baseUrl ?>/item/edit.php?id=<?= (int)$row['product_id'] ?>" class="btn-action btn-edit" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="<?= $baseUrl ?>/item/delete.php?id=<?= (int)$row['product_id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this item?');" 
                                           class="btn-action btn-delete" 
                                           title="Delete">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state-cell">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                                    </svg>
                                    <p>No items found</p>
                                    <a href="<?php echo $baseUrl; ?>/item/create.php" class="btn-secondary">Add Your First Item</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="no-results" style="display: none;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <p>No products match your filters</p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    const resetBtn = document.getElementById('resetFilters');
    const tableBody = document.querySelector('.items-table tbody');
    const noResults = document.getElementById('noResults');
    
    let allRows = Array.from(document.querySelectorAll('.product-row'));
    
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedCategory = categoryFilter.value;
        const sortBy = sortFilter.value;
        
        // Filter rows
        let visibleRows = allRows.filter(row => {
            const productName = row.dataset.name.toLowerCase();
            const productId = row.dataset.id;
            const category = row.dataset.category;
            
            // Search filter
            const matchesSearch = searchTerm === '' || 
                                 productName.includes(searchTerm) || 
                                 productId.includes(searchTerm);
            
            // Category filter
            const matchesCategory = selectedCategory === 'all' || category === selectedCategory;
            
            return matchesSearch && matchesCategory;
        });
        
        // Sort rows
       // Update the sort function in your existing JavaScript
        visibleRows.sort((a, b) => {
            switch(sortBy) {
                case 'latest':
                    return parseInt(b.dataset.id) - parseInt(a.dataset.id);
                case 'oldest':
                    return parseInt(a.dataset.id) - parseInt(b.dataset.id);
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'name-asc':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'name-desc':
                    return b.dataset.name.localeCompare(a.dataset.name);
                default:
                    return 0;
            }
        });
        
        // Update display
        allRows.forEach(row => row.style.display = 'none');
        
        if (visibleRows.length > 0) {
            visibleRows.forEach(row => {
                row.style.display = '';
                tableBody.appendChild(row); // Re-append in sorted order
            });
            noResults.style.display = 'none';
        } else {
            noResults.style.display = 'flex';
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterAndSort);
    categoryFilter.addEventListener('change', filterAndSort);
    sortFilter.addEventListener('change', filterAndSort);
    
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = 'all';
        sortFilter.value = 'latest';
        filterAndSort();
    });
    
    // Initial sort
    filterAndSort();
});
</script>

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

.items-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.items-container {
    max-width: 1400px;
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

/* Filters Section */
.filters-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 25px 30px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
    display: flex;
    align-items: center;
}

.search-box svg {
    position: absolute;
    left: 15px;
    color: rgba(0,0,0,0.4);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 1px solid rgba(0,0,0,0.15);
    background: #fafafa;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    color: #0a0a0a;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.search-input::placeholder {
    color: rgba(0,0,0,0.4);
}

.filter-controls {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 180px;
}

.filter-group label {
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
    font-weight: 600;
}

.filter-select {
    padding: 12px 35px 12px 15px;  /* Added more right padding for the arrow */
    border: 1px solid rgba(0,0,0,0.15);
    background: #fafafa;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    color: #0a0a0a;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(0,0,0,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    border-radius: 4px;
}

.filter-select:hover {
    border-color: rgba(0,0,0,0.25);
}

.filter-select:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn-reset {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #ffffff;
    color: rgba(0,0,0,0.6);
    border: 1px solid rgba(0,0,0,0.15);
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Montserrat', sans-serif;
}

.btn-reset:hover {
    background: #0a0a0a;
    color: #ffffff;
    border-color: #0a0a0a;
}

/* Buttons */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #0a0a0a;
    color: #ffffff;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-secondary {
    display: inline-block;
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
    margin-top: 15px;
}

.btn-secondary:hover {
    background: #0a0a0a;
    color: #ffffff;
    border-color: #0a0a0a;
}

/* Table Section */
.table-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 30px;
}

.table-wrapper {
    overflow-x: auto;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table thead th {
    text-align: left;
    padding: 15px 12px;
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
    font-weight: 600;
    border-bottom: 2px solid rgba(0,0,0,0.08);
    background: #fafafa;
}

.items-table tbody td {
    padding: 18px 12px;
    font-size: 13px;
    color: #0a0a0a;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    vertical-align: middle;
}

.items-table tbody tr {
    transition: all 0.2s ease;
}

.items-table tbody tr:hover {
    background: #fafafa;
}

/* Product Image */
.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.08);
}

.product-img-placeholder {
    width: 50px;
    height: 50px;
    background: #f5f5f5;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 4px;
}

.product-id {
    font-weight: 600;
    color: rgba(0,0,0,0.6);
    font-size: 12px;
}

/* Inventory Badge */
.inventory-badge {
    display: inline-block;
    padding: 5px 12px;
    background: #f0fdf4;
    color: #166534;
    font-size: 11px;
    font-weight: 500;
    border-radius: 2px;
    border: 1px solid #bbf7d0;
}

.inventory-badge.low-stock {
    background: #fef2f2;
    color: #b91c1c;
    border-color: #fecaca;
}

/* Status Badges */
.status-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 600;
    border-radius: 2px;
}

.badge-featured {
    background: #0a0a0a;
    color: #ffffff;
}

.badge-available {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.badge-unavailable {
    background: #fafafa;
    color: #737373;
    border: 1px solid #e5e5e5;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    font-size: 10px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 2px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-edit {
    background: #f0f9ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.btn-edit:hover {
    background: #1e40af;
    color: #ffffff;
    border-color: #1e40af;
}

.btn-delete {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.btn-delete:hover {
    background: #b91c1c;
    color: #ffffff;
    border-color: #b91c1c;
}

/* Empty State */
.empty-state-cell {
    padding: 60px 20px !important;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
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

/* No Results State */
.no-results {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.no-results svg {
    opacity: 0.2;
    margin-bottom: 15px;
}

.no-results p {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .items-table {
        font-size: 12px;
    }

    .items-table thead th,
    .items-table tbody td {
        padding: 12px 10px;
    }

    .action-buttons {
        flex-direction: column;
    }
}

@media (max-width: 768px) {
    .items-page {
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

    .filters-section {
        padding: 20px;
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-controls {
        flex-direction: column;
        width: 100%;
    }

    .filter-group {
        min-width: 100%;
    }

    .btn-reset {
        width: 100%;
        justify-content: center;
    }

    .table-section {
        padding: 20px 15px;
    }

    .table-wrapper {
        margin: 0 -15px;
        padding: 0 15px;
    }

    .items-table thead th {
        font-size: 8px;
        padding: 10px 8px;
    }

    .items-table tbody td {
        padding: 12px 8px;
        font-size: 11px;
    }

    .product-img,
    .product-img-placeholder {
        width: 40px;
        height: 40px;
    }

    .btn-action {
        padding: 6px 10px;
        font-size: 9px;
    }

    .btn-action svg {
        width: 12px;
        height: 12px;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 22px;
    }

    .btn-primary {
        width: 100%;
        justify-content: center;
    }

    .items-table {
        font-size: 10px;
    }

    .status-badges {
        flex-direction: column;
    }

    .action-buttons {
        flex-direction: column;
        width: 100%;
    }

    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>