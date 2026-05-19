<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

$status = $_GET['status'] ?? 'PENDING';
$search = $_GET['search'] ?? '';

// Validate status
$allowedStatuses = ['PENDING', 'APPROVED', 'REJECTED'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'PENDING';
}

// Build query with search
$query = "SELECT c.*, p.full_name, p.phone 
          FROM pf3_cases c 
          JOIN patients p ON c.pf3_number = p.pf3_number 
          WHERE c.status = ?";
$params = [$status];

if (!empty($search)) {
    $query .= " AND (c.pf3_number LIKE ? OR p.full_name LIKE ? OR p.phone LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

$query .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cases = $stmt->fetchAll();

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
    
    .badge-status {
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
            <i class="fas fa-list me-2"></i><?php echo ucfirst(strtolower($status)); ?> Cases
        </h4>
        <p class="text-muted mb-0">Manage and review case applications</p>
    </div>
    
    <!-- Search Form -->
    <form method="GET" class="search-box">
        <input type="hidden" name="status" value="<?php echo $status; ?>">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by PF3, Name or Phone..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($search)): ?>
                <a href="cases.php?status=<?php echo $status; ?>" class="btn btn-secondary">
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
                        <th>Incident Type</th>
                        <th>Police Station</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($cases) > 0): ?>
                        <?php foreach ($cases as $case): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($case['pf3_number']); ?></strong>
                                <?php if ($case['rb_number']): ?>
                                    <br><small class="text-muted">RB: <?php echo htmlspecialchars($case['rb_number']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($case['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($case['phone']); ?></td>
                            <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
                            <td><?php echo htmlspecialchars($case['police_station']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?></td>
                            <td>
                                <span class="badge-status badge bg-<?php 
                                    echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo $case['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_case.php?pf3=<?php echo $case['pf3_number']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                <h6 class="text-muted">No <?php echo strtolower($status); ?> cases found</h6>
                                <?php if (!empty($search)): ?>
                                    <small class="text-muted">for search: '<?php echo htmlspecialchars($search); ?>'</small>
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