<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$total = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$total_pages = ceil($total / $limit);

// Get audit logs - FIXED: Use proper integer binding or direct values
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute();
$logs = $stmt->fetchAll();

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
    
    .table-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .badge-action {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .pagination-custom .page-link {
        border-radius: 10px;
        margin: 0 3px;
        color: #166534;
    }
    
    .pagination-custom .page-item.active .page-link {
        background: linear-gradient(135deg, #A6EDCF 0%, #6ee7b7 100%);
        border-color: #A6EDCF;
        color: #064e3b;
        font-weight: 600;
    }
    
    .filter-section {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .filter-input {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-input input, .filter-input select {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        width: 100%;
    }
    
    .export-btn {
        background: linear-gradient(135deg, #A6EDCF 0%, #6ee7b7 100%);
        border: none;
        color: #064e3b;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="fas fa-history me-2"></i>Audit Log
        </h4>
        <p class="text-muted mb-0">Complete system activity history</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <span class="badge bg-secondary">Total Records: <?php echo $total; ?></span>
        <button class="btn btn-sm export-btn ms-2" onclick="exportToCSV()">
            <i class="fas fa-download me-1"></i> Export CSV
        </button>
    </div>
</div>

<!-- Filter Section -->
<div class="card table-card mb-4">
    <div class="card-body">
        <div class="filter-section">
            <div class="filter-input">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by action, user type, or details..." onkeyup="filterTable()">
            </div>
            <div class="filter-input">
                <select id="userTypeFilter" class="form-select" onchange="filterTable()">
                    <option value="">All User Types</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="police">Police</option>
                </select>
            </div>
            <div class="filter-input">
                <input type="date" id="dateFilter" class="form-control" onchange="filterTable()">
            </div>
            <button class="btn btn-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="auditTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>User Type</th>
                        <th>User ID</th>
                        <th>Details</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>#<?php echo $log['id']; ?></td>
                            <td>
                                <span class="badge-action badge bg-<?php 
                                    if (strpos($log['action'], 'Approved') !== false) echo 'success';
                                    elseif (strpos($log['action'], 'Rejected') !== false) echo 'danger';
                                    elseif (strpos($log['action'], 'Registered') !== false) echo 'info';
                                    elseif (strpos($log['action'], 'Deleted') !== false) echo 'dark';
                                    elseif (strpos($log['action'], 'Updated') !== false) echo 'warning';
                                    elseif (strpos($log['action'], 'Login') !== false) echo 'primary';
                                    else echo 'secondary';
                                ?> text-white">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $icon = '';
                                if ($log['user_type'] == 'admin') $icon = '<i class="fas fa-user-cog me-1"></i>';
                                elseif ($log['user_type'] == 'doctor') $icon = '<i class="fas fa-user-md me-1"></i>';
                                else $icon = '<i class="fas fa-user-shield me-1"></i>';
                                echo $icon . ' ' . ucfirst($log['user_type']);
                                ?>
                            </td>
                            <td><?php echo $log['user_id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($log['details'], 0, 100)); ?><?php echo strlen($log['details']) > 100 ? '...' : ''; ?></td>
                            <td>
                                <small><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-2 d-block"></i>
                                No audit logs found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total_pages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center pagination-custom">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        </li>
        
        <?php 
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=1">1</a>
            </li>
            <?php if ($start_page > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif;
        endif;
        
        for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor;
        
        if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
            </li>
        <?php endif; ?>
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const userTypeFilter = document.getElementById('userTypeFilter').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    
    const table = document.getElementById('auditTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        let action = row.cells[1]?.innerText.toLowerCase() || '';
        let userType = row.cells[2]?.innerText.toLowerCase() || '';
        let details = row.cells[4]?.innerText.toLowerCase() || '';
        let date = row.cells[5]?.innerText.split(' ')[0] || '';
        
        let matchesSearch = searchInput === '' || action.includes(searchInput) || details.includes(searchInput);
        let matchesType = userTypeFilter === '' || userType.includes(userTypeFilter);
        let matchesDate = dateFilter === '' || date.includes(dateFilter.split('-').reverse().join('/'));
        
        if (matchesSearch && matchesType && matchesDate) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('userTypeFilter').value = '';
    document.getElementById('dateFilter').value = '';
    filterTable();
}

function exportToCSV() {
    const table = document.getElementById('auditTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let csv = [];
    
    // Headers
    csv.push(['ID', 'Action', 'User Type', 'User ID', 'Details', 'Date/Time'].join(','));
    
    // Data
    for (let row of rows) {
        if (row.style.display !== 'none') {
            let rowData = [];
            for (let i = 0; i < row.cells.length; i++) {
                let cellText = row.cells[i].innerText.replace(/,/g, ';');
                rowData.push('"' + cellText + '"');
            }
            csv.push(rowData.join(','));
        }
    }
    
    // Download
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `audit_log_${new Date().toISOString().slice(0,19)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Auto-refresh every 30 seconds (optional)
setTimeout(function() {
    location.reload();
}, 30000);
</script>

<?php include 'footer.php'; ?>