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

// Get gender distribution
$gender_stats = $pdo->query("
    SELECT gender, COUNT(*) as count 
    FROM patients 
    GROUP BY gender
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get incidents by type
$incident_stats = $pdo->query("
    SELECT type_of_incident, COUNT(*) as count 
    FROM pf3_cases 
    GROUP BY type_of_incident
    ORDER BY count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get monthly patient registrations
$patient_trends = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM patients 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
")->fetchAll();

// Get police performance
$police_stats = $pdo->query("
    SELECT 
        CONCAT(p.first_name, ' ', p.last_name) as officer_name,
        COUNT(c.id) as case_count
    FROM police_officers p
    LEFT JOIN pf3_cases c ON c.police_notes IS NOT NULL
    GROUP BY p.id
    ORDER BY case_count DESC
    LIMIT 10
")->fetchAll();

include 'header.php';
?>

<style>
    body {
        background: rgba(13, 71, 161, 0.05);
    }
    
    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #0d47a1;
    }
    
    .chart-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
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
        height: 300px;
        width: 100%;
    }
    
    .stat-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
    }
    
    .table th {
        background: #e8eaf6;
        color: #1a237e;
        font-weight: 600;
    }
    
    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }
    
    .summary-box {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #e8eaf6;
    }
    
    .summary-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(13, 71, 161, 0.1);
        border-color: #0d47a1;
    }
    
    .summary-box i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .summary-box h5 {
        font-weight: 700;
        margin-bottom: 0;
    }
    
    .summary-box small {
        color: #6c757d;
    }
    
    .progress {
        border-radius: 10px;
        background-color: #e8eaf6;
        height: 8px;
    }
    
    .progress-bar {
        border-radius: 10px;
        background: linear-gradient(135deg, #0d47a1, #1976d2);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin-top: 0.75rem;
    }
    
    .stats-grid .stat-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem;
        text-align: center;
    }
    
    .stats-grid .stat-item .number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0d47a1;
    }
    
    .stats-grid .stat-item .label {
        font-size: 0.7rem;
        color: #6c757d;
    }
</style>

<div class="page-header">
    <div>
        <h4 class="mb-1 fw-bold text-primary">
            <i class="fas fa-chart-bar me-2"></i>System Reports
        </h4>
        <p class="text-muted mb-0">Analytics and statistics for the PF3 system</p>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row g-3 mb-4">
    <?php
    $total_users = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn() + 
                  $pdo->query("SELECT COUNT(*) FROM police_officers")->fetchColumn() + 
                  $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $total_cases = $pdo->query("SELECT COUNT(*) FROM pf3_cases")->fetchColumn();
    $total_reports = $pdo->query("SELECT COUNT(*) FROM medical_reports")->fetchColumn();
    $pending_cases = $pdo->query("SELECT COUNT(*) FROM pf3_cases WHERE status = 'PENDING'")->fetchColumn();
    $approved_cases = $pdo->query("SELECT COUNT(*) FROM pf3_cases WHERE status = 'APPROVED'")->fetchColumn();
    ?>
    <div class="col-md-3 col-6">
        <div class="summary-box">
            <i class="fas fa-users text-primary"></i>
            <h5><?php echo $total_users; ?></h5>
            <small>Total Users</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-box">
            <i class="fas fa-user-injured text-info"></i>
            <h5><?php echo $total_patients; ?></h5>
            <small>Total Patients</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-box">
            <i class="fas fa-folder-open text-warning"></i>
            <h5><?php echo $total_cases; ?></h5>
            <small>Total Cases</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-box">
            <i class="fas fa-file-medical text-success"></i>
            <h5><?php echo $total_reports; ?></h5>
            <small>Medical Reports</small>
        </div>
    </div>
</div>

<!-- Row 1: Case Status & Monthly Trends -->
<div class="row g-4">
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
</div>

