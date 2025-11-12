<?php
ob_start();
session_start();
require_once('../includes/config.php');

// Check if user is logged in and is admin/staff
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to access the dashboard.";
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user role
$user_stmt = $conn->prepare("SELECT role, name, email, img_name FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Check if user has admin or staff role
if (!in_array($user_data['role'], ['admin', 'staff'])) {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../index.php");
    exit;
}

// Set default profile image
if (empty($user_data['img_name'])) {
    $user_data['img_name'] = 'nopfp.jpg';
}

// Fetch dashboard statistics
// Total Products
$total_products_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE is_available = 1");
$total_products_stmt->execute();
$total_products = $total_products_stmt->get_result()->fetch_assoc()['total'];
$total_products_stmt->close();

// Total Orders
$total_orders_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$total_orders_stmt->execute();
$total_orders = $total_orders_stmt->get_result()->fetch_assoc()['total'];
$total_orders_stmt->close();

// Total Revenue
$total_revenue_stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE payment_status = 'Paid'");
$total_revenue_stmt->execute();
$total_revenue = $total_revenue_stmt->get_result()->fetch_assoc()['revenue'];
$total_revenue_stmt->close();

// Total Users
$total_users_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND is_active = 1");
$total_users_stmt->execute();
$total_users = $total_users_stmt->get_result()->fetch_assoc()['total'];
$total_users_stmt->close();

// Pending Orders
$pending_orders_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE order_status = 'Pending'");
$pending_orders_stmt->execute();
$pending_orders = $pending_orders_stmt->get_result()->fetch_assoc()['total'];
$pending_orders_stmt->close();

// Low Stock Products
$low_stock_stmt = $conn->prepare("SELECT COUNT(*) as total FROM inventory WHERE quantity < reorder_level");
$low_stock_stmt->execute();
$low_stock = $low_stock_stmt->get_result()->fetch_assoc()['total'];
$low_stock_stmt->close();

// Recent Orders (Last 10)
$recent_orders_stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.transaction_id,
        o.total_amount,
        o.order_status,
        o.payment_status,
        o.order_date,
        c.fullname as customer_name,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    INNER JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT 10
");
$recent_orders_stmt->execute();
$recent_orders = $recent_orders_stmt->get_result();
$recent_orders_stmt->close();

// Low Stock Products Details
$low_stock_products_stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.product_name,
        i.quantity,
        i.reorder_level,
        p.price
    FROM inventory i
    INNER JOIN products p ON i.product_id = p.product_id
    WHERE i.quantity < i.reorder_level
    ORDER BY i.quantity ASC
    LIMIT 5
");
$low_stock_products_stmt->execute();
$low_stock_products = $low_stock_products_stmt->get_result();
$low_stock_products_stmt->close();

// Recent Reviews
$recent_reviews_stmt = $conn->prepare("
    SELECT 
        r.review_id,
        r.rating,
        r.review_text,
        r.created_at,
        p.product_name,
        c.fullname as customer_name
    FROM reviews r
    INNER JOIN products p ON r.product_id = p.product_id
    INNER JOIN customers c ON r.customer_id = c.customer_id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recent_reviews_stmt->execute();
$recent_reviews = $recent_reviews_stmt->get_result();
$recent_reviews_stmt->close();

require_once('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="dashboard-page">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="dashboard-title">Dashboard</h1>
                    <p class="dashboard-subtitle">Welcome back, <?php echo htmlspecialchars($user_data['name']); ?></p>
                </div>
                <div class="header-actions">
                    <span class="role-badge"><?php echo ucfirst($user_data['role']); ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_products); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-info">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">₱<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-secondary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>

            <div class="stat-card stat-card-highlight">
                <div class="stat-icon stat-icon-warning">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($pending_orders); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>

            <div class="stat-card stat-card-highlight">
                <div class="stat-icon stat-icon-danger">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($low_stock); ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">Quick Actions</h2>
            <div class="actions-grid">
                <a href="products.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                        </svg>
                    </div>
                    <div class="action-label">Manage Products</div>
                </a>

                <a href="orders.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                    </div>
                    <div class="action-label">View Orders</div>
                </a>

                <a href="users.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="action-label">Manage Users</div>
                </a>

                <a href="inventory.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <div class="action-label">Check Inventory</div>
                </a>

                <a href="categories.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                    </div>
                    <div class="action-label">Categories</div>
                </a>

                <a href="reviews.php" class="action-card">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <div class="action-label">Reviews</div>
                </a>

                <?php if ($user_data['role'] === 'admin'): ?>
                <a href="add_admin.php" class="action-card action-card-highlight">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </div>
                    <div class="action-label">Add Admin</div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Recent Orders Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Orders</h2>
                    <a href="orders.php" class="section-link">View All →</a>
                </div>

                <?php if ($recent_orders->num_rows > 0): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><span class="order-id">#<?php echo $order['order_id']; ?></span></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?></td>
                                        <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($order['order_status']); ?>">
                                                <?php echo $order['order_status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <p>No orders yet</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Low Stock Products Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Low Stock Alert</h2>
                    <a href="inventory.php" class="section-link">View All →</a>
                </div>

                <?php if ($low_stock_products->num_rows > 0): ?>
                    <div class="stock-list">
                        <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                            <div class="stock-item">
                                <div class="stock-info">
                                    <div class="stock-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                    <div class="stock-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                                <div class="stock-status">
                                    <span class="stock-quantity <?php echo $product['quantity'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                        <?php echo $product['quantity']; ?> left
                                    </span>
                                    <span class="stock-reorder">Reorder: <?php echo $product['reorder_level']; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        <p>All products are well stocked</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reviews Section -->
        <?php if ($recent_reviews->num_rows > 0): ?>
        <div class="reviews-section">
            <div class="section-header">
                <h2 class="section-title">Recent Reviews</h2>
                <a href="reviews.php" class="section-link">View All →</a>
            </div>

            <div class="reviews-grid">
                <?php while ($review = $recent_reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? '#0a0a0a' : 'none'; ?>" stroke="#0a0a0a" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                        </div>
                        <div class="review-product"><?php echo htmlspecialchars($review['product_name']); ?></div>
                        <div class="review-text"><?php echo htmlspecialchars(substr($review['review_text'], 0, 100)) . (strlen($review['review_text']) > 100 ? '...' : ''); ?></div>
                        <div class="review-customer">— <?php echo htmlspecialchars($review['customer_name']); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once('../includes/footer.php'); ?>

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

.dashboard-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Dashboard Header */
.dashboard-header {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
    transition: all 0.3s ease;
}

.dashboard-header:hover {
    border-color: rgba(0,0,0,0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 400;
    margin-bottom: 5px;
    color: #0a0a0a;
}

.dashboard-subtitle {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

.role-badge {
    display: inline-block;
    padding: 8px 20px;
    background: #0a0a0a;
    color: #ffffff;
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-weight: 500;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: rgba(0,0,0,0.15);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}

.stat-card-highlight {
    border-left: 3px solid #0a0a0a;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-primary { background: #f0f9ff; color: #1e40af; }
.stat-icon-success { background: #f0fdf4; color: #166534; }
.stat-icon-info { background: #faf5ff; color: #7c3aed; }
.stat-icon-secondary { background: #fafafa; color: #525252; }
.stat-icon-warning { background: #fff7ed; color: #c2410c; }
.stat-icon-danger { background: #fef2f2; color: #b91c1c; }

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #0a0a0a;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
}

/* Quick Actions */
.quick-actions {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 400;
    color: #0a0a0a;
    margin-bottom: 25px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    padding: 25px 15px;
    border: 1px solid rgba(0,0,0,0.08);
    text-decoration: none;
    color: #0a0a0a;
    transition: all 0.3s ease;
}

.action-card:hover {
    border-color: #0a0a0a;
    background: #fafafa;
    transform: translateY(-2px);
}

.action-card-highlight {
    border: 2px solid #0a0a0a;
    background: #fafafa;
}

.action-card-highlight:hover {
    background: #0a0a0a;
    color: #ffffff;
}

.action-card-highlight .action-icon {
    background: #0a0a0a;
    color: #ffffff;
}

.action-card-highlight:hover .action-icon {
    background: #ffffff;
    color: #0a0a0a;
}

.action-icon {
    width: 50px;
    height: 50px;
    background: #fafafa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-card:hover .action-icon {
    background: #0a0a0a;
    color: #ffffff;
}

.action-label {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    text-align: center;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.content-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.section-link {
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    text-decoration: none;
    transition: color 0.3s ease;
}

.section-link:hover {
    color: #0a0a0a;
}

/* Table */
.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    text-align: left;
    padding: 12px 10px;
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.5);
    font-weight: 600;
    border-bottom: 2px solid rgba(0,0,0,0.08);
}

.data-table tbody td {
    padding: 15px 10px;
    font-size: 13px;
    color: #0a0a0a;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    letter-spacing: 0.3px;
}

.data-table tbody tr {
    transition: background 0.2s ease;
}

.data-table tbody tr:hover {
    background: #fafafa;
}

.order-id {
    font-weight: 600;
    color: #0a0a0a;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 5px 12px;
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 600;
    border-radius: 2px;
}

.badge-pending {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}

.badge-paid {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.badge-shipped {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.badge-delivered {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.badge-cancelled {
    background: #fafafa;
    color: #525252;
    border: 1px solid #e5e5e5;
}

/* Stock List */
.stock-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #fafafa;
    border-left: 3px solid #0a0a0a;
    transition: all 0.3s ease;
}

.stock-item:hover {
    background: #f5f5f5;
}

.stock-info {
    flex: 1;
}

.stock-name {
    font-size: 13px;
    font-weight: 500;
    color: #0a0a0a;
    margin-bottom: 4px;
}

.stock-price {
    font-size: 11px;
    color: rgba(0,0,0,0.5);
}

.stock-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.stock-quantity {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 2px;
}

.low-stock {
    background: #fff7ed;
    color: #c2410c;
}

.out-of-stock {
    background: #fef2f2;
    color: #b91c1c;
}

.stock-reorder {
    font-size: 9px;
    color: rgba(0,0,0,0.4);
    letter-spacing: 0.5px;
}

/* Reviews Section */
.reviews-section {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.review-card {
    padding: 25px;
    border: 1px solid rgba(0,0,0,0.08);
    background: #fafafa;
    transition: all 0.3s ease;
}

.review-card:hover {
    border-color: rgba(0,0,0,0.15);
    background: #ffffff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.review-rating {
    display: flex;
    gap: 3px;
}

.review-date {
    font-size: 9px;
    color: rgba(0,0,0,0.4);
    letter-spacing: 0.5px;
}

.review-product {
    font-size: 12px;
    font-weight: 600;
    color: #0a0a0a;
    margin-bottom: 10px;
    letter-spacing: 0.3px;
}

.review-text {
    font-size: 12px;
    color: rgba(0,0,0,0.7);
    line-height: 1.6;
    margin-bottom: 12px;
}

.review-customer {
    font-size: 10px;
    color: rgba(0,0,0,0.5);
    font-style: italic;
    letter-spacing: 0.3px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state svg {
    opacity: 0.2;
    margin-bottom: 15px;
}

.empty-state p {
    font-size: 12px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-page {
        padding: 80px 20px 50px;
    }

    .dashboard-header {
        padding: 30px 25px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .dashboard-title {
        font-size: 26px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
    }

    .stat-value {
        font-size: 20px;
    }

    .quick-actions {
        padding: 30px 20px;
    }

    .actions-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .action-card {
        padding: 20px 10px;
    }

    .action-icon {
        width: 40px;
        height: 40px;
    }

    .action-label {
        font-size: 9px;
    }

    .content-section {
        padding: 25px 20px;
    }

    .reviews-grid {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 20px;
    }

    .table-wrapper {
        margin: 0 -20px;
        padding: 0 20px;
    }

    .data-table {
        font-size: 11px;
    }

    .data-table thead th,
    .data-table tbody td {
        padding: 10px 8px;
    }
}

@media (max-width: 480px) {
    .dashboard-title {
        font-size: 22px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .stock-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .stock-status {
        align-items: flex-start;
        width: 100%;
    }

    .data-table thead th {
        font-size: 8px;
    }

    .data-table tbody td {
        font-size: 11px;
    }
}
</style>

<?php ob_end_flush(); ?>    