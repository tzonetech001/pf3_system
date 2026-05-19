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

// Get audit logs
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
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
        color: #A6EDCF ;
    }
    
    .pagination-custom .page-item.active .page-link {
        background: linear-gradient(135deg, #A6EDCF  0%, #A6EDCF  100%);
        border-color: #A6EDCF ;
        color: white;
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="fas fa-history me-2"></i>Audit Log
        </h4>
        <p class="text-muted mb-0">Complete system activity history</p>
    </div>
    <div>
        <span class="badge bg-secondary">Total Records: <?php echo $total; ?></span>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>#<?php echo $log['id']; ?></td>
                        <td>
                            <span class="badge-action bg-<?php 
                                if (strpos($log['action'], 'Approved') !== false) echo 'success';
                                elseif (strpos($log['action'], 'Rejected') !== false) echo 'danger';
                                elseif (strpos($log['action'], 'Registered') !== false) echo 'info';
                                elseif (strpos($log['action'], 'Deleted') !== false) echo 'dark';
                                elseif (strpos($log['action'], 'Updated') !== false) echo 'warning';
                                else echo 'secondary';
                            ?> text-white">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td>
                            <i class="fas fa-<?php 
                                echo $log['user_type'] == 'admin' ? 'user-cog' : ($log['user_type'] == 'doctor' ? 'user-md' : 'user-shield');
                            ?> me-1"></i>
                            <?php echo ucfirst($log['user_type']); ?>
                        </td>
                        <td><?php echo $log['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td>
                            <small><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No audit logs found</td>
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
        </li>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'footer.php'; ?>