<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/sms_helper.php';
requireLogin('police');

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

$pf3 = $_GET['pf3'] ?? '';

if (empty($pf3)) {
    $_SESSION['error_message'] = "Invalid PF3 number.";
    header('Location: dashboard.php');
    exit;
}

// Fetch case details with patient information
$stmt = $pdo->prepare("
    SELECT c.*, 
           p.full_name, p.gender, p.age, p.address, p.phone as patient_phone, 
           p.guardian_phone, p.incident_date_time, p.created_at as patient_created_at,
           p.last_application_date
    FROM pf3_cases c
    JOIN patients p ON c.pf3_number = p.pf3_number 
    WHERE c.pf3_number = ?
");
$stmt->execute([$pf3]);
$case = $stmt->fetch();

if (!$case) {
    $_SESSION['error_message'] = "Case not found!";
    header('Location: cases.php?status=PENDING');
    exit;
}

// Check if there are any success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

include 'header.php';
?>

<style>
    /* ===== GENERAL STYLES ===== */
    * {
        font-size: 14px;
    }
    
    .page-header {
        background: white;
        padding: 1.2rem;
        border-radius: 12px;
        margin-bottom: 1.2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #0d47a1;
    }
    
    .page-header h4 {
        font-size: 18px;
    }
    
    .page-header p {
        font-size: 14px;
    }
    
    .detail-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.2rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .detail-card:hover {
        box-shadow: 0 5px 20px rgba(13, 71, 161, 0.1);
    }
    
    .detail-card .card-header {
        background: white;
        border-bottom: 2px solid #e8eaf6;
        padding: 0.8rem 1.2rem;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
        font-size: 15px;
        color: #1a237e;
    }
    
    .detail-card .card-header i {
        color: #0d47a1;
    }
    
    .detail-card .card-body {
        padding: 1.2rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.8rem;
    }
    
    .info-item {
        padding: 0.6rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .info-item .label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 0.2rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .info-item .value {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
    }
    
    .badge-status {
        padding: 0.4rem 1.2rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
    }
    
    .action-btn {
        padding: 0.5rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        margin-right: 0.3rem;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    
    .btn-danger {
        background: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        background: #c82333;
        border-color: #c82333;
    }
    
    .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        border-color: #5a6268;
    }
    
    .btn-info {
        background: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }
    
    .btn-info:hover {
        background: #138496;
        border-color: #138496;
        color: white;
    }
    
    /* ===== ALERT STYLES ===== */
    .alert-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        width: 90%;
        max-width: 500px;
        animation: slideDown 0.5s ease forwards;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
    }
    
    .alert-container .alert {
        margin: 0;
        border: none;
        border-radius: 16px;
        padding: 2rem;
        font-size: 15px;
        line-height: 1.6;
    }
    
    .alert-container .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 6px solid #28a745;
    }
    
    .alert-container .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-left: 6px solid #dc3545;
    }
    
    .alert-container .alert i {
        font-size: 40px;
        display: block;
        margin-bottom: 1rem;
    }
    
    .alert-container .alert .alert-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .alert-container .alert .alert-message {
        font-size: 15px;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .alert-container .alert .alert-details {
        font-size: 13px;
        color: rgba(0,0,0,0.6);
        display: block;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .alert-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translate(-50%, -80%) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .alert-close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        background: transparent;
        border: none;
        font-size: 24px;
        color: rgba(0,0,0,0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0 5px;
        line-height: 1;
    }
    
    .alert-close-btn:hover {
        color: rgba(0,0,0,0.8);
        transform: rotate(90deg);
    }
    
    /* ===== CONFIRMATION POPUP ===== */
    .confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }
    
    .confirm-overlay.active {
        display: flex;
    }
    
    .confirm-box {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        text-align: center;
        animation: slideDown 0.4s ease forwards;
        box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        position: relative;
    }
    
    .confirm-box .icon {
        font-size: 48px;
        margin-bottom: 0.8rem;
        display: block;
    }
    
    .confirm-box .title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #2d3748;
    }
    
    .confirm-box .subtitle {
        font-size: 14px;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }
    
    .confirm-box .details {
        font-size: 13px;
        color: #718096;
        background: #f7fafc;
        padding: 0.8rem;
        border-radius: 10px;
        margin: 1rem 0;
        text-align: left;
    }
    
    .confirm-box .details strong {
        color: #2d3748;
    }
    
    .confirm-box .btn-group-confirm {
        display: flex;
        gap: 0.8rem;
        justify-content: center;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    
    .confirm-box .btn-group-confirm .btn {
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        min-width: 100px;
        transition: all 0.3s ease;
    }
    
    .confirm-box .btn-group-confirm .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .confirm-box .btn-group-confirm .btn-cancel {
        background: #e2e8f0;
        color: #4a5568;
        border: none;
    }
    
    .confirm-box .btn-group-confirm .btn-cancel:hover {
        background: #cbd5e0;
    }
    
    .confirm-box .btn-group-confirm .btn-approve {
        background: #28a745;
        color: white;
        border: none;
    }
    
    .confirm-box .btn-group-confirm .btn-approve:hover {
        background: #218838;
    }
    
    .confirm-box .btn-group-confirm .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
    }
    
    .confirm-box .btn-group-confirm .btn-reject:hover {
        background: #c82333;
    }
    
    .confirm-box .close-confirm {
        position: absolute;
        top: 12px;
        right: 18px;
        background: transparent;
        border: none;
        font-size: 28px;
        color: #a0aec0;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0 5px;
        line-height: 1;
    }
    
    .confirm-box .close-confirm:hover {
        color: #4a5568;
        transform: rotate(90deg);
    }
    
    /* ===== REJECT NOTE TEXTAREA ===== */
    .reject-note-area {
        margin: 1rem 0;
        text-align: left;
    }
    
    .reject-note-area textarea {
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.8rem;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
        transition: all 0.3s ease;
    }
    
    .reject-note-area textarea:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.15);
        outline: none;
    }
    
    .reject-note-area label {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: block;
    }
    
    .reject-note-area .text-muted {
        font-size: 12px;
        color: #6c757d;
        margin-top: 0.3rem;
        display: block;
    }
    
    /* ===== STATUS TIMELINE ===== */
    .status-timeline {
        position: relative;
        padding-left: 1.8rem;
    }
    
    .status-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    
    .status-step {
        position: relative;
        padding: 0.4rem 0 0.4rem 1.8rem;
    }
    
    .status-step::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e2e8f0;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e2e8f0;
    }
    
    .status-step.active::before {
        background: #0d47a1;
        box-shadow: 0 0 0 2px #0d47a1;
    }
    
    .status-step.completed::before {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }
    
    .status-step.rejected::before {
        background: #dc3545;
        box-shadow: 0 0 0 2px #dc3545;
    }
    
    .status-step strong {
        font-size: 14px;
    }
    
    .status-step .text-muted {
        font-size: 13px;
    }
    
    .status-step .time {
        font-size: 12px;
        color: #6c757d;
    }
    
    /* ===== SMS MESSAGE PREVIEW ===== */
    .sms-preview {
        background: #e3f2fd;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 0.8rem;
        border-left: 4px solid #0d47a1;
    }
    
    .sms-preview .sms-label {
        font-size: 12px;
        color: #0d47a1;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
    }
    
    .sms-preview .sms-message {
        font-size: 14px;
        color: #2d3748;
        word-wrap: break-word;
    }
    
    .sms-preview .sms-phone {
        font-size: 12px;
        color: #718096;
        margin-top: 0.3rem;
    }
    
    /* ===== STATUS CARD ===== */
    .status-card {
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
    }
    
    .status-card .icon {
        font-size: 48px;
        margin-bottom: 0.8rem;
        display: block;
    }
    
    .status-card .status-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }
    
    .status-card .status-message {
        font-size: 14px;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }
    
    .status-card .status-detail {
        font-size: 13px;
        color: #718096;
        padding-top: 0.5rem;
        border-top: 1px solid rgba(0,0,0,0.1);
        margin-top: 0.5rem;
    }
    
    .status-card.approved {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border: 2px solid #28a745;
    }
    
    .status-card.approved .status-title {
        color: #155724;
    }
    
    .status-card.rejected {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        border: 2px solid #dc3545;
    }
    
    .status-card.rejected .status-title {
        color: #721c24;
    }
    
    .status-card.pending {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border: 2px solid #0d47a1;
    }
    
    .status-card.pending .status-title {
        color: #0d47a1;
    }
    
    /* ===== RESEND BUTTON ===== */
    .resend-btn {
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    
    .resend-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 0.8rem;
            padding: 1rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: 100%;
            margin-right: 0;
            margin-bottom: 0.3rem;
            font-size: 13px;
            padding: 0.4rem 1rem;
        }
        
        .page-header .d-flex {
            flex-wrap: wrap;
            width: 100%;
        }
        
        .page-header .d-flex .action-btn,
        .page-header .d-flex .badge-status {
            width: 100%;
            text-align: center;
            margin-bottom: 0.3rem;
        }
        
        .detail-card .card-body {
            padding: 0.8rem;
        }
        
        .alert-container {
            width: 95%;
            max-width: 95%;
            padding: 1rem;
        }
        
        .alert-container .alert {
            padding: 1.5rem;
            font-size: 14px;
        }
        
        .alert-container .alert i {
            font-size: 32px;
        }
        
        .alert-container .alert .alert-title {
            font-size: 18px;
        }
        
        .confirm-box {
            padding: 1.5rem;
        }
        
        .confirm-box .btn-group-confirm {
            flex-direction: column;
        }
        
        .confirm-box .btn-group-confirm .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .status-timeline {
            padding-left: 1.2rem;
        }
        
        .status-step {
            padding: 0.3rem 0 0.3rem 1.2rem;
        }
        
        .status-step::before {
            width: 10px;
            height: 10px;
            left: -5px;
        }
    }
