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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 2rem;
        border-radius: 20px 20px 0 0;
    }
    
    .search-input {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        border-color: #11998e;
        box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
    }
    
    .search-btn {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }
    
    .info-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card search-card">
            <div class="search-header text-center">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h3 class="mb-2">Search PF3 Number</h3>
                <p class="mb-0 opacity-75">Enter the PF3 number to view patient details and create medical report</p>
            </div>
            <div class="card-body p-4">
                <form action="view_patient.php" method="GET">
                    <div class="mb-4">
                        <label class="form-label fw-bold">PF3 Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-hashtag text-muted"></i>
                            </span>
                            <input type="text" class="form-control search-input" name="pf3" 
                                   placeholder="Example: PF3-2024-001" required>
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle"></i> Enter the PF3 number provided to the patient
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn search-btn text-white">
                            <i class="fas fa-search me-2"></i> Search Patient
                        </button>
                    </div>
                </form>
                
                <div class="info-box mt-4">
                    <div class="d-flex align-items-start gap-3">
                        <i class="fas fa-shield-alt fa-2x text-success"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Secure Medical Reporting</h6>
                            <p class="text-muted small mb-0">
                                Only approved PF3 cases are accessible. Medical reports are confidential 
                                and will be shared only with authorized personnel.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>