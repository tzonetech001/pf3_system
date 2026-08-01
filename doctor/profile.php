<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current doctor data
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$user_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    $_SESSION['error_message'] = "Doctor not found!";
    header('Location: dashboard.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $position = trim($_POST['position']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        $errors = [];
        
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($position)) $errors[] = "Position is required";
        if (empty($email)) $errors[] = "Email is required";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        if (empty($phone)) $errors[] = "Phone number is required";
        
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists for another doctor";
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE doctors 
                    SET first_name = ?, last_name = ?, position = ?, email = ?, phone = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $position, $email, $phone, $user_id]);
                
                $_SESSION['user_name'] = 'Dr. ' . $first_name . ' ' . $last_name;
                $success_message = "Profile updated successfully!";
                
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
                $stmt->execute([$user_id]);
                $doctor = $stmt->fetch();
                
                logAudit($user_id, 'doctor', 'Profile Updated', "Updated profile information");
                
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (empty($current_password)) $errors[] = "Current password is required";
        if (empty($new_password)) $errors[] = "New password is required";
        if (strlen($new_password) < 6) $errors[] = "New password must be at least 6 characters";
        if ($new_password !== $confirm_password) $errors[] = "New passwords do not match";
        
        if (empty($errors)) {
            if (!password_verify($current_password, $doctor['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
        
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE doctors SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                $success_message = "Password changed successfully!";
                logAudit($user_id, 'doctor', 'Password Changed', 'Changed account password');
                
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
}

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_reports FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$total_reports = $stmt->fetch()['total_reports'];

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT pf3_number) as total_patients FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$total_patients = $stmt->fetch()['total_patients'];

// Get severity breakdown
$stmt = $pdo->prepare("
    SELECT severity, COUNT(*) as count 
    FROM medical_reports 
    WHERE doctor_id = ? 
    GROUP BY severity
");
$stmt->execute([$user_id]);
$severity_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent activity
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    WHERE user_id = ? AND user_type = 'doctor'
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
        background: white;
        border-left: 4px solid #0d47a1;
    }
    
    .stat-card-profile:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(13, 71, 161, 0.1);
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
    
    .nav-tabs-custom {
        border-bottom: 2px solid #e2e8f0;
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
    
    .form-control:focus, .form-select:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.15);
    }
</style>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="profile-header-card text-center">
            <div class="profile-avatar-large">
                <span>
                    <?php 
                    $name = $doctor['first_name'] . ' ' . $doctor['last_name'];
                    $initials = '';
                    $nameParts = explode(' ', $name);
                    if (count($nameParts) >= 2) {
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                    } else {
                        $initials = strtoupper(substr($name, 0, 2));
                    }
                    echo $initials;
                    ?>
                </span>
            </div>
            <h4 class="mb-1 fw-bold">Dr. <?php echo htmlspecialchars($name); ?></h4>
            <p class="mb-2">
                <i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($doctor['position']); ?>
            </p>
            <small>
                <i class="fas fa-calendar-alt me-1"></i> Member since <?php echo date('F Y', strtotime($doctor['created_at'])); ?>
            </small>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card stat-card-profile shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small text-uppercase">Total Reports</span>
                                <h3 class="mb-0 fw-bold mt-1 text-primary"><?php echo $total_reports; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-file-medical fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card stat-card-profile shadow-sm" style="border-left-color: #28a745;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small text-uppercase">Total Patients</span>
                                <h3 class="mb-0 fw-bold mt-1 text-success"><?php echo $total_patients; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card stat-card-profile shadow-sm" style="border-left-color: #ff9800;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small text-uppercase">Severe Cases</span>
                                <h3 class="mb-0 fw-bold mt-1 text-warning"><?php echo $severity_stats['Severe'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-address-card me-2"></i>Contact Information
                </h6>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-envelope me-1"></i> Email Address
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($doctor['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-phone me-1"></i> Phone Number
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($doctor['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-id-card me-1"></i> Doctor ID
                    </div>
                    <div class="info-value">#<?php echo str_pad($doctor['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card info-card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom" id="profileTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                            <i class="fas fa-history me-2"></i>Activity Log
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="edit">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($doctor['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($doctor['last_name']); ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Position/Specialization <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="position" value="<?php echo htmlspecialchars($doctor['position']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Update Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="password">
                        <form method="POST" action="" onsubmit="return validatePassword()">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i> Change Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="activity">
                        <?php if (count($activities) > 0): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item-profile">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong class="text-primary"><?php echo htmlspecialchars($activity['action']); ?></strong>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i:s', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0 text-muted small"><?php echo htmlspecialchars($activity['details']); ?></p>
                                </div>
                            <?php endforeach; ?>
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
    </div>
</div>

<script>
function validatePassword() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass.length < 6) {
        alert('New password must be at least 6 characters');
        return false;
    }
    
    if (newPass !== confirmPass) {
        alert('Passwords do not match');
        return false;
    }
    
    return confirm('Are you sure you want to change your password?');
}
</script>

<?php include 'footer.php'; ?>