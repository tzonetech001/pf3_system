<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Handle Bulk Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Clear All
    if (isset($_POST['clear_all']) && $_POST['clear_all'] === 'yes') {
        try {
            $pdo->query("TRUNCATE TABLE audit_logs");
            $_SESSION['success_message'] = "All audit logs have been cleared successfully!";
            logAudit($_SESSION['user_id'], 'admin', 'Cleared All Audit Logs', "All audit logs were cleared");
            header('Location: audit_log.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error clearing audit logs: " . $e->getMessage();
            header('Location: audit_log.php');
            exit;
        }
    }
    
    // Handle Bulk Delete
    if (isset($_POST['bulk_delete']) && isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
        $selected_ids = array_filter($_POST['selected_ids'], 'is_numeric');
        if (!empty($selected_ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id IN ($placeholders)");
                $stmt->execute($selected_ids);
                $deleted_count = $stmt->rowCount();
                $_SESSION['success_message'] = "Successfully deleted $deleted_count audit log(s)!";
                logAudit($_SESSION['user_id'], 'admin', 'Bulk Deleted Audit Logs', "Deleted $deleted_count audit log entries");
                header('Location: audit_log.php');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error deleting audit logs: " . $e->getMessage();
                header('Location: audit_log.php');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "No valid audit logs selected for deletion.";
            header('Location: audit_log.php');
            exit;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$total = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$total_pages = ceil($total / $limit);

// Get audit logs
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
    body {
        background: rgba(13, 71, 161, 0.08);
    }
    
    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #0d47a1;
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
    
    .badge-action {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .pagination-custom .page-link {
        border-radius: 10px;
        margin: 0 3px;
        color: #0d47a1;
    }
    
    .pagination-custom .page-item.active .page-link {
        background: linear-gradient(135deg, #0d47a1, #1976d2);
        border-color: #0d47a1;
        color: white;
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
    
    .filter-input input:focus, .filter-input select:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
    }
    
    .export-btn {
        background: linear-gradient(135deg, #0d47a1, #1976d2);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        color: white;
    }
    
    .bulk-actions-bar {
        background: #e3f2fd;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        display: none;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        border: 1px solid #bbdefb;
    }
    
    .bulk-actions-bar.active {
        display: flex;
    }
    
    .bulk-actions-bar .selected-count {
        font-weight: 600;
        color: #0d47a1;
    }
    
    .bulk-delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .bulk-delete-btn:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        color: white;
    }
    
    .clear-all-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .clear-all-btn:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        color: white;
    }
    
    .select-all-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0d47a1;
    }
    
    .row-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0d47a1;
    }
    
    .action-buttons-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .table th {
        background: #e8eaf6;
        color: #1a237e;
        font-weight: 600;
        border-bottom: 2px solid #0d47a1;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: #e3f2fd;
    }
    
    @media (max-width: 768px) {
        .bulk-actions-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .bulk-actions-bar .d-flex {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold text-primary">
            <i class="fas fa-history me-2"></i>Audit Log
        </h4>
        <p class="text-muted mb-0">Complete system activity history</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <span class="badge bg-primary">Total Records: <?php echo $total; ?></span>
        <button class="btn btn-sm export-btn ms-2" onclick="exportToCSV()">
            <i class="fas fa-download me-1"></i> Export CSV
        </button>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card table-card mb-4">
    <div class="card-body p-3">
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

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="selected-count">
            <i class="fas fa-check-circle me-1"></i>
            <span id="selectedCount">0</span> log(s) selected
        </span>
        <button class="bulk-delete-btn" onclick="confirmBulkDelete()">
            <i class="fas fa-trash me-1"></i> Delete Selected
        </button>
        <button class="clear-all-btn" onclick="confirmClearAll()">
            <i class="fas fa-eraser me-1"></i> Clear All Logs
        </button>
        <button class="btn btn-sm btn-secondary" onclick="deselectAll()">
            <i class="fas fa-times"></i> Deselect All
        </button>
    </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" method="POST" action="">
    <input type="hidden" name="bulk_delete" value="1">
    <div id="selectedIdsContainer"></div>
</form>

<!-- Clear All Form -->
<form id="clearAllForm" method="POST" action="">
    <input type="hidden" name="clear_all" value="yes">
</form>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="auditTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="select-all-checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                        </th>
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
                            <td>
                                <input type="checkbox" class="row-checkbox" data-id="<?php echo $log['id']; ?>" onchange="updateBulkActions()">
                            </td>
                            <td>#<?php echo $log['id']; ?></td>
                            <td>
                                <span class="badge-action badge bg-<?php 
                                    if (strpos($log['action'], 'Approved') !== false) echo 'success';
                                    elseif (strpos($log['action'], 'Rejected') !== false) echo 'danger';
                                    elseif (strpos($log['action'], 'Registered') !== false) echo 'info';
                                    elseif (strpos($log['action'], 'Deleted') !== false) echo 'dark';
                                    elseif (strpos($log['action'], 'Updated') !== false) echo 'warning';
                                    elseif (strpos($log['action'], 'Login') !== false) echo 'primary';
                                    elseif (strpos($log['action'], 'Cleared') !== false) echo 'danger';
                                    else echo 'secondary';
                                ?> text-white">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $icon = '';
                                if ($log['user_type'] == 'admin') $icon = '<i class="fas fa-user-cog me-1 text-primary"></i>';
                                elseif ($log['user_type'] == 'doctor') $icon = '<i class="fas fa-user-md me-1 text-info"></i>';
                                else $icon = '<i class="fas fa-user-shield me-1 text-warning"></i>';
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
                            <td colspan="7" class="text-center py-4">
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
        if (row.cells.length < 7) continue;
        
        let action = row.cells[2]?.innerText.toLowerCase() || '';
        let userType = row.cells[3]?.innerText.toLowerCase() || '';
        let details = row.cells[5]?.innerText.toLowerCase() || '';
        let date = row.cells[6]?.innerText.split(' ')[0] || '';
        
        let matchesSearch = searchInput === '' || action.includes(searchInput) || details.includes(searchInput);
        let matchesType = userTypeFilter === '' || userType.includes(userTypeFilter);
        let matchesDate = dateFilter === '' || date.includes(dateFilter.split('-').reverse().join('/'));
        
        if (matchesSearch && matchesType && matchesDate) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
            const checkbox = row.querySelector('.row-checkbox');
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    }
    updateBulkActions();
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('userTypeFilter').value = '';
    document.getElementById('dateFilter').value = '';
    filterTable();
}

function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
        const row = cb.closest('tr');
        return row.style.display !== 'none';
    });
    
    visibleCheckboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const selectedIds = Array.from(checkboxes).map(cb => cb.dataset.id);
    const count = selectedIds.length;
    
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkBar.classList.add('active');
        selectedCount.textContent = count;
    } else {
        bulkBar.classList.remove('active');
    }
    
    const container = document.getElementById('selectedIdsContainer');
    container.innerHTML = '';
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const count = checkboxes.length;
    
    if (count === 0) {
        alert('Please select at least one audit log to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${count} selected audit log(s)? This action cannot be undone.`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

function confirmClearAll() {
    const totalRecords = <?php echo $total; ?>;
    if (totalRecords === 0) {
        alert('There are no audit logs to clear.');
        return;
    }
    
    if (confirm(`⚠️ WARNING: You are about to delete ALL ${totalRecords} audit logs. This action cannot be undone!\n\nAre you sure you want to continue?`)) {
        if (confirm('FINAL CONFIRMATION: This will permanently delete all audit logs. Proceed?')) {
            document.getElementById('clearAllForm').submit();
        }
    }
}

function deselectAll() {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function exportToCSV() {
    const table = document.getElementById('auditTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let csv = [];
    
    csv.push(['ID', 'Action', 'User Type', 'User ID', 'Details', 'Date/Time'].join(','));
    
    for (let row of rows) {
        if (row.style.display !== 'none' && row.cells.length >= 7) {
            let rowData = [];
            for (let i = 1; i < 7; i++) {
                let cellText = row.cells[i].innerText.replace(/,/g, ';');
                rowData.push('"' + cellText + '"');
            }
            csv.push(rowData.join(','));
        }
    }
    
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
</script>

<?php include 'footer.php'; ?>