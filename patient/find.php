<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find PF3 Result</title>
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
                    <div class="card-body">
                        <?php
                        require_once '../includes/db.php';
                        $phone = $_GET['phone'] ?? '';

                        $stmt = $pdo->prepare("SELECT pf3_number, full_name FROM patients WHERE phone = ?");
                        $stmt->execute([$phone]);
                        $result = $stmt->fetch();

                        if ($result) {
                            echo '<h4 class="card-title" id="find-result-title">PF3 Found</h4>';
                            echo '<p><strong id="label-name">Name:</strong> ' . $result['full_name'] . '</p>';
                            echo '<p><strong id="label-pf3-number">PF3 Number:</strong> ' . $result['pf3_number'] . '</p>';
                            echo '<a href="view_status.php?pf3=' . $result['pf3_number'] . '" class="btn btn-primary" id="view-status-button">View Status</a>';
                        } else {
                            echo '<div class="alert alert-warning" id="no-pf3-message">No PF3 found for this phone number.</div>';
                        }
                        ?>
                        <a href="find_pf3.php" class="btn btn-secondary" id="back-button">Back</a>
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
                pf3Found: 'PF3 Found',
                nameLabel: 'Name:',
                pf3NumberLabel: 'PF3 Number:',
                viewStatusButton: 'View Status',
                noPf3Message: 'No PF3 found for this phone number.',
                backButton: 'Back'
            },
            sw: {
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                login: 'Ingia',
                pf3Found: 'PF3 Imepatikana',
                nameLabel: 'Jina:',
                pf3NumberLabel: 'Nambari ya PF3:',
                viewStatusButton: 'Angalia Hali',
                noPf3Message: 'Hakuna PF3 iliyopatikana kwa nambari hii ya simu.',
                backButton: 'Rudi'
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

            const resultTitle = document.getElementById('find-result-title');
            const nameLabel = document.getElementById('label-name');
            const pf3Label = document.getElementById('label-pf3-number');
            const viewStatus = document.getElementById('view-status-button');
            const noMessage = document.getElementById('no-pf3-message');
            const backButton = document.getElementById('back-button');

            if (resultTitle) resultTitle.textContent = translations[lang].pf3Found;
            if (nameLabel) nameLabel.textContent = translations[lang].nameLabel;
            if (pf3Label) pf3Label.textContent = translations[lang].pf3NumberLabel;
            if (viewStatus) viewStatus.textContent = translations[lang].viewStatusButton;
            if (noMessage) noMessage.textContent = translations[lang].noPf3Message;
            if (backButton) backButton.textContent = translations[lang].backButton;
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