</style>

<!-- ===== ALERT OVERLAY AND CONTAINER ===== -->
<?php if ($success_message): ?>
<div class="alert-overlay" id="alertOverlay"></div>
<div class="alert-container" id="alertContainer">
    <div class="alert alert-success">
        <button type="button" class="alert-close-btn" onclick="closeAlert()">&times;</button>
        <i class="fas fa-check-circle"></i>
        <span class="alert-title">Success!</span>
        <span class="alert-message"><?php echo htmlspecialchars($success_message); ?></span>
        <span class="alert-details">Case #<?php echo htmlspecialchars($pf3); ?> has been processed successfully.</span>
    </div>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="alert-overlay" id="alertOverlay"></div>
<div class="alert-container" id="alertContainer">
    <div class="alert alert-danger">
        <button type="button" class="alert-close-btn" onclick="closeAlert()">&times;</button>
        <i class="fas fa-exclamation-circle"></i>
        <span class="alert-title">Error!</span>
        <span class="alert-message"><?php echo htmlspecialchars($error_message); ?></span>
        <span class="alert-details">Please try again or contact support if the issue persists.</span>
    </div>
</div>
<?php endif; ?>

<!-- ===== CONFIRMATION POPUPS ===== -->

<!-- Approve Confirmation Popup -->
<div class="confirm-overlay" id="approveConfirm">
    <div class="confirm-box">
        <button type="button" class="close-confirm" onclick="closeConfirm('approveConfirm')">&times;</button>
        <span class="icon">✅</span>
        <div class="title">Approve Case</div>
        <div class="subtitle">Are you sure you want to approve this case? SMS will be sent automatically.</div>
        <div class="details">
            <strong>Patient:</strong> <?php echo htmlspecialchars($case['full_name']); ?><br>
            <strong>PF3 Number:</strong> #<?php echo htmlspecialchars($pf3); ?><br>
            <strong>RB Number:</strong> <span id="displayRbNumber" style="color:#28a745;font-weight:700;"></span>
        </div>
        
        <!-- SMS Preview for Approval -->
        <div class="sms-preview" style="border-left-color:#28a745;">
            <div class="sms-label">📱 SMS to be sent to patient:</div>
            <div class="sms-message" id="approveSmsPreview">
                <strong>APPROVED</strong> - <?php echo htmlspecialchars($case['full_name']); ?>, application #<?php echo htmlspecialchars($pf3); ?> APPROVED. 
                RB: <span id="previewRbNumber"></span>. Visit hospital. Thank you!
            </div>
            <div class="sms-phone">📞 To: <?php echo htmlspecialchars($case['patient_phone']); ?></div>
        </div>
        
        <div class="btn-group-confirm">
            <button type="button" class="btn btn-cancel" onclick="closeConfirm('approveConfirm')">Cancel</button>
            <button type="button" class="btn btn-approve" id="confirmApproveBtn">Approve & Send SMS</button>
        </div>
    </div>
