<?php
ob_start();
session_start();
require_once('../includes/config.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to access this page.";
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user role
$user_stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Only admin can access this page
if ($user_data['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: dashboard.php");
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    // Validation
    $errors = [];
    
    // Name validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name can only contain letters and spaces.";
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Check if email already exists
        $check_email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_result = $check_email_stmt->get_result();
        if ($check_email_result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email.";
        }
        $check_email_stmt->close();
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    
    // Confirm password validation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Role validation
    if (!in_array($role, ['admin', 'staff'])) {
        $errors[] = "Invalid role selected.";
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin/staff
        $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, img_name, is_active) VALUES (?, ?, ?, ?, 'nopfp.jpg', 1)");
        $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        if ($insert_stmt->execute()) {
            $success_message = ucfirst($role) . " account created successfully!";
            // Clear form fields
            $name = $email = $password = $confirm_password = '';
            $role = 'staff';
        } else {
            $error_message = "Error creating account. Please try again.";
        }
        $insert_stmt->close();
    } else {
        $error_message = implode("<br>", $errors);
    }
}

require_once('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="add-admin-page">
    <div class="add-admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <a href="dashboard.php" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Dashboard
                </a>
                <h1 class="page-title">Add Admin / Staff</h1>
                <p class="page-subtitle">Create a new administrator or staff account</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span><?php echo $success_message; ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?php echo $error_message; ?></span>
        </div>
        <?php endif; ?>

        <!-- Add Admin Form -->
        <div class="form-container">
            <form method="POST" action="" id="addAdminForm" novalidate>
                <div class="form-grid">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Full Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input"
                            value="<?php echo htmlspecialchars($name ?? ''); ?>"
                            placeholder="Enter full name"
                        >
                        <span class="form-error" id="name-error"></span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email Address <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            placeholder="admin@example.com"
                        >
                        <span class="form-error" id="email-error"></span>
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label for="role" class="form-label">
                            Role <span class="required">*</span>
                        </label>
                        <select id="role" name="role" class="form-input">
                            <option value="staff" <?php echo (isset($role) && $role === 'staff') ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                        <span class="form-hint">Staff have limited access, Admins have full access</span>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input"
                                placeholder="Min. 8 characters"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-hint">Must contain uppercase, lowercase, and number</span>
                        <span class="form-error" id="password-error"></span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            Confirm Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input"
                                placeholder="Re-enter password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-error" id="confirm_password-error"></span>
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <div class="requirements-title">Password Requirements:</div>
                    <ul class="requirements-list">
                        <li id="req-length">At least 8 characters</li>
                        <li id="req-uppercase">One uppercase letter</li>
                        <li id="req-lowercase">One lowercase letter</li>
                        <li id="req-number">One number</li>
                    </ul>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Create Account
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once('../includes/footer.php'); ?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Montserrat', sans-serif;
    background: #ffffff;
    color: #1a1a1a;
    line-height: 1.6;
}

.add-admin-page {
    min-height: 100vh;
    padding: 100px 30px 60px;
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.add-admin-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
    margin-bottom: 30px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    text-decoration: none;
    margin-bottom: 20px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #0a0a0a;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 400;
    margin-bottom: 8px;
    color: #0a0a0a;
}

.page-subtitle {
    font-size: 13px;
    color: rgba(0,0,0,0.5);
    letter-spacing: 0.3px;
}

/* Alerts */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px 25px;
    margin-bottom: 30px;
    border-left: 3px solid;
    font-size: 13px;
    letter-spacing: 0.3px;
}

.alert svg {
    flex-shrink: 0;
    margin-top: 2px;
}

.alert-success {
    background: #f0fdf4;
    border-color: #166534;
    color: #166534;
}

.alert-error {
    background: #fef2f2;
    border-color: #b91c1c;
    color: #b91c1c;
}

/* Form Container */
.form-container {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 40px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.7);
    font-weight: 500;
}

.required {
    color: #b91c1c;
}

.form-input {
    width: 100%;
    padding: 14px 18px;
    border: 1px solid rgba(0,0,0,0.15);
    background: #ffffff;
    font-size: 14px;
    font-family: 'Montserrat', sans-serif;
    color: #0a0a0a;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #0a0a0a;
    background: #fafafa;
}

