<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="register_doctor.php" class="list-group-item list-group-item-action">Register Doctor</a>
                    <a href="register_police.php" class="list-group-item list-group-item-action">Register Police</a>
                    <a href="manage_users.php" class="list-group-item list-group-item-action">Manage Users</a>
                    <a href="reports.php" class="list-group-item list-group-item-action">Reports</a>
                </div>
            </div>
            <div class="col-md-10">
                <h3>System Overview</h3>
                <div class="row">
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
                    $patients = $stmt->fetch()['count'];
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors");
                    $doctors = $stmt->fetch()['count'];
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM police_officers");
                    $police = $stmt->fetch()['count'];
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pf3_cases WHERE status = 'APPROVED'");
                    $approved = $stmt->fetch()['count'];
                    ?>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Total Patients</h5>
                                <p class="card-text"><?php echo $patients; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Doctors</h5>
                                <p class="card-text"><?php echo $doctors; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Police Officers</h5>
                                <p class="card-text"><?php echo $police; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Approved Cases</h5>
                                <p class="card-text"><?php echo $approved; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>