</div>

<!-- Reject Confirmation Popup with Note -->
<div class="confirm-overlay" id="rejectConfirm">
    <div class="confirm-box">
        <button type="button" class="close-confirm" onclick="closeConfirm('rejectConfirm')">&times;</button>
        <span class="icon">❌</span>
        <div class="title">Reject Case</div>
        <div class="subtitle">Are you sure you want to reject this case? SMS will be sent automatically.</div>
        <div class="details">
            <strong>Patient:</strong> <?php echo htmlspecialchars($case['full_name']); ?><br>
            <strong>PF3 Number:</strong> #<?php echo htmlspecialchars($pf3); ?>
        </div>
        
        <!-- Rejection Note Area -->
        <div class="reject-note-area">
            <label for="rejectNote">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea id="rejectNote" placeholder="Please provide a clear reason for rejecting this case..." required></textarea>
            <span class="text-muted">This reason will be included in the SMS notification to the patient.</span>
        </div>
        
        <!-- SMS Preview for Rejection -->
        <div class="sms-preview" style="border-left-color:#dc3545;">
            <div class="sms-label">📱 SMS to be sent to patient:</div>
            <div class="sms-message" id="rejectSmsPreview">
                <strong>REJECTED</strong> - <?php echo htmlspecialchars($case['full_name']); ?>, application #<?php echo htmlspecialchars($pf3); ?> REJECTED. 
                Reason: <span id="previewRejectReason">[Your reason will appear here]</span>. Contact police. Thank you!
            </div>
            <div class="sms-phone">📞 To: <?php echo htmlspecialchars($case['patient_phone']); ?></div>
        </div>
        
        <div class="btn-group-confirm">
            <button type="button" class="btn btn-cancel" onclick="closeConfirm('rejectConfirm')">Cancel</button>
            <button type="button" class="btn btn-reject" id="confirmRejectBtn">Reject & Send SMS</button>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="mb-1 fw-bold text-primary">
            <i class="fas fa-file-alt me-2"></i>Case Details
        </h4>
        <p class="text-muted mb-0">PF3 Number: <strong><?php echo htmlspecialchars($pf3); ?></strong></p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Status Badge -->
        <span class="badge-status badge bg-<?php 
            echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'warning'); 
        ?>">
            <i class="fas fa-<?php 
                echo $case['status'] == 'APPROVED' ? 'check-circle' : ($case['status'] == 'REJECTED' ? 'times-circle' : 'clock'); 
            ?> me-1"></i>
            <?php 
                $status_display = $case['status'];
                if ($status_display == 'APPROVED') echo 'APPROVED ✓';
                elseif ($status_display == 'REJECTED') echo 'REJECTED ✗';
                else echo 'PENDING ⏳';
            ?>
        </span>
        
        <!-- Approve/Reject Buttons (only for pending) -->
        <?php if ($case['status'] == 'PENDING'): ?>
            <button class="btn btn-success action-btn" onclick="showApproveConfirm()">
                <i class="fas fa-check me-2"></i>Approve
            </button>
            <button class="btn btn-danger action-btn" onclick="showRejectConfirm()">
                <i class="fas fa-times me-2"></i>Reject
            </button>
        <?php endif; ?>
        
        <!-- Resend SMS Button (only for approved/rejected) -->
        <?php if ($case['status'] == 'APPROVED' || $case['status'] == 'REJECTED'): ?>
            <a href="resend_sms.php?pf3=<?php echo $pf3; ?>" class="btn btn-info action-btn resend-btn" 
               onclick="return confirm('Are you sure you want to resend SMS to patient?')">
                <i class="fas fa-sms me-2"></i>Resend SMS
            </a>
        <?php endif; ?>
        
        <!-- Back Button -->
        <a href="cases.php?status=<?php echo $case['status']; ?>" class="btn btn-secondary action-btn">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="row g-4">
    <!-- Left Column - Patient Info & Case Details -->
    <div class="col-lg-8">
        <!-- Patient Information -->
        <div class="card detail-card">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Patient Information
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Full Name</div>
                        <div class="value"><?php echo htmlspecialchars($case['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Gender</div>
                        <div class="value"><?php echo htmlspecialchars($case['gender']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Age</div>
                        <div class="value"><?php echo htmlspecialchars($case['age']); ?> years</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Phone Number</div>
                        <div class="value"><?php echo htmlspecialchars($case['patient_phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Guardian Phone</div>
                        <div class="value"><?php echo htmlspecialchars($case['guardian_phone']) ?: 'N/A'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Address</div>
                        <div class="value"><?php echo htmlspecialchars($case['address']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Incident Date & Time</div>
                        <div class="value"><?php echo date('d/m/Y H:i', strtotime($case['incident_date_time'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Application Date</div>
                        <div class="value"><?php echo date('d/m/Y H:i', strtotime($case['last_application_date'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Case Information -->
        <div class="card detail-card">
            <div class="card-header">
                <i class="fas fa-folder-open me-2"></i>Case Information
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">PF3 Number</div>
                        <div class="value"><?php echo htmlspecialchars($case['pf3_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Type of Incident</div>
                        <div class="value"><?php echo htmlspecialchars($case['type_of_incident']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Police Station</div>
                        <div class="value"><?php echo htmlspecialchars($case['police_station']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Guardian Name</div>
                        <div class="value"><?php echo htmlspecialchars($case['guardian_name']); ?></div>
                    </div>
                    <?php if ($case['rb_number']): ?>
                    <div class="info-item">
                        <div class="label">RB Number</div>
                        <div class="value"><span class="badge bg-primary"><?php echo htmlspecialchars($case['rb_number']); ?></span></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="label">Current Status</div>
                        <div class="value">
                            <span class="badge bg-<?php 
                                echo $case['status'] == 'APPROVED' ? 'success' : ($case['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                            ?>">
                                <?php echo $case['status']; ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($case['police_notes']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="label">Police Notes</div>
                        <div class="value"><?php echo nl2br(htmlspecialchars($case['police_notes'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mt-3">
                    <div class="label">Description</div>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Status Timeline -->
        <div class="card detail-card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Case Timeline
            </div>
            <div class="card-body">
                <div class="status-timeline">
                    <!-- Application Submitted -->
                    <div class="status-step completed">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Application Submitted</strong>
                                <div class="text-muted small">PF3 #<?php echo htmlspecialchars($pf3); ?> was created</div>
                            </div>
                            <div class="time"><?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <!-- Pending Review -->
                    <div class="status-step <?php echo $case['status'] == 'PENDING' ? 'active' : ($case['status'] != 'PENDING' ? 'completed' : ''); ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Pending Police Review</strong>
                                <div class="text-muted small">Waiting for police officer review</div>
                            </div>
                            <div class="time"><?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <!-- Decision -->
                    <?php if ($case['status'] != 'PENDING'): ?>
                    <div class="status-step <?php echo $case['status'] == 'APPROVED' ? 'completed' : 'rejected'; ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong class="<?php echo $case['status'] == 'APPROVED' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $case['status'] == 'APPROVED' ? 'Approved' : 'Rejected'; ?>
                                </strong>
                                <div class="text-muted small">
                                    <?php if ($case['status'] == 'APPROVED' && $case['rb_number']): ?>
                                        RB Number: <?php echo htmlspecialchars($case['rb_number']); ?>
                                    <?php elseif ($case['status'] == 'REJECTED' && $case['police_notes']): ?>
                                        Reason: <?php echo substr(htmlspecialchars($case['police_notes']), 0, 100); ?>
                                        <?php if (strlen($case['police_notes']) > 100): ?>...<?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="time"><?php echo date('d/m/Y H:i', strtotime($case['updated_at'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Status Card -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="status-card <?php echo strtolower($case['status']); ?>">
            <?php if ($case['status'] == 'APPROVED'): ?>
                <span class="icon">✅</span>
                <div class="status-title">APPROVED</div>
                <div class="status-message">This case has been approved.</div>
                <?php if ($case['rb_number']): ?>
                    <div class="status-detail">
                        <strong>RB Number:</strong> <?php echo htmlspecialchars($case['rb_number']); ?>
                    </div>
                <?php endif; ?>
                <div class="status-detail">
                    <i class="fas fa-hospital me-1"></i> Patient should visit hospital for medical examination.
                </div>
                <div class="mt-2">
                    <a href="resend_sms.php?pf3=<?php echo $pf3; ?>" class="btn btn-sm btn-info resend-btn" 
                       onclick="return confirm('Resend SMS to patient?')">
                        <i class="fas fa-sms me-1"></i> Resend SMS
                    </a>
                </div>
            <?php elseif ($case['status'] == 'REJECTED'): ?>
                <span class="icon">❌</span>
                <div class="status-title">REJECTED</div>
                <div class="status-message">This case has been rejected.</div>
                <?php if ($case['police_notes']): ?>
                    <div class="status-detail">
                        <strong>Reason:</strong><br>
                        <?php echo nl2br(htmlspecialchars($case['police_notes'])); ?>
                    </div>
                <?php endif; ?>
                <div class="mt-2">
                    <a href="resend_sms.php?pf3=<?php echo $pf3; ?>" class="btn btn-sm btn-info resend-btn" 
                       onclick="return confirm('Resend SMS to patient?')">
                        <i class="fas fa-sms me-1"></i> Resend SMS
                    </a>
                </div>
            <?php else: ?>
                <span class="icon">⏳</span>
                <div class="status-title">PENDING</div>
                <div class="status-message">This case is waiting for police review.</div>
                <div class="status-detail">
                    <i class="fas fa-info-circle me-1"></i> Please review the case details and make a decision.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- SMS Message Preview (for approved/rejected) -->
        <?php if ($case['status'] == 'APPROVED'): ?>
        <div class="card detail-card" style="margin-top:1.2rem;">
            <div class="card-header">
                <i class="fas fa-sms me-2 text-success"></i>SMS Sent to Patient
            </div>
            <div class="card-body">
                <div class="sms-preview" style="border-left-color:#28a745;">
                    <div class="sms-label">📱 Message:</div>
                    <div class="sms-message">
                        <strong>APPROVED</strong> - <?php echo htmlspecialchars($case['full_name']); ?>, application #<?php echo htmlspecialchars($pf3); ?> APPROVED. 
                        RB: <?php echo htmlspecialchars($case['rb_number']); ?>. Visit hospital. Thank you!
                    </div>
                    <div class="sms-phone">📞 To: <?php echo htmlspecialchars($case['patient_phone']); ?></div>
                </div>
                <div class="mt-2 text-center">
                    <a href="resend_sms.php?pf3=<?php echo $pf3; ?>" class="btn btn-sm btn-info resend-btn" 
                       onclick="return confirm('Resend SMS to patient?')">
                        <i class="fas fa-sms me-1"></i> Resend SMS
                    </a>
                </div>
            </div>
        </div>
        <?php elseif ($case['status'] == 'REJECTED'): ?>
        <div class="card detail-card" style="margin-top:1.2rem;">
            <div class="card-header">
                <i class="fas fa-sms me-2 text-danger"></i>SMS Sent to Patient
            </div>
            <div class="card-body">
                <div class="sms-preview" style="border-left-color:#dc3545;">
                    <div class="sms-label">📱 Message:</div>
                    <div class="sms-message">
                        <strong>REJECTED</strong> - <?php echo htmlspecialchars($case['full_name']); ?>, application #<?php echo htmlspecialchars($pf3); ?> REJECTED. 
                        Reason: <?php echo htmlspecialchars(substr($case['police_notes'], 0, 30)) . (strlen($case['police_notes']) > 30 ? '...' : ''); ?>. 
                        Contact police. Thank you!
                    </div>
                    <div class="sms-phone">📞 To: <?php echo htmlspecialchars($case['patient_phone']); ?></div>
                </div>
                <div class="mt-2 text-center">
                    <a href="resend_sms.php?pf3=<?php echo $pf3; ?>" class="btn btn-sm btn-info resend-btn" 
                       onclick="return confirm('Resend SMS to patient?')">
                        <i class="fas fa-sms me-1"></i> Resend SMS
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== APPROVE FORM (Hidden - Submitted via JS) ===== -->
<form id="approveForm" action="process_case.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="approve">
    <input type="hidden" name="pf3_number" value="<?php echo htmlspecialchars($pf3); ?>">
    <input type="hidden" name="rb_number" id="hiddenRbNumber">
    <input type="hidden" name="police_notes" value="">
</form>

<!-- ===== REJECT FORM (Hidden - Submitted via JS) ===== -->
<form id="rejectForm" action="process_case.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="reject">
    <input type="hidden" name="pf3_number" value="<?php echo htmlspecialchars($pf3); ?>">
    <input type="hidden" name="police_notes" id="hiddenRejectNote">
</form>

<script>
// ===== GENERATE RB NUMBER (Client-side) =====
function generateRbNumber() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = 'RB-';
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

// ===== CLOSE ALERT =====
function closeAlert() {
    const overlay = document.getElementById('alertOverlay');
    const container = document.getElementById('alertContainer');
    if (overlay) overlay.remove();
    if (container) container.remove();
}

// ===== AUTO CLOSE ALERT AFTER 5 SECONDS =====
setTimeout(function() {
    closeAlert();
}, 5000);

// ===== CONFIRMATION POPUPS =====
function showApproveConfirm() {
    const overlay = document.getElementById('approveConfirm');
    const rbNumber = generateRbNumber();
    document.getElementById('displayRbNumber').textContent = rbNumber;
    document.getElementById('previewRbNumber').textContent = rbNumber;
    document.getElementById('hiddenRbNumber').value = rbNumber;
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function showRejectConfirm() {
    const overlay = document.getElementById('rejectConfirm');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    // Clear previous note
    document.getElementById('rejectNote').value = '';
    // Reset preview
    document.getElementById('previewRejectReason').textContent = '[Your reason will appear here]';
}

function closeConfirm(id) {
    const overlay = document.getElementById(id);
    overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// ===== LIVE PREVIEW FOR REJECT REASON =====
document.getElementById('rejectNote').addEventListener('input', function() {
    const reason = this.value.trim();
    const previewElement = document.getElementById('previewRejectReason');
    if (reason.length === 0) {
        previewElement.textContent = '[Your reason will appear here]';
    } else {
        const shortReason = reason.length > 30 ? reason.substring(0, 30) + '...' : reason;
        previewElement.textContent = shortReason;
    }
});

// ===== CONFIRM APPROVE =====
document.getElementById('confirmApproveBtn').addEventListener('click', function() {
    const rbNumber = document.getElementById('hiddenRbNumber').value;
    if (!rbNumber) {
        alert('Error: RB number not generated. Please try again.');
        return;
    }
    // Show loading state
    this.textContent = 'Processing...';
    this.disabled = true;
    document.getElementById('approveForm').submit();
});

// ===== CONFIRM REJECT =====
document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const note = document.getElementById('rejectNote').value.trim();
    
    if (!note) {
        alert('Please provide a reason for rejection.');
        document.getElementById('rejectNote').focus();
        return;
    }
    
    if (note.length < 5) {
        alert('Please provide a clear reason (at least 5 characters).');
        document.getElementById('rejectNote').focus();
        return;
    }
    
    // Show loading state
    this.textContent = 'Processing...';
    this.disabled = true;
    
    // Set the note in hidden field and submit
    document.getElementById('hiddenRejectNote').value = note;
    document.getElementById('rejectForm').submit();
});

// ===== CLOSE ON ESC KEY =====
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirm('approveConfirm');
        closeConfirm('rejectConfirm');
        closeAlert();
    }
});

// ===== CLOSE ON OVERLAY CLICK =====
document.querySelectorAll('.confirm-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ===== GENERATE RB NUMBER ON PAGE LOAD FOR APPROVE =====
document.addEventListener('DOMContentLoaded', function() {
    // Pre-generate RB number for display
    if (document.getElementById('displayRbNumber')) {
        const rb = generateRbNumber();
        document.getElementById('displayRbNumber').textContent = rb;
    }
});
</script>

<?php include 'footer.php'; ?>