.form-input::placeholder {
    color: rgba(0,0,0,0.3);
}

.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: rgba(0,0,0,0.4);
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: #0a0a0a;
}

.form-hint {
    font-size: 10px;
    color: rgba(0,0,0,0.4);
    letter-spacing: 0.3px;
}

.form-error {
    font-size: 11px;
    color: #b91c1c;
    letter-spacing: 0.3px;
    display: none;
}

.form-error.show {
    display: block;
}

/* Password Requirements */
.password-requirements {
    padding: 20px 25px;
    background: #fafafa;
    border-left: 3px solid rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.requirements-title {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.7);
    font-weight: 600;
    margin-bottom: 12px;
}

.requirements-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.requirements-list li {
    font-size: 12px;
    color: rgba(0,0,0,0.5);
    padding-left: 25px;
    position: relative;
    transition: color 0.3s ease;
}

.requirements-list li::before {
    content: "○";
    position: absolute;
    left: 0;
    color: rgba(0,0,0,0.3);
}

.requirements-list li.met {
    color: #166534;
}

.requirements-list li.met::before {
    content: "✓";
    color: #166534;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 32px;
    border: 1px solid;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #0a0a0a;
    border-color: #0a0a0a;
    color: #ffffff;
}

.btn-primary:hover {
    background: #2a2a2a;
    border-color: #2a2a2a;
}

.btn-secondary {
    background: transparent;
    border-color: rgba(0,0,0,0.15);
    color: #0a0a0a;
}

.btn-secondary:hover {
    border-color: #0a0a0a;
    background: #fafafa;
}

/* Responsive */
@media (max-width: 768px) {
    .add-admin-page {
        padding: 80px 20px 50px;
    }

    .page-header {
        padding: 30px 25px;
    }

    .page-title {
        font-size: 26px;
    }

    .form-container {
        padding: 30px 25px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 22px;
    }

    .form-container {
        padding: 25px 20px;
    }
}
</style>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}

// Real-time password validation
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    
    // Check length
    const lengthReq = document.getElementById('req-length');
    if (password.length >= 8) {
        lengthReq.classList.add('met');
    } else {
        lengthReq.classList.remove('met');
    }
    
    // Check uppercase
    const uppercaseReq = document.getElementById('req-uppercase');
    if (/[A-Z]/.test(password)) {
        uppercaseReq.classList.add('met');
    } else {
        uppercaseReq.classList.remove('met');
    }
    
    // Check lowercase
    const lowercaseReq = document.getElementById('req-lowercase');
    if (/[a-z]/.test(password)) {
        lowercaseReq.classList.add('met');
    } else {
        lowercaseReq.classList.remove('met');
    }
    
    // Check number
    const numberReq = document.getElementById('req-number');
    if (/[0-9]/.test(password)) {
        numberReq.classList.add('met');
    } else {
        numberReq.classList.remove('met');
    }
});

// Form validation
document.getElementById('addAdminForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.form-error').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
    });
    
    // Name validation
    const name = document.getElementById('name').value.trim();
    if (name === '') {
        showError('name', 'Name is required');
        isValid = false;
    } else if (name.length < 2) {
        showError('name', 'Name must be at least 2 characters');
        isValid = false;
    } else if (!/^[a-zA-Z\s]+$/.test(name)) {
        showError('name', 'Name can only contain letters and spaces');
        isValid = false;
    }
    
    // Email validation
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Password validation
    const password = document.getElementById('password').value;
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 8) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    } else if (!/[A-Z]/.test(password)) {
        showError('password', 'Password must contain an uppercase letter');
        isValid = false;
    } else if (!/[a-z]/.test(password)) {
        showError('password', 'Password must contain a lowercase letter');
        isValid = false;
    } else if (!/[0-9]/.test(password)) {
        showError('password', 'Password must contain a number');
        isValid = false;
    }
    
    // Confirm password validation
    const confirmPassword = document.getElementById('confirm_password').value;
    if (confirmPassword === '') {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});

function showError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + '-error');
    errorEl.textContent = message;
    errorEl.classList.add('show');
}
</script>

<?php ob_end_flush(); ?>