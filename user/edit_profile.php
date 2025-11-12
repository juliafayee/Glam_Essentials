<?php
ob_start();
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$error = '';
$success = '';
$redirect = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Basic validation
    if ($name === '' || $email === '') {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check email uniqueness
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = "Email is already in use by another account.";
        }
        $check->close();
    }

    // Handle profile picture upload
    $img_name = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        // Validate file type and size (max 5MB)
        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($file_size > 5242880) {
            $error = "Image size must be less than 5MB.";
        } else {
			// Create upload directory if it doesn't exist (relative to this file)
			$upload_dir = rtrim(str_replace('\\', '/', __DIR__), '/') . '/images/profile_pictures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $img_name = $new_filename;
                
                // Delete old profile picture if exists
                $old_pic_stmt = $conn->prepare("SELECT img_name FROM users WHERE user_id = ?");
                $old_pic_stmt->bind_param("i", $user_id);
                $old_pic_stmt->execute();
                $old_pic_result = $old_pic_stmt->get_result();
                $old_pic_data = $old_pic_result->fetch_assoc();
                $old_pic_stmt->close();
                
					if (!empty($old_pic_data['img_name'])) {
                    $old_pic_full_path = $upload_dir . $old_pic_data['img_name'];
					if (file_exists($old_pic_full_path) && $old_pic_data['img_name'] !== 'nopfp.jpg') {
                        unlink($old_pic_full_path);
                    }
                }
            } else {
                $error = "Failed to upload profile picture.";
            }
        }
    }

    // Update database if no errors
    if ($error === '') {
        if ($img_name !== null) {
            // Update with new profile picture
            $up = $conn->prepare("UPDATE users SET name = ?, email = ?, img_name = ? WHERE user_id = ?");
            $up->bind_param("sssi", $name, $email, $img_name, $user_id);
        } else {
            // Update without changing profile picture
            $up = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            $up->bind_param("ssi", $name, $email, $user_id);
        }
        
        if ($up->execute()) {
            $_SESSION['user_name'] = $name; // Update session
            $success = "Profile updated successfully. Redirecting...";
            $redirect = true;
        } else {
            $error = "Unable to update profile. Please try again.";
        }
        $up->close();
    }
}

