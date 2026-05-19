<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    require_once '../includes/db.php';
    requireLogin('doctor');
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Doctor Dashboard</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="search_pf3.php" class="list-group-item list-group-item-action">Search PF3</a>
                    <a href="my_reports.php" class="list-group-item list-group-item-action active">My Reports</a>
                </div>
            </div>
            <div class="col-md-10">
                <h3>My Medical Reports</h3>
                <table class="table table-striped">
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
                        <?php
                        $stmt = $pdo->prepare("SELECT mr.*, p.full_name FROM medical_reports mr JOIN patients p ON mr.pf3_number = p.pf3_number WHERE mr.doctor_id = ? ORDER BY mr.created_at DESC");
                        $stmt->execute([$_SESSION['user_id']]);
                        while ($report = $stmt->fetch()) {
                            echo '<tr>';
                            echo '<td>' . $report['pf3_number'] . '</td>';
                            echo '<td>' . $report['full_name'] . '</td>';
                            echo '<td>' . $report['injury_type'] . '</td>';
                            echo '<td>' . $report['severity'] . '</td>';
                            echo '<td>' . date('Y-m-d', strtotime($report['created_at'])) . '</td>';
                            echo '<td><a href="view_patient.php?pf3=' . $report['pf3_number'] . '" class="btn btn-sm btn-primary">View</a></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>