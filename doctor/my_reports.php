<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT mr.*, p.full_name, p.phone, p.gender, p.age
    FROM medical_reports mr 
    JOIN patients p ON mr.pf3_number = p.pf3_number 
    WHERE mr.doctor_id = ?
";
$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (mr.pf3_number LIKE ? OR p.full_name LIKE ? OR p.phone LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

$query .= " ORDER BY mr.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get statistics
$total_reports = count($reports);

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT pf3_number) as count FROM medical_reports WHERE doctor_id = ?");
$stmt->execute([$user_id]);
$total_patients = $stmt->fetch()['count'];

// Get severity breakdown
$stmt = $pdo->prepare("
    SELECT severity, COUNT(*) as count 
    FROM medical_reports 
    WHERE doctor_id = ? 
    GROUP BY severity
");
$stmt->execute([$user_id]);
$severity_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

include 'header.php';
?>

<style>
    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #0d47a1;
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
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    
    .stat-card h6 {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .search-box {
        max-width: 400px;
    }
    
    .search-box .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 12px 0 0 12px;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }
    
    .search-box .form-control:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
    }
    
    .search-box .btn {
        border-radius: 0 12px 12px 0;
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .search-box .btn:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: white;
    }
    
    .table-card .card-body {
        padding: 0;
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
    
    .badge-severity {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .severity-Mild {
        background: #d4edda;
        color: #155724;
    }
    
    .severity-Moderate {
        background: #fff3cd;
        color: #856404;
    }
    
    .severity-Severe {
        background: #f8d7da;
        color: #721c24;
    }
    
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }
    
    .empty-state h6 {
        color: #4a5568;
        font-weight: 600;
    }
    
    .empty-state p {
        color: #a0aec0;
        font-size: 0.9rem;
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
    
    .btn-primary {
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-primary:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }
        .search-box {
            max-width: 100%;
        }
        .stat-card h3 {
            font-size: 1.2rem;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        .table th, .table td {
            font-size: 0.8rem;
        }
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold text-primary">
            <i class="fas fa-file-alt me-2"></i>My Medical Reports
        </h4>
        <p class="text-muted mb-0">View all medical reports you have created</p>
    </div>
    
    <!-- Search Form -->
    <form method="GET" class="search-box">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by PF3, Name or Phone..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($search)): ?>
                <a href="my_reports.php" class="btn btn-secondary" style="border-radius: 0 12px 12px 0;">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
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
    <div class="col-md-4 col-6">
        <div class="card stat-card shadow-sm" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Patients</h6>
                        <h3 class="mb-0 text-success"><?php echo $total_patients; ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="card stat-card shadow-sm" style="border-left-color: #ff9800;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Severe Cases</h6>
                        <h3 class="mb-0 text-warning"><?php echo $severity_stats['Severe'] ?? 0; ?></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>PF3 Number</th>
                        <th>Patient Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Phone</th>
                        <th>Injury Type</th>
                        <th>Severity</th>
                        <th>Report Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?php echo htmlspecialchars($report['pf3_number']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $report['gender'] == 'Male' ? 'info' : ($report['gender'] == 'Female' ? 'danger' : 'secondary'); 
                                ?>">
                                    <?php echo htmlspecialchars($report['gender']); ?>
                                </span>
                            </td>
                            <td><?php echo $report['age']; ?></td>
                            <td><?php echo htmlspecialchars($report['phone']); ?></td>
                            <td><?php echo htmlspecialchars($report['injury_type']); ?></td>
                            <td>
                                <span class="badge-severity severity-<?php echo $report['severity']; ?>">
                                    <?php echo htmlspecialchars($report['severity']); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></small>
                            </td>
                            <td>
                                <a href="view_patient.php?pf3=<?php echo $report['pf3_number']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt"></i>
                                    <h6>No medical reports found</h6>
                                    <?php if (!empty($search)): ?>
                                        <p>for search: '<?php echo htmlspecialchars($search); ?>'</p>
                                        <a href="my_reports.php" class="btn btn-sm btn-primary">
                                            <i class="fas fa-times me-1"></i> Clear Search
                                        </a>
                                    <?php else: ?>
                                        <p>Start by searching for a PF3 number and creating a report</p>
                                        <a href="search_pf3.php" class="btn btn-sm btn-primary">
                                            <i class="fas fa-search me-1"></i> Search PF3
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (count($reports) > 0): ?>
    <div class="mt-3 text-muted small">
        <i class="fas fa-info-circle me-1"></i> 
        Showing <?php echo count($reports); ?> report(s)
        <?php if (!empty($search)): ?>
            for search: '<?php echo htmlspecialchars($search); ?>'
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>