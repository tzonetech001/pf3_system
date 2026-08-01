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

// Get monthly case trends for chart
$monthly_trends = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending
    FROM pf3_cases 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
    LIMIT 6
")->fetchAll();

// Get case status distribution
$case_stats = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM pf3_cases 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent activities
$stmt = $pdo->query("
    SELECT * FROM audit_logs 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recent_activities = $stmt->fetchAll();

// Get recent cases
$recent_cases = $pdo->query("
    SELECT p.pf3_number, p.full_name, c.status, c.created_at 
    FROM pf3_cases c
    JOIN patients p ON c.pf3_number = p.pf3_number
    ORDER BY c.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get today's statistics
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM patients WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_patients = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pf3_cases WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_cases = $stmt->fetch()['count'];

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
    
    .chart-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: white;
        height: 100%;
    }
    
    .chart-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 1rem 1.25rem;
        border-radius: 15px 15px 0 0;
    }
    
    .chart-card .card-header h6 {
        color: #1a237e;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .chart-card .card-body {
        padding: 1rem;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
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
    
    .today-stats {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e8eaf6;
        transition: all 0.3s ease;
    }
    
    .today-stats:hover {
        border-color: #0d47a1;
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.1);
    }
    
    .today-stats .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0d47a1;
    }
    
    .today-stats .label {
        font-size: 0.8rem;
        color: #6c757d;
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
        .chart-container {
            height: 200px;
        }
    }
</style>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
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
        <div class="card stat-card shadow-sm" style="border-left-color: #17a2b8;">
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

<!-- Today's Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $today_patients; ?></div>
            <div class="label"><i class="fas fa-user-plus me-1"></i>Patients Today</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $today_cases; ?></div>
            <div class="label"><i class="fas fa-folder-open me-1"></i>Cases Today</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $pending_cases; ?></div>
            <div class="label"><i class="fas fa-clock me-1"></i>Pending Cases</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $approved_cases; ?></div>
            <div class="label"><i class="fas fa-check-circle me-1"></i>Approved</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Monthly Case Trends
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>Case Status Distribution
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="row mt-3 text-center">
                    <div class="col-4">
                        <span class="badge bg-warning">Pending: <?php echo $case_stats['PENDING'] ?? 0; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-success">Approved: <?php echo $case_stats['APPROVED'] ?? 0; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-danger">Rejected: <?php echo $case_stats['REJECTED'] ?? 0; ?></span>
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
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="window.location.href='manage_users.php'">
                            <i class="fas fa-users text-primary"></i>
                            <div class="fw-bold small">Manage Users</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="window.location.href='reports.php'">
                            <i class="fas fa-chart-bar text-success"></i>
                            <div class="fw-bold small">View Reports</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="window.location.href='audit_log.php'">
                            <i class="fas fa-history text-secondary"></i>
                            <div class="fw-bold small">Audit Log</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="quick-action-btn" onclick="window.location.href='profile.php'">
                            <i class="fas fa-user-cog text-danger"></i>
                            <div class="fw-bold small">My Profile</div>
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
                                            elseif (strpos($activity['action'], 'Registered') !== false) echo 'success';
                                            else echo 'secondary';
                                        ?>">
                                            <?php echo htmlspecialchars($activity['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst(htmlspecialchars($activity['user_type'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr($activity['details'], 0, 50)); ?><?php echo strlen($activity['details']) > 50 ? '...' : ''; ?></td>
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

<!-- Recent Cases -->
<div class="row g-4 mt-2">
    <div class="col-lg-12">
        <div class="card table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-folder-open me-2 text-primary"></i>Recent Cases
                </h6>
                <a href="../police/view_cases.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PF3 Number</th>
                                <th>Patient Name</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_cases) > 0): ?>
                                <?php foreach ($recent_cases as $case): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($case['pf3_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($case['status'] == 'APPROVED') echo 'success';
                                            elseif ($case['status'] == 'REJECTED') echo 'danger';
                                            else echo 'warning';
                                        ?>">
                                            <?php echo $case['status']; ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No cases found</td>
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
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="tel" class="form-control" name="phone" 
                                       placeholder="7XXXXXXXX or 6XXXXXXXX" required
                                       minlength="9" maxlength="9"
                                       pattern="[67][0-9]{8}">
                            </div>
                            <div class="form-text">Enter 9 digits after 255 (e.g., 7XXXXXXXX or 6XXXXXXXX)</div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Default password is: <strong>123456</strong>
                            </div>
                            <input type="hidden" name="password" value="123456">
                            <input type="hidden" name="confirm_password" value="123456">
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
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="tel" class="form-control" name="phone" 
                                       placeholder="7XXXXXXXX or 6XXXXXXXX" required
                                       minlength="9" maxlength="9"
                                       pattern="[67][0-9]{8}">
                            </div>
                            <div class="form-text">Enter 9 digits after 255 (e.g., 7XXXXXXXX or 6XXXXXXXX)</div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Default password is: <strong>123456</strong>
                            </div>
                            <input type="hidden" name="password" value="123456">
                            <input type="hidden" name="confirm_password" value="123456">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const months = <?php echo json_encode(array_column($monthly_trends, 'month')); ?>;
    const approved = <?php echo json_encode(array_column($monthly_trends, 'approved')); ?>;
    const rejected = <?php echo json_encode(array_column($monthly_trends, 'rejected')); ?>;
    const pending = <?php echo json_encode(array_column($monthly_trends, 'pending')); ?>;

    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: months.map(m => {
                const date = new Date(m + '-01');
                return date.toLocaleDateString('en', { month: 'short', year: 'numeric' });
            }),
            datasets: [
                {
                    label: 'Approved',
                    data: approved,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                },
                {
                    label: 'Pending',
                    data: pending,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                },
                {
                    label: 'Rejected',
                    data: rejected,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: { size: 11 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    <?php echo $case_stats['PENDING'] ?? 0; ?>,
                    <?php echo $case_stats['APPROVED'] ?? 0; ?>,
                    <?php echo $case_stats['REJECTED'] ?? 0; ?>
                ],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: { size: 11 }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Modal functions
    function openRegisterDoctorModal() {
        const modal = new bootstrap.Modal(document.getElementById('registerDoctorModal'));
        modal.show();
    }

    function openRegisterPoliceModal() {
        const modal = new bootstrap.Modal(document.getElementById('registerPoliceModal'));
        modal.show();
    }

    function validateDoctorForm() {
        return true;
    }

    function validatePoliceForm() {
        return true;
    }
</script>

<?php include 'footer.php'; ?>