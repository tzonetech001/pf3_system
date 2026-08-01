<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current police officer data
$stmt = $pdo->prepare("SELECT * FROM police_officers WHERE id = ?");
$stmt->execute([$user_id]);
$officer = $stmt->fetch();

if (!$officer) {
    $_SESSION['error_message'] = "Officer not found!";
    header('Location: dashboard.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $rank = trim($_POST['rank']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        // Validation
        $errors = [];
        
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($rank)) $errors[] = "Rank is required";
        if (empty($email)) $errors[] = "Email is required";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        if (empty($phone)) $errors[] = "Phone number is required";
        
        // Check if email already exists for another officer
        $stmt = $pdo->prepare("SELECT id FROM police_officers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists for another officer";
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE police_officers 
                    SET first_name = ?, last_name = ?, rank = ?, email = ?, phone = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $rank, $email, $phone, $user_id]);
                
                // Update session name
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                
                $success_message = "Profile updated successfully!";
                
                // Refresh officer data
                $stmt = $pdo->prepare("SELECT * FROM police_officers WHERE id = ?");
                $stmt->execute([$user_id]);
                $officer = $stmt->fetch();
                
                logAudit($user_id, 'police', 'Profile Updated', "Updated profile information");
                
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (empty($current_password)) $errors[] = "Current password is required";
        if (empty($new_password)) $errors[] = "New password is required";
        if (strlen($new_password) < 6) $errors[] = "New password must be at least 6 characters";
        if ($new_password !== $confirm_password) $errors[] = "New passwords do not match";
        
        // Verify current password
        if (empty($errors)) {
            if (!password_verify($current_password, $officer['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
        
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE police_officers SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                $success_message = "Password changed successfully! Please use your new password next time you login.";
                logAudit($user_id, 'police', 'Password Changed', 'Changed account password');
                
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
}

// Get statistics for the officer
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_processed,
        SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as total_approved,
        SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as total_rejected
    FROM pf3_cases 
    WHERE police_notes IS NOT NULL
");
$stmt->execute();
$stats = $stmt->fetch();

// Get recent activity
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    WHERE user_id = ? AND user_type = 'police'
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .profile-header-card {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
        color: white;
    }
    
    .profile-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        transform: rotate(45deg);
    }
    
    .profile-avatar-large {
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
        z-index: 1;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .profile-avatar-large span {
        font-size: 48px;
        font-weight: 700;
        color: #0d47a1;
    }
    
    .stat-card-profile {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
        border-left: 4px solid #0d47a1;
    }
    
    .stat-card-profile:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(13, 71, 161, 0.15);
    }
    
    .stat-card-profile .card-body {
        padding: 1rem 1.25rem;
    }
    
    .info-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        background: white;
    }
    
    .info-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 1.25rem 1.5rem;
        border-radius: 20px 20px 0 0;
    }
    
    .info-card .card-header h6 {
        color: #1a237e;
        font-weight: 600;
    }
    
    .info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #2d3748;
    }
    
    .activity-timeline {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .timeline-item-profile {
        padding: 1rem;
        border-left: 3px solid #0d47a1;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .timeline-item-profile:hover {
        background: #e3f2fd;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transform: translateX(5px);
    }
    
    .form-control-modern, .form-select-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.15);
    }
    
    .btn-modern {
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .btn-primary {
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-primary:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    .btn-warning {
        background: #ff9800;
        border-color: #ff9800;
        color: white;
    }
    
    .btn-warning:hover {
        background: #e68900;
        border-color: #e68900;
        color: white;
    }
    
    .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
    }
    
    .nav-tabs-custom {
        border-bottom: 2px solid #e8eaf6;
        margin-bottom: 1.5rem;
    }
    
    .nav-tabs-custom .nav-link {
        border: none;
        color: #4a5568;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .nav-tabs-custom .nav-link:hover {
        color: #0d47a1;
        background: transparent;
    }
    
    .nav-tabs-custom .nav-link.active {
        color: #0d47a1;
        background: transparent;
    }
    
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
    }
    
    .password-strength {
        height: 5px;
        border-radius: 3px;
        margin-top: 8px;
        transition: all 0.3s ease;
    }
    
    .security-tip-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: none;
        border-radius: 20px;
    }
    
    .security-tip-card .text-primary {
        color: #0d47a1 !important;
    }
    
    .alert-success {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
    }
    
    .alert-danger {
        background: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }
    
    @media (max-width: 768px) {
        .profile-avatar-large {
            width: 80px;
            height: 80px;
        }
        .profile-avatar-large span {
            font-size: 32px;
        }
        .nav-tabs-custom .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        .stat-card-profile h3 {
            font-size: 1.3rem;
        }
    }
</style>

