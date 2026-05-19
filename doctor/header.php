<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PF3 System - Doctor Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            height: 70px;
            padding: 0 1.5rem;
        }

        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            bottom: 0;
            width: 280px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            z-index: 1020;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            left: -280px;
        }

        .main-content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            min-height: calc(100vh - 70px);
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
            flex: 1;
        }

        .menu-item {
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-item:hover {
            background: #f7fafc;
            color: #11998e;
        }

        .menu-item.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
        }

        .menu-item span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 1rem 0;
            border-top: 1px solid #e2e8f0;
            margin-top: auto;
        }

        .sidebar-user {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
        }

        .sidebar-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .sidebar-user-name {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .sidebar-user-role {
            font-size: 0.8rem;
            color: #11998e;
            font-weight: 500;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            min-width: 280px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1050;
        }

        .dropdown-menu-custom.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            padding-left: 1.25rem;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1015;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }

        .toggle-btn {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #4a5568;
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            color: #11998e;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img {
            height: 40px;
            width: auto;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.mobile-open {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .top-navbar {
                padding: 0 1rem;
            }
            .logo-text {
                font-size: 1rem;
            }
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 5px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .menu-item {
            animation: slideIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <div class="d-flex justify-content-between align-items-center h-100">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo-section">
                    <img src="../assets/images/hospital.png" alt="Hospital Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/40'">
                    <span class="logo-text">PF3 SYSTEM</span>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <div class="avatar" id="avatarBtn">
                        <?php 
                        $name = $_SESSION['user_name'] ?? 'Medical Doctor';
                        $initials = '';
                        $nameParts = explode(' ', $name);
                        if (count($nameParts) >= 2) {
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                        } else {
                            $initials = strtoupper(substr($name, 0, 2));
                        }
                        echo $initials;
                        ?>
                    </div>
                    
                    <div class="dropdown-menu-custom" id="dropdownMenu">
                        <div class="p-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <?php echo $initials; ?>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($name); ?></h6>
                                    <small class="text-muted">Medical Doctor</small>
                                </div>
                            </div>
                        </div>
                        <div class="py-2">
                            <a href="profile.php" class="dropdown-item d-flex align-items-center gap-3 px-4 py-2">
                                <i class="fas fa-user-circle fa-lg"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="profile.php#password" class="dropdown-item d-flex align-items-center gap-3 px-4 py-2">
                                <i class="fas fa-key fa-lg"></i>
                                <span>Change Password</span>
                            </a>
                            <a href="profile.php#activity" class="dropdown-item d-flex align-items-center gap-3 px-4 py-2">
                                <i class="fas fa-history fa-lg"></i>
                                <span>Activity Log</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item d-flex align-items-center gap-3 px-4 py-2 text-danger">
                                <i class="fas fa-sign-out-alt fa-lg"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?php echo $initials; ?>
            </div>
            <div class="sidebar-user-name"><?php echo htmlspecialchars($name); ?></div>
            <div class="sidebar-user-role">
                <i class="fas fa-user-md me-1"></i> Medical Doctor
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="search_pf3.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'search_pf3.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i>
                <span>Search PF3</span>
            </a>
            <a href="my_reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'my_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>My Reports</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <a href="profile.php" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="profile.php#password" class="menu-item">
                <i class="fas fa-key"></i>
                <span>Change Password</span>
            </a>
            <a href="profile.php#activity" class="menu-item">
                <i class="fas fa-history"></i>
                <span>Activity Log</span>
            </a>
            <a href="../logout.php" class="menu-item" style="color: #A6EDCF ;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content" id="mainContent">