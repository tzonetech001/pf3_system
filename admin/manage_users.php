<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                    <a href="manage_users.php" class="list-group-item list-group-item-action active">Manage Users</a>
                    <a href="reports.php" class="list-group-item list-group-item-action">Reports</a>
                </div>
            </div>
            <div class="col-md-10">
                <h3>Manage Users</h3>
                <ul class="nav nav-tabs" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="doctors-tab" data-bs-toggle="tab" data-bs-target="#doctors" type="button" role="tab">Doctors</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="police-tab" data-bs-toggle="tab" data-bs-target="#police" type="button" role="tab">Police Officers</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">Admins</button>
                    </li>
                </ul>
                <div class="tab-content" id="userTabsContent">
                    <div class="tab-pane fade show active" id="doctors" role="tabpanel">
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM doctors");
                                while ($doctor = $stmt->fetch()) {
                                    echo '<tr>';
                                    echo '<td>' . $doctor['first_name'] . ' ' . $doctor['last_name'] . '</td>';
                                    echo '<td>' . $doctor['email'] . '</td>';
                                    echo '<td>' . $doctor['position'] . '</td>';
                                    echo '<td>' . $doctor['phone'] . '</td>';
                                    echo '<td><button class="btn btn-sm btn-danger" onclick="deleteUser(\'doctor\', ' . $doctor['id'] . ')">Delete</button></td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="police" role="tabpanel">
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Rank</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM police_officers");
                                while ($officer = $stmt->fetch()) {
                                    echo '<tr>';
                                    echo '<td>' . $officer['first_name'] . ' ' . $officer['last_name'] . '</td>';
                                    echo '<td>' . $officer['email'] . '</td>';
                                    echo '<td>' . $officer['rank'] . '</td>';
                                    echo '<td>' . $officer['phone'] . '</td>';
                                    echo '<td><button class="btn btn-sm btn-danger" onclick="deleteUser(\'police\', ' . $officer['id'] . ')">Delete</button></td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="admins" role="tabpanel">
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM admins");
                                while ($admin = $stmt->fetch()) {
                                    echo '<tr>';
                                    echo '<td>' . $admin['username'] . '</td>';
                                    echo '<td>' . $admin['email'] . '</td>';
                                    echo '<td>' . $admin['phone'] . '</td>';
                                    echo '<td><button class="btn btn-sm btn-danger" onclick="deleteUser(\'admin\', ' . $admin['id'] . ')">Delete</button></td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteUser(type, id) {
            if (confirm('Are you sure you want to delete this user?')) {
                // Implement delete functionality
                alert('Delete not implemented yet.');
            }
        }
    </script>
</body>
</html>