// Load current user data
$stmt = $conn->prepare("SELECT name, email, img_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ensure default profile image is set in DB for users without one
if (empty($current['img_name'])) {
	$defaultImg = 'nopfp.jpg';
	$upd = $conn->prepare("UPDATE users SET img_name = ? WHERE user_id = ?");
	$upd->bind_param("si", $defaultImg, $user_id);
	if ($upd->execute()) {
		$current['img_name'] = $defaultImg;
	}
	$upd->close();
}

// Check if user has customer details
$customer_stmt = $conn->prepare("SELECT customer_id, address, contact_no, title, town, zipcode FROM customers WHERE user_id = ?");
$customer_stmt->bind_param("i", $user_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer_data = $customer_result->fetch_assoc();
$customer_stmt->close();

// Handle customer details update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    $title = trim($_POST['title'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $town = trim($_POST['town'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $fullname = trim($_POST['fullname'] ?? $name);

    if ($customer_data) {
        // Update existing customer record
        $cust_up = $conn->prepare("UPDATE customers SET fullname = ?, title = ?, address = ?, contact_no = ?, town = ?, zipcode = ? WHERE user_id = ?");
        $cust_up->bind_param("ssssssi", $fullname, $title, $address, $contact_no, $town, $zipcode, $user_id);
        $cust_up->execute();
        $cust_up->close();
    } elseif (!empty($address) || !empty($contact_no) || !empty($town) || !empty($zipcode) || !empty($title)) {
        // Create new customer record
        $cust_ins = $conn->prepare("INSERT INTO customers (user_id, fullname, title, address, contact_no, town, zipcode) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $cust_ins->bind_param("issssss", $user_id, $fullname, $title, $address, $contact_no, $town, $zipcode);
        $cust_ins->execute();
        $cust_ins->close();
    }
}

require_once('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="edit-profile-page">
    <div class="edit-container">
        <div class="page-header">
            <h1 class="lux-title">Edit Profile</h1>
            <p class="lux-subtitle">Refine your personal details</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <!-- Current Profile Picture Preview -->
            <div class="current-avatar-section">
                <div class="avatar-preview">
                    <?php 
					if (!empty($current['img_name'])) {
						$current_pic = htmlspecialchars($baseUrl) . '/user/images/profile_pictures/' . $current['img_name'];
                    } else {
						$current_pic = htmlspecialchars($baseUrl) . '/user/images/profile_pictures/nopfp.jpg';
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($current_pic); ?>" 
                         alt="Current Profile Picture" 
                         class="current-avatar"
                         id="avatarPreview"
						 onerror="this.src='<?php echo htmlspecialchars($baseUrl); ?>/user/images/profile_pictures/nopfp.jpg';">
                </div>
                <div class="avatar-info">
                    <h3 class="avatar-name"><?php echo htmlspecialchars($current['name']); ?></h3>
                    <p class="avatar-username"><?php echo htmlspecialchars($current['email']); ?></p>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="edit-form">
                <div class="form-section">
                    <h3 class="section-title">Profile Picture</h3>
                    
                    <div class="form-group file-upload-group">
                        <label for="profile_picture" class="file-upload-label">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span class="upload-text">Choose New Picture</span>
                            <span class="upload-hint">JPG, PNG, GIF • Max 5MB</span>
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/jpg,image/gif" class="file-input">
                        <div class="file-name" id="fileName"></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Personal Information</h3>
                    
                    <div class="form-grid">
                        <!-- Title Radio Buttons -->
                        <div class="form-group full-width">
                            <label>Title</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="title" value="Mr." 
                                           <?php echo (isset($customer_data['title']) && $customer_data['title'] === 'Mr.') ? 'checked' : ''; ?>>
                                    <span class="radio-text">Mr.</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="title" value="Ms." 
                                           <?php echo (isset($customer_data['title']) && $customer_data['title'] === 'Ms.') ? 'checked' : ''; ?>>
                                    <span class="radio-text">Ms.</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="title" value="Mrs." 
                                           <?php echo (isset($customer_data['title']) && $customer_data['title'] === 'Mrs.') ? 'checked' : ''; ?>>
                                    <span class="radio-text">Mrs.</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($current['name'] ?? ''); ?>" 
                                   required class="form-input">
                        </div>

                        <div class="form-group full-width">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($current['email'] ?? ''); ?>" 
                                   required class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="contact_no">Contact Number</label>
                            <input type="text" id="contact_no" name="contact_no" 
                                   value="<?php echo htmlspecialchars($customer_data['contact_no'] ?? ''); ?>" 
                                   class="form-input" placeholder="+63 XXX XXX XXXX">
                        </div>

                        <div class="form-group full-width">
                            <label for="address">Delivery Address</label>
                            <textarea id="address" name="address" rows="3" class="form-textarea" 
                                      placeholder="Enter your complete delivery address"><?php echo htmlspecialchars($customer_data['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="town">Town/City</label>
                            <input type="text" id="town" name="town" 
                                   value="<?php echo htmlspecialchars($customer_data['town'] ?? ''); ?>" 
                                   class="form-input" placeholder="Enter your town or city">
                        </div>

                        <div class="form-group">
                            <label for="zipcode">Zip Code</label>
                            <input type="text" id="zipcode" name="zipcode" 
                                   value="<?php echo htmlspecialchars($customer_data['zipcode'] ?? ''); ?>" 
                                   class="form-input" placeholder="Enter zip code" maxlength="10">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">
                        <span>Save Changes</span>
                    </button>
                    <a href="../index.php" class="btn btn-cancel">
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php if ($redirect): ?>
<script>
    setTimeout(function() {
        window.location.href = 'profile.php';
    }, 1500);
</script>
<?php endif; ?>

<script>
// File input preview
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileName = file?.name;
    const fileNameDisplay = document.getElementById('fileName');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (fileName) {
        fileNameDisplay.textContent = fileName;
        fileNameDisplay.style.display = 'block';
        
        // Validate file size (5MB)
        if (file.size > 5242880) {
            alert('File size must be less than 5MB');
            e.target.value = '';
            fileNameDisplay.style.display = 'none';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG, PNG, and GIF images are allowed');
            e.target.value = '';
            fileNameDisplay.style.display = 'none';
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(event) {
            avatarPreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        fileNameDisplay.style.display = 'none';
    }
});
</script>

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

.edit-profile-page {
  min-height: 100vh;
  padding: 100px 30px 60px;
  background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.edit-container {
  max-width: 800px;
  margin: 0 auto;
}

/* ===== PAGE HEADER ===== */
.page-header {
  text-align: center;
  margin-bottom: 50px;
  padding-bottom: 30px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.lux-title {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 400;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
  color: #0a0a0a;
}

.lux-subtitle {
  font-family: 'Montserrat', sans-serif;
  font-size: 10px;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.5);
  font-weight: 400;
}

/* ===== ALERTS ===== */
.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  margin-bottom: 30px;
  border-radius: 0;
  font-size: 12px;
  letter-spacing: 0.3px;
  border: 1px solid;
}

.alert svg {
  flex-shrink: 0;
}

.alert-error {
  background: #fef2f2;
  border-color: #fecaca;
  color: #b02a37;
}

.alert-success {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

/* ===== PROFILE CARD ===== */
.profile-card {
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.08);
  padding: 40px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.profile-card:hover {
  border-color: rgba(0,0,0,0.12);
  box-shadow: 0 15px 40px rgba(0,0,0,0.06);
}

/* ===== CURRENT AVATAR ===== */
.current-avatar-section {
  display: flex;
  align-items: center;
  gap: 25px;
  padding-bottom: 35px;
  margin-bottom: 35px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.avatar-preview {
  flex-shrink: 0;
}

.current-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

.current-avatar:hover {
  border-color: rgba(0,0,0,0.2);
  transform: scale(1.05);
}

.avatar-info {
  flex: 1;
}

.avatar-name {
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  font-weight: 400;
  margin-bottom: 6px;
  color: #0a0a0a;
}

.avatar-username {
  font-size: 11px;
  letter-spacing: 1.5px;
  color: rgba(0,0,0,0.5);
  font-weight: 400;
}

/* ===== FORM SECTIONS ===== */
.form-section {
  margin-bottom: 40px;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 400;
  margin-bottom: 25px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
  color: #0a0a0a;
}

/* ===== FILE UPLOAD ===== */
.file-upload-group {
  position: relative;
}

.file-upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px;
  border: 2px dashed rgba(0,0,0,0.15);
  background: #fafafa;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.file-upload-label:hover {
  border-color: rgba(0,0,0,0.3);
  background: #f5f5f5;
}

.file-upload-label svg {
  opacity: 0.5;
}

.upload-text {
  font-size: 11px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-weight: 500;
  color: #0a0a0a;
}

.upload-hint {
  font-size: 10px;
  letter-spacing: 0.5px;
  color: rgba(0,0,0,0.5);
}

.file-input {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.file-name {
  display: none;
  margin-top: 12px;
  padding: 10px 15px;
  background: #f0f0f0;
  font-size: 11px;
  letter-spacing: 0.3px;
  color: #2a2a2a;
  border-left: 3px solid #0a0a0a;
}

/* ===== RADIO BUTTONS ===== */
.radio-group {
  display: flex;
  gap: 20px;
  margin-top: 12px;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.radio-label input[type="radio"] {
  appearance: none;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.2);
  border-radius: 50%;
  position: relative;
  cursor: pointer;
  transition: all 0.2s ease;
}

.radio-label input[type="radio"]:checked {
  border-color: #0a0a0a;
}

.radio-label input[type="radio"]:checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 10px;
  height: 10px;
  background: #0a0a0a;
  border-radius: 50%;
}

.radio-label:hover input[type="radio"] {
  border-color: #0a0a0a;
}

.radio-text {
  font-size: 13px;
  letter-spacing: 0.3px;
  color: #0a0a0a;
  user-select: none;
}

/* ===== FORM GRID ===== */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(0,0,0,0.6);
  font-weight: 500;
}

.required {
  color: #b02a37;
  margin-left: 2px;
}

.form-input,
.form-textarea {
  padding: 14px 18px;
  border: 1px solid rgba(0,0,0,0.12);
  background: #ffffff;
  font-size: 13px;
  letter-spacing: 0.3px;
  color: #0a0a0a;
  font-family: 'Montserrat', sans-serif;
  transition: all 0.3s ease;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #0a0a0a;
  box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: rgba(0,0,0,0.3);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

/* ===== FORM ACTIONS ===== */
.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid rgba(0,0,0,0.06);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 35px;
  border: 1px solid rgba(0,0,0,0.15);
  background: transparent;
  color: #0a0a0a;
  text-transform: uppercase;
  font-size: 9px;
  letter-spacing: 2px;
  font-weight: 500;
  position: relative;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}

.btn span {
  position: relative;
  z-index: 2;
  transition: color 0.4s ease;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 100%;
  background: #0a0a0a;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
}

.btn:hover::before {
  width: 100%;
}

.btn:hover {
  border-color: #0a0a0a;
}

.btn:hover span {
  color: #ffffff;
}

.btn-save {
  background: #0a0a0a;
  color: #ffffff;
  border-color: #0a0a0a;
}

.btn-save::before {
  background: #2a2a2a;
}

.btn-save span {
  color: #ffffff;
}

.btn-cancel {
  border-color: rgba(0,0,0,0.12);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .edit-profile-page {
    padding: 80px 20px 50px;
  }

  .page-header {
    margin-bottom: 40px;
    padding-bottom: 25px;
  }

  .lux-title {
    font-size: 28px;
  }

  .lux-subtitle {
    font-size: 9px;
    letter-spacing: 2px;
  }

  .profile-card {
    padding: 30px 25px;
  }

  .current-avatar-section {
    flex-direction: column;
    text-align: center;
    padding-bottom: 30px;
    margin-bottom: 30px;
  }

  .current-avatar {
    width: 80px;
    height: 80px;
  }

  .avatar-name {
    font-size: 20px;
  }

  .form-section {
    margin-bottom: 35px;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .file-upload-label {
    padding: 30px 20px;
  }

  .radio-group {
    gap: 15px;
  }

  .form-actions {
    flex-direction: column;
    gap: 12px;
    margin-top: 30px;
    padding-top: 25px;
  }

  .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .lux-title {
    font-size: 24px;
  }

  .profile-card {
    padding: 25px 20px;
  }

  .section-title {
    font-size: 16px;
  }

  .form-input,
  .form-textarea {
    padding: 12px 15px;
    font-size: 12px;
  }

  .radio-group {
    flex-direction: column;
    gap: 12px;
  }
}
</style>

<?php ob_end_flush(); ?>