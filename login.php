<?php
// login.php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE (PFNumber = ? OR IDNumber = ?) AND IsActive = TRUE";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['user_name'] = $user['FullName'];
        $_SESSION['user_role'] = $user['Role'];
        $_SESSION['user_pf'] = $user['PFNumber'];
        
        logActivity($user['UserID'], 'Login', 'User logged into the system');
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-chart-bar"></i>
                <div class="logo-text">
                    <h1>KENYA NATIONAL BUREAU OF STATISTICS</h1>
                    <p>Visitor Registration Help Desk System</p>
                </div>
            </div>
        </div>

        <div class="login-form-container">
            <div class="login-card">
                <h2>System Login</h2>
                <p class="login-subtitle">Enter your credentials to access the system</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">PF Number / ID Number</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter your PF or ID number"
                               value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-group">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login to System
                    </button>
                </form>
                
                <div class="login-help">
                    <h3>Default Login Credentials</h3>
                    <div class="credentials">
                        <div class="credential-item">
                            <strong>System Administrator:</strong><br>
                            PF: ADMIN001 | Password: password
                        </div>
                        <div class="credential-item">
                            <strong>Main Front Desk Officer:</strong><br>
                            PF: REC001 | Password: password
                        </div>
                        <div class="credential-item">
                            <strong>Secondary Front Desk Officer:</strong><br>
                            PF: SDO001 | Password: password
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-footer">
            <p>&copy; 2023 Kenya National Bureau of Statistics. All Rights Reserved.</p>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>