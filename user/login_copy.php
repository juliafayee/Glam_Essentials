<?php
require_once("../includes/config.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process login form before any output
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === '' || $password === '') {
        $_SESSION['message'] = 'Please enter email and password.';
    } else {
        // Fetch user record by email
        $sql = "SELECT user_id, email, role, password FROM users WHERE email=? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $_SESSION['message'] = 'Database error.';
        } else {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $db_email, $role, $db_hash);
                mysqli_stmt_fetch($stmt);

                $authenticated = false;

                // Prefer password_verify (bcrypt/argon2). If stored hash is legacy sha1, allow it and migrate.
                if (!empty($db_hash) && password_verify($password, $db_hash)) {
                    $authenticated = true;
                } elseif (sha1($password) === $db_hash) {
                    // Legacy SHA1 match — migrate to password_hash
                    $authenticated = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = mysqli_prepare($conn, "UPDATE users SET password=? WHERE user_id=?");
                    if ($upd) {
                        mysqli_stmt_bind_param($upd, 'si', $newHash, $user_id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                }

                if ($authenticated) {
                    // Successful login
                    $_SESSION['email'] = $db_email;
                    $_SESSION['userId'] = $user_id;
                    $_SESSION['role'] = $role;
                    // Redirect to home
                    header("Location: ../index.php");
                    exit;
                } else {
                    $_SESSION['message'] = 'Wrong email or password.';
                }
            } else {
                $_SESSION['message'] = 'Wrong email or password.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// load page-specific CSS through header.php
$pageCss = 'login.css';
include("../includes/header.php");
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === '' || $password === '') {
        $_SESSION['message'] = 'Please enter email and password.';
    } else {
        // Fetch user record by email
        $sql = "SELECT user_id, email, role, password FROM users WHERE email=? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $_SESSION['message'] = 'Database error.';
        } else {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $db_email, $role, $db_hash);
                mysqli_stmt_fetch($stmt);

                $authenticated = false;

                // Prefer password_verify (bcrypt/argon2). If stored hash is legacy sha1, allow it and migrate.
                if (!empty($db_hash) && password_verify($password, $db_hash)) {
                    $authenticated = true;
                } elseif (sha1($password) === $db_hash) {
                    // Legacy SHA1 match — migrate to password_hash
                    $authenticated = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = mysqli_prepare($conn, "UPDATE users SET password=? WHERE user_id=?");
                    if ($upd) {
                        mysqli_stmt_bind_param($upd, 'si', $newHash, $user_id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                }

                if ($authenticated) {
                    // Successful login
                    $_SESSION['email'] = $db_email;
                    $_SESSION['userId'] = $user_id;
                    $_SESSION['role'] = $role;
                    // Redirect to home
                    header("Location: ../index.php");
                    exit;
                } else {
                    $_SESSION['message'] = 'Wrong email or password.';
                }
            } else {
                $_SESSION['message'] = 'Wrong email or password.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

?>
<main class="auth-page">
    <div class="auth-card">
        <?php include("../includes/alert.php"); ?>
        <h2 class="text-center">Sign in to your account</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <!-- Email input -->
        <div class="form-outline mb-4">
            <input type="email" id="form2Example1" class="form-control" name="email" />
            <label class="form-label" for="form2Example1">Email address</label>
        </div>

        <!-- Password input -->
        <div class="form-outline mb-4">
            <input type="password" id="form2Example2" class="form-control" name="password" />
            <label class="form-label" for="form2Example2">Password</label>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary btn-block mb-4" name="submit">Sign in</button>

        <!-- Register buttons -->
      
        </form>
        <div class="auth-footer text-center">
            <p>Not a member? <a href="register.php">Register</a></p>
        </div>
    </div>
</main>
<?php
include("../includes/footer.php");
?>