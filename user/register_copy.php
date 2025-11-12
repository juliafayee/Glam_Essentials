<?php
session_start();

// Use the same simple login stylesheet for register
$pageCss = 'login.css';
include("../includes/header.php");

// Pre-fill values if returned from store.php on validation error
$old = $_SESSION['old'] ?? ['name' => '', 'email' => ''];
unset($_SESSION['old']);
?>

<main class="auth-page">
    <div class="auth-card">
        <?php include("../includes/alert.php"); ?>
        
        <h2 style="text-align: center;">Create Account</h2>

        <form action="store.php" method="POST">
            <div class="form-outline">
                <input type="text" name="name" class="form-control" placeholder="Full name" required value="<?php echo htmlspecialchars($old['name'] ?? '', ENT_QUOTES); ?>">
            </div>

            <div class="form-outline">
                <input type="email" name="email" class="form-control" placeholder="Email address" required value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES); ?>">
            </div>

            <div class="form-outline password-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <div class="password-requirements" id="passwordRequirements">
                    <div class="req" data-requirement="length">• 8-12 characters</div>
                    <div class="req" data-requirement="uppercase">• At least one uppercase letter (A-Z)</div>
                    <div class="req" data-requirement="lowercase">• At least one lowercase letter (a-z)</div>
                    <div class="req" data-requirement="number">• At least one number (0-9)</div>
                    <div class="req" data-requirement="special">• At least one special character (!@#$%^&*)</div>
                </div>
            </div>

            <div class="form-outline confirm-password-group">
                <input type="password" name="confirmPass" id="password2" class="form-control" placeholder="Confirm password" required>
                <div class="password-match-indicator" id="passwordMatchIndicator">✕</div>
            </div>

            <button type="submit" class="btn btn-primary" id="registerBtn" disabled>Register</button>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const password = document.getElementById('password');
                const requirements = document.getElementById('passwordRequirements');
                const submit = document.getElementById('registerBtn');
                const confirmPass = document.getElementById('password2');
                
                function checkPassword(value) {
                    const checks = {
                        length: value.length >= 8 && value.length <= 12,
                        uppercase: /[A-Z]/.test(value),
                        lowercase: /[a-z]/.test(value),
                        number: /[0-9]/.test(value),
                        special: /[!@#$%^&*]/.test(value)
                    };
                    
                    let allMet = true;
                    Object.entries(checks).forEach(([key, met]) => {
                        const req = requirements.querySelector(`[data-requirement="${key}"]`);
                        if (met) {
                            req.classList.add('met');
                        } else {
                            req.classList.remove('met');
                            allMet = false;
                        }
                    });
                    
                    // Remove the requirements box entirely if all conditions are met
                    if (allMet) {
                        requirements.remove();
                    } else {
                        // Re-insert requirements if they were previously removed
                        if (!document.getElementById('passwordRequirements')) {
                            password.parentNode.appendChild(requirements.cloneNode(true));
                        }
                        requirements.style.display = value.length === 0 ? 'none' : 'block';
                    }
                    
                    return allMet;
                }
                
                function validateForm() {
                    const isPasswordValid = checkPassword(password.value);
                    const doPasswordsMatch = password.value === confirmPass.value;
                    const indicator = document.getElementById('passwordMatchIndicator');
                    
                    // Show/hide password match indicator
                    if (confirmPass.value.length > 0) {
                        indicator.style.display = 'block';
                        if (doPasswordsMatch) {
                            indicator.textContent = '✓';
                            indicator.style.color = '#059669';
                        } else {
                            indicator.textContent = '✕';
                            indicator.style.color = '#dc2626';
                        }
                    } else {
                        indicator.style.display = 'none';
                    }
                    
                    submit.disabled = !(isPasswordValid && doPasswordsMatch && password.value && confirmPass.value);
                }
                
                password.addEventListener('input', () => validateForm());
                confirmPass.addEventListener('input', () => validateForm());
                password.addEventListener('focus', () => {
                    if (password.value.length > 0) {
                        requirements.classList.add('visible');
                    }
                });
            });
            </script>
        </form>

        <div class="auth-footer text-center">
            <p>Already a member? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>