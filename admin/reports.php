<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Get statistics
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pf3_cases GROUP BY status");
$case_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get monthly case trends
$monthly_trends = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
    FROM pf3_cases 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
")->fetchAll();

// Get doctor report counts
$doctor_stats = $pdo->query("
    SELECT 
        CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
        COUNT(mr.id) as report_count
    FROM doctors d
    LEFT JOIN medical_reports mr ON d.id = mr.doctor_id
    GROUP BY d.id
    ORDER BY report_count DESC
    LIMIT 10
")->fetchAll();

// Get police officer case counts
$police_stats = $pdo->query("
    SELECT 
        CONCAT(p.first_name, ' ', p.last_name) as officer_name,
        COUNT(c.id) as case_count
    FROM police_officers p
    LEFT JOIN pf3_cases c ON 1=1
    GROUP BY p.id
    ORDER BY case_count DESC
    LIMIT 10
")->fetchAll();

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
    
    .chart-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .chart-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }
    
    .stat-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
    }
</style>

<div class="page-header">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="fas fa-chart-bar me-2"></i>System Reports
        </h4>
        <p class="text-muted mb-0">Analytics and statistics for the PF3 system</p>
    </div>
</div>

<div class="row g-4">
    <!-- Case Status Chart -->
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-pie me-2"></i>Case Status Distribution
                </h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
                <div class="row mt-3 text-center">
                    <div class="col-4">
                        <div class="stat-badge bg-warning bg-opacity-10 text-warning">
                            <div>Pending</div>
                            <strong><?php echo $case_stats['PENDING'] ?? 0; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-badge bg-success bg-opacity-10 text-success">
                            <div>Approved</div>
                            <strong><?php echo $case_stats['APPROVED'] ?? 0; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-badge bg-danger bg-opacity-10 text-danger">
                            <div>Rejected</div>
                            <strong><?php echo $case_stats['REJECTED'] ?? 0; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Trends Chart -->
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-line me-2"></i>Monthly Case Trends
                </h6>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Doctor Performance -->
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-user-md me-2"></i>Top Doctors by Reports
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Doctor Name</th>
                                <th>Reports</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctor_stats as $doctor): ?>
                            <tr>
                                <td>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></td>
                                <td><strong><?php echo $doctor['report_count']; ?></strong></td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <?php 
                                        $max_reports = max(array_column($doctor_stats, 'report_count'));
                                        $percentage = $max_reports > 0 ? ($doctor['report_count'] / $max_reports) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Summary -->
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-info-circle me-2"></i>System Summary
                </h6>
            </div>
            <div class="card-body">
                <?php
                $total_users = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn() + 
                              $pdo->query("SELECT COUNT(*) FROM police_officers")->fetchColumn() + 
                              $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
                $total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
                $total_cases = $pdo->query("SELECT COUNT(*) FROM pf3_cases")->fetchColumn();
                $total_reports = $pdo->query("SELECT COUNT(*) FROM medical_reports")->fetchColumn();
                ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h5 class="mb-0"><?php echo $total_users; ?></h5>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-user-injured fa-2x text-info mb-2"></i>
                            <h5 class="mb-0"><?php echo $total_patients; ?></h5>
                            <small class="text-muted">Total Patients</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-folder-open fa-2x text-warning mb-2"></i>
                            <h5 class="mb-0"><?php echo $total_cases; ?></h5>
                            <small class="text-muted">Total Cases</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-file-medical fa-2x text-success mb-2"></i>
                            <h5 class="mb-0"><?php echo $total_reports; ?></h5>
                            <small class="text-muted">Medical Reports</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{
            data: [<?php echo $case_stats['PENDING'] ?? 0; ?>, <?php echo $case_stats['APPROVED'] ?? 0; ?>, <?php echo $case_stats['REJECTED'] ?? 0; ?>],
            backgroundColor: ['#ffc107', '#28a745', '#A6EDCF '],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Monthly Trends Line Chart
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
const months = <?php echo json_encode(array_reverse(array_column($monthly_trends, 'month'))); ?>;
const approved = <?php echo json_encode(array_reverse(array_column($monthly_trends, 'approved'))); ?>;
const rejected = <?php echo json_encode(array_reverse(array_column($monthly_trends, 'rejected'))); ?>;

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
                fill: true
            },
            {
                label: 'Rejected',
                data: rejected,
                borderColor: '#A6EDCF ',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>