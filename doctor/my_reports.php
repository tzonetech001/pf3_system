<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT mr.*, p.full_name, p.phone 
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
    
    .search-box {
        max-width: 400px;
    }
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .badge-severity {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }
        .search-box {
            max-width: 100%;
        }
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold">
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
                <a href="my_reports.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>PF3 Number</th>
                        <th>Patient Name</th>
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
                                <strong><?php echo htmlspecialchars($report['pf3_number']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['phone']); ?></td>
                            <td><?php echo htmlspecialchars($report['injury_type']); ?></td>
                            <td>
                                <span class="badge-severity badge bg-<?php 
                                    echo $report['severity'] == 'Severe' ? 'danger' : ($report['severity'] == 'Moderate' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo htmlspecialchars($report['severity']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                            <td>
                                <a href="view_patient.php?pf3=<?php echo $report['pf3_number']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3 d-block"></i>
                                <h6 class="text-muted">No medical reports found</h6>
                                <?php if (!empty($search)): ?>
                                    <small class="text-muted">for search: '<?php echo htmlspecialchars($search); ?>'</small>
                                <?php else: ?>
                                    <small class="text-muted">Start by searching for a PF3 number and creating a report</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>