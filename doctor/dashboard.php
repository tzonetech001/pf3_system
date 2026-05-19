<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$user_id = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$total_reports = $stmt->fetch()['count'];

// Get recent reports
$stmt = $pdo->prepare("
    SELECT mr.*, p.full_name, p.pf3_number 
    FROM medical_reports mr 
    JOIN patients p ON mr.pf3_number = p.pf3_number 
    WHERE mr.doctor_id = ? 
    ORDER BY mr.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_reports = $stmt->fetchAll();

// Get approved cases pending medical report
$stmt = $pdo->prepare("
    SELECT c.*, p.full_name, p.phone 
    FROM pf3_cases c 
    JOIN patients p ON c.pf3_number = p.pf3_number 
    WHERE c.status = 'APPROVED' 
    AND c.pf3_number NOT IN (SELECT pf3_number FROM medical_reports WHERE doctor_id = ?)
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$pending_reports = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }
    
    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
</style>

<div class="row g-4 mb-4">
    <!-- Statistics Cards -->
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Reports</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $total_reports; ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-medical"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Reports</h6>
                        <h3 class="mb-0 fw-bold text-warning"><?php echo count($pending_reports); ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Patients</h6>
                        <h3 class="mb-0 fw-bold text-success">
                            <?php 
                            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT pf3_number) as count FROM medical_reports WHERE doctor_id = ?");
                            $stmt->execute([$user_id]);
                            echo $stmt->fetch()['count'];
                            ?>
                        </h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Reports -->
    <div class="col-lg-7">
        <div class="card table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-history me-2"></i>Recent Medical Reports
                </h6>
                <a href="my_reports.php" class="btn btn-sm btn-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>PF3 Number</th>
                                <th>Patient Name</th>
                                <th>Injury Type</th>
                                <th>Severity</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_reports) > 0): ?>
                                <?php foreach ($recent_reports as $report): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($report['pf3_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($report['injury_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $report['severity'] == 'Severe' ? 'danger' : ($report['severity'] == 'Moderate' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo htmlspecialchars($report['severity']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <a href="view_patient.php?pf3=<?php echo $report['pf3_number']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-file-alt fa-2x text-muted mb-2 d-block"></i>
                                        No reports found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Reports -->
    <div class="col-lg-5">
        <div class="card table-card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-tasks me-2"></i>Pending Medical Reports
                </h6>
                <small class="text-muted">Approved cases awaiting your report</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>PF3 Number</th>
                                <th>Patient Name</th>
                                <th>Incident Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_reports) > 0): ?>
                                <?php foreach ($pending_reports as $case): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($case['pf3_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
                                    <td>
                                        <a href="view_patient.php?pf3=<?php echo $case['pf3_number']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Create Report
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                        No pending reports
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>