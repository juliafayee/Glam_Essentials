<?php
ob_start();
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user data
$user_stmt = $conn->prepare("SELECT user_id, name, email, img_name, role, created_at, last_login FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Check if user data exists, redirect if not
if (!$user_data) {
    header("Location: login.php");
    exit;
}

// Set default values for missing fields
$user_data['name'] = $user_data['name'] ?? 'User';
$user_data['email'] = $user_data['email'] ?? '';
$user_data['img_name'] = $user_data['img_name'] ?? '';
$user_data['role'] = $user_data['role'] ?? 'customer';
$user_data['created_at'] = $user_data['created_at'] ?? date('Y-m-d H:i:s');
$user_data['last_login'] = $user_data['last_login'] ?? null;

// Ensure default profile image is set in DB for users without one
if (empty($user_data['img_name'])) {
	$defaultImg = 'nopfp.jpg';
	$upd = $conn->prepare("UPDATE users SET img_name = ? WHERE user_id = ?");
	$upd->bind_param("si", $defaultImg, $user_id);
	if ($upd->execute()) {
		$user_data['img_name'] = $defaultImg;
	}
	$upd->close();
}

// Fetch customer data if exists
$customer_stmt = $conn->prepare("SELECT customer_id, address, contact_no, town, zipcode FROM customers WHERE user_id = ?");
$customer_stmt->bind_param("i", $user_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer_data = $customer_result->fetch_assoc();
$customer_stmt->close();

// Fetch order statistics
$order_stats_stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent,
        COUNT(CASE WHEN o.order_status = 'Pending' THEN 1 END) as pending_orders,
        COUNT(CASE WHEN o.order_status = 'Delivered' THEN 1 END) as delivered_orders
    FROM orders o
    INNER JOIN customers c ON o.customer_id = c.customer_id
    WHERE c.user_id = ?
");
$order_stats_stmt->bind_param("i", $user_id);
$order_stats_stmt->execute();
$order_stats = $order_stats_stmt->get_result()->fetch_assoc();
$order_stats_stmt->close();

// Fetch recent orders
$recent_orders_stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.transaction_id,
        o.total_amount,
        o.order_status,
        o.payment_status,
        o.order_date,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    INNER JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE c.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$recent_orders_stmt->bind_param("i", $user_id);
$recent_orders_stmt->execute();
$recent_orders = $recent_orders_stmt->get_result();
$recent_orders_stmt->close();

// Fetch shopping cart count
$cart_stmt = $conn->prepare("SELECT COUNT(*) as cart_count FROM shopping_cart WHERE user_id = ?");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_data = $cart_result->fetch_assoc();
$cart_stmt->close();

// Fetch review count
$review_stmt = $conn->prepare("
    SELECT COUNT(*) as review_count 
    FROM reviews r
    INNER JOIN customers c ON r.customer_id = c.customer_id
    WHERE c.user_id = ?
");
$review_stmt->bind_param("i", $user_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
$review_data = $review_result->fetch_assoc();
$review_stmt->close();

// Set default values if no stats available
$order_stats['total_orders'] = $order_stats['total_orders'] ?? 0;
$order_stats['total_spent'] = $order_stats['total_spent'] ?? 0;
$order_stats['pending_orders'] = $order_stats['pending_orders'] ?? 0;
$order_stats['delivered_orders'] = $order_stats['delivered_orders'] ?? 0;

require_once('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="profile-page">
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar-section">
                <div class="avatar-wrapper">
                    <?php 
                    if (!empty($user_data['img_name'])) {
						$profile_pic = htmlspecialchars($baseUrl) . '/user/images/profile_pictures/' . htmlspecialchars($user_data['img_name']);
					} else {
						$profile_pic = htmlspecialchars($baseUrl) . '/user/images/profile_pictures/nopfp.jpg';
                    }
                    ?>
                    <img src="<?php echo $profile_pic; ?>" 
                         alt="Profile Picture" 
                         class="profile-avatar"
						 onerror="this.src='<?php echo htmlspecialchars($baseUrl); ?>/user/images/profile_pictures/nopfp.jpg';">
                    <div class="avatar-badge"><?php echo strtoupper(substr($user_data['name'], 0, 1)); ?></div>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($user_data['name']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user_data['email'] ?? 'Not provided'); ?></p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <?php echo ucfirst($user_data['role']); ?>
                        </span>
                        <span class="meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Member since <?php echo $user_data['created_at'] ? date('M Y', strtotime($user_data['created_at'])) : 'N/A'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="profile-actions">
                <a href="edit_profile.php" class="btn btn-edit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($order_stats['total_orders']); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">₱<?php echo number_format($order_stats['total_spent'], 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($order_stats['pending_orders']); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($order_stats['delivered_orders']); ?></div>
                    <div class="stat-label">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Contact Information -->
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">Contact Information</h2>
                    <a href="edit_profile.php" class="card-action">Edit</a>
                </div>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Email
                        </span>
                        <span class="info-value"><?php echo htmlspecialchars($user_data['email'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            Phone
                        </span>
                        <span class="info-value"><?php echo $customer_data && !empty($customer_data['contact_no']) ? htmlspecialchars($customer_data['contact_no']) : 'Not provided'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            Address
                        </span>
                        <span class="info-value"><?php echo $customer_data && !empty($customer_data['address']) ? htmlspecialchars($customer_data['address']) : 'Not provided'; ?></span>
                    </div>
                    <?php if ($customer_data && (!empty($customer_data['town']) || !empty($customer_data['zipcode']))): ?>
                    <div class="info-item">
                        <span class="info-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            City / Zip Code
                        </span>
                        <span class="info-value">
                            <?php 
                            $location_parts = array_filter([
                                !empty($customer_data['town']) ? htmlspecialchars($customer_data['town']) : '',
                                !empty($customer_data['zipcode']) ? htmlspecialchars($customer_data['zipcode']) : ''
                            ]);
                            echo !empty($location_parts) ? implode(', ', $location_parts) : 'Not provided';
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Activity -->
            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">Account Activity</h2>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-label">Items in Cart</div>
                            <div class="activity-value"><?php echo number_format($cart_data['cart_count']); ?></div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-label">Reviews Written</div>
                            <div class="activity-value"><?php echo number_format($review_data['review_count']); ?></div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-label">Last Login</div>
                            <div class="activity-value"><?php echo $user_data['last_login'] ? date('M d, Y', strtotime($user_data['last_login'])) : 'First login'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="orders-section">
            <div class="section-header">
                <h2 class="section-title">Recent Orders</h2>
                <a href="orders.php" class="section-link">View All Orders →</a>
            </div>

            <?php if ($recent_orders->num_rows > 0): ?>
                <div class="orders-table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="order-id">#<?php echo $order['order_id']; ?></span></td>
                                    <td><code class="transaction-id"><?php echo htmlspecialchars($order['transaction_id']); ?></code></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?></td>
                                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($order['payment_status']); ?>">
                                            <?php echo $order['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($order['order_status']); ?>">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <h3>No orders yet</h3>
                    <p>Start shopping to see your orders here</p>
                    <a href="../shop.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once('../includes/footer.php'); ?>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Remove body padding-top to prevent header conflicts - header is already fixed */
body {
  font-family: 'Montserrat', sans-serif;
  background: #ffffff;
  color: #1a1a1a;
  line-height: 1.6;
  /* padding-top removed - header is fixed and body padding is handled in style.css */
}

.profile-page {
  min-height: 100vh;
  padding: 100px 30px 60px;
  /* padding-top: 100px; */
  background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* ===== PROFILE HEADER ===== */
.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 30px;
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.08);
  padding: 40px;
  /* margin-top: 100px; */
  margin-bottom: 30px;
  transition: all 0.3s ease;
}

.profile-header:hover {
  border-color: rgba(0,0,0,0.12);
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.profile-avatar-section {
  display: flex;
  gap: 25px;
  align-items: center;
  flex: 1;
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

.profile-avatar:hover {
  border-color: rgba(0,0,0,0.2);
  transform: scale(1.05);
}

.avatar-badge {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 35px;
  height: 35px;
  background: #0a0a0a;
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 600;
  border: 3px solid #ffffff;
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  font-weight: 400;
  margin-bottom: 8px;
  color: #0a0a0a;
}

.profile-email {
  font-size: 14px;
  color: rgba(0,0,0,0.6);
  margin-bottom: 15px;
  letter-spacing: 0.3px;
}

.profile-meta {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  letter-spacing: 0.5px;
  color: rgba(0,0,0,0.5);
  text-transform: uppercase;
}

.meta-item svg {
  opacity: 0.5;
}

.profile-actions {
  flex-shrink: 0;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border: 1px solid rgba(0,0,0,0.15);
  background: transparent;
  color: #0a0a0a;
  text-transform: uppercase;
  font-size: 9px;
  letter-spacing: 2px;
  font-weight: 500;
  transition: all 0.3s ease;
  text-decoration: none;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}

.btn:hover {
  background: #0a0a0a;
  color: #ffffff;
  border-color: #0a0a0a;
}

.btn-edit svg {
  width: 14px;
  height: 14px;
}

/* ===== STATS GRID ===== */
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

.stat-icon {
  width: 50px;
  height: 50px;
  background: #fafafa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  opacity: 0.6;
}

.stat-content {
  flex: 1;
}

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

/* ===== CONTENT GRID ===== */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.info-card {
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.08);
  padding: 30px;
  transition: all 0.3s ease;
}

.info-card:hover {
  border-color: rgba(0,0,0,0.12);
  box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.card-title {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 400;
  color: #0a0a0a;
}

.card-action {
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.6);
  text-decoration: none;
  transition: color 0.3s ease;
}

.card-action:hover {
  color: #0a0a0a;
}

/* Info List */
.info-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.5);
  font-weight: 500;
}

.info-label svg {
  opacity: 0.5;
}

.info-value {
  font-size: 13px;
  color: #0a0a0a;
  letter-spacing: 0.3px;
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #fafafa;
  border-left: 3px solid #0a0a0a;
  transition: all 0.3s ease;
}

.activity-item:hover {
  background: #f5f5f5;
  border-left-width: 4px;
}

.activity-icon {
  width: 45px;
  height: 45px;
  background: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-label {
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.5);
  margin-bottom: 4px;
}

.activity-value {
  font-size: 16px;
  font-weight: 500;
  color: #0a0a0a;
}

/* ===== ORDERS SECTION ===== */
.orders-section {
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.08);
  padding: 40px;
  transition: all 0.3s ease;
}

.orders-section:hover {
  border-color: rgba(0,0,0,0.12);
  box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  font-weight: 400;
  color: #0a0a0a;
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

/* Orders Table */
.orders-table-wrapper {
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table thead th {
  text-align: left;
  padding: 15px 10px;
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.5);
  font-weight: 600;
  border-bottom: 2px solid rgba(0,0,0,0.08);
}

.orders-table tbody td {
  padding: 18px 10px;
  font-size: 13px;
  color: #0a0a0a;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  letter-spacing: 0.3px;
}

.orders-table tbody tr {
  transition: background 0.2s ease;
}

.orders-table tbody tr:hover {
  background: #fafafa;
}

.order-id {
  font-weight: 600;
  color: #0a0a0a;
}

.transaction-id {
  font-family: 'Courier New', monospace;
  font-size: 11px;
  background: #f5f5f5;
  padding: 4px 8px;
  border-radius: 3px;
  color: rgba(0,0,0,0.7);
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

.badge-refunded {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
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

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-state svg {
  opacity: 0.2;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  font-weight: 400;
  margin-bottom: 10px;
  color: #0a0a0a;
}

.empty-state p {
  font-size: 13px;
  color: rgba(0,0,0,0.5);
  margin-bottom: 25px;
  letter-spacing: 0.3px;
}

.btn-primary {
  background: #0a0a0a;
  color: #ffffff;
  border-color: #0a0a0a;
}

.btn-primary:hover {
  background: #2a2a2a;
  border-color: #2a2a2a;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .profile-page {
    padding: 80px 20px 50px;
  }

  .profile-header {
    flex-direction: column;
    padding: 30px 25px;
  }

  .profile-avatar-section {
    flex-direction: column;
    text-align: center;
    width: 100%;
  }

  .profile-avatar {
    width: 100px;
    height: 100px;
  }

  .avatar-badge {
    width: 30px;
    height: 30px;
    font-size: 14px;
  }

  .profile-name {
    font-size: 26px;
  }

  .profile-meta {
    justify-content: center;
  }

  .profile-actions {
    width: 100%;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .stat-card {
    padding: 25px;
  }

  .content-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .info-card {
    padding: 25px;
  }

  .orders-section {
    padding: 30px 20px;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .orders-table-wrapper {
    margin: 0 -20px;
    padding: 0 20px;
  }

  .orders-table {
    font-size: 12px;
  }

  .orders-table thead th,
  .orders-table tbody td {
    padding: 12px 8px;
  }
}

@media (max-width: 480px) {
  .profile-name {
    font-size: 22px;
  }

  .profile-email {
    font-size: 12px;
  }

  .stat-value {
    font-size: 20px;
  }

  .section-title {
    font-size: 20px;
  }

  .orders-table {
    font-size: 11px;
  }

  .orders-table thead th {
    font-size: 8px;
  }

  .transaction-id {
    font-size: 10px;
    padding: 3px 6px;
  }
}
</style>

<?php ob_end_flush(); ?>