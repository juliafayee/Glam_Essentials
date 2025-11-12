<?php
// Start output buffering to prevent header errors
ob_start();

require_once('../includes/config.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    // Check user role
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        ob_end_clean();
        header("Location: ../index.php");
        exit();
    } else {
        ob_end_clean();
        header("Location: ../index.php");
        exit();
    }
}

$error = '';
$success = '';

// Check if redirected from unauthorized access
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = "You must be logged in to access that page.";
}

if (isset($_GET['error']) && $_GET['error'] === 'admin_only') {
    $error = "Access denied. Admin privileges required.";
}

// Check for redirect parameter
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Server-side validation function
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    $length = strlen($password) >= 8 && strlen($password) <= 12;
    $uppercase = preg_match('/[A-Z]/', $password);
    $lowercase = preg_match('/[a-z]/', $password);
    $number = preg_match('/[0-9]/', $password);
    $special = preg_match('/[!@#$%^&*]/', $password);
    
    return $length && $uppercase && $lowercase && $number && $special;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Login Logic - automatically detects role from database
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Server-side validation
        if (empty($email)) {
            $error = "Email address is required.";
        } elseif (empty($password)) {
            $error = "Password is required.";
        } elseif (!validateEmail($email)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check users table for any role (using bcrypt password verification)
            $stmt = $conn->prepare("SELECT user_id, name, email, password, role, is_active FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password using password_verify (bcrypt)
                if (password_verify($password, $user['password'])) {
                    // Check if user is active
                    if ($user['is_active'] != 1) {
                        $error = "Your account has been deactivated. Please contact support.";
                    } else {
                        // Update last login
                        $update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                        $update_login->bind_param("i", $user['user_id']);
                        $update_login->execute();
                        $update_login->close();
                        
                        // Set session based on user's role
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_name'] = $user['name'];
                            $_SESSION['admin_id'] = $user['user_id'];
                            ob_end_clean();
                            header("Location: ../index.php");
                            exit();
                        } else {
                            // Customer login
                            ob_end_clean();
                            // Redirect to the original page if redirect parameter exists
                            if (!empty($redirect_url)) {
                                header("Location: ../" . $redirect_url);
                            } else {
                                header("Location: ../index.php");
                            }
                            exit();
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }

            $stmt->close();
        }
    } elseif (isset($_POST['register'])) {
        // Registration Logic with Server-side Validation
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Comprehensive server-side validation
        if (empty($name)) {
            $error = "Name is required.";
        } elseif (strlen($name) < 2) {
            $error = "Name must be at least 2 characters long.";
        } elseif (empty($email)) {
            $error = "Email address is required.";
        } elseif (!validateEmail($email)) {
            $error = "Please enter a valid email address.";
        } elseif (empty($password)) {
            $error = "Password is required.";
        } elseif (!validatePassword($password)) {
            $error = "Password must be 8-12 characters and include uppercase, lowercase, number, and special character (!@#$%^&*).";
        } elseif (empty($confirm_password)) {
            $error = "Please confirm your password.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                // Only proceed if no error from file upload
                if (empty($error)) {
                    // Hash password using bcrypt (password_hash)
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Insert into users table
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, 'customer', 1)");
                    $stmt->bind_param("sss", $name, $email, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success = "Registration successful! You can now login.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

ob_end_flush();
require_once('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<section class="luxury-auth-section">
    <div class="auth-background-overlay"></div>
    
    <div class="luxury-auth-container">
        <!-- Tab Navigation -->
        <div class="luxury-auth-toggle">
            <button class="luxury-tab active" data-tab="login">
                <span>Sign In</span>
            </button>
            <button class="luxury-tab" data-tab="register">
                <span>Create Account</span>
            </button>
        </div>

        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div class="luxury-alert luxury-alert-error">
                <i class="alert-icon">✕</i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="luxury-alert luxury-alert-success">
                <i class="alert-icon">✓</i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="luxury-form-wrapper active" id="login-form">
            <div class="form-header">
                <h2 class="luxury-auth-title">Welcome Back</h2>
                <p class="luxury-auth-subtitle">Sign in to continue your journey</p>
            </div>
            
            <form method="POST" action="" class="luxury-auth-form" onsubmit="return validateLoginForm()">
                <?php if (!empty($redirect_url)): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_url); ?>">
                <?php endif; ?>
                
                <!-- Email Field -->
                <div class="luxury-form-group">
                    <label for="login-email" class="luxury-label">Email Address</label>
                    <input type="text" id="login-email" name="email" class="luxury-input" placeholder="Enter your email">
                    <span class="error-message" id="login-email-error"></span>
                </div>
                
                <!-- Password Field -->
                <div class="luxury-form-group">
                    <label for="login-password" class="luxury-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="login-password" name="password" class="luxury-input" placeholder="Enter your password">
                        <button type="button" class="password-toggle" onclick="toggleLoginPassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <span class="error-message" id="login-password-error"></span>
                </div>
                
                <button type="submit" name="login" class="luxury-btn luxury-btn-primary">
                    <span>Sign In</span>
                </button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="luxury-form-wrapper" id="register-form">
            <div class="form-header">
                <h2 class="luxury-auth-title">Create Account</h2>
                <p class="luxury-auth-subtitle">Join GlamEssentials</p>
            </div>
            
            <form method="POST" action="" class="luxury-auth-form" onsubmit="return validateRegisterForm()">
                <div class="luxury-form-group">
                    <label for="register-name" class="luxury-label">Full Name <span class="required">*</span></label>
                    <input type="text" id="register-name" name="name" class="luxury-input" placeholder="Shami Morales">
                    <span class="error-message" id="register-name-error"></span>
                </div>
                
                <div class="luxury-form-group">
                    <label for="register-email" class="luxury-label">Email Address <span class="required">*</span></label>
                    <input type="text" id="register-email" name="email" class="luxury-input" placeholder="shami@example.com">
                    <span class="error-message" id="register-email-error"></span>
                </div>
                
                <div class="luxury-form-group">
                    <label for="register-password" class="luxury-label">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="register-password" name="password" class="luxury-input" placeholder="Enter secure password">
                        <button type="button" class="password-toggle" onclick="toggleRegisterPassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="req" data-requirement="length">* 8-12 characters</div>
                        <div class="req" data-requirement="uppercase">* At least one uppercase letter (A-Z)</div>
                        <div class="req" data-requirement="lowercase">* At least one lowercase letter (a-z)</div>
                        <div class="req" data-requirement="number">* At least one number (0-9)</div>
                        <div class="req" data-requirement="special">* At least one special character (!@#$%^&*)</div>
                    </div>
                    <span class="error-message" id="register-password-error"></span>
                </div>
                
                <div class="luxury-form-group">
                    <label for="register-confirm" class="luxury-label">Confirm Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="register-confirm" name="confirm_password" class="luxury-input" placeholder="Re-enter password">
                        <button type="button" class="password-toggle" onclick="toggleConfirmPassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <span class="error-message" id="register-confirm-error"></span>
                </div>
                
                <button type="submit" name="register" class="luxury-btn luxury-btn-primary">
                    <span>Create Account</span>
                </button>
            </form>
        </div>
    </div>
</section>

<style>
.luxury-auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    /* background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); */
    padding: 140px 20px 80px;
    position: relative;
    overflow: hidden;
}

.auth-background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.02) 0%, transparent 50%);
    pointer-events: none;
}

.luxury-auth-container {
    max-width: 550px;
    width: 100%;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    position: relative;
    z-index: 1;
}

.luxury-auth-toggle {
    display: flex;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.luxury-tab {
    flex: 1;
    padding: 25px 20px;
    background: transparent;
    border: none;
    color: rgba(0, 0, 0, 0.4);
    font-size: 11px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
    position: relative;
}

.luxury-tab::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #0a0a0a;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.luxury-tab.active {
    color: #0a0a0a;
    background: #fafafa;
}

.luxury-tab.active::before {
    width: 100%;
}

.luxury-tab:hover {
    color: #0a0a0a;
}

.luxury-alert {
    padding: 18px 25px;
    margin: 25px 40px 0;
    border: 1px solid;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    letter-spacing: 0.3px;
    animation: slideDown 0.4s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.luxury-alert .alert-icon {
    font-size: 16px;
    font-weight: 600;
}

.luxury-alert-error {
    background: #fef5f5;
    border-color: rgba(176, 42, 55, 0.2);
    color: #b02a37;
}

.luxury-alert-success {
    background: #f0f8f4;
    border-color: rgba(21, 87, 36, 0.2);
    color: #155724;
}

.luxury-form-wrapper {
    display: none;
    padding: 50px 40px;
    animation: fadeInForm 0.5s ease;
}

.luxury-form-wrapper.active {
    display: block;
}

@keyframes fadeInForm {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-header {
    margin-bottom: 40px;
    text-align: center;
}

.luxury-auth-title {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 400;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
    color: #0a0a0a;
}

.luxury-auth-subtitle {
    font-size: 13px;
    color: rgba(0, 0, 0, 0.5);
    letter-spacing: 1px;
    font-weight: 300;
}

.luxury-auth-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.luxury-form-group {
    display: flex;
    flex-direction: column;
}

.luxury-label {
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #0a0a0a;
    margin-bottom: 10px;
    font-weight: 600;
    font-family: 'Montserrat', sans-serif;
}

.required {
    color: #b02a37;
}

.luxury-input {
    padding: 16px 20px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    background: #fafafa;
    font-size: 14px;
    letter-spacing: 0.3px;
    transition: all 0.3s ease;
    font-weight: 300;
    font-family: 'Lato', sans-serif;
    color: #0a0a0a;
}

.luxury-input:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #ffffff;
}

.luxury-input::placeholder {
    color: rgba(0, 0, 0, 0.3);
}

.luxury-textarea {
    resize: vertical;
    min-height: 90px;
}

.error-message {
    display: none;
    color: #b02a37;
    font-size: 11px;
    margin-top: 6px;
    letter-spacing: 0.3px;
}

.error-message.show {
    display: block;
}

.luxury-input.error {
    border-color: #b02a37;
    background: #fef5f5;
}

.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: rgba(0, 0, 0, 0.4);
    cursor: pointer;
    padding: 8px;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: #0a0a0a;
}

.file-upload-wrapper {
    position: relative;
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px 20px;
    border: 1px dashed rgba(0, 0, 0, 0.25);
    background: #fafafa;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 13px;
    letter-spacing: 0.5px;
    color: rgba(0, 0, 0, 0.6);
    font-family: 'Montserrat', sans-serif;
}

.file-upload-label:hover {
    border-color: #0a0a0a;
    background: #ffffff;
    color: #0a0a0a;
}

.file-upload-label svg {
    opacity: 0.6;
}

.file-note {
    margin-top: 8px;
    font-size: 11px;
    color: rgba(0, 0, 0, 0.5);
    letter-spacing: 0.3px;
}

.password-requirements {
    margin-top: 12px;
    padding: 15px;
    background: #fafafa;
    border: 1px solid rgba(0, 0, 0, 0.08);
    display: none;
}

.password-requirements.show {
    display: block;
}

.password-requirements .req {
    font-size: 11px;
    color: rgba(0, 0, 0, 0.5);
    padding: 5px 0;
    letter-spacing: 0.3px;
    transition: color 0.3s ease;
}

.password-requirements .req.valid {
    color: #155724;
    font-weight: 500;
}

.password-requirements .req.invalid {
    color: #b02a37;
}

.image-preview {
    margin-top: 15px;
    display: none;
}

.image-preview.active {
    display: block;
}

.image-preview img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}

