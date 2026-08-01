<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$user_id = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$total_reports = $stmt->fetch()['count'];

// Get severity breakdown for chart
$stmt = $pdo->prepare("
    SELECT severity, COUNT(*) as count 
    FROM medical_reports 
    WHERE doctor_id = ? 
    GROUP BY severity
");
$stmt->execute([$user_id]);
$severity_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get monthly report trends
$monthly_trends = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM medical_reports 
    WHERE doctor_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$monthly_trends->execute([$user_id]);
$monthly_data = $monthly_trends->fetchAll();

// Get injury type breakdown
$injury_stats = $pdo->prepare("
    SELECT injury_type, COUNT(*) as count 
    FROM medical_reports 
    WHERE doctor_id = ? 
    GROUP BY injury_type 
    ORDER BY count DESC 
    LIMIT 5
");
$injury_stats->execute([$user_id]);
$injury_data = $injury_stats->fetchAll();

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
    SELECT c.*, p.full_name, p.phone, p.pf3_number 
    FROM pf3_cases c 
    JOIN patients p ON c.pf3_number = p.pf3_number 
    WHERE c.status = 'APPROVED' 
    AND c.pf3_number NOT IN (SELECT pf3_number FROM medical_reports WHERE doctor_id = ?)
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$pending_reports = $stmt->fetchAll();

// Get active patients count
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT pf3_number) as count FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$active_patients = $stmt->fetch()['count'];

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
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
    }
    
    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }
    
    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .btn-primary {
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-primary:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    .btn-success {
        background: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background: #218838;
        border-color: #218838;
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
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Reports</h6>
                        <h3 class="mb-0 text-primary"><?php echo $total_reports; ?></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-medical"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card shadow-sm" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Active Patients</h6>
                        <h3 class="mb-0 text-success"><?php echo $active_patients; ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card shadow-sm" style="border-left-color: #ff9800;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Reports</h6>
                        <h3 class="mb-0 text-warning"><?php echo count($pending_reports); ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Monthly Report Trends
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
                    <i class="fas fa-chart-pie me-2 text-primary"></i>Severity Distribution
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="severityChart"></canvas>
                </div>
                <div class="row mt-3 text-center">
                    <div class="col-4">
                        <span class="badge bg-success">Mild: <?php echo $severity_stats['Mild'] ?? 0; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-warning">Moderate: <?php echo $severity_stats['Moderate'] ?? 0; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-danger">Severe: <?php echo $severity_stats['Severe'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Injury Types Chart -->
<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Top Injury Types
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="injuryChart"></canvas>
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
                    <i class="fas fa-history me-2 text-primary"></i>Recent Medical Reports
                </h6>
                <a href="my_reports.php" class="btn btn-sm btn-primary">
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
                                        <strong class="text-primary"><?php echo htmlspecialchars($report['pf3_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($report['injury_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $report['severity'] == 'Severe' ? 'danger' : ($report['severity'] == 'Moderate' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo htmlspecialchars($report['severity']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <a href="view_patient.php?pf3=<?php echo $report['pf3_number']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
                    <i class="fas fa-tasks me-2 text-primary"></i>Pending Medical Reports
                </h6>
                <small class="text-muted">Approved cases awaiting your report</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
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
                                        <strong class="text-primary"><?php echo htmlspecialchars($case['pf3_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
                                    <td>
                                        <a href="view_patient.php?pf3=<?php echo $case['pf3_number']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Create
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const months = <?php echo json_encode(array_column($monthly_data, 'month')); ?>;
    const counts = <?php echo json_encode(array_column($monthly_data, 'count')); ?>;

    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: months.map(m => {
                const date = new Date(m + '-01');
                return date.toLocaleDateString('en', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Reports',
                data: counts,
                borderColor: '#0d47a1',
                backgroundColor: 'rgba(13, 71, 161, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#0d47a1'
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
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Severity Pie Chart
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    const severityData = {
        Mild: <?php echo $severity_stats['Mild'] ?? 0; ?>,
        Moderate: <?php echo $severity_stats['Moderate'] ?? 0; ?>,
        Severe: <?php echo $severity_stats['Severe'] ?? 0; ?>
    };

    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mild', 'Moderate', 'Severe'],
            datasets: [{
                data: [severityData.Mild, severityData.Moderate, severityData.Severe],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
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

    // Injury Types Chart
    const injuryCtx = document.getElementById('injuryChart').getContext('2d');
    const injuryLabels = <?php echo json_encode(array_column($injury_data, 'injury_type')); ?>;
    const injuryCounts = <?php echo json_encode(array_column($injury_data, 'count')); ?>;

    new Chart(injuryCtx, {
        type: 'bar',
        data: {
            labels: injuryLabels,
            datasets: [{
                label: 'Cases',
                data: injuryCounts,
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
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>