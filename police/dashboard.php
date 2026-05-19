<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

// Get statistics
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pf3_cases GROUP BY status");
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent cases
$recentCases = $pdo->query("
    SELECT c.*, p.full_name 
    FROM pf3_cases c 
    JOIN patients p ON c.pf3_number = p.pf3_number 
    ORDER BY c.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get monthly statistics
$monthlyStats = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
    FROM pf3_cases 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
")->fetchAll();

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
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Cases</h6>
                        <h3 class="mb-0 fw-bold"><?php echo array_sum($stats); ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Cases</h6>
                        <h3 class="mb-0 fw-bold text-warning"><?php echo $stats['PENDING'] ?? 0; ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Approved Cases</h6>
                        <h3 class="mb-0 fw-bold text-success"><?php echo $stats['APPROVED'] ?? 0; ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Rejected Cases</h6>
                        <h3 class="mb-0 fw-bold text-danger"><?php echo $stats['REJECTED'] ?? 0; ?></h3>
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
    <!-- Recent Cases -->
    <div class="col-lg-8">
        <div class="card table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-history me-2"></i>Recent Cases
                </h6>
                <a href="cases.php?status=PENDING" class="btn btn-sm btn-primary">
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
                                <th>Incident Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentCases) > 0): ?>
                                <?php foreach ($recentCases as $case): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($case['pf3_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
                                    <td>
                                        <span class="badge-status badge bg-<?php 
                                            echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo $case['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($case['created_at'])); ?></td>
                                    <td>
                                        <a href="view_case.php?pf3=<?php echo $case['pf3_number']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        No cases found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Statistics -->
    <div class="col-lg-4">
        <div class="card table-card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-line me-2"></i>Monthly Statistics
                </h6>
                <small class="text-muted">Last 6 months</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Approved</th>
                                <th>Rejected</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($monthlyStats) > 0): ?>
                                <?php foreach ($monthlyStats as $stat): ?>
                                <tr>
                                    <td><?php echo date('M Y', strtotime($stat['month'] . '-01')); ?></td>
                                    <td class="text-success fw-bold"><?php echo $stat['approved']; ?></td>
                                    <td class="text-danger fw-bold"><?php echo $stat['rejected']; ?></td>
                                    <td class="fw-bold"><?php echo $stat['total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3">No data available</td>
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