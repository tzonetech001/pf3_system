<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="search_pf3.php" class="list-group-item list-group-item-action">Search PF3</a>
                    <a href="my_reports.php" class="list-group-item list-group-item-action">My Reports</a>
                </div>
            </div>
            <div class="col-md-10">
                <h3>Dashboard Overview</h3>
                <div class="row">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM medical_reports WHERE doctor_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $reports = $stmt->fetch()['count'];
                    ?>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">My Reports</h5>
                                <p class="card-text"><?php echo $reports; ?></p>
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