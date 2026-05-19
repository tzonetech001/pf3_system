<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    require_once '../includes/db.php';
    requireLogin('admin');
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, Admin</span>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="register_doctor.php" class="list-group-item list-group-item-action">Register Doctor</a>
                    <a href="register_police.php" class="list-group-item list-group-item-action">Register Police</a>
                    <a href="manage_users.php" class="list-group-item list-group-item-action">Manage Users</a>
                    <a href="reports.php" class="list-group-item list-group-item-action active">Reports</a>
                </div>
            </div>
            <div class="col-md-10">
                <h3>System Reports</h3>
                <?php
                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pf3_cases GROUP BY status");
                $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Case Status Summary</div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Recent Audit Logs</div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10");
                                    while ($log = $stmt->fetch()) {
                                        echo '<li class="list-group-item">' . $log['action'] . ' by ' . $log['user_type'] . ' - ' . date('Y-m-d H:i', strtotime($log['created_at'])) . '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Simple chart for status
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    data: [<?php echo $stats['PENDING'] ?? 0; ?>, <?php echo $stats['APPROVED'] ?? 0; ?>, <?php echo $stats['REJECTED'] ?? 0; ?>],
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                }]
            }
        });
    </script>
</body>
</html>