<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted</title>
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
                        <a class="nav-link" href="find_pf3.php" id="nav-find">Find PF3</a>
                        <a class="nav-link" href="continue_application.php" id="nav-continue">Continue Application</a>
                        <a class="nav-link" href="create_pf3.php" id="nav-create">Create PF3</a>
                        <a class="nav-link" href="track_status.php" id="nav-track">Track Status</a>
                        <a class="nav-link" href="../login.php" id="nav-login">Login</a>
                    </div>
                    <img src="../assets/images/police.png" alt="Police Logo" class="ms-3 header-logo d-none d-lg-block">
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="card-title text-success" id="submitted-title">Application Submitted Successfully!</h4>
                        <p class="card-text" id="submitted-message">Your PF3 application has been submitted and is now pending review.</p>
                        <p id="submitted-use">Use your PF3 Number to track the status.</p>
                        <a href="../index.php" class="btn btn-primary" id="submitted-button">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
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
                submittedTitle: 'Application Submitted Successfully!',
                submittedMessage: 'Your PF3 application has been submitted and is now pending review.',
                submittedUse: 'Use your PF3 Number to track the status.',
                submittedButton: 'Back to Home'
            },
            sw: {
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia',
                submittedTitle: 'Maombi Yamewasilishwa kwa Mafanikio!',
                submittedMessage: 'Maombi yako ya PF3 yamewasilishwa na yako kwenye ukaguzi.',
                submittedUse: 'Tumia nambari yako ya PF3 kufuatilia hali.',
                submittedButton: 'Rudi Nyumbani'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);
            document.getElementById('nav-home').textContent = translations[lang].home;
            document.getElementById('nav-find').textContent = translations[lang].find;
            document.getElementById('nav-continue').textContent = translations[lang].continue;
            document.getElementById('nav-create').textContent = translations[lang].create;
            document.getElementById('nav-track').textContent = translations[lang].track;
            document.getElementById('nav-login').textContent = translations[lang].login;

            const title = document.getElementById('submitted-title');
            const message = document.getElementById('submitted-message');
            const useText = document.getElementById('submitted-use');
            const button = document.getElementById('submitted-button');

            if (title) title.textContent = translations[lang].submittedTitle;
            if (message) message.textContent = translations[lang].submittedMessage;
            if (useText) useText.textContent = translations[lang].submittedUse;
            if (button) button.textContent = translations[lang].submittedButton;
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