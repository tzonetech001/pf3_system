<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create PF3 - Step 2</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 id="step2-header">Step 2: Incident Details</h4>
                        <p class="mb-0">PF3 Number: <strong id="pf3-number"><?php session_start(); echo $_SESSION['pf3_number']; ?></strong>
                        <button class="btn btn-sm btn-outline-secondary ms-2" id="copy-pf3-button" onclick="copyPF3()">Copy</button></p>
                    </div>
                    <div class="card-body">
                        <form action="process_step2.php" method="POST">
                            <div class="mb-3">
                                <label for="type_of_incident" class="form-label" id="label-type-of-incident">Type of Incident/Injury</label>
                                <select class="form-select" id="type_of_incident" name="type_of_incident" required>
                                    <option id="option-select-type" value="">Select Type</option>
                                    <option id="option-assault" value="Assault">Assault</option>
                                    <option id="option-accident" value="Accident">Accident</option>
                                    <option id="option-rape" value="Rape">Rape</option>
                                    <option id="option-injury" value="Injury">Injury</option>
                                    <option id="option-violence" value="Violence">Violence</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label" id="label-description">Short Description of Incident</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="police_station" class="form-label" id="label-police-station">Police Station Name</label>
                                <input type="text" class="form-control" id="police_station" name="police_station" required>
                            </div>
                            <div class="mb-3">
                                <label for="guardian_name" class="form-label" id="label-guardian-name">Guardian Name</label>
                                <input type="text" class="form-control" id="guardian_name" name="guardian_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submit-button">Submit Application</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyPF3() {
            const pf3 = document.getElementById('pf3-number').textContent;
            navigator.clipboard.writeText(pf3).then(() => {
                alert('PF3 Number copied to clipboard!');
            });
        }

        const translations = {
            en: {
                home: 'Home',
                find: 'Find PF3',
                continue: 'Continue Application',
                create: 'Create PF3',
                track: 'Track Status',
                login: 'Login',
                step2Header: 'Step 2: Incident Details',
                copyButton: 'Copy',
                typeOfIncident: 'Type of Incident/Injury',
                selectType: 'Select Type',
                assault: 'Assault',
                accident: 'Accident',
                rape: 'Rape',
                injury: 'Injury',
                violence: 'Violence',
                description: 'Short Description of Incident',
                policeStation: 'Police Station Name',
                guardianName: 'Guardian Name',
                submitButton: 'Submit Application'
            },
            sw: {
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia',
                step2Header: 'Hatua ya 2: Maelezo ya Tukio',
                copyButton: 'Nakili',
                typeOfIncident: 'Aina ya Tukio/Ajeraha',
                selectType: 'Chagua Aina',
                assault: 'Shambulio',
                accident: 'Ajali',
                rape: 'Ubakaji',
                injury: 'Jeraha',
                violence: 'Ukatili',
                description: 'Maelezo Mafupi ya Tukio',
                policeStation: 'Jina la Kituo cha Polisi',
                guardianName: 'Jina la Mlezi',
                submitButton: 'Wasilisha Maombi'
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

            const header = document.getElementById('step2-header');
            const copyButton = document.getElementById('copy-pf3-button');
            const incidentLabel = document.getElementById('label-type-of-incident');
            const selectOption = document.getElementById('option-select-type');
            const assaultOption = document.getElementById('option-assault');
            const accidentOption = document.getElementById('option-accident');
            const rapeOption = document.getElementById('option-rape');
            const injuryOption = document.getElementById('option-injury');
            const violenceOption = document.getElementById('option-violence');
            const descriptionLabel = document.getElementById('label-description');
            const policeStationLabel = document.getElementById('label-police-station');
            const guardianLabel = document.getElementById('label-guardian-name');
            const submitButton = document.getElementById('submit-button');

            if (header) header.textContent = translations[lang].step2Header;
            if (copyButton) copyButton.textContent = translations[lang].copyButton;
            if (incidentLabel) incidentLabel.textContent = translations[lang].typeOfIncident;
            if (selectOption) selectOption.textContent = translations[lang].selectType;
            if (assaultOption) assaultOption.textContent = translations[lang].assault;
            if (accidentOption) accidentOption.textContent = translations[lang].accident;
            if (rapeOption) rapeOption.textContent = translations[lang].rape;
            if (injuryOption) injuryOption.textContent = translations[lang].injury;
            if (violenceOption) violenceOption.textContent = translations[lang].violence;
            if (descriptionLabel) descriptionLabel.textContent = translations[lang].description;
            if (policeStationLabel) policeStationLabel.textContent = translations[lang].policeStation;
            if (guardianLabel) guardianLabel.textContent = translations[lang].guardianName;
            if (submitButton) submitButton.textContent = translations[lang].submitButton;
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