<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Handle different actions
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Deactivate/Activate
if ($action === 'toggle' && $type && $id) {
    try {
        if ($type === 'doctor') {
            $stmt = $pdo->prepare("UPDATE doctors SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Doctor status updated successfully!";
            logAudit($_SESSION['user_id'], 'admin', 'Toggled Doctor Status', "Doctor ID: $id");
        } elseif ($type === 'police') {
            $stmt = $pdo->prepare("UPDATE police_officers SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Police officer status updated successfully!";
            logAudit($_SESSION['user_id'], 'admin', 'Toggled Police Status', "Police ID: $id");
        } elseif ($type === 'admin') {
            // Prevent deactivating own account
            if ($id == $_SESSION['user_id']) {
                $error = "You cannot deactivate your own account!";
            } else {
                $stmt = $pdo->prepare("UPDATE admins SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Admin status updated successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Toggled Admin Status', "Admin ID: $id");
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle Delete
if ($action === 'delete' && $type && $id) {
    try {
        if ($type === 'doctor') {
            // Check if doctor has reports
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM medical_reports WHERE doctor_id = ?");
            $stmt->execute([$id]);
            $report_count = $stmt->fetchColumn();
            
            if ($report_count > 0) {
                $error = "Cannot delete this doctor. They have $report_count medical report(s) associated.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Doctor deleted successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Deleted Doctor', "Doctor ID: $id");
            }
        } elseif ($type === 'police') {
            // Check if police has cases
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pf3_cases WHERE police_notes IS NOT NULL");
            $stmt->execute();
            $case_count = $stmt->fetchColumn();
            
            if ($case_count > 0) {
                $error = "Cannot delete this police officer. They have processed $case_count case(s).";
            } else {
                $stmt = $pdo->prepare("DELETE FROM police_officers WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Police officer deleted successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Deleted Police Officer', "Police ID: $id");
            }
        } elseif ($type === 'admin') {
            // Prevent deleting own account
            if ($id == $_SESSION['user_id']) {
                $error = "You cannot delete your own account!";
            } else {
                $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Admin deleted successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Deleted Admin', "Admin ID: $id");
            }
        }
    } catch (PDOException $e) {
        $error = "Cannot delete user. They may have associated records.";
    }
}

// Handle Edit User - Get user data for modal
$edit_user = null;
if ($action === 'edit' && $type && $id) {
    if ($type === 'doctor') {
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $edit_user = $stmt->fetch();
    } elseif ($type === 'police') {
        $stmt = $pdo->prepare("SELECT * FROM police_officers WHERE id = ?");
        $stmt->execute([$id]);
        $edit_user = $stmt->fetch();
    } elseif ($type === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $edit_user = $stmt->fetch();
    }
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $type = $_POST['user_type'];
    $user_id = (int)$_POST['user_id'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    
    try {
        if ($type === 'doctor') {
            $position = trim($_POST['position']);
            if (empty($position)) $errors[] = "Position is required";
            
            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE doctors SET first_name = ?, last_name = ?, position = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $position, $email, $phone, $user_id]);
                $success = "Doctor updated successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Updated Doctor', "Doctor ID: $user_id");
            }
        } elseif ($type === 'police') {
            $rank = trim($_POST['rank']);
            if (empty($rank)) $errors[] = "Rank is required";
            
            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE police_officers SET first_name = ?, last_name = ?, rank = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $rank, $email, $phone, $user_id]);
                $success = "Police officer updated successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Updated Police Officer', "Police ID: $user_id");
            }
        } elseif ($type === 'admin') {
            $username = trim($_POST['username']);
            if (empty($username)) $errors[] = "Username is required";
            
            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$username, $email, $phone, $user_id]);
                $success = "Admin updated successfully!";
                logAudit($_SESSION['user_id'], 'admin', 'Updated Admin', "Admin ID: $user_id");
            }
        }
        
        if (!empty($errors)) {
            $error = implode("<br>", $errors);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $type = $_POST['user_type'];
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            if ($type === 'doctor') {
                $stmt = $pdo->prepare("UPDATE doctors SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            } elseif ($type === 'police') {
                $stmt = $pdo->prepare("UPDATE police_officers SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            } elseif ($type === 'admin') {
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            }
            $success = "Password reset successfully!";
            logAudit($_SESSION['user_id'], 'admin', 'Reset Password', "$type ID: $user_id");
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all users with status
$doctors = $pdo->query("SELECT * FROM doctors ORDER BY created_at DESC")->fetchAll();
$police_officers = $pdo->query("SELECT * FROM police_officers ORDER BY created_at DESC")->fetchAll();
$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC")->fetchAll();

// Get statistics
$active_doctors = count(array_filter($doctors, function($d) { return $d['status'] == 'active'; }));
$active_police = count(array_filter($police_officers, function($p) { return $p['status'] == 'active'; }));
$active_admins = count(array_filter($admins, function($a) { return $a['status'] == 'active'; }));

include 'header.php';
?>

<style>
    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        color: #A6EDCF ;
        background: transparent;
    }
    
    .nav-tabs-custom .nav-link.active {
        color: #A6EDCF ;
        background: transparent;
    }
    
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(135deg, #A6EDCF  0%, #A6EDCF  100%);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .user-avatar-small {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #A6EDCF  0%, #A6EDCF  100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .filter-input {
        max-width: 300px;
        margin-bottom: 1rem;
    }
    
    .filter-input input {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.5rem 1rem;
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="fas fa-users me-2"></i>Manage Users
        </h4>
        <p class="text-muted mb-0">View, edit, delete, and manage user accounts</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button class="btn btn-danger" onclick="openRegisterDoctorModal()">
            <i class="fas fa-user-md me-2"></i>Add Doctor
        </button>
        <button class="btn btn-warning" onclick="openRegisterPoliceModal()">
            <i class="fas fa-user-shield me-2"></i>Add Police
        </button>
    </div>
</div>

<!-- Statistics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-user-md fa-2x text-info mb-2"></i>
            <h5 class="mb-1">Doctors</h5>
            <div class="d-flex justify-content-center gap-3">
                <span class="badge bg-success">Active: <?php echo $active_doctors; ?></span>
                <span class="badge bg-secondary">Total: <?php echo count($doctors); ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
            <h5 class="mb-1">Police Officers</h5>
            <div class="d-flex justify-content-center gap-3">
                <span class="badge bg-success">Active: <?php echo $active_police; ?></span>
                <span class="badge bg-secondary">Total: <?php echo count($police_officers); ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-user-cog fa-2x text-danger mb-2"></i>
            <h5 class="mb-1">Admins</h5>
            <div class="d-flex justify-content-center gap-3">
                <span class="badge bg-success">Active: <?php echo $active_admins; ?></span>
                <span class="badge bg-secondary">Total: <?php echo count($admins); ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card table-card">
    <div class="card-body">
        <ul class="nav nav-tabs-custom" id="userTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="doctors-tab" data-bs-toggle="tab" data-bs-target="#doctors" type="button">
                    <i class="fas fa-user-md me-2"></i>Doctors 
                    <span class="badge bg-secondary ms-1"><?php echo count($doctors); ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="police-tab" data-bs-toggle="tab" data-bs-target="#police" type="button">
                    <i class="fas fa-user-shield me-2"></i>Police Officers
                    <span class="badge bg-secondary ms-1"><?php echo count($police_officers); ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button">
                    <i class="fas fa-user-cog me-2"></i>Admins
                    <span class="badge bg-secondary ms-1"><?php echo count($admins); ?></span>
                </button>
            </li>
        </ul>
        
        <div class="tab-content mt-3">
            <!-- Doctors Tab -->
            <div class="tab-pane fade show active" id="doctors">
                <div class="filter-input float-end">
                    <input type="text" class="form-control" id="searchDoctors" placeholder="Search doctors..." onkeyup="filterTable('doctorsTable', this.value)">
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="table table-hover" id="doctorsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td>#<?php echo $doctor['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-small">
                                            <?php echo strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($doctor['position']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($doctor['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($doctor['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($doctor['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info" onclick="editUser('doctor', <?php echo $doctor['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="resetPassword('doctor', <?php echo $doctor['id']; ?>, '<?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-sm <?php echo ($doctor['status'] ?? 'active') == 'active' ? 'btn-secondary' : 'btn-success'; ?>" 
                                                onclick="toggleUserStatus('doctor', <?php echo $doctor['id']; ?>)">
                                            <i class="fas <?php echo ($doctor['status'] ?? 'active') == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser('doctor', <?php echo $doctor['id']; ?>, '<?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($doctors)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No doctors found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Police Tab -->
            <div class="tab-pane fade" id="police">
                <div class="filter-input float-end">
                    <input type="text" class="form-control" id="searchPolice" placeholder="Search police..." onkeyup="filterTable('policeTable', this.value)">
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="table table-hover" id="policeTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Rank</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($police_officers as $officer): ?>
                            <tr>
                                <td>#<?php echo $officer['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-small">
                                            <?php echo strtoupper(substr($officer['first_name'], 0, 1) . substr($officer['last_name'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($officer['rank']); ?></td>
                                <td><?php echo htmlspecialchars($officer['email']); ?></td>
                                <td><?php echo htmlspecialchars($officer['phone']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($officer['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($officer['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($officer['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info" onclick="editUser('police', <?php echo $officer['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="resetPassword('police', <?php echo $officer['id']; ?>, '<?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?>')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-sm <?php echo ($officer['status'] ?? 'active') == 'active' ? 'btn-secondary' : 'btn-success'; ?>" 
                                                onclick="toggleUserStatus('police', <?php echo $officer['id']; ?>)">
                                            <i class="fas <?php echo ($officer['status'] ?? 'active') == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser('police', <?php echo $officer['id']; ?>, '<?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($police_officers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No police officers found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Admins Tab -->
            <div class="tab-pane fade" id="admins">
                <div class="filter-input float-end">
                    <input type="text" class="form-control" id="searchAdmins" placeholder="Search admins..." onkeyup="filterTable('adminsTable', this.value)">
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="table table-hover" id="adminsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>#<?php echo $admin['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-small">
                                            <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($admin['username']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['phone']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($admin['status'] ?? 'active') == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($admin['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-info" onclick="editUser('admin', <?php echo $admin['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="resetPassword('admin', <?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn btn-sm <?php echo ($admin['status'] ?? 'active') == 'active' ? 'btn-secondary' : 'btn-success'; ?>" 
                                                    onclick="toggleUserStatus('admin', <?php echo $admin['id']; ?>)">
                                                <i class="fas <?php echo ($admin['status'] ?? 'active') == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser('admin', <?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-info">Current User</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No admins found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" onsubmit="return validateEditForm()">
                <div class="modal-body">
                    <input type="hidden" name="update_user" value="1">
                    <input type="hidden" name="user_type" id="edit_user_type">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6" id="edit_first_name_div">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-6" id="edit_last_name_div">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="col-md-12" id="edit_position_div">
                            <label class="form-label">Position/Specialization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="position" id="edit_position">
                        </div>
                        <div class="col-md-12" id="edit_rank_div" style="display: none;">
                            <label class="form-label">Rank <span class="text-danger">*</span></label>
                            <select class="form-select" name="rank" id="edit_rank">
                                <option value="">Select Rank</option>
                                <option value="Police Constable">Police Constable (PC)</option>
                                <option value="Corporal">Corporal (CPL)</option>
                                <option value="Sergeant">Sergeant (SGT)</option>
                                <option value="Inspector">Inspector (INSP)</option>
                                <option value="Chief Inspector">Chief Inspector (C/INSP)</option>
                                <option value="Assistant Superintendent">Assistant Superintendent (ASP)</option>
                                <option value="Superintendent">Superintendent (SP)</option>
                                <option value="Senior Superintendent">Senior Superintendent (SSP)</option>
                                <option value="Commissioner">Commissioner (CP)</option>
                            </select>
                        </div>
                        <div class="col-md-12" id="edit_username_div" style="display: none;">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="edit_username">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" id="edit_phone" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" onsubmit="return validateResetPassword()">
                <div class="modal-body">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="hidden" name="user_type" id="reset_user_type">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control" id="reset_user_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Doctor Modal -->
<div class="modal fade" id="registerDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-md me-2"></i>Register New Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_register.php" method="POST" onsubmit="return validateDoctorForm()">
                <div class="modal-body">
                    <input type="hidden" name="type" value="doctor">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Position/Specialization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="position" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="doctor_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="doctor_confirm_password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Register Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Police Modal -->
<div class="modal fade" id="registerPoliceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Register New Police Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_register.php" method="POST" onsubmit="return validatePoliceForm()">
                <div class="modal-body">
                    <input type="hidden" name="type" value="police">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Rank <span class="text-danger">*</span></label>
                            <select class="form-select" name="rank" required>
                                <option value="">Select Rank</option>
                                <option value="Police Constable">Police Constable (PC)</option>
                                <option value="Corporal">Corporal (CPL)</option>
                                <option value="Sergeant">Sergeant (SGT)</option>
                                <option value="Inspector">Inspector (INSP)</option>
                                <option value="Chief Inspector">Chief Inspector (C/INSP)</option>
                                <option value="Assistant Superintendent">Assistant Superintendent (ASP)</option>
                                <option value="Superintendent">Superintendent (SP)</option>
                                <option value="Senior Superintendent">Senior Superintendent (SSP)</option>
                                <option value="Commissioner">Commissioner (CP)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="police_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="police_confirm_password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Register Police Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filter table function
function filterTable(tableId, searchText) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const search = searchText.toLowerCase();
    
    for (let row of rows) {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    }
}

// Delete user function
function deleteUser(type, id, name) {
    if (confirm(`Are you sure you want to delete ${name}? This action cannot be undone.`)) {
        window.location.href = `manage_users.php?action=delete&type=${type}&id=${id}`;
    }
}

// Toggle user status function
function toggleUserStatus(type, id) {
    if (confirm(`Are you sure you want to change the status of this user?`)) {
        window.location.href = `manage_users.php?action=toggle&type=${type}&id=${id}`;
    }
}

// Edit user function
function editUser(type, id) {
    fetch(`get_user.php?type=${type}&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_user_type').value = type;
                document.getElementById('edit_user_id').value = id;
                
                if (type === 'doctor') {
                    document.getElementById('edit_first_name_div').style.display = 'block';
                    document.getElementById('edit_last_name_div').style.display = 'block';
                    document.getElementById('edit_position_div').style.display = 'block';
                    document.getElementById('edit_rank_div').style.display = 'none';
                    document.getElementById('edit_username_div').style.display = 'none';
                    
                    document.getElementById('edit_first_name').value = data.user.first_name;
                    document.getElementById('edit_last_name').value = data.user.last_name;
                    document.getElementById('edit_position').value = data.user.position;
                    document.getElementById('edit_position').required = true;
                } else if (type === 'police') {
                    document.getElementById('edit_first_name_div').style.display = 'block';
                    document.getElementById('edit_last_name_div').style.display = 'block';
                    document.getElementById('edit_position_div').style.display = 'none';
                    document.getElementById('edit_rank_div').style.display = 'block';
                    document.getElementById('edit_username_div').style.display = 'none';
                    
                    document.getElementById('edit_first_name').value = data.user.first_name;
                    document.getElementById('edit_last_name').value = data.user.last_name;
                    document.getElementById('edit_rank').value = data.user.rank;
                } else if (type === 'admin') {
                    document.getElementById('edit_first_name_div').style.display = 'none';
                    document.getElementById('edit_last_name_div').style.display = 'none';
                    document.getElementById('edit_position_div').style.display = 'none';
                    document.getElementById('edit_rank_div').style.display = 'none';
                    document.getElementById('edit_username_div').style.display = 'block';
                    
                    document.getElementById('edit_username').value = data.user.username;
                    document.getElementById('edit_username').required = true;
                }
                
                document.getElementById('edit_email').value = data.user.email;
                document.getElementById('edit_phone').value = data.user.phone;
                
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            } else {
                alert('Error loading user data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user data');
        });
}

// Reset password function
function resetPassword(type, id, name) {
    document.getElementById('reset_user_type').value = type;
    document.getElementById('reset_user_id').value = id;
    document.getElementById('reset_user_name').value = name;
    
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

// Validate edit form
function validateEditForm() {
    const type = document.getElementById('edit_user_type').value;
    
    if (type === 'doctor') {
        const position = document.getElementById('edit_position').value;
        if (!position) {
            alert('Position is required');
            return false;
        }
    } else if (type === 'police') {
        const rank = document.getElementById('edit_rank').value;
        if (!rank) {
            alert('Rank is required');
            return false;
        }
    } else if (type === 'admin') {
        const username = document.getElementById('edit_username').value;
        if (!username) {
            alert('Username is required');
            return false;
        }
    }
    
    return true;
}

// Validate reset password
function validateResetPassword() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    if (newPass !== confirmPass) {
        alert('Passwords do not match');
        return false;
    }
    
    return confirm('Are you sure you want to reset the password? The user will need to use the new password.');
}

// Validate doctor registration
function validateDoctorForm() {
    const password = document.getElementById('doctor_password').value;
    const confirm = document.getElementById('doctor_confirm_password').value;
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters');
        return false;
    }
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    return true;
}

// Validate police registration
function validatePoliceForm() {
    const password = document.getElementById('police_password').value;
    const confirm = document.getElementById('police_confirm_password').value;
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters');
        return false;
    }
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    return true;
}

// Open modals
function openRegisterDoctorModal() {
    const modal = new bootstrap.Modal(document.getElementById('registerDoctorModal'));
    modal.show();
}

function openRegisterPoliceModal() {
    const modal = new bootstrap.Modal(document.getElementById('registerPoliceModal'));
    modal.show();
}

// Search functionality
function filterTable(tableId, searchText) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const search = searchText.toLowerCase();
    
    for (let row of rows) {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    }
}
</script>

<?php include 'footer.php'; ?>