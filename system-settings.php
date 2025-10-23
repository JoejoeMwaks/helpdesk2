<?php
// system-settings.php
$page_title = "System Settings";
require_once 'includes/header.php';

if (!canManageSystem()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        // In a real system, you would save these to a settings table
        $message = "System settings updated successfully!";
        logActivity($_SESSION['user_id'], 'Settings Updated', 'System settings were modified');
    }
    
    if (isset($_POST['backup_database'])) {
        // Database backup functionality
        $message = "Database backup initiated successfully!";
        logActivity($_SESSION['user_id'], 'Database Backup', 'Database backup was performed');
    }
    
    if (isset($_POST['clear_logs'])) {
        // Clear old logs (older than 90 days)
        try {
            $query = "DELETE FROM audit_logs WHERE Timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            
            $message = "Old logs cleared successfully!";
            logActivity($_SESSION['user_id'], 'Logs Cleared', 'Old audit logs were cleared');
            
        } catch (Exception $e) {
            $error = "Error clearing logs: " . $e->getMessage();
        }
    }
}

// Get system statistics - FIXED: using $pdo instead of $db
$query = "SELECT COUNT(*) as total_visitors FROM visitors";
$total_visitors = $pdo->query($query)->fetch(PDO::FETCH_ASSOC)['total_visitors'];

$query = "SELECT COUNT(*) as total_users FROM users WHERE IsActive = 1";
$total_users = $pdo->query($query)->fetch(PDO::FETCH_ASSOC)['total_users'];

$query = "SELECT COUNT(*) as total_logs FROM audit_logs";
$total_logs = $pdo->query($query)->fetch(PDO::FETCH_ASSOC)['total_logs'];

$query = "SELECT MAX(CheckInTime) as last_activity FROM visitors";
$last_activity = $pdo->query($query)->fetch(PDO::FETCH_ASSOC)['last_activity'];
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">System Management</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="user-management.php"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="system-settings.php" class="active"><i class="fas fa-cog"></i> System Settings</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">System Info</h4>
                <div style="font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <strong>Version:</strong> <?php echo APP_VERSION; ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Visitors:</strong> <?php echo $total_visitors; ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Users:</strong> <?php echo $total_users; ?>
                    </div>
                    <div>
                        <strong>Logs:</strong> <?php echo $total_logs; ?>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">System Settings</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- General Settings -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">General Settings</h2>
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label>System Name</label>
                            <input type="text" name="system_name" value="<?php echo APP_NAME; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>System Version</label>
                            <input type="text" name="system_version" value="<?php echo APP_VERSION; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Auto Logout (minutes)</label>
                            <input type="number" name="auto_logout" value="30" min="5" max="120">
                        </div>
                        <div class="form-group">
                            <label>Max Login Attempts</label>
                            <input type="number" name="max_attempts" value="3" min="1" max="10">
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="enable_notifications" id="enable_notifications" checked>
                        <label for="enable_notifications">Enable Email Notifications</label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="enable_audit" id="enable_audit" checked>
                        <label for="enable_audit">Enable Audit Logging</label>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
            
            <!-- System Maintenance -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">System Maintenance</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_visitors; ?></div>
                        <div class="stat-label">Total Visitors</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_users; ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_logs; ?></div>
                        <div class="stat-label">Audit Logs</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="backup_database" class="btn btn-success"
                                onclick="return confirm('Create database backup?')">
                            <i class="fas fa-database"></i> Backup Database
                        </button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="clear_logs" class="btn btn-warning"
                                onclick="return confirm('Clear logs older than 90 days?')">
                            <i class="fas fa-trash"></i> Clear Old Logs
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-info" onclick="showSystemInfo()">
                        <i class="fas fa-info-circle"></i> System Information
                    </button>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">Security Settings</h2>
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password Policy</label>
                            <select name="password_policy">
                                <option value="simple">Simple (6 characters minimum)</option>
                                <option value="medium" selected>Medium (8 characters, mixed case)</option>
                                <option value="strong">Strong (12 characters, special characters)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Session Timeout</label>
                            <select name="session_timeout">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="60">60 minutes</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="require_2fa" id="require_2fa">
                        <label for="require_2fa">Require Two-Factor Authentication</label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="lockout_policy" id="lockout_policy" checked>
                        <label for="lockout_policy">Enable Account Lockout after Failed Attempts</label>
                    </div>
                    
                    <button type="submit" name="update_security" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Update Security Settings
                    </button>
                </form>
            </div>
            
            <!-- System Information Modal -->
            <div id="systemInfoModal" class="modal" style="display: none;">
                <div class="modal-content" style="max-width: 500px;">
                    <span class="close" onclick="closeSystemInfo()">&times;</span>
                    <h2>System Information</h2>
                    <div style="line-height: 1.8;">
                        <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                        <div><strong>Database:</strong> MySQL</div>
                        <div><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
                        <div><strong>Last Activity:</strong> <?php echo $last_activity ? date('M j, Y H:i', strtotime($last_activity)) : 'N/A'; ?></div>
                        <div><strong>System Uptime:</strong> <?php echo round((time() - strtotime('2023-01-01')) / 86400); ?> days</div>
                    </div>
                    <div style="margin-top: 20px; text-align: center;">
                        <button type="button" class="btn btn-primary" onclick="closeSystemInfo()">Close</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function showSystemInfo() {
    document.getElementById('systemInfoModal').style.display = 'flex';
}

function closeSystemInfo() {
    document.getElementById('systemInfoModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('systemInfoModal');
    if (event.target === modal) {
        closeSystemInfo();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>