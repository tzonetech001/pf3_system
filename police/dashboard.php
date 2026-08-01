<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

// Get statistics
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pf3_cases GROUP BY status");
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get total cases
$total_cases = array_sum($stats);

// Get pending cases
$pending_cases = $stats['PENDING'] ?? 0;

// Get approved cases
$approved_cases = $stats['APPROVED'] ?? 0;

// Get rejected cases
$rejected_cases = $stats['REJECTED'] ?? 0;

// Get recent cases
$recentCases = $pdo->query("
    SELECT c.*, p.full_name 
    FROM pf3_cases c 
    JOIN patients p ON c.pf3_number = p.pf3_number 
    ORDER BY c.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get monthly statistics for chart
$monthlyStats = $pdo->query("
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
")->fetchAll();

// Get incident type breakdown
$incidentTypes = $pdo->query("
    SELECT type_of_incident, COUNT(*) as count 
    FROM pf3_cases 
    GROUP BY type_of_incident
    ORDER BY count DESC
    LIMIT 5
")->fetchAll();

// Get today's statistics
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pf3_cases WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_cases = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pf3_cases WHERE DATE(created_at) = ? AND status = 'PENDING'");
$stmt->execute([$today]);
$today_pending = $stmt->fetch()['count'];

include 'header.php';
?>

<style>
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
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    
    .stat-card h3 {
        font-size: 1.8rem;
        font-weight: 700;
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
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: white;
    }
    
    .table-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 1rem 1.25rem;
        border-radius: 15px 15px 0 0;
    }
    
    .table-card .card-header h6 {
        color: #1a237e;
        font-weight: 600;
    }
    
    .table th {
        background: #e8eaf6;
        color: #1a237e;
        font-weight: 600;
        border-bottom: 2px solid #0d47a1;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }
    
    .badge-status {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
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
        transform: translateY(-3px);
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
    
    .btn-primary {
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-primary:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    .btn-primary:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.25);
    }
    
    .btn-outline-primary {
        color: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-outline-primary:hover {
        background: #0d47a1;
        color: white;
        border-color: #0d47a1;
    }
    
    .text-primary {
        color: #0d47a1 !important;
    }
    
    .bg-primary {
        background: #0d47a1 !important;
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
        .table th, .table td {
            font-size: 0.8rem;
        }
        .badge-status {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
        .today-stats .number {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 480px) {
        .stat-card h6 {
            font-size: 0.65rem;
        }
        .stat-card h3 {
            font-size: 1.1rem;
        }
        .stat-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
        .chart-container {
            height: 180px;
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
                        <h6 class="text-muted mb-1">Total Cases</h6>
                        <h3 class="mb-0 text-primary"><?php echo $total_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-folder-open"></i>
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
                        <h3 class="mb-0 text-danger"><?php echo $rejected_cases; ?></h3>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-times-circle"></i>
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
            <div class="number"><?php echo $today_cases; ?></div>
            <div class="label"><i class="fas fa-calendar-day me-1 text-primary"></i>Cases Today</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $today_pending; ?></div>
            <div class="label"><i class="fas fa-clock me-1 text-warning"></i>Pending Today</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $approved_cases; ?></div>
            <div class="label"><i class="fas fa-check-circle me-1 text-success"></i>Total Approved</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="today-stats">
            <div class="number"><?php echo $rejected_cases; ?></div>
            <div class="label"><i class="fas fa-times-circle me-1 text-danger"></i>Total Rejected</div>
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
                        <span class="badge bg-warning">Pending: <?php echo $pending_cases; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-success">Approved: <?php echo $approved_cases; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-danger">Rejected: <?php echo $rejected_cases; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incident Types Chart -->
<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Top Incident Types
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="incidentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Cases -->
<div class="row g-4">
    <div class="col-lg-12">
        <div class="card table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-history me-2 text-primary"></i>Recent Cases
                </h6>
                <a href="cases.php?status=PENDING" class="btn btn-sm btn-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>PF3 Number</th>
                                <th>Patient Name</th>
                                <th>Incident Type</th>
                                <th>Police Station</th>
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
                                        <strong class="text-primary"><?php echo htmlspecialchars($case['pf3_number']); ?></strong>
                                        <?php if ($case['rb_number']): ?>
                                            <br><small class="text-muted">RB: <?php echo htmlspecialchars($case['rb_number']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
                                    <td><?php echo htmlspecialchars($case['police_station']); ?></td>
                                    <td>
                                        <span class="badge-status badge bg-<?php 
                                            echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php 
                                                if ($case['status'] == 'APPROVED') echo '✅ APPROVED';
                                                elseif ($case['status'] == 'REJECTED') echo '❌ REJECTED';
                                                else echo '⏳ PENDING';
                                            ?>
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
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        <h6 class="text-muted">No cases found</h6>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const months = <?php echo json_encode(array_column($monthlyStats, 'month')); ?>;
    const approved = <?php echo json_encode(array_column($monthlyStats, 'approved')); ?>;
    const rejected = <?php echo json_encode(array_column($monthlyStats, 'rejected')); ?>;
    const pending = <?php echo json_encode(array_column($monthlyStats, 'pending')); ?>;

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
                    pointRadius: 4,
                    pointBackgroundColor: '#28a745'
                },
                {
                    label: 'Pending',
                    data: pending,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffc107'
                },
                {
                    label: 'Rejected',
                    data: rejected,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc3545'
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
                    ticks: { 
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
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
                    <?php echo $pending_cases; ?>,
                    <?php echo $approved_cases; ?>,
                    <?php echo $rejected_cases; ?>
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

    // Incident Types Chart
    const incidentCtx = document.getElementById('incidentChart').getContext('2d');
    const incidentLabels = <?php echo json_encode(array_column($incidentTypes, 'type_of_incident')); ?>;
    const incidentCounts = <?php echo json_encode(array_column($incidentTypes, 'count')); ?>;

    new Chart(incidentCtx, {
        type: 'bar',
        data: {
            labels: incidentLabels,
            datasets: [{
                label: 'Cases',
                data: incidentCounts,
                backgroundColor: [
                    '#0d47a1', '#1976d2', '#2196f3', '#42a5f5', '#64b5f6'
                ],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>