.luxury-btn {
    padding: 18px 40px;
    border: 1px solid rgba(0, 0, 0, 0.2);
    background: transparent;
    color: #0a0a0a;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
    margin-top: 10px;
    position: relative;
    overflow: hidden;
}

.luxury-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: #0a0a0a;
    transition: left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 0;
}

.luxury-btn:hover {
    color: #ffffff;
    border-color: #0a0a0a;
}

.luxury-btn:hover::before {
    left: 0;
}

.luxury-btn span {
    position: relative;
    z-index: 1;
}

@media (max-width: 768px) {
    .luxury-auth-section {
        padding: 120px 20px 60px;
    }

    .luxury-auth-container {
        max-width: 100%;
    }

    .luxury-form-wrapper {
        padding: 40px 30px;
    }

    .luxury-auth-title {
        font-size: 28px;
    }

    .luxury-auth-subtitle {
        font-size: 12px;
    }

    .luxury-tab {
        font-size: 10px;
        padding: 20px 15px;
    }

    .luxury-alert {
        margin: 20px 25px 0;
        padding: 15px 20px;
    }
}

@media (max-width: 480px) {
    .luxury-form-wrapper {
        padding: 35px 25px;
    }

    .luxury-auth-title {
        font-size: 24px;
    }

    .luxury-alert {
        margin: 15px 20px 0;
    }
}
</style>

