<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/config.php';

if (!isset($baseUrl)) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $baseUrl = str_replace($docRoot, '', $projectRoot);
    if ($baseUrl === '') $baseUrl = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlamEssentials - Professional Salon Supplies</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/includes/style/style.css">
    <?php if (!empty($pageCss)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/includes/style/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
    <script src="<?= htmlspecialchars($baseUrl) ?>/script.js" defer></script>
</head>
<body>

<!-- 🌸 Top Banner -->
<div class="top-banner">
    <div class="banner-content">
        <span class="banner-text">SIGN UP NOW TO START SHOPPING</span>
        <span class="banner-text">EXCLUSIVE DEALS OFFERED BY GLAMESSENTIALS</span>
        <span class="banner-text">SIGN UP NOW TO START SHOPPING</span>
        <span class="banner-text">EXCLUSIVE DEALS OFFERED BY GLAMESSENTIALS</span>
    </div>
</div>

<!-- 🖤 Header -->
<header class="header-container">
    <div class="header-main">
        <!-- Logo -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="logo">
            <img src="<?= htmlspecialchars($baseUrl) ?>/assets/logo1.png" alt="GlamEssentials" class="logo-img">
        </a>

        <!-- Navigation -->
        <nav class="nav-container" id="mobile-nav">
            <ul class="main-nav">
                <!-- Common links for everyone -->
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="nav-link">Home</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/product-list.php" class="nav-link">Products</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/about.php" class="nav-link">About</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/faq.php" class="nav-link">FAQ</a></li>
                <!-- <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php" class="nav-link">Login/Register</a></li> -->

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <!-- 🧭 ADMIN NAV -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link">Admin Panel</a>
                            <div class="dropdown-menu">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/dashboard.php">Dashboard</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/item/index.php">Manage Products</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories.php">Manage Categories</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/users.php">Manage Users</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders.php">Manage Orders</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reviews.php">Manage Reviews</a>
                            </div>
                        </li>
                        <!-- <li class="mobile-only-nav-item">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="nav-link">
                                <span class="nav-icon">👤</span> Logout
                            </a>
                        </li> -->
                    <?php else: ?>
                        <!-- 👤 USER NAV -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link">Account</a>
                            <div class="dropdown-menu">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/user/profile.php">Profile</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/user/myorders.php">My Orders</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php">My Cart</a>
                                <a href="<?= htmlspecialchars($baseUrl) ?>/user/myreviews.php">My Reviews</a>
                            </div>
                        </li>
                        <!-- <li class="mobile-only-nav-item">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="nav-link">
                                <span class="nav-icon">👤</span> Logout
                            </a>
                        </li> -->
                    <?php endif; ?>
                <?php else: ?>
                    <!-- 🧭 GUEST NAV -->
                    <!-- <li class="mobile-only-nav-item">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php" class="nav-link">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="10" cy="7" r="3.5"/>
                                <path d="M4 18c0-3.5 2.5-6 6-6s6 2.5 6 6"/>
                            </svg> SIGN IN/REGISTER
                        </a>
                    </li> -->
                <?php endif; ?>

                <!-- Common Cart link (still visible for all, redirects to login for guests) -->
                <!-- <li class="mobile-only-nav-item">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php" class="nav-link">
                        <span class="nav-icon">🛒</span> Cart

                    </a>
                </li> -->
            </ul>
        </nav>

        <!-- Header Actions (Desktop Only) -->
        <div class="header-actions desktop-only">
            <!-- Search Icon -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/search.php" button class="icon-btn" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 
                        105.196 5.196a7.5 7.5 0 
                        0010.607 10.607z" />
                </svg>
            </button>

            <!-- Cart Icon -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php" class="icon-btn" aria-label="Shopping Cart">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M15.75 10.5V6a3.75 3.75 0 
                        10-7.5 0v4.5m11.356-1.993l1.263 
                        12c.07.665-.45 1.243-1.119 
                        1.243H4.25a1.125 1.125 0 
                        01-1.12-1.243l1.264-12A1.125 
                        1.125 0 015.513 
                        7.5h12.974c.576 0 1.059.435 
                        1.119 1.007zM8.625 
                        10.5a.375.375 0 
                        11-.75 0 .375.375 0 
                        01.75 0zm7.5 
                        0a.375.375 0 11-.75 0 .375.375 0 
                        01.75 0z" />
                </svg>
                <!-- <span class="cart-count"><?php echo $cart_count; ?></span> -->
            </a>

            <!-- Account Icon / Dropdown -->
            <?php if (isset($_SESSION['user_id'])): 
                // Fetch minimal user data for dropdown (avoid overriding $user_data in pages)
                $header_user_id = (int) $_SESSION['user_id'];
                $header_stmt = $conn->prepare("SELECT name, img_name FROM users WHERE user_id = ?");
                $header_stmt->bind_param("i", $header_user_id);
                $header_stmt->execute();
                $header_result = $header_stmt->get_result();
                $header_user = $header_result->fetch_assoc();
                $header_stmt->close();

                // Set defaults safely
                if (empty($header_user['img_name'])) {
                    $header_user['img_name'] = 'nopfp.jpg';
                }

                $profile_pic = htmlspecialchars($baseUrl) . '/user/images/profile_pictures/' . htmlspecialchars($header_user['img_name']);
                $user_name = htmlspecialchars($header_user['name'] ?? 'User');
            ?>

            <div class="account-dropdown-wrapper">
                <button class="icon-btn account-dropdown-btn" aria-label="Account" type="button">
                    <img src="<?php echo $profile_pic; ?>" 
                         alt="Profile" 
                         class="account-profile-img"
                         onerror="this.src='<?php echo htmlspecialchars($baseUrl); ?>/user/images/profile_pictures/nopfp.jpg';">
                </button>
                <div class="account-dropdown-menu">
                    <div class="account-dropdown-header">
                        <img src="<?php echo $profile_pic; ?>" 
                             alt="Profile" 
                             class="account-dropdown-avatar"
                             onerror="this.src='<?php echo htmlspecialchars($baseUrl); ?>/user/images/profile_pictures/nopfp.jpg';">
                        <div class="account-dropdown-name"><?php echo $user_name; ?></div>
                    </div>
                    <div class="account-dropdown-divider"></div>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php" class="account-dropdown-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>Sign Out</span>
                    </a>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php" class="icon-btn" aria-label="Account">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="10" cy="7" r="3.5"/>
                    <path d="M4 18c0-3.5 2.5-6 6-6s6 2.5 6 6"/>
                </svg>
            </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Button -->
        <button class="hamburger-btn" aria-label="Menu">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>
    </div>
</header>