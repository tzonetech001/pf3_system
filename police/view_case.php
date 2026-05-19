<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

$pf3 = $_GET['pf3'] ?? '';

// Get patient and case details
$patient = getPatientByPF3($pf3);
$case = getCaseByPF3($pf3);

if (!$patient || !$case) {
    $_SESSION['error_message'] = "Case not found!";
    header('Location: dashboard.php');
    exit;
}

// Get medical report if exists
$medicalReport = null;
if ($case['status'] == 'APPROVED') {
    $stmt = $pdo->prepare("
        SELECT mr.*, d.first_name, d.last_name, d.position 
        FROM medical_reports mr 
        JOIN doctors d ON mr.doctor_id = d.id 
        WHERE mr.pf3_number = ?
        ORDER BY mr.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$pf3]);
    $medicalReport = $stmt->fetch();
}

// Get audit history
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    WHERE details LIKE ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute(["%$pf3%"]);
$auditHistory = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .status-banner {
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .detail-card .card-header {
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
    
    .decision-card {
        border: 2px solid #667eea;
        border-radius: 15px;
    }
    
    @media print {
        .sidebar, .top-navbar, .decision-card, .btn, form, .action-buttons {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .status-banner {
            border: 1px solid #ddd;
        }
    }
</style>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <!-- Status Banner -->
        <div class="status-banner alert alert-<?php 
            echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'info'); 
        ?> mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-<?php 
                        echo $case['status'] == 'APPROVED' ? 'check-circle' : ($case['status'] == 'REJECTED' ? 'times-circle' : 'clock'); 
                    ?> fa-2x"></i>
                    <div>
                        <h5 class="mb-0">Case Status: <?php echo $case['status']; ?></h5>
                        <small>Last updated: <?php echo date('d/m/Y H:i', strtotime($case['updated_at'])); ?></small>
                    </div>
                </div>
                <?php if ($case['rb_number']): ?>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge bg-light text-dark p-2">
                            <i class="fas fa-hashtag"></i> RB Number: <?php echo htmlspecialchars($case['rb_number']); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Case Details -->
        <div class="card detail-card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-file-alt me-2"></i>Case Details - PF3: <?php echo htmlspecialchars($pf3); ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-user-injured me-2 text-primary"></i>Patient Information
                        </h6>
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
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Incident Details
                        </h6>
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
                                <th>Submitted On:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <strong>Incident Description:</strong>
                            <p class="text-muted mt-2"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Medical Report -->
                <?php if ($medicalReport): ?>
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-stethoscope me-2 text-primary"></i>Medical Report
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Doctor</small>
                                <strong>Dr. <?php echo htmlspecialchars($medicalReport['first_name'] . ' ' . $medicalReport['last_name']); ?></strong>
                                <br><small>(<?php echo htmlspecialchars($medicalReport['position']); ?>)</small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Injury Type</small>
                                <strong><?php echo htmlspecialchars($medicalReport['injury_type']); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Severity</small>
                                <span class="badge bg-<?php 
                                    echo $medicalReport['severity'] == 'Critical' ? 'danger' : 
                                        ($medicalReport['severity'] == 'Moderate' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo htmlspecialchars($medicalReport['severity']); ?>
                                </span>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Patient Condition</small>
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($medicalReport['patient_condition'])); ?></p>
                                
                                <small class="text-muted d-block mt-2">Medical Findings</small>
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($medicalReport['medical_findings'])); ?></p>
                                
                                <small class="text-muted d-block mt-2">Recommendations</small>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($medicalReport['recommendations'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Police Notes -->
                <?php if ($case['police_notes']): ?>
                <div class="mt-4">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-sticky-note me-2"></i>Police Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($case['police_notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Decision Form -->
                <?php if ($case['status'] === 'PENDING'): ?>
                <div class="mt-4">
                    <div class="card decision-card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-gavel me-2"></i>Make Decision</h6>
                        </div>
                        <div class="card-body">
                            <form action="process_case.php" method="POST" onsubmit="return confirmDecision();">
                                <input type="hidden" name="pf3" value="<?php echo $pf3; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Decision</label>
                                    <select class="form-select" id="decision" name="decision" required onchange="toggleRejectReason()">
                                        <option value="">Select Decision</option>
                                        <option value="APPROVE">✅ Approve - Issue RB Number</option>
                                        <option value="REJECT">❌ Reject - Provide Reason</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3" id="rejectReasonDiv" style="display: none;">
                                    <label class="form-label fw-bold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4" 
                                              placeholder="Please provide detailed reason for rejection..."></textarea>
                                    <small class="text-muted">This reason will be visible to the hospital and patient.</small>
                                </div>
                                
                                <div class="mb-3" id="approveNotesDiv">
                                    <label class="form-label">Additional Notes (Optional)</label>
                                    <textarea class="form-control" id="approve_notes" name="notes" rows="3" 
                                              placeholder="Any additional instructions or notes..."></textarea>
                                </div>
                                
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Submit Decision
                                    </button>
                                    <a href="cases.php?status=PENDING" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2 mb-4">
            <a href="cases.php?status=PENDING" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Cases
            </a>
            <?php if ($case['status'] == 'APPROVED' && $case['rb_number']): ?>
            <button onclick="window.print();" class="btn btn-info">
                <i class="fas fa-print me-1"></i> Print Details
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDecision() {
    var decision = document.getElementById('decision').value;
    if (!decision) {
        alert('Please select a decision');
        return false;
    }
    
    if (decision === 'REJECT') {
        var notes = document.getElementById('notes').value.trim();
        if (!notes) {
            alert('Please provide a reason for rejection');
            return false;
        }
        return confirm('Are you sure you want to REJECT this case? This action cannot be undone.');
    } else if (decision === 'APPROVE') {
        return confirm('Are you sure you want to APPROVE this case? An RB number will be generated.');
    }
    return false;
}

function toggleRejectReason() {
    var decision = document.getElementById('decision').value;
    var rejectDiv = document.getElementById('rejectReasonDiv');
    var approveDiv = document.getElementById('approveNotesDiv');
    
    if (decision === 'REJECT') {
        rejectDiv.style.display = 'block';
        approveDiv.style.display = 'none';
        document.getElementById('notes').required = true;
    } else if (decision === 'APPROVE') {
        rejectDiv.style.display = 'none';
        approveDiv.style.display = 'block';
        document.getElementById('notes').required = false;
    } else {
        rejectDiv.style.display = 'none';
        approveDiv.style.display = 'block';
    }
}
</script>

<?php include 'footer.php'; ?>