<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
$patients = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors");
$doctors = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM police_officers");
$police = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM pf3_cases");
$total_cases = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM pf3_cases WHERE status = 'APPROVED'");
$approved_cases = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM pf3_cases WHERE status = 'PENDING'");
$pending_cases = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM medical_reports");
$medical_reports = $stmt->fetch()['count'];

// Get recent activities
$stmt = $pdo->query("
    SELECT * FROM audit_logs 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recent_activities = $stmt->fetchAll();

include 'header.php';
?>

<style>
    body {
        background: rgba(13, 71, 161, 0.05);
    }
    
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        background: white;
        border-left: 4px solid #0d47a1;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(13, 71, 161, 0.15);
    }
    
    .stat-card .card-body {
        padding: 1.25rem;
    }
    
    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-card h6 {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-card h3 {
        font-size: 1.8rem;
        font-weight: 700;
    }
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: white;
    }
    
    .table-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 1.25rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }
    
    .table-card .card-header h6 {
        color: #1a237e;
        font-weight: 600;
    }
    
    .quick-action-btn {
        padding: 1rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        text-align: center;
        background: #f5f7fa;
        border: 1px solid #e8eaf6;
        cursor: pointer;
    }
    
    .quick-action-btn:hover {
        background: linear-gradient(135deg, #0d47a1, #1976d2);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(13, 71, 161, 0.3);
        border-color: #0d47a1;
    }
    
    .quick-action-btn i {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .quick-action-btn:hover i {
        color: white !important;
    }
    
    .quick-action-btn:hover div {
        color: white !important;
    }
    
    .table th {
        background: #e8eaf6;
        color: #1a237e;
        font-weight: 600;
    }
    
    .badge-activity {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .stat-card h3 {
            font-size: 1.3rem;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }
</style>

<div class="row g-4 mb-4">
    <!-- Statistics Cards -->
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Patients</h6>
                        <h3 class="mb-0 text-primary"><?php echo $patients; ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #0d47a1;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Doctors</h6>
                        <h3 class="mb-0 text-info"><?php echo $doctors; ?></h3>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #ff9800;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Police Officers</h6>
                        <h3 class="mb-0 text-warning"><?php echo $police; ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Medical Reports</h6>
                        <h3 class="mb-0 text-success"><?php echo $medical_reports; ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-file-medical"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #6c757d;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Cases</h6>
                        <h3 class="mb-0 text-secondary"><?php echo $total_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Cases</h6>
                        <h3 class="mb-0 text-warning"><?php echo $pending_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Approved Cases</h6>
                        <h3 class="mb-0 text-success"><?php echo $approved_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm" style="border-left-color: #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Rejected Cases</h6>
                        <h3 class="mb-0 text-danger"><?php echo $total_cases - $approved_cases - $pending_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card table-card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="openRegisterDoctorModal()">
                            <i class="fas fa-user-md text-info"></i>
                            <div class="fw-bold small">Register Doctor</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="openRegisterPoliceModal()">
                            <i class="fas fa-user-shield text-warning"></i>
                            <div class="fw-bold small">Register Police</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="quick-action-btn" onclick="window.location.href='manage_users.php'">
                            <i class="fas fa-users text-primary"></i>
                            <div class="fw-bold small">Manage Users</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="quick-action-btn" onclick="window.location.href='reports.php'">
                            <i class="fas fa-chart-bar text-success"></i>
                            <div class="fw-bold small">View Reports</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-lg-8">
        <div class="card table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>Recent Activities
                </h6>
                <a href="audit_log.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>User Type</th>
                                <th>Details</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_activities) > 0): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td>
                                        <span class="badge-activity badge bg-<?php 
                                            if (strpos($activity['action'], 'Approved') !== false) echo 'success';
                                            elseif (strpos($activity['action'], 'Rejected') !== false) echo 'danger';
                                            elseif (strpos($activity['action'], 'Login') !== false) echo 'primary';
                                            elseif (strpos($activity['action'], 'Updated') !== false) echo 'info';
                                            elseif (strpos($activity['action'], 'Deleted') !== false) echo 'dark';
                                            else echo 'secondary';
                                        ?>">
                                            <?php echo htmlspecialchars($activity['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst(htmlspecialchars($activity['user_type'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr($activity['details'], 0, 50)); ?>...</td>
                                    <td><small><?php echo date('d/m/Y H:i:s', strtotime($activity['created_at'])); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No activities found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Doctor Modal -->
<div class="modal fade" id="registerDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-md me-2"></i>Register New Doctor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_register.php" method="POST" onsubmit="return validateDoctorForm()">
                <div class="modal-body">
                    <input type="hidden" name="type" value="doctor">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Position/Specialization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="position" placeholder="e.g., Cardiologist, General Physician" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="doctor_password" name="password" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="doctor_confirm_password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Police Modal -->
<div class="modal fade" id="registerPoliceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-shield me-2"></i>Register New Police Officer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_register.php" method="POST" onsubmit="return validatePoliceForm()">
                <div class="modal-body">
                    <input type="hidden" name="type" value="police">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Rank/Position <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="police_password" name="password" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="police_confirm_password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Police Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRegisterDoctorModal() {
    const modal = new bootstrap.Modal(document.getElementById('registerDoctorModal'));
    modal.show();
}

function openRegisterPoliceModal() {
    const modal = new bootstrap.Modal(document.getElementById('registerPoliceModal'));
    modal.show();
}

function validateDoctorForm() {
    const password = document.getElementById('doctor_password').value;
    const confirm = document.getElementById('doctor_confirm_password').value;
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    
    return true;
}

function validatePoliceForm() {
    const password = document.getElementById('police_password').value;
    const confirm = document.getElementById('police_confirm_password').value;
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    
    return true;
}
</script>

<?php include 'footer.php'; ?>