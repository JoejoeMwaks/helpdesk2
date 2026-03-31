<?php
// config.php (IN ROOT DIRECTORY)
session_start();

// Database configuration
$db_host = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$db_port = getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: '3306');
$db_name = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'knbs_visitor_system');
$db_user = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
$db_pass = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');

define('DB_HOST', $db_host);
define('DB_PORT', $db_port);
define('DB_NAME', $db_name);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Auto-detect site URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('SITE_URL', $protocol . "://" . $host . $path);

// Application settings
define('APP_NAME', 'KNBS Visitor Registration System');
define('APP_VERSION', '1.0');

// Available roles
define('ROLES', [
    'System Administrator',
    'Main Front Desk Officer', 
    'Secondary Front Desk Officer'
]);

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Check user role
function hasRole($allowed_roles) {
    if (!isLoggedIn()) return false;
    if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];
    return in_array($_SESSION['user_role'], $allowed_roles);
}

// Check permission for specific actions
function canManageUsers() {
    return hasRole(['System Administrator']);
}

function canViewReports() {
    return hasRole(['System Administrator', 'Main Front Desk Officer']);
}

function canRegisterVisitors() {
    return hasRole(['System Administrator', 'Main Front Desk Officer', 'Secondary Front Desk Officer']);
}

function canManageSystem() {
    return hasRole(['System Administrator']);
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Log activity
function logActivity($user_id, $action, $description = '') {
    global $pdo;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $query = "INSERT INTO audit_logs (UserID, Action, Description, IPAddress) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $action, $description, $ip_address]);
    
    return $stmt;
}

// Generate badge number
function generateBadgeNumber() {
    $prefix = "KNBS";
    $date = date('Ymd');
    $random = rand(100, 999);
    return $prefix . $date . $random;
}

// Get dashboard statistics
function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    // Today's visitors
    $query = "SELECT COUNT(*) as count FROM visitors WHERE DATE(CheckInTime) = CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['today_visitors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Active visitors
    $query = "SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked In'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['active_visitors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Pending badges
    $query = "SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked Out' AND BadgeReturned = FALSE";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['pending_badges'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // This week's visitors
    $query = "SELECT COUNT(*) as count FROM visitors WHERE YEARWEEK(CheckInTime) = YEARWEEK(CURDATE())";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['week_visitors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return $stats;
}

// Get recent visitors
function getRecentVisitors($limit = 10) {
    global $pdo;
    
    $query = "SELECT v.* FROM visitors v ORDER BY v.CheckInTime DESC LIMIT ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all users
function getAllUsers() {
    global $pdo;
    
    $query = "SELECT * FROM users ORDER BY FullName";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user by ID
function getUserById($user_id) {
    global $pdo;
    
    $query = "SELECT * FROM users WHERE UserID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get visitor by ID
function getVisitorById($visitor_id) {
    global $pdo;
    
    $query = "SELECT v.* FROM visitors v WHERE v.VisitorID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$visitor_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get visitors with filters
function getVisitors($filters = []) {
    global $pdo;
    
    $query = "SELECT v.* FROM visitors v WHERE 1=1";
    $params = [];
    
    if (isset($filters['status']) && $filters['status']) {
        $query .= " AND v.Status = ?";
        $params[] = $filters['status'];
    }
    
    if (isset($filters['search']) && $filters['search']) {
        $query .= " AND (v.FullName LIKE ? OR v.IDNumber LIKE ? OR v.Organization LIKE ?)";
        $search_term = "%{$filters['search']}%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }
    
    if (isset($filters['date_from']) && $filters['date_from']) {
        $query .= " AND DATE(v.CheckInTime) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (isset($filters['date_to']) && $filters['date_to']) {
        $query .= " AND DATE(v.CheckInTime) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $query .= " ORDER BY v.CheckInTime DESC";
    
    if (isset($filters['limit']) && $filters['limit']) {
        $query .= " LIMIT ?";
        $params[] = $filters['limit'];
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get report data
function getReportData($start_date, $end_date) {
    global $pdo;
    
    $data = [];
    
    // Basic statistics
    $query = "SELECT 
        COUNT(*) as total_visitors,
        COUNT(CASE WHEN Status = 'Checked In' THEN 1 END) as active_visitors,
        COUNT(CASE WHEN PWDStatus = 1 THEN 1 END) as pwd_visitors,
        COUNT(CASE WHEN Gender = 'Male' THEN 1 END) as male_visitors,
        COUNT(CASE WHEN Gender = 'Female' THEN 1 END) as female_visitors,
        COUNT(CASE WHEN HasLuggage = 1 THEN 1 END) as luggage_visitors
        FROM visitors 
        WHERE DATE(CheckInTime) BETWEEN ? AND ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $data['stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Daily trends
    $query = "SELECT 
        DATE(CheckInTime) as visit_date,
        COUNT(*) as visitor_count
        FROM visitors 
        WHERE DATE(CheckInTime) BETWEEN ? AND ?
        GROUP BY DATE(CheckInTime)
        ORDER BY visit_date";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $data['daily_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organization breakdown
    $query = "SELECT 
        Organization,
        COUNT(*) as visit_count
        FROM visitors 
        WHERE DATE(CheckInTime) BETWEEN ? AND ? AND Organization IS NOT NULL
        GROUP BY Organization
        ORDER BY visit_count DESC
        LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $data['organizations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Peak hours
    $query = "SELECT 
        HOUR(CheckInTime) as hour,
        COUNT(*) as visit_count
        FROM visitors 
        WHERE DATE(CheckInTime) BETWEEN ? AND ?
        GROUP BY HOUR(CheckInTime)
        ORDER BY hour";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $data['peak_hours'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $data;
}
?>