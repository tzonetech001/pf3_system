<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Status</title>
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 id="track-header">Track Application Status</h4>
                    </div>
                    <div class="card-body">
                        <form action="view_status.php" method="GET">
                            <div class="mb-3">
                                <label for="pf3_number" class="form-label" id="track-label">Enter PF3 Number</label>
                                <input type="text" class="form-control" id="pf3_number" name="pf3" required placeholder="PF3-XXXXXX">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="track-button">Check Status</button>
                        </form>
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
                trackHeader: 'Track Application Status',
                trackLabel: 'Enter PF3 Number',
                trackButton: 'Check Status'
            },
            sw: {
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia',
                trackHeader: 'Fuatilia Hali ya Maombi',
                trackLabel: 'Weka Nambari ya PF3',
                trackButton: 'Angalia Hali'
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

            const header = document.getElementById('track-header');
            const label = document.getElementById('track-label');
            const button = document.getElementById('track-button');

            if (header) header.textContent = translations[lang].trackHeader;
            if (label) label.textContent = translations[lang].trackLabel;
            if (button) button.textContent = translations[lang].trackButton;
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