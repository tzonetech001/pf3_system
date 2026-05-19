<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create PF3 - Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Step 1: Basic Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="process_step1.php" method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="guardian_phone" class="form-label">Guardian Phone Number</label>
                                <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone">
                            </div>
                            <div class="mb-3">
                                <label for="incident_date_time" class="form-label">Date and Time of Incident</label>
                                <input type="datetime-local" class="form-control" id="incident_date_time" name="incident_date_time" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save & Continue</button>
                        </form>
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
                step1: 'Step 1: Basic Information',
                fullName: 'Full Name',
                gender: 'Gender',
                age: 'Age',
                address: 'Address',
                phone: 'Phone Number',
                guardianPhone: 'Guardian Phone Number',
                incidentDate: 'Date and Time of Incident',
                saveContinue: 'Save & Continue',
                home: 'Home',
                find: 'Find PF3',
                continue: 'Continue Application',
                create: 'Create PF3',
                track: 'Track Status',
                login: 'Login'
            },
            sw: {
                step1: 'Hatua ya 1: Taarifa za Msingi',
                fullName: 'Jina Kamili',
                gender: 'Jinsia',
                age: 'Umri',
                address: 'Anwani',
                phone: 'Nambari ya Simu',
                guardianPhone: 'Nambari ya Simu ya Mlezi',
                incidentDate: 'Tarehe na Wakati wa Tukio',
                saveContinue: 'Hifadhi na Endelea',
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);
            document.querySelector('h4').textContent = translations[lang].step1;
            document.querySelector('label[for="full_name"]').textContent = translations[lang].fullName;
            document.querySelector('label[for="gender"]').textContent = translations[lang].gender;
            document.querySelector('label[for="age"]').textContent = translations[lang].age;
            document.querySelector('label[for="address"]').textContent = translations[lang].address;
            document.querySelector('label[for="phone"]').textContent = translations[lang].phone;
            document.querySelector('label[for="guardian_phone"]').textContent = translations[lang].guardianPhone;
            document.querySelector('label[for="incident_date_time"]').textContent = translations[lang].incidentDate;
            document.querySelector('button[type="submit"]').textContent = translations[lang].saveContinue;
            document.getElementById('nav-home').textContent = translations[lang].home;
            document.getElementById('nav-find').textContent = translations[lang].find;
            document.getElementById('nav-continue').textContent = translations[lang].continue;
            document.getElementById('nav-create').textContent = translations[lang].create;
            document.getElementById('nav-track').textContent = translations[lang].track;
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