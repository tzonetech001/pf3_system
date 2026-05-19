<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PF3 System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 id="login-title">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        session_start();
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        <form action="includes/auth.php" method="POST" id="login-form">
                            <div class="mb-3">
                                <label for="email" class="form-label" id="email-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label" id="password-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="login-btn">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="#" id="forgot-password-link">Forgot Password?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgot-title">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="step-email" class="step">
                        <p>Step 1: Enter your email address</p>
                        <div class="mb-3">
                            <label for="forgot-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="forgot-email" required>
                        </div>
                        <button type="button" class="btn btn-primary w-100" id="check-email-btn">Check Email</button>
                    </div>
                    <div id="step-phone" class="step" style="display: none;">
                        <p>Step 2: Enter your phone number</p>
                        <div class="mb-3">
                            <label for="forgot-phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="forgot-phone" required>
                        </div>
                        <button type="button" class="btn btn-primary w-100" id="check-phone-btn">Continue to Phone Number</button>
                    </div>
                    <div id="step-password" class="step" style="display: none;">
                        <p>Step 3: Set new password</p>
                        <div class="mb-3">
                            <label for="new-password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-new-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-confirm-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100" id="reset-password-btn">Reset Password</button>
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
                login: 'Login',
                email: 'Email',
                password: 'Password',
                loginBtn: 'Login',
                forgot: 'Forgot Password?',
                forgotTitle: 'Forgot Password',
                checkEmail: 'Check Email',
                phone: 'Phone Number',
                newPass: 'New Password',
                confirmPass: 'Confirm Password',
                resetPass: 'Reset Password',
                home: 'Home',
                find: 'Find PF3',
                continue: 'Continue Application',
                create: 'Create PF3',
                track: 'Track Status',
                loginNav: 'Login'
            },
            sw: {
                login: 'Ingia',
                email: 'Barua pepe',
                password: 'Nenosiri',
                loginBtn: 'Ingia',
                forgot: 'Umesahau Nenosiri?',
                forgotTitle: 'Umesahau Nenosiri',
                checkEmail: 'Angalia Barua Pepe',
                phone: 'Nambari ya Simu',
                newPass: 'Nenosiri Mpya',
                confirmPass: 'Thibitisha Nenosiri',
                resetPass: 'Badilisha Nenosiri',
                home: 'Nyumbani',
                find: 'Tafuta PF3',
                continue: 'Endelea Maombi',
                create: 'Tengeneza PF3',
                track: 'Fuatilia Hali',
                loginNav: 'Ingia'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);
            document.getElementById('login-title').textContent = translations[lang].login;
            document.getElementById('email-label').textContent = translations[lang].email;
            document.getElementById('password-label').textContent = translations[lang].password;
            document.getElementById('login-btn').textContent = translations[lang].loginBtn;
            document.getElementById('forgot-password-link').textContent = translations[lang].forgot;
            document.getElementById('forgot-title').textContent = translations[lang].forgotTitle;
            document.getElementById('nav-home').textContent = translations[lang].home;
            document.getElementById('nav-find').textContent = translations[lang].find;
            document.getElementById('nav-continue').textContent = translations[lang].continue;
            document.getElementById('nav-create').textContent = translations[lang].create;
            document.getElementById('nav-track').textContent = translations[lang].track;
            document.getElementById('nav-login').textContent = translations[lang].loginNav;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('language').value = currentLang;
            updateLanguage(currentLang);
        });

        document.getElementById('language').addEventListener('change', function() {
            updateLanguage(this.value);
        });

        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Forgot password functionality
        document.getElementById('forgot-password-link').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('step-email').style.display = 'block';
            document.getElementById('step-phone').style.display = 'none';
            document.getElementById('step-password').style.display = 'none';
            new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
        });

        document.getElementById('check-email-btn').addEventListener('click', function() {
            const email = document.getElementById('forgot-email').value;
            fetch('includes/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'check_email', email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('step-email').style.display = 'none';
                    document.getElementById('step-phone').style.display = 'block';
                } else {
                    alert(data.message);
                }
            });
        });

        document.getElementById('check-phone-btn').addEventListener('click', function() {
            const email = document.getElementById('forgot-email').value;
            const phone = document.getElementById('forgot-phone').value;
            fetch('includes/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'check_phone', email: email, phone: phone })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('step-phone').style.display = 'none';
                    document.getElementById('step-password').style.display = 'block';
                } else {
                    alert(data.message);
                }
            });
        });

        document.getElementById('reset-password-btn').addEventListener('click', function() {
            const email = document.getElementById('forgot-email').value;
            const newPass = document.getElementById('new-password').value;
            const confirmPass = document.getElementById('confirm-password').value;
            if (newPass !== confirmPass) {
                alert('Passwords do not match');
                return;
            }
            fetch('includes/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'reset_password', email: email, password: newPass })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                }
            });
        });

        // Toggle new password
        document.getElementById('toggle-new-password').addEventListener('click', function() {
            const password = document.getElementById('new-password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Toggle confirm password
        document.getElementById('toggle-confirm-password').addEventListener('click', function() {
            const password = document.getElementById('confirm-password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>