<?php
// includes/header.php
require_once __DIR__ . '/../config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        .logo-img {
            height: 50px;
            width: auto;
            max-width: 120px;
            object-fit: contain;
        }
        .logo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }
        .logo-text {
            flex: 1;
        }
        .logo-text h1 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .logo-text p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .logo {
                min-width: auto;
                justify-content: center;
            }
            .logo-text h1 {
                font-size: 1.1rem;
            }
            .logo-text p {
                font-size: 0.8rem;
            }
            .logo-img {
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="full-width-header">
        <div class="header-inner container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-placeholder">
                        <?php if (file_exists('images/logo.png')): ?>
                            <img src="images/logo.png" alt="KNBS Logo" class="logo-img">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; background: var(--accent-gold); display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                <i class="fas fa-chart-bar" style="color: var(--primary-brown); font-size: 1.5rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="logo-text">
                        <h1>KENYA NATIONAL BUREAU OF STATISTICS</h1>
                        <p>Visitor Registration Help Desk System</p>
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-avatar"><?php echo substr($_SESSION['user_name'], 0, 2); ?></div>
                    <div>
                        <div style="font-weight: 600;"><?php echo $_SESSION['user_name']; ?></div>
                        <div style="font-size: 0.8rem; opacity: 0.9;"><?php echo $_SESSION['user_role']; ?></div>
                    </div>
                    
                     <!-- 🌗 Theme Toggle Button -->
                    <button id="theme-toggle" class="theme-toggle" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                    </button>

                    <a href="logout.php" class="btn btn-warning" style="margin-left: 15px; text-decoration: none; white-space: nowrap;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav>
        <div class="container">
            <ul class="nav-menu">
                <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
                <li><a href="visitor-registration.php" <?php echo basename($_SERVER['PHP_SELF']) == 'visitor-registration.php' ? 'class="active"' : ''; ?>>Register Visitor</a></li>
                <li><a href="visitor-management.php" <?php echo basename($_SERVER['PHP_SELF']) == 'visitor-management.php' ? 'class="active"' : ''; ?>>Manage Visitors</a></li>
                <?php if (canViewReports()): ?>
                <li><a href="reports.php" <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'class="active"' : ''; ?>>Reports & Analytics</a></li>
                <?php endif; ?>
                <?php if (canManageUsers()): ?>
                <li><a href="user-management.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'class="active"' : ''; ?>>User Management</a></li>
                <?php endif; ?>
                <?php if (canManageSystem()): ?>
                <li><a href="system-settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'system-settings.php' ? 'class="active"' : ''; ?>>System Settings</a></li>
                <?php endif; ?>
                <li><a href="universal-feedback-qr.php"><i class="fas fa-qrcode"></i> Universal QR Code</a></li>
            </ul>
        </div>
    </nav>