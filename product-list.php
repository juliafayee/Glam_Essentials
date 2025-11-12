<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = 'product-list.css';
include __DIR__ . '/includes/header.php';

// Get filter and sort parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch all categories for filter
$categories_sql = "SELECT category_id, category_name FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_sql);

// Build the main query
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_available = 1";

// Add category filter
if ($category_filter > 0) {
    $sql .= " AND p.category_id = " . $category_filter;
}

// Add search filter
if (!empty($search_query)) {
    $search_escaped = mysqli_real_escape_string($conn, $search_query);
    $sql .= " AND (p.product_name LIKE '%$search_escaped%' OR p.description LIKE '%$search_escaped%' OR c.category_name LIKE '%$search_escaped%')";
}

// Add sorting
switch ($sort_by) {
    case 'name_asc':
        $sql .= " ORDER BY p.product_name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.product_name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'latest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    default:
        $sql .= " ORDER BY p.product_name ASC";
}

$result = mysqli_query($conn, $sql);
$total_products = mysqli_num_rows($result);
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="products-page">
    <div class="products-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1 class="page-title">Our Products</h1>
                <p class="page-subtitle">Discover our premium collection of beauty essentials</p>
            </div>
            <div class="results-count">
                <span><?php echo $total_products; ?></span> Product<?php echo $total_products != 1 ? 's' : ''; ?> Found
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="filters-section">
            <!-- Search Bar -->
            <div class="search-wrapper">
                <form method="GET" action="" class="search-form" id="searchForm">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input 
                            type="text" 
                            name="search" 
                            id="searchInput"
                            class="search-input" 
                            placeholder="Search products..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                        >
                        <?php if (!empty($search_query)): ?>
                        <button type="button" class="clear-search" onclick="clearSearch()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                </form>
            </div>

            <!-- Filter and Sort Controls -->
            <div class="controls-wrapper">
                <!-- Category Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Category
                    </label>
                    <select name="category" class="filter-select" onchange="applyFilters()">
                        <option value="0" <?php echo $category_filter == 0 ? 'selected' : ''; ?>>All Categories</option>
                        <?php 
                        mysqli_data_seek($categories_result, 0);
                        while ($category = mysqli_fetch_assoc($categories_result)): 
                        ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="filter-group">
                    <label class="filter-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="16" y2="12"/><line x1="4" y1="18" x2="12" y2="18"/>
                        </svg>
                        Sort By
                    </label>
                    <select name="sort" class="filter-select" onchange="applyFilters()">
                        <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="latest" <?php echo $sort_by == 'latest' ? 'selected' : ''; ?>>Latest</option>
                        <option value="oldest" <?php echo $sort_by == 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <?php if ($category_filter > 0 || !empty($search_query) || $sort_by != 'name_asc'): ?>
                <button type="button" class="clear-filters-btn" onclick="clearFilters()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Clear Filters
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Filters Display -->
        <?php if ($category_filter > 0 || !empty($search_query)): ?>
        <div class="active-filters">
            <span class="active-filters-label">Active Filters:</span>
            <?php if (!empty($search_query)): ?>
            <span class="filter-tag">
                Search: "<?php echo htmlspecialchars($search_query); ?>"
                <button onclick="removeFilter('search')" class="remove-tag">×</button>
            </span>
            <?php endif; ?>
            <?php if ($category_filter > 0): 
                // Get category name
                mysqli_data_seek($categories_result, 0);
                while ($cat = mysqli_fetch_assoc($categories_result)) {
                    if ($cat['category_id'] == $category_filter) {
                        $selected_category_name = $cat['category_name'];
                        break;
                    }
                }
            ?>
            <span class="filter-tag">
                Category: <?php echo htmlspecialchars($selected_category_name); ?>
                <button onclick="removeFilter('category')" class="remove-tag">×</button>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): 
                    // Get product image
                    $imgPath = '/assets/default.png';
                    if (!empty($product['main_img_name'])) {
                        $imgBase = $product['main_img_name'];
                        $extensions = ['.jpg', '.png', '.webp'];
                        foreach ($extensions as $ext) {
                            $fullPath = __DIR__ . '/item/products/' . $imgBase . $ext;
                            if (file_exists($fullPath)) {
                                $imgPath = '/item/products/' . $imgBase . $ext;
                                break;
                            }
                        }
                    }
                ?>
                    <div class="product-card">
                        <a href="<?php echo $baseUrl; ?>/products.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                            <div class="product-image-wrapper">
                                <img src="<?php echo $baseUrl . $imgPath; ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     class="product-image"
                                     onerror="this.src='<?php echo $baseUrl; ?>/assets/default.png'">
                                <?php if ($product['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                <?php if (!empty($product['description'])): ?>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <span class="view-details">
                                    View Details
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <button onclick="clearFilters()" class="btn btn-primary">Clear All Filters</button>
                </div>
            <?php endif; ?>
        </div>
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

.products-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.products-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.page-header:hover {
    border-color: rgba(0,0,0,0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
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

.results-count {
    font-size: 11px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
}

.results-count span {
    font-weight: 600;
    color: #0a0a0a;
    font-size: 16px;
}

/* Filters Section */
.filters-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 30px;
}

.search-wrapper {
    margin-bottom: 25px;
}

.search-form {
    width: 100%;
}

.search-input-wrapper {
    position: relative;
    width: 100%;
}

.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(0,0,0,0.4);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 14px 50px 14px 50px;
    border: 1px solid rgba(0,0,0,0.15);
    background: #fafafa;
    font-size: 14px;
    font-family: 'Montserrat', sans-serif;
    color: #0a0a0a;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #ffffff;
}

.search-input::placeholder {
    color: rgba(0,0,0,0.3);
}

.clear-search {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: rgba(0,0,0,0.4);
    transition: color 0.3s ease;
}

.clear-search:hover {
    color: #0a0a0a;
}

.controls-wrapper {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    min-width: 200px;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    font-weight: 500;
}

.filter-select {
    padding: 12px 15px;
    border: 1px solid rgba(0,0,0,0.15);
    background: #ffffff;
    font-size: 13px;
    font-family: 'Montserrat', sans-serif;
    color: #0a0a0a;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #0a0a0a;
}

.clear-filters-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: 1px solid rgba(0,0,0,0.15);
    background: transparent;
    color: #0a0a0a;
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
}

.clear-filters-btn:hover {
    background: #0a0a0a;
    color: #ffffff;
    border-color: #0a0a0a;
}

/* Active Filters */
.active-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px 25px;
    background: #fafafa;
    border-left: 3px solid #0a0a0a;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.active-filters-label {
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    font-weight: 600;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.1);
    font-size: 11px;
    color: #0a0a0a;
}