<!-- Row 2: Gender Distribution & Incident Types -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-venus-mars me-2 text-primary"></i>Gender Distribution
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="stats-grid">
                    <?php foreach ($gender_stats as $gender => $count): ?>
                    <div class="stat-item">
                        <div class="number"><?php echo $count; ?></div>
                        <div class="label"><?php echo $gender; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Incident Types
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="incidentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Patient Registrations & Doctor Performance -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-plus me-2 text-primary"></i>Monthly Patient Registrations
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="patientChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-md me-2 text-info"></i>Top Doctors by Reports
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Doctor Name</th>
                                <th>Reports</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($doctor_stats) > 0): ?>
                                <?php 
                                $max_reports = max(array_column($doctor_stats, 'report_count'));
                                foreach ($doctor_stats as $doctor): 
                                ?>
                                <tr>
                                    <td>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></td>
                                    <td><strong><?php echo $doctor['report_count']; ?></strong></td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <?php 
                                            $percentage = $max_reports > 0 ? ($doctor['report_count'] / $max_reports) * 100 : 0;
                                            ?>
                                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">No doctor reports found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Police Performance -->
<div class="row g-4 mt-2">
    <div class="col-lg-12">
        <div class="card chart-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-shield me-2 text-warning"></i>Police Officer Performance
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="policeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Color palette
    const colors = {
        blue: '#0d47a1',
        lightBlue: '#1976d2',
        green: '#28a745',
        red: '#dc3545',
        orange: '#ff9800',
        purple: '#6f42c1',
        teal: '#20c997',
        pink: '#e83e8c',
        indigo: '#6610f2',
        cyan: '#17a2b8'
    };

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
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
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

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderLabels = <?php echo json_encode(array_keys($gender_stats)); ?>;
    const genderData = <?php echo json_encode(array_values($gender_stats)); ?>;
    const genderColors = ['#0d47a1', '#e83e8c', '#6f42c1'];

    new Chart(genderCtx, {
        type: 'bar',
        data: {
            labels: genderLabels,
            datasets: [{
                label: 'Patients',
                data: genderData,
                backgroundColor: genderColors.slice(0, genderData.length),
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Incident Types Chart
    const incidentCtx = document.getElementById('incidentChart').getContext('2d');
    const incidentLabels = <?php echo json_encode(array_keys($incident_stats)); ?>;
    const incidentData = <?php echo json_encode(array_values($incident_stats)); ?>;

    new Chart(incidentCtx, {
        type: 'bar',
        data: {
            labels: incidentLabels,
            datasets: [{
                label: 'Cases',
                data: incidentData,
                backgroundColor: [
                    '#0d47a1', '#1976d2', '#2196f3', '#42a5f5',
                    '#64b5f6', '#90caf9', '#bbdefb', '#e3f2fd'
                ],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Patient Registrations Chart
    const patientCtx = document.getElementById('patientChart').getContext('2d');
    const patientMonths = <?php echo json_encode(array_reverse(array_column($patient_trends, 'month'))); ?>;
    const patientCounts = <?php echo json_encode(array_reverse(array_column($patient_trends, 'count'))); ?>;

    new Chart(patientCtx, {
        type: 'bar',
        data: {
            labels: patientMonths.map(m => {
                const date = new Date(m + '-01');
                return date.toLocaleDateString('en', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'New Patients',
                data: patientCounts,
                backgroundColor: 'rgba(13, 71, 161, 0.7)',
                borderColor: '#0d47a1',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Police Performance Chart
    const policeCtx = document.getElementById('policeChart').getContext('2d');
    const policeNames = <?php echo json_encode(array_column($police_stats, 'officer_name')); ?>;
    const policeCases = <?php echo json_encode(array_column($police_stats, 'case_count')); ?>;

    new Chart(policeCtx, {
        type: 'bar',
        data: {
            labels: policeNames,
            datasets: [{
                label: 'Cases Processed',
                data: policeCases,
                backgroundColor: 'rgba(255, 152, 0, 0.7)',
                borderColor: '#ff9800',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>