<?php
// dashboard.php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Get dashboard statistics
$stats = getDashboardStats();
$recent_visitors = getRecentVisitors(10);
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="visitor-management.php?filter=history"><i class="fas fa-history"></i> Visitor History</a></li>
                <?php if (canViewReports()): ?>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <?php endif; ?>
                <?php if (canManageUsers()): ?>
                <li><a href="user-management.php"><i class="fas fa-users"></i> User Management</a></li>
                <?php endif; ?>
                <?php if (canManageSystem()): ?>
                <li><a href="system-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <?php endif; ?>
                <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">System Status</h4>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: var(--success-green);"></div>
                    <span>All Systems Operational</span>
                </div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">
                    Last updated: <span id="currentTime"><?php echo date('M j, Y H:i'); ?></span>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Dashboard Overview</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['today_visitors']; ?></div>
                    <div class="stat-label">Visitors Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['active_visitors']; ?></div>
                    <div class="stat-label">Currently Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_badges']; ?></div>
                    <div class="stat-label">Pending Badge Returns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['week_visitors']; ?></div>
                    <div class="stat-label">This Week</div>
                </div>
            </div>
            
            <!-- Recent Visitors Table -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">Recent Visitors</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Organization</th>
                            <th>Purpose</th>
                            <th>Host Name</th>
                            <th>Check-in Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_visitors) > 0): ?>
                            <?php foreach ($recent_visitors as $visitor): ?>
                            <tr>
                                <td><?php echo sanitize($visitor['FullName']); ?></td>
                                <td><?php echo sanitize($visitor['Organization']); ?></td>
                                <td><?php echo strlen($visitor['PurposeOfVisit']) > 50 ? substr(sanitize($visitor['PurposeOfVisit']), 0, 50) . '...' : sanitize($visitor['PurposeOfVisit']); ?></td>
                                <td><?php echo sanitize($visitor['HostName']); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($visitor['CheckInTime'])); ?></td>
                                <td>
                                    <span style="color: <?php echo $visitor['Status'] == 'Checked In' ? 'var(--success-green)' : 'var(--error-red)'; ?>; font-weight: 600;">
                                        <?php echo $visitor['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($visitor['Status'] == 'Checked In'): ?>
                                        <a href="visitor-management.php?checkout=<?php echo $visitor['VisitorID']; ?>" class="btn btn-warning">Check Out</a>
                                    <?php elseif (!$visitor['BadgeReturned']): ?>
                                        <a href="visitor-management.php?return_badge=<?php echo $visitor['VisitorID']; ?>" class="btn btn-success">Mark Badge Returned</a>
                                    <?php endif; ?>
                                    <a href="generate-badge.php?id=<?php echo $visitor['VisitorID']; ?>" class="btn btn-info">View Badge</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No visitors found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Quick Actions -->
            <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="visitor-registration.php" class="btn btn-primary" style="text-align: center; padding: 15px;">
                    <i class="fas fa-user-plus" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                    Register Visitor
                </a>
                <a href="visitor-management.php" class="btn btn-success" style="text-align: center; padding: 15px;">
                    <i class="fas fa-list" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                    Manage Visitors
                </a>
                <?php if (canViewReports()): ?>
                <a href="reports.php" class="btn btn-info" style="text-align: center; padding: 15px;">
                    <i class="fas fa-chart-bar" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                    View Reports
                </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>