<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$types = ['success' => '#ffb6c1', 'error' => '#7a1530', 'message' => '#2d3748'];
foreach ($types as $key => $color) {
  if (!empty($_SESSION[$key])) {
    $msg = htmlspecialchars($_SESSION[$key]);
    echo '<div class="site-alert" role="alert" data-alert-type="' . $key . '">';
    echo '<div class="site-alert-msg">' . $msg . '</div>';
    echo '<button type="button" class="site-alert-close" aria-label="Dismiss" onclick="this.parentNode.style.display=\'none\';">&times;</button>';
    echo '</div>';
    unset($_SESSION[$key]);
  }
}
?>
