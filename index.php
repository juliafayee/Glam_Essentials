<?php

$pageCss = 'index.css';
include('./includes/header.php');
include('./includes/config.php');
?>
<style>
<?php include('./includes/style/index.css'); ?>
</style>
<link rel="stylesheet" href="./includes/style/hero.css">

<section class="hero">
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>DISCOVER ELEGANCE</h1>
            <p>Premium salon essentials curated for the modern professional.</p>
            <a href="item/index.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>
</section>

<style>
.category-showcase {
    padding: 60px 0;
    background: #FFF;
}

.category-showcase h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 50px;
    color: #333;
    font-weight: 300;
    letter-spacing: 1px;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    
    /* Mobile styles */
    @media (max-width: 768px) {
        font-size: 1.8rem;
        margin-bottom: 30px;
    }
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    
    /* Mobile styles */
    @media (max-width: 768px) {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding: 0 10px;
    }
}

.category-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 240px;
    justify-content: flex-end;
    padding: 15px;
    border-radius: 8px;
    
    /* Mobile styles */
    @media (max-width: 768px) {
        min-height: 160px;
        padding: 8px;
    }
}

.category-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

.category-image-wrapper {
    width: 180px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    min-height: 120px;
    margin-bottom: 10px;
    
    /* Mobile styles */
    @media (max-width: 768px) {
        width: 120px;
        min-height: 80px;
        margin-bottom: 8px;
    }
}

.category-image-wrapper img {
    max-width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}

.category-item h3 {
    font-size: 1.1rem;
    color: #333;
    margin: 0;
    font-weight: 400;
    padding: 0 10px;
    width: 100%;
    margin-top: auto;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    
    /* Mobile styles */
    @media (max-width: 768px) {
        font-size: 0.9rem;
        padding: 0 5px;
    }
}


</style>

<!-- Shop by Category Section -->
<section class="category-showcase">
    <div class="container">
        <h2>SHOP BY CATEGORY</h2>
        <div class="category-grid">
            <?php
            // Fetch all categories
            $sql_categories = "SELECT * FROM categories ORDER BY category_id";
            $categories_result = mysqli_query($conn, $sql_categories);
            
            if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                while ($category = mysqli_fetch_assoc($categories_result)) {
                    $category_id = $category['category_id'];
                    $category_name = $category['category_name'];
                    
                    // Image path - check for common extensions
                    $img_path = './assets/default.png'; // Default image
                    $categoryImagesDir = __DIR__ . '/item/product_category/';
                    $extensions = ['.png', '.jpg', '.jpeg', '.webp'];
                    
                    // First try the exact filename from the database
                    if (!empty($category['img_name'])) {
                        $imgName = strtolower(str_replace(' ', '_', $category['img_name']));
                        foreach ($extensions as $ext) {
                            $fullPath = $categoryImagesDir . $imgName . $ext;
                            if (file_exists($fullPath)) {
                                $img_path = './item/product_category/' . $imgName . $ext;
                                break;
                            }
                        }
                    }
                    
                    // If no image found, try the category_{id} pattern
                    if ($img_path === './assets/default.png') {
                        foreach ($extensions as $ext) {
                            $fullPath = $categoryImagesDir . 'category_' . $category_id . $ext;
                            if (file_exists($fullPath)) {
                                $img_path = './item/product_category/category_' . $category_id . $ext;
                                break;
                            }
                        }
                    }
                    
                    echo '<div class="category-item" onclick="window.location.href=\'item/index.php?category=' . $category_id . '\'">';
                    echo '    <div class="category-image-wrapper">';
                    echo '        <img src="' . $img_path . '" alt="' . htmlspecialchars($category_name) . '">';
                    echo '    </div>';
                    echo '    <h3>' . htmlspecialchars($category_name) . '</h3>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        
    </div>
</section>

<?php
include('./includes/config.php');

// --- Check login ---
$is_logged_in = isset($_SESSION['userId']);
$user_id = $is_logged_in ? $_SESSION['userId'] : 0;

echo '<div class="container">';

// --- Mini-cart summary ---
if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
    $total_items = 0;
    $total_price = 0;
    foreach ($_SESSION["cart_products"] as $item) {
        $total_items += $item["item_qty"];
        $total_price += $item["item_price"] * $item["item_qty"];
    }
    echo "<div class='cart-summary mb-4 text-center'>
            <strong>Cart:</strong> {$total_items} items | 
            <strong>Total:</strong> ₱" . number_format($total_price, 2) . "
            <a href='./cart/view_cart.php' class='btn btn-primary btn-sm ms-2'>View Cart</a>
          </div>";
}

?>
</div>

<!-- Who Are We Section -->
<link rel="stylesheet" href="./includes/style/whoarewe.css">

<section class="who-are-we">
  <div class="who-text">
    <h3>Who Are We</h3>
    <h1>Your New Go-to for<br>Salon Essentials</h1>
    <p>Essential Salon Supplies is your trusted local destination for professional hair and beauty products. With everything in stock, we offer fast, reliable island-wide delivery straight to your door.</p>
    <a href="#products" class="shop-btn">Shop Now</a>
  </div>

  <div class="who-image">
    <img src="./includes/images/salon_essentials.jpg" alt="Salon Essentials">
  </div>
</section>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
</style>
<?php include('./includes/footer.php'); ?>