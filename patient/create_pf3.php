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
                        <h4 id="step1-header">Step 1: Basic Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>
                        
                        <form action="process_step1.php" method="POST" onsubmit="return validatePhoneNumbers()">
                            <div class="mb-3">
                                <label for="full_name" class="form-label" id="label-full-name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label" id="label-gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label" id="label-age">Age <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label" id="label-address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label" id="label-phone">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">255</span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="7XXXXXXXX or 6XXXXXXXX" required
                                           minlength="9" maxlength="9"
                                           pattern="[67][0-9]{8}">
                                </div>
                                <div class="form-text" id="phone-hint"></div>
                                <div id="phone-error" class="text-danger" style="display: none;">Please enter a valid phone number (9 digits starting with 6 or 7)</div>
                            </div>
                            <div class="mb-3">
                                <label for="guardian_phone" class="form-label" id="label-guardian-phone">Guardian Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">255</span>
                                    <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone" 
                                           placeholder="7XXXXXXXX or 6XXXXXXXX" 
                                           minlength="9" maxlength="9"
                                           pattern="[67][0-9]{8}">
                                </div>
                                <div class="form-text" id="guardian-phone-hint"></div>
                                <div id="guardian-phone-error" class="text-danger" style="display: none;">Please enter a valid phone number (9 digits starting with 6 or 7)</div>
                            </div>
                            <div class="mb-3">
                                <label for="incident_date_time" class="form-label" id="label-incident-date">Date and Time of Incident <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="incident_date_time" name="incident_date_time" required>
                                <div class="form-text" id="incident-date-hint">Current date and time is set by default. You cannot change it.</div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btn-save-continue">Save & Continue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set current date and time in Tanzania timezone
        function setCurrentDateTime() {
            const now = new Date();
            // Tanzania is UTC+3 (East Africa Time)
            // Adjust to Tanzania time
            const tzOffset = 3 * 60; // Tanzania is UTC+3
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const tzTime = new Date(utc + (tzOffset * 60000));
            
            // Format as YYYY-MM-DDTHH:mm
            const year = tzTime.getFullYear();
            const month = String(tzTime.getMonth() + 1).padStart(2, '0');
            const day = String(tzTime.getDate()).padStart(2, '0');
            const hours = String(tzTime.getHours()).padStart(2, '0');
            const minutes = String(tzTime.getMinutes()).padStart(2, '0');
            
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Phone validation functions
        function validatePhoneInput(input, errorElement, isRequired = true) {
            const value = input.value.trim();
            
            if (!isRequired && value === '') {
                errorElement.style.display = 'none';
                input.classList.remove('is-invalid');
                return true;
            }
            
            // Check if it's 9 digits and starts with 6 or 7
            const phoneRegex = /^[67][0-9]{8}$/;
            
            if (phoneRegex.test(value) && value.length === 9) {
                errorElement.style.display = 'none';
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return true;
            } else {
                errorElement.style.display = 'block';
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                return false;
            }
        }

        function validatePhoneNumbers() {
            const phoneInput = document.getElementById('phone');
            const guardianInput = document.getElementById('guardian_phone');
            const phoneError = document.getElementById('phone-error');
            const guardianError = document.getElementById('guardian-phone-error');
            
            let isValid = true;
            
            // Validate main phone (required)
            if (!validatePhoneInput(phoneInput, phoneError, true)) {
                isValid = false;
            }
            
            // Validate guardian phone (optional)
            if (guardianInput.value.trim() !== '') {
                if (!validatePhoneInput(guardianInput, guardianError, false)) {
                    isValid = false;
                }
            } else {
                guardianError.style.display = 'none';
                guardianInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                alert('Please fix the phone number errors before continuing.');
                return false;
            }
            
            return true;
        }

        // Set default date on page load
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('incident_date_time');
            if (dateInput) {
                dateInput.value = setCurrentDateTime();
                dateInput.readOnly = true;
                // Make it look like a disabled/readonly field
                dateInput.style.backgroundColor = '#e9ecef';
                dateInput.style.cursor = 'not-allowed';
            }
            
            // Add input event listeners for phone validation
            const phoneInput = document.getElementById('phone');
            const guardianInput = document.getElementById('guardian_phone');
            const phoneError = document.getElementById('phone-error');
            const guardianError = document.getElementById('guardian-phone-error');
            
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    validatePhoneInput(this, phoneError, true);
                });
                phoneInput.addEventListener('blur', function() {
                    validatePhoneInput(this, phoneError, true);
                });
            }
            
            if (guardianInput) {
                guardianInput.addEventListener('input', function() {
                    validatePhoneInput(this, guardianError, false);
                });
                guardianInput.addEventListener('blur', function() {
                    validatePhoneInput(this, guardianError, false);
                });
            }
            
            // Prevent pasting invalid characters in phone fields
            document.querySelectorAll('input[type="tel"]').forEach(function(input) {
                input.addEventListener('keypress', function(e) {
                    const char = String.fromCharCode(e.keyCode || e.which);
                    if (!/[0-9]/.test(char) && e.key !== 'Backspace' && e.key !== 'Delete') {
                        e.preventDefault();
                    }
                });
            });
        });

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
                phoneError: 'Please enter a valid phone number (9 digits starting with 6 or 7)',
                home: 'Home',
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
                phoneError: 'Tafadhali weka nambari sahihi ya simu (tarakimu 9 zinazoanza na 6 au 7)',
                home: 'Nyumbani',
                login: 'Ingia'
            }
        };

        let currentLang = localStorage.getItem('pf3_lang') || 'en';

        function updateLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('pf3_lang', lang);
            
            const translations_lang = translations[lang];
            
            document.querySelector('h4').textContent = translations_lang.step1;
            document.getElementById('label-full-name').textContent = translations_lang.fullName + ' *';
            document.getElementById('label-gender').textContent = translations_lang.gender + ' *';
            document.getElementById('label-age').textContent = translations_lang.age + ' *';
            document.getElementById('label-address').textContent = translations_lang.address + ' *';
            document.getElementById('label-phone').textContent = translations_lang.phone + ' *';
            document.getElementById('label-guardian-phone').textContent = translations_lang.guardianPhone;
            document.getElementById('label-incident-date').textContent = translations_lang.incidentDate + ' *';
            document.getElementById('incident-date-hint').textContent = translations_lang.incidentDateHint;
            document.getElementById('btn-save-continue').textContent = translations_lang.saveContinue;
            document.getElementById('phone-hint').textContent = translations_lang.phoneHint;
            document.getElementById('guardian-phone-hint').textContent = translations_lang.guardianPhoneHint;
            document.getElementById('phone-error').textContent = translations_lang.phoneError;
            document.getElementById('nav-home').textContent = translations_lang.home;
            document.getElementById('nav-login').textContent = translations_lang.login;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const langSelect = document.getElementById('language');
            if (langSelect) {
                langSelect.value = currentLang;
                langSelect.addEventListener('change', function() {
                    updateLanguage(this.value);
                });
            }
            updateLanguage(currentLang);
        });
    </script>
</body>
</html>