<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <img src="../assets/images/hospital.png" alt="Hospital Logo" class="me-2 header-logo">
                <span class="navbar-brand mb-0 h1">PF3 SYSTEM</span>
            </div>
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse d-lg-flex justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center flex-column flex-lg-row">
                    <select class="form-select language-switch mb-2 mb-lg-0 me-lg-3" id="language">
                        <option value="en">EN</option>
                        <option value="sw">SW</option>
                    </select>
                    <div class="navbar-nav flex-column flex-lg-row">
                        <a class="nav-link" href="../index.php" id="nav-home">Home</a>
                       
                        <a class="nav-link" href="../login.php" id="nav-login">Login</a>
                    </div>
                    <img src="../assets/images/police.png" alt="Police Logo" class="ms-3 header-logo d-none d-lg-block">
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php
        require_once '../includes/db.php';
        $pf3 = $_GET['pf3'] ?? '';
        $patient = getPatientByPF3($pf3);
        $case = getCaseByPF3($pf3);
        $medical = getMedicalReportByPF3($pf3);

        if (!$patient || !$case) {
            echo '<div class="alert alert-danger" id="invalid-message">Invalid PF3 Number.</div>';
        } else {
            $status = $case['status'];
            $badgeClass = $status === 'APPROVED' ? 'success' : ($status === 'REJECTED' ? 'danger' : 'warning');
        ?>
        <div class="card">
            <div class="card-header">
                <h4 id="status-header" data-pf3="<?php echo htmlspecialchars($pf3); ?>">Application Status for PF3: <?php echo htmlspecialchars($pf3); ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong id="label-status">Status:</strong> <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                </div>
                <div class="mb-3">
                    <strong id="label-patient-name">Patient Name:</strong> <?php echo $patient['full_name']; ?>
                </div>
                <div class="mb-3">
                    <strong id="label-incident-type">Incident Type:</strong> <?php echo $case['type_of_incident']; ?>
                </div>
                <div class="mb-3">
                    <strong id="label-description">Description:</strong> <?php echo $case['description']; ?>
                </div>
                <?php if ($case['police_notes']) { ?>
                <div class="mb-3">
                    <strong id="label-police-notes">Police Notes:</strong> <?php echo $case['police_notes']; ?>
                </div>
                <?php } ?>
                <?php if ($case['rb_number']) { ?>
                <div class="mb-3">
                    <strong id="label-rb-number">RB Number:</strong> <?php echo $case['rb_number']; ?>
                </div>
                <?php } ?>
                <?php if ($medical) { ?>
                <div class="mb-3">
                    <strong id="label-medical-report">Medical Report by Dr. <?php echo $medical['first_name'] . ' ' . $medical['last_name']; ?>:</strong>
                    <p id="medical-findings"><?php echo $medical['medical_findings']; ?></p>
                </div>
                <?php } ?>
                <?php if ($status === 'REJECTED') { ?>
                <a href="request_review.php?pf3=<?php echo $pf3; ?>" class="btn btn-warning" id="request-review-button">Request Review</a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const translations = {
            en: {
                home: 'Home',
                find: 'Find PF3',
                continue: 'Continue Application',
                create: 'Create PF3',
                track: 'Track Status',
                login: 'Login',
                invalidMessage: 'Invalid PF3 Number.',
                statusHeader: 'Application Status for PF3: {pf3}',
                labelStatus: 'Status:',
                labelPatientName: 'Patient Name:',
                labelIncidentType: 'Incident Type:',
                labelDescription: 'Description:',
                labelPoliceNotes: 'Police Notes:',
                labelRBNumber: 'RB Number:',
                labelMedicalReport: 'Medical Report by Dr.',
                requestReview: 'Request Review'
            },
            sw: {
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia',
                invalidMessage: 'Nambari ya PF3 si sahihi au maombi hayajakamilika.',
                statusHeader: 'Hali ya Maombi kwa PF3: {pf3}',
                labelStatus: 'Hali:',
                labelPatientName: 'Jina la Mgonjwa:',
                labelIncidentType: 'Aina ya Tukio:',
                labelDescription: 'Maelezo:',
                labelPoliceNotes: 'Maandishi ya Polisi:',
                labelRBNumber: 'Nambari ya RB:',
                labelMedicalReport: 'Ripoti ya Daktari:',
                requestReview: 'Omba Mapitio'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);

            const mappings = {
                'nav-home': 'home',
                'nav-find': 'find',
                'nav-continue': 'continue',
                'nav-create': 'create',
                'nav-track': 'track',
                'nav-login': 'login'
            };

            Object.entries(mappings).forEach(([id, key]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = translations[lang][key];
                }
            });

            const invalidMessage = document.getElementById('invalid-message');
            const statusHeader = document.getElementById('status-header');
            const labelStatus = document.getElementById('label-status');
            const labelPatientName = document.getElementById('label-patient-name');
            const labelIncidentType = document.getElementById('label-incident-type');
            const labelDescription = document.getElementById('label-description');
            const labelPoliceNotes = document.getElementById('label-police-notes');
            const labelRBNumber = document.getElementById('label-rb-number');
            const labelMedicalReport = document.getElementById('label-medical-report');
            const requestReview = document.getElementById('request-review-button');

            if (invalidMessage) invalidMessage.textContent = translations[lang].invalidMessage;
            if (statusHeader) {
                const pf3 = statusHeader.getAttribute('data-pf3') || '';
                statusHeader.textContent = translations[lang].statusHeader.replace('{pf3}', pf3);
            }
            if (labelStatus) labelStatus.textContent = translations[lang].labelStatus;
            if (labelPatientName) labelPatientName.textContent = translations[lang].labelPatientName;
            if (labelIncidentType) labelIncidentType.textContent = translations[lang].labelIncidentType;
            if (labelDescription) labelDescription.textContent = translations[lang].labelDescription;
            if (labelPoliceNotes) labelPoliceNotes.textContent = translations[lang].labelPoliceNotes;
            if (labelRBNumber) labelRBNumber.textContent = translations[lang].labelRBNumber;
            if (labelMedicalReport) labelMedicalReport.textContent = translations[lang].labelMedicalReport;
            if (requestReview) requestReview.textContent = translations[lang].requestReview;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const languageSelect = document.getElementById('language');
            if (languageSelect) {
                languageSelect.value = currentLang;
                languageSelect.addEventListener('change', function() {
                    updateLanguage(this.value);
                });
            }
            updateLanguage(currentLang);
        });
    </script>
</body>
</html>