<script>
document.querySelectorAll('.luxury-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const targetTab = this.getAttribute('data-tab');
        document.querySelectorAll('.luxury-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.luxury-form-wrapper').forEach(form => {
            form.classList.remove('active');
        });
        document.getElementById(targetTab + '-form').classList.add('active');
        clearAllErrors();
    });
});

function toggleLoginPassword() {
    const input = document.getElementById('login-password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function toggleRegisterPassword() {
    const input = document.getElementById('register-password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function toggleConfirmPassword() {
    const input = document.getElementById('register-confirm');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const fileLabel = document.getElementById('file-label');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            preview.classList.add('active');
            fileLabel.textContent = input.files[0].name;
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
        preview.classList.remove('active');
        fileLabel.textContent = 'Choose Profile Picture';
    }
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorSpan = document.getElementById(fieldId + '-error');
    
    field.classList.add('error');
    errorSpan.textContent = message;
    errorSpan.classList.add('show');
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorSpan = document.getElementById(fieldId + '-error');
    
    field.classList.remove('error');
    errorSpan.textContent = '';
    errorSpan.classList.remove('show');
}

function clearAllErrors() {
    const errorMessages = document.querySelectorAll('.error-message');
    const errorInputs = document.querySelectorAll('.luxury-input.error');
    
    errorMessages.forEach(msg => {
        msg.textContent = '';
        msg.classList.remove('show');
    });
    
    errorInputs.forEach(input => {
        input.classList.remove('error');
    });
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function checkPasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8 && password.length <= 12,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*]/.test(password)
    };
    
    return requirements;
}

