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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: edit.php?id=' . $id);
    exit;
}

$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$product_name = trim($_POST['product_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$main_img_name = trim($_POST['main_img_name'] ?? '');
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$is_available = isset($_POST['is_available']) ? 1 : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$unit = trim($_POST['unit'] ?? 'pcs');
$reorder_level = isset($_POST['reorder_level']) ? (int)$_POST['reorder_level'] : 10;

if ($category_id <= 0 || $product_name === '' || $price < 0) {
    $_SESSION['error'] = 'Please fill all required fields correctly.';
    header('Location: edit.php?id=' . $id);
    exit;
}

// Handle new main image upload if provided
$allowedExts = ['jpg','jpeg','png','webp'];
$maxSize = 5 * 1024 * 1024;
$productsDir = __DIR__ . '/products';
$galleryDir = __DIR__ . '/product_images';
if (!is_dir($productsDir)) { @mkdir($productsDir, 0777, true); }
if (!is_dir($galleryDir)) { @mkdir($galleryDir, 0777, true); }

if (!empty($_FILES['main_image']['name']) && is_uploaded_file($_FILES['main_image']['tmp_name'])) {
    $f = $_FILES['main_image'];
    if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= $maxSize) {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExts, true)) {
            $base = $main_img_name !== '' ? preg_replace('/[^a-z0-9_\-]/i','_', $main_img_name) : (uniqid('prod_', true));
            $dest = $productsDir . '/' . $base . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $main_img_name = $base;
            }
        }
    }
}

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, "UPDATE products SET category_id=?, product_name=?, description=?, price=?, main_img_name=?, is_featured=?, is_available=? WHERE product_id=?");
    mysqli_stmt_bind_param($stmt, 'issdsiii', $category_id, $product_name, $description, $price, $main_img_name, $is_featured, $is_available, $id);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Failed to update product.'); }
    mysqli_stmt_close($stmt);

    // Upsert inventory: try update first; if no rows, insert
    $stmt2 = mysqli_prepare($conn, "UPDATE inventory SET quantity=?, unit=?, reorder_level=? WHERE product_id=?");
    mysqli_stmt_bind_param($stmt2, 'isii', $quantity, $unit, $reorder_level, $id);
    if (!mysqli_stmt_execute($stmt2)) { throw new Exception('Failed to update inventory.'); }
    if (mysqli_stmt_affected_rows($stmt2) === 0) {
        mysqli_stmt_close($stmt2);
        $stmt3 = mysqli_prepare($conn, "INSERT INTO inventory (product_id, quantity, unit, reorder_level) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt3, 'iisi', $id, $quantity, $unit, $reorder_level);
        if (!mysqli_stmt_execute($stmt3)) { throw new Exception('Failed to insert inventory.'); }
        mysqli_stmt_close($stmt3);
    } else {
        mysqli_stmt_close($stmt2);
    }

    // Handle new gallery images
    if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
        $names = $_FILES['gallery_images']['name'];
        $tmps = $_FILES['gallery_images']['tmp_name'];
        $errs = $_FILES['gallery_images']['error'];
        $sizes = $_FILES['gallery_images']['size'];
        for ($i=0; $i<count($names); $i++) {
            if (!isset($names[$i]) || $errs[$i] !== UPLOAD_ERR_OK || $sizes[$i] > $maxSize) { continue; }
            if (!is_uploaded_file($tmps[$i])) { continue; }
            $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) { continue; }
            $base = uniqid('img_', true);
            $dest = $galleryDir . '/' . $base . '.' . $ext;
            if (move_uploaded_file($tmps[$i], $dest)) {
                $ins = mysqli_prepare($conn, 'INSERT INTO product_images (product_id, img_name) VALUES (?,?)');
                mysqli_stmt_bind_param($ins, 'is', $id, $base);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
        }
    }

    mysqli_commit($conn);
    $_SESSION['success'] = 'Item updated successfully.';
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: edit.php?id=' . $id);
    exit;
}
