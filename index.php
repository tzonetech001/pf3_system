<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Digital PF3 Coordination and Medical Reporting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <img src="assets/images/hospital.png" alt="Hospital Logo" class="me-2 header-logo">
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
                        <a class="nav-link" href="index.php" id="nav-home">Home</a>
                        <a class="nav-link" href="login.php" id="nav-login">Login</a>
                    </div>
                    <img src="assets/images/police.png" alt="Police Logo" class="ms-3 header-logo d-none d-lg-block">
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-primary text-white">
                        <h2 id="patient-portal" class="mb-0">Patient Portal</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="patient/find_pf3.php" class="btn btn-outline-primary btn-lg w-100 py-3" id="find-pf3">
                                    <i class="fas fa-search me-2"></i>Find PF3
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="patient/create_pf3.php" class="btn btn-primary btn-lg w-100 py-3" id="create-pf3">
                                    <i class="fas fa-plus-circle me-2"></i>Create PF3
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="patient/continue_application.php" class="btn btn-outline-secondary btn-lg w-100 py-3" id="continue-app">
                                    <i class="fas fa-arrow-right me-2"></i>Continue Application
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="patient/track_status.php" class="btn btn-secondary btn-lg w-100 py-3" id="track-status">
                                    <i class="fas fa-chart-line me-2"></i>Track Status
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Language translations
        const translations = {
            en: {
                patientPortal: 'Patient Portal',
                findPF3: 'Find PF3',
                createPF3: 'Create PF3',
                continueApp: 'Continue Application',
                trackStatus: 'Track Status',
                home: 'Home',
                login: 'Login'
            },
            sw: {
                patientPortal: 'Kituo cha Mgonjwa',
                findPF3: 'Tafuta PF3',
                createPF3: 'Tengeneza PF3',
                continueApp: 'Endelea Maombi',
                trackStatus: 'Fuatilia Hali',
                home: 'Nyumbani',
                login: 'Ingia'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);
            document.getElementById('patient-portal').textContent = translations[lang].patientPortal;
            document.getElementById('find-pf3').innerHTML = '<i class="fas fa-search me-2"></i>' + translations[lang].findPF3;
            document.getElementById('create-pf3').innerHTML = '<i class="fas fa-plus-circle me-2"></i>' + translations[lang].createPF3;
            document.getElementById('continue-app').innerHTML = '<i class="fas fa-arrow-right me-2"></i>' + translations[lang].continueApp;
            document.getElementById('track-status').innerHTML = '<i class="fas fa-chart-line me-2"></i>' + translations[lang].trackStatus;
            document.getElementById('nav-home').textContent = translations[lang].home;
            document.getElementById('nav-login').textContent = translations[lang].login;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('language').value = currentLang;
            updateLanguage(currentLang);
        });

        document.getElementById('language').addEventListener('change', function() {
            updateLanguage(this.value);
        });
    </script>
</body>
</html>