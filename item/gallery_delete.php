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

$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$imgId = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
if ($pid <= 0 || $imgId <= 0) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: edit.php?id=' . max(1, $pid));
    exit;
}

// Fetch image basename
$stmt = mysqli_prepare($conn, 'SELECT img_name FROM product_images WHERE image_id = ? AND product_id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'ii', $imgId, $pid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    $_SESSION['error'] = 'Image not found.';
    header('Location: edit.php?id=' . $pid);
    exit;
}

$base = $row['img_name'];
$dir = __DIR__ . '/product_images/';
$extensions = ['.jpg', '.jpeg', '.png', '.webp'];
foreach ($extensions as $ext) {
    $path = $dir . $base . $ext;
    if (is_file($path)) { @unlink($path); }
}

// Delete DB row
$del = mysqli_prepare($conn, 'DELETE FROM product_images WHERE image_id = ? AND product_id = ?');
mysqli_stmt_bind_param($del, 'ii', $imgId, $pid);
mysqli_stmt_execute($del);
mysqli_stmt_close($del);

$_SESSION['success'] = 'Gallery image deleted.';
header('Location: edit.php?id=' . $pid);
exit;