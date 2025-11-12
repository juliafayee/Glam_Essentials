<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = '';
include __DIR__ . '/includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$is_logged_in = isset($_SESSION['userId']);
?>

<style>
  .search-wrapper{max-width:1200px;margin:40px auto;padding:0 16px}
  .search-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
  .search-title{font-size:24px;font-weight:600;margin:0}
  .search-form{display:flex;gap:8px}
  .search-form input[type=text]{padding:10px 12px;border:1px solid #ddd;border-radius:6px;min-width:280px}
  .search-form button{padding:10px 14px;background:#111;color:#fff;border:0;border-radius:6px;cursor:pointer}

  .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px;margin-top:18px}
  .card{border:1px solid #eee;border-radius:10px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
  .card img{width:100%;height:180px;object-fit:cover;background:#f6f6f6}
  .card-body{padding:12px 12px 14px;display:flex;flex-direction:column;gap:6px}
  .card-title{font-weight:600;font-size:16px;margin:0}
  .price{font-weight:600}
  .muted{color:#6b7280;font-size:13px}
  .card form button{background:#000;color:#fff;border:none;padding:8px 10px;border-radius:4px;cursor:pointer;margin-top:6px}
</style>

<main class="search-wrapper">
  <div class="search-heading">
    <h1 class="search-title">Search Products</h1>
    <form class="search-form" action="search.php" method="get">
      <input type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($q) ?>" />
      <button type="submit">Search</button>
    </form>
  </div>

  <?php if ($q === ''): ?>
    <p class="muted">Type a product name or keyword, then press Enter.</p>
  <?php else: ?>
    <?php
      $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, p.description, p.price, p.main_img_name,
                COALESCE(i.quantity,0) AS quantity, COALESCE(i.unit,'pcs') AS unit, COALESCE(i.reorder_level,0) AS reorder_level
         FROM products p
         LEFT JOIN inventory i ON i.product_id = p.product_id
         WHERE p.product_name LIKE ? OR p.description LIKE ?
         ORDER BY p.created_at DESC"
      );
      $like = "%" . $q . "%";
      $stmt->bind_param('ss', $like, $like);
      $stmt->execute();
      $res = $stmt->get_result();
    ?>

    <p class="muted">Showing results for "<?= htmlspecialchars($q) ?>" (<?= (int)$res->num_rows ?>)</p>

    <div class="cards">
      <?php if ($res && $res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
          <?php
            $product_id = (int)$row['product_id'];
            // Determine image path similar to index.php logic
            $imgName = $row['main_img_name'];
            $main_img = './assets/default.png';
            if (!empty($imgName)) {
              $productImagesDir = 'C:/infomanagement/htdocs/GlamEssentials/item/products/';
              $extensions = ['.jpg', '.png', '.webp'];
              foreach ($extensions as $ext) {
                $fullPath = $productImagesDir . $imgName . $ext;
                if (file_exists($fullPath)) {
                  $main_img = './item/products/' . $imgName . $ext;
                  break;
                }
              }
            }

            // Reviews
            $avg_rating = 'N/A';
            $total_reviews = 0;
            $rev = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id={$product_id}");
            if ($rev) { $r = $rev->fetch_assoc(); $avg_rating = $r['avg_rating'] ? number_format($r['avg_rating'],1) : 'N/A'; $total_reviews = (int)$r['total_reviews']; }

            $add_onclick = $is_logged_in ? '' : "alert('Please log in first to add items to cart.'); return false;";
          ?>
          <div class="card">
            <a href="./product.php?id=<?= $product_id ?>"><img src="<?= htmlspecialchars($main_img) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>"></a>
            <div class="card-body">
              <h3 class="card-title"><a href="./product.php?id=<?= $product_id ?>" style="text-decoration:none;color:inherit;"><?= htmlspecialchars($row['product_name']) ?></a></h3>
              <div class="muted"><?php if ($avg_rating !== 'N/A'): ?><?= $avg_rating ?> ⭐ (<?= $total_reviews ?>)<?php else: ?>No ratings yet<?php endif; ?></div>
              <div class="price">₱<?= number_format((float)$row['price'], 2) ?></div>
              <form method="POST" action="./cart/cart_update.php">
                <input type="hidden" name="item_id" value="<?= $product_id ?>">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="redirect" value="/GlamEssentials/cart/view_cart.php">
                <button type="submit" onclick="<?= $add_onclick ?>">Add To Bag</button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products found. Try a different keyword.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>