function updatePasswordRequirements(password) {
    const requirements = checkPasswordRequirements(password);
    const reqContainer = document.getElementById('passwordRequirements');
    const allValid = Object.values(requirements).every(val => val === true);

    if (password.length === 0) {
        reqContainer.classList.remove('show');
    } else if (!allValid) {
        reqContainer.classList.add('show');
    } else {
        // ✅ Hide once all requirements are met
        reqContainer.classList.remove('show');
    }

    Object.keys(requirements).forEach(req => {
        const element = document.querySelector(`[data-requirement="${req}"]`);
        if (element) {
            element.classList.remove('valid', 'invalid');
            if (password.length > 0) {
                element.classList.add(requirements[req] ? 'valid' : 'invalid');
            }
        }
    });

    return allValid;
}


function validateLoginForm() {
    clearAllErrors();
    let isValid = true;
    
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    
    if (email === '') {
        showError('login-email', 'Email address is required.');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('login-email', 'Please enter a valid email address.');
        isValid = false;
    }
    
    if (password === '') {
        showError('login-password', 'Password is required.');
        isValid = false;
    }
    
    return isValid;
}

function validateRegisterForm() {
    clearAllErrors();
    let isValid = true;
    
    const name = document.getElementById('register-name').value.trim();
    const email = document.getElementById('register-email').value.trim();
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm').value;
    
    if (name === '') {
        showError('register-name', 'Name is required.');
        isValid = false;
    } else if (name.length < 2) {
        showError('register-name', 'Name must be at least 2 characters long.');
        isValid = false;
    }
    
    if (email === '') {
        showError('register-email', 'Email address is required.');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('register-email', 'Please enter a valid email address.');
        isValid = false;
    }
    
    if (password === '') {
        showError('register-password', 'Password is required.');
        isValid = false;
    } else if (!updatePasswordRequirements(password)) {
        showError('register-password', 'Password does not meet all requirements.');
        isValid = false;
    }
    
    if (confirmPassword === '') {
        showError('register-confirm', 'Please confirm your password.');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('register-confirm', 'Passwords do not match.');
        isValid = false;
    }
    
    return isValid;
}

