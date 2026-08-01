<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

$pf3 = $_GET['pf3'] ?? '';
$success = $_GET['success'] ?? 0;

// Get patient and case details
$patient = getPatientByPF3($pf3);
$case = getCaseByPF3($pf3);

if (!$patient || !$case) {
    $_SESSION['error_message'] = "Patient not found!";
    header('Location: search_pf3.php');
    exit;
}

if ($case['status'] !== 'APPROVED') {
    $_SESSION['error_message'] = "This PF3 case is not approved yet. Only approved cases can have medical reports.";
    header('Location: search_pf3.php');
    exit;
}

// Check if medical report already exists
$medical = getMedicalReportByPF3($pf3);
$existing_report = false;

if ($medical) {
    $existing_report = true;
}

include 'header.php';
?>

<style>
    .patient-header {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 20px;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .patient-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%);
        transform: rotate(45deg);
    }
    
    .patient-header h4, .patient-header p {
        position: relative;
        z-index: 1;
    }
    
    .info-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        background: white;
    }
    
    .info-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 1rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }
    
    .info-card .card-header h6 {
        color: #1a237e;
        font-weight: 600;
    }
    
    .info-table td, .info-table th {
        padding: 0.75rem;
        border: none;
    }
    
    .info-table tr {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-table tr:last-child {
        border-bottom: none;
    }
    
    .info-table th {
        color: #4a5568;
        font-weight: 600;
        width: 35%;
    }
    
    .info-table td {
        color: #2d3748;
    }
    
    .report-form {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
    }
    
    .form-control-custom {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control-custom:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.15);
    }
    
    .form-label-custom {
        font-weight: 600;
        color: #2d3748;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        color: white;
    }
    
    .report-view {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        border-left: 4px solid #0d47a1;
    }
    
    .report-view .label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .report-view .value {
        font-size: 1rem;
        color: #2d3748;
        margin-top: 0.25rem;
    }
    
    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .btn-outline-primary {
        color: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-outline-primary:hover {
        background: #0d47a1;
        color: white;
        border-color: #0d47a1;
    }
    
    .btn-primary {
        background: #0d47a1;
        border-color: #0d47a1;
    }
    
    .btn-primary:hover {
        background: #0a3a8a;
        border-color: #0a3a8a;
    }
    
    .btn-success {
        background: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background: #218838;
        border-color: #218838;
    }
    
    .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        border-color: #5a6268;
    }
    
    @media print {
        .sidebar, .top-navbar, .btn, form, .action-buttons {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .patient-header {
            background: #0d47a1 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    
    @media (max-width: 768px) {
        .patient-header {
            padding: 1rem;
        }
        .patient-header h4 {
            font-size: 1.1rem;
        }
        .info-table th, .info-table td {
            font-size: 0.9rem;
            padding: 0.5rem;
        }
    }
</style>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Medical report saved successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="patient-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-user-injured me-2"></i>Patient Details
            </h4>
            <p class="mb-0 opacity-75">
                <i class="fas fa-hashtag me-1"></i>PF3 Number: <strong><?php echo htmlspecialchars($pf3); ?></strong>
            </p>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="badge bg-success px-3 py-2 me-1">
                <i class="fas fa-check-circle me-1"></i> <?php echo $case['status']; ?>
            </span>
            <?php if ($case['rb_number']): ?>
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="fas fa-hashtag me-1 text-primary"></i> RB: <?php echo htmlspecialchars($case['rb_number']); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <!-- Patient Information -->
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2 text-primary"></i>Patient Information
                </h6>
            </div>
            <div class="card-body">
                <table class="info-table w-100">
                    <tr>
                        <th>Full Name:</th>
                        <td><strong><?php echo htmlspecialchars($patient['full_name']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td>
                            <span class="badge bg-<?php 
                                echo $patient['gender'] == 'Male' ? 'info' : ($patient['gender'] == 'Female' ? 'danger' : 'secondary'); 
                            ?>">
                                <?php echo htmlspecialchars($patient['gender']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?php echo $patient['age']; ?> years</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>" class="text-primary"><?php echo htmlspecialchars($patient['phone']); ?></a></td>
                    </tr>
                    <tr>
                        <th>Guardian Phone:</th>
                        <td><?php echo htmlspecialchars($patient['guardian_phone'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?php echo htmlspecialchars($patient['address']); ?></td>
                    </tr>
                    <tr>
                        <th>Registered:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($patient['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Incident Information -->
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Incident Information
                </h6>
            </div>
            <div class="card-body">
                <table class="info-table w-100">
                    <tr>
                        <th>Incident Date/Time:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($patient['incident_date_time'])); ?></td>
                    </tr>
                    <tr>
                        <th>Type of Incident:</th>
                        <td>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($case['type_of_incident']); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Police Station:</th>
                        <td><?php echo htmlspecialchars($case['police_station']); ?></td>
                    </tr>
                    <tr>
                        <th>Guardian Name:</th>
                        <td><?php echo htmlspecialchars($case['guardian_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Police Notes:</th>
                        <td><?php echo nl2br(htmlspecialchars($case['police_notes'] ?? 'N/A')); ?></td>
                    </tr>
                </table>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <strong class="text-primary">Incident Description:</strong>
                    <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Medical Report Section -->
        <?php if (!$existing_report): ?>
            <!-- Add Medical Report Form -->
            <div class="card info-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-stethoscope me-2 text-success"></i>Create Medical Report
                    </h6>
                </div>
                <div class="card-body">
                    <form action="save_report.php" method="POST" onsubmit="return validateForm()">
                        <input type="hidden" name="pf3" value="<?php echo $pf3; ?>">
                        
                        <div class="mb-3">
                            <label for="injury_type" class="form-label form-label-custom">
                                Injury Type <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="injury_type" name="injury_type" 
                                   placeholder="e.g., Fracture, Burn, Wound" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="severity" class="form-label form-label-custom">
                                Severity Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-control-custom" id="severity" name="severity" required>
                                <option value="">Select Severity</option>
                                <option value="Mild">Mild - Minor injuries</option>
                                <option value="Moderate">Moderate - Requires treatment</option>
                                <option value="Severe">Severe - Serious injuries</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="condition" class="form-label form-label-custom">
                                Patient Condition <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-custom" id="condition" name="condition" rows="3" 
                                      placeholder="Describe the patient's current condition..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="findings" class="form-label form-label-custom">
                                Medical Findings <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-custom" id="findings" name="findings" rows="4" 
                                      placeholder="Include detailed clinical findings, test results, and observations..." required></textarea>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle text-primary me-1"></i> Include detailed clinical findings, test results, and observations
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="recommendations" class="form-label form-label-custom">
                                Recommendations <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-custom" id="recommendations" name="recommendations" rows="3" 
                                      placeholder="Treatment plan, follow-up requirements, or referrals..." required></textarea>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle text-primary me-1"></i> Treatment plan, follow-up requirements, or referrals
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-save me-2"></i> Save Medical Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- View Existing Report -->
            <div class="card info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-medical me-2 text-success"></i>Medical Report
                    </h6>
                    <div>
                        <button onclick="window.print();" class="btn btn-sm btn-outline-primary me-1">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="my_reports.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-list"></i> All Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-view">
                        <div class="mb-3">
                            <div class="label">Injury Type</div>
                            <div class="value">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($medical['injury_type']); ?></span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="label">Severity</div>
                            <div class="value">
                                <span class="badge bg-<?php 
                                    echo $medical['severity'] == 'Severe' ? 'danger' : ($medical['severity'] == 'Moderate' ? 'warning' : 'success'); 
                                ?>">
                                    <?php echo htmlspecialchars($medical['severity']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="label">Patient Condition</div>
                            <div class="value"><?php echo nl2br(htmlspecialchars($medical['patient_condition'])); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="label">Medical Findings</div>
                            <div class="value"><?php echo nl2br(htmlspecialchars($medical['medical_findings'])); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="label">Recommendations</div>
                            <div class="value"><?php echo nl2br(htmlspecialchars($medical['recommendations'])); ?></div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> Report Date: <?php echo date('d/m/Y H:i', strtotime($medical['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-user-md me-1"></i> Doctor: Dr. <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
    <a href="search_pf3.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Search
    </a>
    <?php if ($existing_report): ?>
        <a href="my_reports.php" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> View All Reports
        </a>
        <button onclick="window.print();" class="btn btn-outline-primary">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    <?php else: ?>
        <a href="my_reports.php" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> My Reports
        </a>
    <?php endif; ?>
</div>

<script>
function validateForm() {
    const injuryType = document.getElementById('injury_type').value;
    const severity = document.getElementById('severity').value;
    const condition = document.getElementById('condition').value;
    const findings = document.getElementById('findings').value;
    const recommendations = document.getElementById('recommendations').value;
    
    if (!injuryType || !severity || !condition || !findings || !recommendations) {
        alert('Please fill in all required fields');
        return false;
    }
    
    if (condition.length < 10) {
        alert('Patient condition must be at least 10 characters long');
        return false;
    }
    
    if (findings.length < 10) {
        alert('Medical findings must be at least 10 characters long');
        return false;
    }
    
    if (recommendations.length < 10) {
        alert('Recommendations must be at least 10 characters long');
        return false;
    }
    
    return confirm('Are you sure you want to save this medical report?');
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(function() {
            bsAlert.close();
        }, 5000);
    });
}, 1000);
</script>

<?php include 'footer.php'; ?>