<div class="row g-4">
    <!-- Left Column - Profile Info & Stats -->
    <div class="col-lg-4">
        <!-- Profile Header Card -->
        <div class="profile-header-card text-center">
            <div class="profile-avatar-large">
                <?php 
                $name = $officer['first_name'] . ' ' . $officer['last_name'];
                $initials = '';
                $nameParts = explode(' ', $name);
                if (count($nameParts) >= 2) {
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                } else {
                    $initials = strtoupper(substr($name, 0, 2));
                }
                ?>
                <span><?php echo $initials; ?></span>
            </div>
            <h4 class="mb-1 fw-bold"><?php echo htmlspecialchars($name); ?></h4>
            <p class="mb-2">
                <i class="fas fa-badge me-1"></i> <?php echo htmlspecialchars($officer['rank']); ?>
            </p>
            <small>
                <i class="fas fa-calendar-alt me-1"></i> Member since <?php echo date('F Y', strtotime($officer['created_at'])); ?>
            </small>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card stat-card-profile shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small text-uppercase">Total Processed</span>
                                <h3 class="mb-0 fw-bold mt-1 text-primary"><?php echo $stats['total_processed'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-folder-open fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card-profile shadow-sm" style="border-left-color: #28a745;">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle d-inline-block mb-2">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <h4 class="mb-0 fw-bold text-success"><?php echo $stats['total_approved'] ?? 0; ?></h4>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card-profile shadow-sm" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="bg-danger bg-opacity-10 p-2 rounded-circle d-inline-block mb-2">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <h4 class="mb-0 fw-bold text-danger"><?php echo $stats['total_rejected'] ?? 0; ?></h4>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Info Card -->
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-address-card me-2 text-primary"></i>Contact Information
                </h6>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-envelope me-1"></i> Email Address
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($officer['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-phone me-1"></i> Phone Number
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($officer['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-id-card me-1"></i> Officer ID
                    </div>
                    <div class="info-value">#<?php echo str_pad($officer['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-clock me-1"></i> Last Login
                    </div>
                    <div class="info-value">
                        <?php 
                        $lastLogin = !empty($activities) ? $activities[0]['created_at'] : $officer['created_at'];
                        echo date('d/m/Y H:i', strtotime($lastLogin));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Tabs -->
    <div class="col-lg-8">
        <div class="card info-card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                            <i class="fas fa-history me-2"></i>Activity Log
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="tab-content">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="edit" role="tabpanel">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label fw-semibold">
                                        <i class="fas fa-user me-1 text-primary"></i> First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-modern" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($officer['first_name']); ?>" required>
                                    <div class="invalid-feedback">First name is required</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-semibold">
                                        <i class="fas fa-user me-1 text-primary"></i> Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-modern" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($officer['last_name']); ?>" required>
                                    <div class="invalid-feedback">Last name is required</div>
                                </div>
                                
                                <div class="col-md-12">
                                    <label for="rank" class="form-label fw-semibold">
                                        <i class="fas fa-badge me-1 text-primary"></i> Rank <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-modern" id="rank" name="rank" required>
                                        <option value="">Select Rank</option>
                                        <option value="Police Constable" <?php echo $officer['rank'] == 'Police Constable' ? 'selected' : ''; ?>>Police Constable (PC)</option>
                                        <option value="Corporal" <?php echo $officer['rank'] == 'Corporal' ? 'selected' : ''; ?>>Corporal (CPL)</option>
                                        <option value="Sergeant" <?php echo $officer['rank'] == 'Sergeant' ? 'selected' : ''; ?>>Sergeant (SGT)</option>
                                        <option value="Inspector" <?php echo $officer['rank'] == 'Inspector' ? 'selected' : ''; ?>>Inspector (INSP)</option>
                                        <option value="Chief Inspector" <?php echo $officer['rank'] == 'Chief Inspector' ? 'selected' : ''; ?>>Chief Inspector (C/INSP)</option>
                                        <option value="Assistant Superintendent" <?php echo $officer['rank'] == 'Assistant Superintendent' ? 'selected' : ''; ?>>Assistant Superintendent (ASP)</option>
                                        <option value="Superintendent" <?php echo $officer['rank'] == 'Superintendent' ? 'selected' : ''; ?>>Superintendent (SP)</option>
                                        <option value="Senior Superintendent" <?php echo $officer['rank'] == 'Senior Superintendent' ? 'selected' : ''; ?>>Senior Superintendent (SSP)</option>
                                        <option value="Commissioner" <?php echo $officer['rank'] == 'Commissioner' ? 'selected' : ''; ?>>Commissioner (CP)</option>
                                    </select>
                                    <div class="invalid-feedback">Rank is required</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-1 text-primary"></i> Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control form-control-modern" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($officer['email']); ?>" required>
                                    <div class="invalid-feedback">Valid email is required</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">
                                        <i class="fas fa-phone me-1 text-primary"></i> Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control form-control-modern" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($officer['phone']); ?>" required>
                                    <div class="invalid-feedback">Phone number is required</div>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" name="update_profile" class="btn btn-primary btn-modern">
                                        <i class="fas fa-save me-2"></i> Update Profile
                                    </button>
                                    <button type="reset" class="btn btn-secondary btn-modern">
                                        <i class="fas fa-undo me-2"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <form method="POST" action="" onsubmit="return validatePasswordForm()">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="current_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-1 text-primary"></i> Current Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-modern" id="current_password" 
                                           name="current_password" required>
                                    <div class="form-text">Please enter your current password to verify identity</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label fw-semibold">
                                        <i class="fas fa-key me-1 text-primary"></i> New Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-modern" id="new_password" 
                                           name="new_password" required onkeyup="checkPasswordStrength()">
                                    <div class="password-strength" id="passwordStrength"></div>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="fas fa-check-circle me-1 text-primary"></i> Confirm New Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-modern" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                                
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2 text-primary"></i> Password requirements:
                                        <ul class="mb-0 mt-2">
                                            <li>Minimum 6 characters long</li>
                                            <li>Use a mix of letters and numbers</li>
                                            <li>Avoid common passwords</li>
                                            <li>Don't use personal information</li>
                                        </ul>
                                    </div>
                                    
                                    <hr>
                                    <button type="submit" name="change_password" class="btn btn-warning btn-modern">
                                        <i class="fas fa-key me-2"></i> Change Password
                                    </button>
                                    <button type="reset" class="btn btn-secondary btn-modern">
                                        <i class="fas fa-undo me-2"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Activity Log Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel">
                        <?php if (count($activities) > 0): ?>
                            <div class="activity-timeline">
                                <?php foreach ($activities as $activity): ?>
                                    <div class="timeline-item-profile">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <?php 
                                                    $icon = 'fa-clock';
                                                    $bgColor = 'bg-secondary';
                                                    if (strpos($activity['action'], 'Approved') !== false) {
                                                        $icon = 'fa-check-circle';
                                                        $bgColor = 'bg-success';
                                                    }
                                                    elseif (strpos($activity['action'], 'Rejected') !== false) {
                                                        $icon = 'fa-times-circle';
                                                        $bgColor = 'bg-danger';
                                                    }
                                                    elseif (strpos($activity['action'], 'Updated') !== false) {
                                                        $icon = 'fa-edit';
                                                        $bgColor = 'bg-info';
                                                    }
                                                    elseif (strpos($activity['action'], 'Login') !== false) {
                                                        $icon = 'fa-sign-in-alt';
                                                        $bgColor = 'bg-primary';
                                                    }
                                                    elseif (strpos($activity['action'], 'Password') !== false) {
                                                        $icon = 'fa-key';
                                                        $bgColor = 'bg-warning';
                                                    }
                                                    elseif (strpos($activity['action'], 'Registered') !== false) {
                                                        $icon = 'fa-user-plus';
                                                        $bgColor = 'bg-success';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $bgColor; ?> me-2">
                                                    <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo htmlspecialchars($activity['action']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i:s', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-muted small">
                                            <i class="fas fa-info-circle me-1 text-primary"></i>
                                            <?php echo htmlspecialchars($activity['details']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No activity logs found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Tips Card -->
        <div class="card security-tip-card">
            <div class="card-body">
                <h6 class="card-title fw-bold mb-3 text-primary">
                    <i class="fas fa-shield-alt me-2"></i>Security Best Practices
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <small>Never share your password with anyone</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <small>Use a strong, unique password for this account</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <small>Always log out when leaving your workstation</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <small>Report any suspicious activity to system administrator</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-primary mt-1"></i>
                            <small>Keep your contact information up to date</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation for edit profile
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Password strength checker
function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthBar = document.getElementById('passwordStrength');
    
    if (password.length === 0) {
        strengthBar.style.display = 'none';
        return;
    }
    
    strengthBar.style.display = 'block';
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    const width = (strength / 5) * 100;
    strengthBar.style.width = width + '%';
    
    if (strength <= 2) {
        strengthBar.style.backgroundColor = '#dc3545';
    } else if (strength <= 4) {
        strengthBar.style.backgroundColor = '#ffc107';
    } else {
        strengthBar.style.backgroundColor = '#28a745';
    }
}

// Password validation
function validatePasswordForm() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    
    if (!currentPassword) {
        alert('Please enter your current password');
        return false;
    }
    
    if (newPassword.length < 6) {
        alert('New password must be at least 6 characters long');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return false;
    }
    
    if (newPassword === currentPassword) {
        alert('New password cannot be the same as current password');
        return false;
    }
    
    return confirm('Are you sure you want to change your password? You will need to use the new password for future logins.');
}

// Phone number formatting
document.getElementById('phone')?.addEventListener('input', function(e) {
    let phone = this.value.replace(/\D/g, '');
    if (phone.length > 10) phone = phone.slice(0, 10);
    this.value = phone;
});

// Show appropriate tab based on URL hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash === '#password') {
        const passwordTab = document.getElementById('password-tab');
        if (passwordTab) {
            bootstrap.Tab.getOrCreateInstance(passwordTab).show();
        }
    } else if (hash === '#activity') {
        const activityTab = document.getElementById('activity-tab');
        if (activityTab) {
            bootstrap.Tab.getOrCreateInstance(activityTab).show();
        }
    }
    
    // Initialize password strength checker
    const passwordInput = document.getElementById('new_password');
    if (passwordInput) {
        passwordInput.addEventListener('keyup', checkPasswordStrength);
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(function() {
            bsAlert.close();
        }, 5000);
    });
}, 1000);
</script>

<?php include 'footer.php'; ?>