.remove-tag {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    color: rgba(0,0,0,0.5);
    padding: 0;
    margin-left: 4px;
    transition: color 0.3s ease;
}

.remove-tag:hover {
    color: #0a0a0a;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.product-card {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    border-color: rgba(0,0,0,0.2);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transform: translateY(-5px);
}

.product-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-image-wrapper {
    position: relative;
    width: 100%;
    height: 280px;
    overflow: hidden;
    background: #fafafa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #0a0a0a;
    color: #ffffff;
    padding: 6px 12px;
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
}

.product-info {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-category {
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
    font-weight: 500;
}

.product-name {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 400;
    color: #0a0a0a;
    margin: 0;
    line-height: 1.3;
}

.product-price {
    font-size: 16px;
    font-weight: 600;
    color: #0a0a0a;
    margin: 5px 0;
}

.product-description {
    font-size: 12px;
    color: rgba(0,0,0,0.6);
    line-height: 1.5;
    margin-top: 5px;
}

.product-actions {
    padding: 0 20px 20px;
    border-top: 1px solid rgba(0,0,0,0.05);
    padding-top: 15px;
    margin-top: auto;
}

.view-details {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    font-weight: 500;
    transition: color 0.3s ease;
}

.product-card:hover .view-details {
    color: #0a0a0a;
}

.view-details svg {
    transition: transform 0.3s ease;
}

.product-card:hover .view-details svg {
    transform: translateX(5px);
}

/* No Products State */
.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
}

.no-products svg {
    opacity: 0.2;
    margin-bottom: 20px;
}

.no-products h3 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 400;
    margin-bottom: 10px;
    color: #0a0a0a;
}

.no-products p {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    margin-bottom: 25px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border: 1px solid;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 2px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
}

.btn-primary {
    background: #0a0a0a;
    border-color: #0a0a0a;
    color: #ffffff;
}

.btn-primary:hover {
    background: #2a2a2a;
    border-color: #2a2a2a;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .products-page {
        padding: 80px 20px 50px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        padding: 30px 25px;
    }

    .page-title {
        font-size: 26px;
    }

    .filters-section {
        padding: 25px 20px;
    }

    .controls-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        min-width: 100%;
    }

    .clear-filters-btn {
        width: 100%;
        justify-content: center;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 22px;
    }

    .products-grid {
        grid-template-columns: 1fr;
    }

    .product-image-wrapper {
        height: 250px;
    }
}
</style>

<script>
// Apply filters when dropdown changes
function applyFilters() {
    const category = document.querySelector('select[name="category"]').value;
    const sort = document.querySelector('select[name="sort"]').value;
    const search = document.getElementById('searchInput').value;
    
    let url = window.location.pathname + '?';
    const params = [];
    
    if (category && category !== '0') {
        params.push('category=' + category);
    }
    if (sort && sort !== 'name_asc') {
        params.push('sort=' + sort);
    }
    if (search) {
        params.push('search=' + encodeURIComponent(search));
    }
    
    url += params.join('&');
    window.location.href = url;
}

// Clear all filters
function clearFilters() {
    window.location.href = window.location.pathname;
}

// Remove specific filter
function removeFilter(filterType) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete(filterType);
    
    let url = window.location.pathname;
    if (urlParams.toString()) {
        url += '?' + urlParams.toString();
    }
    window.location.href = url;
}

// Clear search
function clearSearch() {
    document.getElementById('searchInput').value = '';
    applyFilters();
}

// Real-time search with debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Prevent form submission on Enter (handled by real-time search)
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    applyFilters();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>