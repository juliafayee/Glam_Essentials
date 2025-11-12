<?php
session_start();
include("../includes/config.php");

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Get and sanitize form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPass = $_POST['confirmPass'] ?? '';

// Keep old inputs for repopulation on error
$_SESSION['old'] = ['name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), 'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8')];

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    $_SESSION['message'] = 'All fields are required.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Please enter a valid email address.';
    header('Location: register.php');
    exit;
}

if ($password !== $confirmPass) {
    $_SESSION['message'] = 'Passwords do not match.';
    header('Location: register.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['message'] = 'Password must be at least 8 characters.';
    header('Location: register.php');
    exit;
}

// Check duplicate email
$checkSql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
$checkStmt = mysqli_prepare($conn, $checkSql);
if ($checkStmt) {
    mysqli_stmt_bind_param($checkStmt, 's', $email);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $_SESSION['message'] = 'Email already exists. Please login or use a different email.';
        mysqli_stmt_close($checkStmt);
        header('Location: register.php');
        exit;
    }
    mysqli_stmt_close($checkStmt);
} else {
    $_SESSION['message'] = 'Database error.';
    header('Location: register.php');
    exit;
}

// Hash password securely
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$insertSql = "INSERT INTO users (`name`, `email`, `password`, `role`, `img_name`) VALUES (?, ?, ?, 'customer', 'nopfp.jpg')";
$ins = mysqli_prepare($conn, $insertSql);
if ($ins) {
    mysqli_stmt_bind_param($ins, 'sss', $name, $email, $hash);
    $ok = mysqli_stmt_execute($ins);
    if ($ok) {
        // clear old inputs
        unset($_SESSION['old']);
        // set session and log user in
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'customer';
        $_SESSION['message'] = 'Registration successful!';
        mysqli_stmt_close($ins);
        header('Location: profile.php');
        exit;
    } else {
        // Attempt to provide useful message
        if (mysqli_errno($conn) === 1062) {
            $_SESSION['message'] = 'Email already exists.';
        } else {
            $_SESSION['message'] = 'Registration failed. Please try again.';
        }
        mysqli_stmt_close($ins);
        header('Location: register.php');
        exit;
    }
} else {
    $_SESSION['message'] = 'Database error (insert).';
    header('Location: register.php');
    exit;
}
