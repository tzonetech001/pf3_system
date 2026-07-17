<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

// Get filter parameters
$status = $_GET['status'] ?? 'ALL';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Validate status
$allowedStatuses = ['ALL', 'PENDING', 'APPROVED', 'REJECTED'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'ALL';
}

// Build query with filters
$query = "SELECT c.*, p.full_name, p.phone, p.gender, p.age 
          FROM pf3_cases c 
          JOIN patients p ON c.pf3_number = p.pf3_number 
          WHERE 1=1";
$params = [];

if ($status !== 'ALL') {
    $query .= " AND c.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (c.pf3_number LIKE ? OR p.full_name LIKE ? OR p.phone LIKE ? OR c.rb_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($date_from)) {
    $query .= " AND DATE(c.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(c.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Get statistics for summary cards
$totalQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
FROM pf3_cases";
$statsStmt = $pdo->query($totalQuery);
$stats = $statsStmt->fetch();

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
    
    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .filter-section .form-control,
    .filter-section .form-select {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
    }
    
    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
        border-color: #A6EDCF;
        box-shadow: 0 0 0 3px rgba(166, 237, 207, 0.2);
    }
    
    .btn-filter {
        border-radius: 10px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
    }
    
    .clear-filter {
        color: #6c757d;
        text-decoration: none;
        font-weight: 500;
    }
    
    .clear-filter:hover {
        color: #dc3545;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }
        .search-box {
            max-width: 100%;
        }
        .filter-section .row > div {
            margin-bottom: 0.5rem;
        }
        .stat-card {
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-list-alt me-2 text-primary"></i>All Requests
            </h4>
            <p class="text-muted mb-0">View and manage all case applications</p>
        </div>
        
        <!-- Search Form -->
        <form method="GET" class="search-box">
            <div class="input-group">
                <input type="hidden" name="status" value="<?php echo $status; ?>">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by PF3, Name, Phone or RB..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search) || !empty($date_from) || !empty($date_to)): ?>
                    <a href="all_requests.php?status=<?php echo $status; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Statistics Summary Cards -->
    <div class="row g-3 mt-3">
        <div class="col-6 col-md-3">
            <a href="all_requests.php?status=ALL" class="text-decoration-none">
                <div class="card stat-card shadow-sm <?php echo $status == 'ALL' ? 'border-primary border-2' : ''; ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Total</h6>
                                <h4 class="mb-0 fw-bold"><?php echo $stats['total'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-folder-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="all_requests.php?status=PENDING" class="text-decoration-none">
                <div class="card stat-card shadow-sm <?php echo $status == 'PENDING' ? 'border-warning border-2' : ''; ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Pending</h6>
                                <h4 class="mb-0 fw-bold text-warning"><?php echo $stats['pending'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="all_requests.php?status=APPROVED" class="text-decoration-none">
                <div class="card stat-card shadow-sm <?php echo $status == 'APPROVED' ? 'border-success border-2' : ''; ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Approved</h6>
                                <h4 class="mb-0 fw-bold text-success"><?php echo $stats['approved'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="all_requests.php?status=REJECTED" class="text-decoration-none">
                <div class="card stat-card shadow-sm <?php echo $status == 'REJECTED' ? 'border-danger border-2' : ''; ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Rejected</h6>
                                <h4 class="mb-0 fw-bold text-danger"><?php echo $stats['rejected'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" class="row g-3 align-items-end">
        <input type="hidden" name="status" value="<?php echo $status; ?>">
        
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Date From</label>
            <input type="date" name="date_from" class="form-control" 
                   value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Date To</label>
            <input type="date" name="date_to" class="form-control" 
                   value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        
        <div class="col-md-3">
            <label class="form-label fw-semibold small">Status</label>
            <select name="status" class="form-select">
                <option value="ALL" <?php echo $status == 'ALL' ? 'selected' : ''; ?>>All Status</option>
                <option value="PENDING" <?php echo $status == 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                <option value="APPROVED" <?php echo $status == 'APPROVED' ? 'selected' : ''; ?>>Approved</option>
                <option value="REJECTED" <?php echo $status == 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-filter w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
                <?php if (!empty($date_from) || !empty($date_to) || !empty($search)): ?>
                    <a href="all_requests.php?status=<?php echo $status; ?>" class="btn btn-outline-secondary btn-filter">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Results Table -->
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
                                    <?php 
                                        if ($case['status'] == 'APPROVED') echo '✅ APPROVED';
                                        elseif ($case['status'] == 'REJECTED') echo '❌ REJECTED';
                                        else echo '⏳ PENDING';
                                    ?>
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
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <h6 class="text-muted">No cases found</h6>
                                <?php if (!empty($search) || !empty($date_from) || !empty($date_to)): ?>
                                    <small class="text-muted">Try adjusting your search filters</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="#" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Print
    </a>
    <a href="#" class="btn btn-sm btn-outline-success" onclick="exportTableToCSV()">
        <i class="fas fa-file-csv me-1"></i> Export CSV
    </a>
</div>

<script>
// Export table to CSV
function exportTableToCSV() {
    const table = document.querySelector('.table');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    // Get headers
    let headers = [];
    const headerRow = rows[0];
    headerRow.querySelectorAll('th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Get data rows (skip last row if it's "No cases found")
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        // Skip if it's the "no cases" row
        if (row.querySelector('td[colspan]')) continue;
        
        let rowData = [];
        row.querySelectorAll('td').forEach((td, index) => {
            // Skip action column (last column)
            if (index === row.querySelectorAll('td').length - 1) return;
            
            // Clean the data
            let text = td.textContent.trim();
            // Remove nested elements (like RB number)
            text = text.replace(/\s+/g, ' ');
            // If contains comma, wrap in quotes
            if (text.includes(',')) {
                text = '"' + text + '"';
            }
            rowData.push(text);
        });
        csv.push(rowData.join(','));
    }
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'all_requests_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();
}

// Auto-submit filter on status change
document.querySelector('select[name="status"]')?.addEventListener('change', function() {
    this.closest('form').submit();
});
</script>

<?php include 'footer.php'; ?>