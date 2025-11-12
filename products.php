<?php
require_once __DIR__ . '/includes/config.php';
// Get base URL
$baseUrl = rtrim($baseUrl ?? '', '/');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function mask_bad_words($text) {
  $bad = ['fuck','shit','bitch','asshole','bastard','dick','pussy','cunt','slut','whore'];
  $pattern = '/\b(' . implode('|', array_map(function($w){ return preg_quote($w, '/'); }, $bad)) . ')\b/i';
  return preg_replace_callback($pattern, function($m){
    $w = $m[0];
    $len = mb_strlen($w);
    if ($len <= 2) return str_repeat('*', $len);
    $first = mb_substr($w, 0, 1);
    return $first . str_repeat('*', $len - 1);
  }, $text);
}

// Get product id
// Get product id
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// In products.php
if ($productId <= 0) {
    // Redirect to the home page with the correct base URL
    header('Location: ' . $baseUrl . '/index.php');
    exit;
}

// Resolve logged in user and customer
$user_id = 0;
if (!empty($_SESSION['user_id'])) { 
    $user_id = (int)$_SESSION['user_id'];
}
$customerId = 0;
if ($user_id) {
  $stmt = mysqli_prepare($conn, 'SELECT customer_id, fullname FROM customers WHERE user_id = ? LIMIT 1');
  mysqli_stmt_bind_param($stmt, 'i', $user_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  if ($res && $row = mysqli_fetch_assoc($res)) {
    $customerId = (int)$row['customer_id'];
  }
  mysqli_stmt_close($stmt);
}

// Handle review submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit']) && $user_id && $customerId) {
  $rating = isset($_POST['rating']) ? max(1, min(5, (int)$_POST['rating'])) : 5;
  $review = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
  $review = mask_bad_words($review);

  // Check if user already has a review for this product
  $stmt = mysqli_prepare($conn, 'SELECT review_id FROM reviews WHERE customer_id = ? AND product_id = ?');
  mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $existing = $res ? mysqli_fetch_assoc($res) : null;
  mysqli_stmt_close($stmt);

  if ($existing) {
    $stmt = mysqli_prepare($conn, 'UPDATE reviews SET rating = ?, review_text = ? WHERE review_id = ?');
    mysqli_stmt_bind_param($stmt, 'isi', $rating, $review, $existing['review_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['message'] = 'Your review has been updated.';
  } else {
    $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (customer_id, product_id, rating, review_text) VALUES (?,?,?,?)');
    mysqli_stmt_bind_param($stmt, 'iiis', $customerId, $productId, $rating, $review);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['message'] = 'Thank you for your review!';
  }

  header('Location: /GlamEssentials/product.php?id=' . $productId);
  exit;
}

// Load product data
$stmt = mysqli_prepare($conn, 'SELECT p.product_id, p.product_name, p.description, p.price, p.main_img_name, c.category_name
  FROM products p JOIN categories c ON c.category_id = p.category_id WHERE p.product_id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);
if (!$product) { header('Location: /GlamEssentials/index.php'); exit; }

// Determine product main image path (reuse logic from index)
$main_img = $baseUrl . '/assets/default.png';
$imgName = $product['main_img_name'] ?? '';
if (!empty($imgName)) {
    $productImagesDir = __DIR__ . '/item/products/';
    $extensions = ['.jpg', '.png', '.webp'];
    foreach ($extensions as $ext) {
        $fullPath = $productImagesDir . $imgName . $ext;
        if (file_exists($fullPath)) { 
            $main_img = $baseUrl . '/item/products/' . $imgName . $ext; 
            break; 
        }
    }
}

// Reviews summary
$avg_rating = 'N/A'; $total_reviews = 0;
$q = $conn->query('SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id=' . (int)$productId);
if ($q) { $r = $q->fetch_assoc(); $avg_rating = $r['avg_rating'] ? number_format($r['avg_rating'],1) : 'N/A'; $total_reviews = (int)$r['total_reviews']; }

// Reviews list
$reviews = [];
$stmt = mysqli_prepare($conn, 'SELECT r.review_id, r.rating, r.review_text, r.created_at, c.fullname
  FROM reviews r JOIN customers c ON c.customer_id = r.customer_id
  WHERE r.product_id = ? ORDER BY r.created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($res) { $reviews = mysqli_fetch_all($res, MYSQLI_ASSOC); }
mysqli_stmt_close($stmt);

// Fetch current user's review if any
$userReview = null;
if ($customerId) {
  $stmt = mysqli_prepare($conn, 'SELECT review_id, rating, review_text FROM reviews WHERE customer_id = ? AND product_id = ?');
  mysqli_stmt_bind_param($stmt, 'ii', $customerId, $productId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  if ($res) { $userReview = mysqli_fetch_assoc($res); }
  mysqli_stmt_close($stmt);
}

$pageCss = '';
include __DIR__ . '/includes/header.php';
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap');
  .prod-wrapper{max-width:1100px;margin:40px auto 80px;padding:0 16px}
  .prod-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:28px}
  .prod-title{font-family:'Playfair Display',serif;font-size:34px;margin:0}
  .muted{color:#6b7280}
  .price{font-weight:700;font-size:20px;margin-top:8px}
  .prod-img{width:100%;border-radius:12px;background:#f6f6f6;object-fit:cover;max-height:460px}
  .btn{background:#111;color:#fff;border:0;border-radius:6px;padding:10px 14px;cursor:pointer;text-decoration:none;display:inline-block}
  .reviews{margin-top:40px}
  .reviews h2{font-family:'Playfair Display',serif;font-size:28px;margin:0 0 10px}
  .rev-item{border-top:1px solid #f0f0f0;padding:12px 0}
  .rating{color:#111;font-weight:600}
  .rev-form{margin-top:12px;border:1px solid #eee;border-radius:12px;padding:16px}
  .rev-form textarea{width:100%;min-height:100px;padding:10px;border:1px solid #ddd;border-radius:6px}
  .rev-form select{padding:8px;border:1px solid #ddd;border-radius:6px}
  @media(max-width:900px){ .prod-grid{grid-template-columns:1fr} }
</style>

<main class="prod-wrapper">
  <?php if (!empty($_SESSION['message'])): ?><p class="muted"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p><?php endif; ?>

  <div class="prod-grid">
    <div>
      <img class="prod-img" src="<?php echo htmlspecialchars($main_img); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
    </div>
    <div>
      <h1 class="prod-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
      <div class="muted">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
      <div class="price">₱<?php echo number_format((float)$product['price'],2); ?></div>
      <div class="muted" style="margin-top:6px;">Rating: <?php echo ($avg_rating==='N/A'?'No ratings yet':$avg_rating.' ⭐'); ?> (<?php echo (int)$total_reviews; ?>)</div>
      <p style="margin-top:12px;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

      <?php if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
        <form method="POST" action="<?php echo $baseUrl; ?>/cart/cart_update.php" style="margin-top:14px;display:flex;gap:8px;align-items:center;">
          <input type="hidden" name="item_id" value="<?php echo (int)$productId; ?>">
          <input type="hidden" name="type" value="add">
          <input type="number" name="quantity" min="1" value="1" style="width:90px;padding:8px;border:1px solid #ddd;border-radius:6px">
          <input type="hidden" name="redirect" value="<?php echo $baseUrl; ?>/cart/view_cart.php">
          <button class="btn" type="submit">Add To Bag</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <section class="reviews">
    <h2>Reviews</h2>
    <?php if (!$reviews): ?>
      <p class="muted">No reviews yet. Be the first to review this product.</p>
    <?php else: ?>
      <?php foreach ($reviews as $rv): $name = $rv['fullname'] ?? 'Customer';?>
        <div class="rev-item">
          <div class="rating"><?php echo (int)$rv['rating']; ?> ⭐ <span class="muted" style="font-weight:400;margin-left:6px;">by <?php echo htmlspecialchars($name ?: 'Customer'); ?> • <?php echo htmlspecialchars($rv['created_at']); ?></span></div>
          <?php if (!empty($rv['review_text'])): ?><div><?php echo nl2br(htmlspecialchars(mask_bad_words($rv['review_text']))); ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($user_id && $customerId): ?>
      <div class="rev-form">
        <form method="post" action="/GlamEssentials/product.php?id=<?php echo (int)$productId; ?>">
          <label for="rating">Your Rating</label><br>
          <select id="rating" name="rating">
            <?php for ($i=5;$i>=1;$i--): ?>
              <option value="<?php echo $i; ?>" <?php if ($userReview && (int)$userReview['rating']===$i) echo 'selected'; ?>><?php echo $i; ?> ⭐</option>
            <?php endfor; ?>
          </select>
          <br><br>
          <label for="review_text">Your Review</label>
          <textarea id="review_text" name="review_text" placeholder="Share your thoughts..."><?php echo $userReview ? htmlspecialchars(mask_bad_words($userReview['review_text'])) : ''; ?></textarea>
          <br>
          <button class="btn" type="submit" name="review_submit"><?php echo $userReview ? 'Update Review' : 'Submit Review'; ?></button>
         <?php if (empty($row['fullname'])): ?><div class="muted" style="margin-top:8px;">Your review will appear with your profile name.</div><?php endif; ?>
        </form>
      </div>
    <?php else: ?>
      <p class="muted">Please <a href="<?php echo $baseUrl; ?>/user/login.php"> sign in</a> to write a review.</p>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>