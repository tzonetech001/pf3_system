<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    require_once '../includes/db.php';
    requireLogin('doctor');
    $pf3 = $_GET['pf3'] ?? '';
    $patient = getPatientByPF3($pf3);
    $case = getCaseByPF3($pf3);
    $medical = getMedicalReportByPF3($pf3);
    if (!$patient || !$case || $case['status'] !== 'APPROVED') {
        echo '<div class="alert alert-danger">Invalid or unapproved PF3.</div>';
        exit;
    }
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

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Patient Details - PF3: <?php echo $pf3; ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Patient Information</h5>
                        <p><strong>Name:</strong> <?php echo $patient['full_name']; ?></p>
                        <p><strong>Gender:</strong> <?php echo $patient['gender']; ?></p>
                        <p><strong>Age:</strong> <?php echo $patient['age']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $patient['phone']; ?></p>
                        <p><strong>Address:</strong> <?php echo $patient['address']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Incident & Police Details</h5>
                        <p><strong>Incident Type:</strong> <?php echo $case['type_of_incident']; ?></p>
                        <p><strong>Description:</strong> <?php echo $case['description']; ?></p>
                        <p><strong>Police Status:</strong> <span class="badge bg-success"><?php echo $case['status']; ?></span></p>
                        <p><strong>Police Notes:</strong> <?php echo $case['police_notes']; ?></p>
                        <p><strong>RB Number:</strong> <?php echo $case['rb_number']; ?></p>
                    </div>
                </div>
                <hr>
                <?php if (!$medical) { ?>
                <h5>Add Medical Report</h5>
                <form action="save_report.php" method="POST">
                    <input type="hidden" name="pf3" value="<?php echo $pf3; ?>">
                    <div class="mb-3">
                        <label for="injury_type" class="form-label">Injury Type</label>
                        <input type="text" class="form-control" id="injury_type" name="injury_type" required>
                    </div>
                    <div class="mb-3">
                        <label for="severity" class="form-label">Severity Level</label>
                        <select class="form-select" id="severity" name="severity" required>
                            <option value="Mild">Mild</option>
                            <option value="Moderate">Moderate</option>
                            <option value="Severe">Severe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="condition" class="form-label">Patient Condition</label>
                        <textarea class="form-control" id="condition" name="condition" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="findings" class="form-label">Medical Findings</label>
                        <textarea class="form-control" id="findings" name="findings" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="recommendations" class="form-label">Recommendations</label>
                        <textarea class="form-control" id="recommendations" name="recommendations" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Medical Report</button>
                </form>
                <?php } else { ?>
                <h5>Medical Report</h5>
                <p><strong>Injury Type:</strong> <?php echo $medical['injury_type']; ?></p>
                <p><strong>Severity:</strong> <?php echo $medical['severity']; ?></p>
                <p><strong>Condition:</strong> <?php echo $medical['patient_condition']; ?></p>
                <p><strong>Findings:</strong> <?php echo $medical['medical_findings']; ?></p>
                <p><strong>Recommendations:</strong> <?php echo $medical['recommendations']; ?></p>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>