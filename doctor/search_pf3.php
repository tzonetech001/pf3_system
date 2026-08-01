<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

include 'header.php';
?>

<style>
    .search-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }
    
    .search-header {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: 20px 20px 0 0;
    }
    
    .search-header i {
        color: rgba(255,255,255,0.8);
    }
    
    .search-input {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.15);
    }
    
    .search-btn {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }
    
    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        color: white;
    }
    
    .info-box {
        background: #e3f2fd;
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        border-left: 4px solid #0d47a1;
    }
    
    .info-box i {
        color: #0d47a1;
    }
    
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .feature-item {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .feature-item:hover {
        border-color: #0d47a1;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.1);
    }
    
    .feature-item i {
        font-size: 1.8rem;
        color: #0d47a1;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .feature-item h6 {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }
    
    .feature-item p {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card search-card">
            <div class="search-header text-center">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h3 class="mb-2 fw-bold">Search PF3 Number</h3>
                <p class="mb-0 opacity-75">Enter the PF3 number to view patient details and create medical report</p>
            </div>
            <div class="card-body p-4">
                <form action="view_patient.php" method="GET">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">PF3 Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-hashtag text-primary"></i>
                            </span>
                            <input type="text" class="form-control search-input" name="pf3" 
                                   placeholder="Example: PF3-296984" required>
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle text-primary"></i> 
                            Enter the PF3 number provided to the patient during registration
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn search-btn">
                            <i class="fas fa-search me-2"></i> Search Patient
                        </button>
                    </div>
                </form>
                
                <div class="info-box mt-4">
                    <div class="d-flex align-items-start gap-3">
                        <i class="fas fa-shield-alt fa-2x"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-primary">Secure Medical Reporting</h6>
                            <p class="text-muted small mb-0">
                                Only approved PF3 cases are accessible. Medical reports are confidential 
                                and will be shared only with authorized personnel.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="feature-grid">
                    <div class="feature-item">
                        <i class="fas fa-user-injured"></i>
                        <h6>Patient Details</h6>
                        <p>View complete patient information</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-file-medical"></i>
                        <h6>Medical Report</h6>
                        <p>Create or view medical reports</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-print"></i>
                        <h6>Print Report</h6>
                        <p>Print medical reports for records</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>