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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 20px;
        margin-bottom: 1.5rem;
    }
    
    .info-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .info-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }
    
    .info-table td, .info-table th {
        padding: 0.75rem;
        border: none;
    }
    
    .info-table tr {
        border-bottom: 1px solid #f0f0f0;
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
        border-color: #11998e;
        box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }
    
    .report-view {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
    }
    
    @media print {
        .sidebar, .top-navbar, .btn, form, .action-buttons {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
    }
</style>

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
            <p class="mb-0 opacity-75">PF3 Number: <strong><?php echo htmlspecialchars($pf3); ?></strong></p>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="fas fa-check-circle text-success me-1"></i> Status: <?php echo $case['status']; ?>
            </span>
            <?php if ($case['rb_number']): ?>
                <span class="badge bg-light text-dark px-3 py-2 ms-2">
                    <i class="fas fa-hashtag me-1"></i> RB: <?php echo htmlspecialchars($case['rb_number']); ?>
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
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-user me-2 text-primary"></i>Patient Information
                </h6>
            </div>
            <div class="card-body">
                <table class="info-table w-100">
                    <tr>
                        <th width="35%">Full Name:</th>
                        <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?php echo $patient['age']; ?> years</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Guardian Phone:</th>
                        <td><?php echo htmlspecialchars($patient['guardian_phone'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?php echo htmlspecialchars($patient['address']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Incident Information -->
        <div class="card info-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Incident Information
                </h6>
            </div>
            <div class="card-body">
                <table class="info-table w-100">
                    <tr>
                        <th width="35%">Incident Date/Time:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($patient['incident_date_time'])); ?></td>
                    </tr>
                    <tr>
                        <th>Type of Incident:</th>
                        <td><?php echo htmlspecialchars($case['type_of_incident']); ?></td>
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
                
                <div class="mt-3">
                    <strong>Incident Description:</strong>
                    <p class="text-muted mt-2"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
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
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-stethoscope me-2 text-success"></i>Create Medical Report
                    </h6>
                </div>
                <div class="card-body">
                    <form action="save_report.php" method="POST" onsubmit="return validateForm()">
                        <input type="hidden" name="pf3" value="<?php echo $pf3; ?>">
                        
                        <div class="mb-3">
                            <label for="injury_type" class="form-label fw-semibold">Injury Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" id="injury_type" name="injury_type" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="severity" class="form-label fw-semibold">Severity Level <span class="text-danger">*</span></label>
                            <select class="form-select form-control-custom" id="severity" name="severity" required>
                                <option value="">Select Severity</option>
                                <option value="Mild">Mild - Minor injuries</option>
                                <option value="Moderate">Moderate - Requires treatment</option>
                                <option value="Severe">Severe - Serious injuries</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="condition" class="form-label fw-semibold">Patient Condition <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-custom" id="condition" name="condition" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="findings" class="form-label fw-semibold">Medical Findings <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-custom" id="findings" name="findings" rows="4" required></textarea>
                            <div class="form-text">Include detailed clinical findings, test results, and observations</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="recommendations" class="form-label fw-semibold">Recommendations <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-custom" id="recommendations" name="recommendations" rows="3" required></textarea>
                            <div class="form-text">Treatment plan, follow-up requirements, or referrals</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-submit text-white">
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
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-file-medical me-2 text-success"></i>Medical Report
                    </h6>
                    <button onclick="window.print();" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                <div class="card-body">
                    <div class="report-view">
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Injury Type</label>
                            <p class="mb-0"><?php echo htmlspecialchars($medical['injury_type']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Severity</label>
                            <div>
                                <span class="badge bg-<?php 
                                    echo $medical['severity'] == 'Severe' ? 'danger' : ($medical['severity'] == 'Moderate' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo htmlspecialchars($medical['severity']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Patient Condition</label>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($medical['patient_condition'])); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Medical Findings</label>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($medical['medical_findings'])); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Recommendations</label>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($medical['recommendations'])); ?></p>
                        </div>
                        
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i> Report Date: <?php echo date('d/m/Y H:i', strtotime($medical['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <a href="search_pf3.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Search
    </a>
    <?php if ($existing_report): ?>
        <a href="my_reports.php" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> View All Reports
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
    
    return confirm('Are you sure you want to save this medical report?');
}
</script>

<?php include 'footer.php'; ?>