document.addEventListener('DOMContentLoaded', function() {
    const loginEmail = document.getElementById('login-email');
    const loginPassword = document.getElementById('login-password');
    
    if (loginEmail) {
        loginEmail.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value === '') {
                showError('login-email', 'Email address is required.');
            } else if (!validateEmail(value)) {
                showError('login-email', 'Please enter a valid email address.');
            } else {
                clearError('login-email');
            }
        });
        
        loginEmail.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError('login-email');
            }
        });
    }
    
    if (loginPassword) {
        loginPassword.addEventListener('blur', function() {
            if (this.value === '') {
                showError('login-password', 'Password is required.');
            } else {
                clearError('login-password');
            }
        });
        
        loginPassword.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError('login-password');
            }
        });
    }
    
    const regName = document.getElementById('register-name');
    const regEmail = document.getElementById('register-email');
    const regPassword = document.getElementById('register-password');
    const regConfirm = document.getElementById('register-confirm');
    
    if (regName) {
        regName.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value === '') {
                showError('register-name', 'Name is required.');
            } else if (value.length < 2) {
                showError('register-name', 'Name must be at least 2 characters long.');
            } else {
                clearError('register-name');
            }
        });
        
        regName.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError('register-name');
            }
        });
    }
    
    if (regEmail) {
        regEmail.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value === '') {
                showError('register-email', 'Email address is required.');
            } else if (!validateEmail(value)) {
                showError('register-email', 'Please enter a valid email address.');
            } else {
                clearError('register-email');
            }
        });
        
        regEmail.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError('register-email');
            }
        });
    }
    
    if (regPassword) {
        regPassword.addEventListener('focus', function() {
            document.getElementById('passwordRequirements').classList.add('show');
        });
        
        regPassword.addEventListener('input', function() {
            updatePasswordRequirements(this.value);
            if (this.classList.contains('error')) {
                clearError('register-password');
            }
        });
        
        regPassword.addEventListener('blur', function() {
            const value = this.value;
            if (value === '') {
                showError('register-password', 'Password is required.');
                document.getElementById('passwordRequirements').classList.remove('show');
            } else if (!updatePasswordRequirements(value)) {
                showError('register-password', 'Password does not meet all requirements.');
            } else {
                clearError('register-password');
            }
        });
    }
    
    if (regConfirm) {
        regConfirm.addEventListener('blur', function() {
            const password = document.getElementById('register-password').value;
            const value = this.value;
            if (value === '') {
                showError('register-confirm', 'Please confirm your password.');
            } else if (password !== value) {
                showError('register-confirm', 'Passwords do not match.');
            } else {
                clearError('register-confirm');
            }
        });
        
        regConfirm.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError('register-confirm');
            }
        });
    }
    
    const alerts = document.querySelectorAll('.luxury-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>

<?php
require_once('../includes/footer.php');
?>