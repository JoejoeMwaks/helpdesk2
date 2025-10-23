<?php
// reports.php
$page_title = "Reports & Analytics";
require_once 'includes/header.php';

if (!canViewReports()) {
    header("Location: dashboard.php");
    exit();
}

// Set default date range (current month)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get visitors data
$query = "SELECT * FROM visitors WHERE DATE(CheckInTime) BETWEEN ? AND ? ORDER BY CheckInTime DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$start_date, $end_date]);
$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get summary statistics
$stats_query = "SELECT 
    COUNT(*) as total_visitors,
    COUNT(CASE WHEN Status = 'Checked In' THEN 1 END) as active_visitors,
    COUNT(CASE WHEN Status = 'Checked Out' THEN 1 END) as checked_out_visitors,
    COUNT(CASE WHEN PWDStatus = 1 THEN 1 END) as pwd_visitors,
    COUNT(CASE WHEN Gender = 'Male' THEN 1 END) as male_visitors,
    COUNT(CASE WHEN Gender = 'Female' THEN 1 END) as female_visitors,
    COUNT(CASE WHEN HasLuggage = 1 THEN 1 END) as luggage_visitors,
    COUNT(CASE WHEN HostAvailable = 0 THEN 1 END) as host_unavailable_visitors
    FROM visitors 
    WHERE DATE(CheckInTime) BETWEEN ? AND ?";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$start_date, $end_date]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get daily trends
$daily_query = "SELECT 
    DATE(CheckInTime) as visit_date,
    COUNT(*) as visitor_count
    FROM visitors 
    WHERE DATE(CheckInTime) BETWEEN ? AND ?
    GROUP BY DATE(CheckInTime)
    ORDER BY visit_date";
$daily_stmt = $pdo->prepare($daily_query);
$daily_stmt->execute([$start_date, $end_date]);
$daily_trends = $daily_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get organization breakdown
$org_query = "SELECT 
    Organization,
    COUNT(*) as visit_count
    FROM visitors 
    WHERE DATE(CheckInTime) BETWEEN ? AND ? AND Organization IS NOT NULL AND Organization != ''
    GROUP BY Organization
    ORDER BY visit_count DESC
    LIMIT 10";
$org_stmt = $pdo->prepare($org_query);
$org_stmt->execute([$start_date, $end_date]);
$organizations = $org_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get peak hours
$peak_query = "SELECT 
    HOUR(CheckInTime) as hour,
    COUNT(*) as visit_count
    FROM visitors 
    WHERE DATE(CheckInTime) BETWEEN ? AND ?
    GROUP BY HOUR(CheckInTime)
    ORDER BY hour";
$peak_stmt = $pdo->prepare($peak_query);
$peak_stmt->execute([$start_date, $end_date]);
$peak_hours = $peak_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top admitting officers
$officer_query = "SELECT 
    AdmittingOfficer,
    COUNT(*) as visit_count
    FROM visitors 
    WHERE DATE(CheckInTime) BETWEEN ? AND ?
    GROUP BY AdmittingOfficer
    ORDER BY visit_count DESC
    LIMIT 10";
$officer_stmt = $pdo->prepare($officer_query);
$officer_stmt->execute([$start_date, $end_date]);
$top_officers = $officer_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <?php if (canManageUsers()): ?>
                <li><a href="user-management.php"><i class="fas fa-users"></i> User Management</a></li>
                <?php endif; ?>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">Report Summary</h4>
                <div style="font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <strong>Period:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Total Visitors:</strong> <?php echo $stats['total_visitors']; ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Active:</strong> <?php echo $stats['active_visitors']; ?>
                    </div>
                    <div>
                        <strong>Checked Out:</strong> <?php echo $stats['checked_out_visitors']; ?>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Reports & Analytics</h1>
            
            <!-- User Info and Report Header -->
            <div style="background: var(--bg-card); padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--primary-brown);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <strong>Report Generated By:</strong> 
                        <span style="color: var(--primary-brown); font-weight: 600;"><?php echo $_SESSION['user_name']; ?></span> 
                        (<?php echo $_SESSION['user_role']; ?>)
                    </div>
                    <div>
                        <strong>Generated On:</strong> <?php echo date('F j, Y \a\t g:i A'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Report Period Selector -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">Report Period</h2>
                <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 15px; align-items: end;">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                    <div>
                        <a href="export-report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                           class="btn btn-success" target="_blank">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Summary Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_visitors']; ?></div>
                    <div class="stat-label">Total Visitors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['active_visitors']; ?></div>
                    <div class="stat-label">Currently Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['checked_out_visitors']; ?></div>
                    <div class="stat-label">Checked Out</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pwd_visitors']; ?></div>
                    <div class="stat-label">PWD Visitors</div>
                </div>
            </div>
            
            <!-- Additional Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['male_visitors']; ?></div>
                    <div class="stat-label">Male Visitors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['female_visitors']; ?></div>
                    <div class="stat-label">Female Visitors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['luggage_visitors']; ?></div>
                    <div class="stat-label">With Luggage</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['host_unavailable_visitors']; ?></div>
                    <div class="stat-label">Host Unavailable</div>
                </div>
            </div>
            
            <!-- Detailed Reports -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">Visitor Details</h2>
                <div style="margin-bottom: 15px; display: flex; justify-content: between; align-items: center;">
                    <span>Showing <?php echo count($visitors); ?> visitors</span>
                    <div>
                        <button type="button" class="btn btn-info btn-sm" onclick="toggleTableVisibility()">
                            <i class="fas fa-eye"></i> Toggle Table
                        </button>
                    </div>
                </div>
                <div id="visitorTable" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Badge No.</th>
                                <th>Visitor Name</th>
                                <th>Organization</th>
                                <th>Purpose</th>
                                <th>Host Name</th>
                                <th>Host Available</th>
                                <th>Check-in Time</th>
                                <th>Check-out Time</th>
                                <th>Duration</th>
                                <th>Admitted By</th>
                                <th>Checked Out By</th>
                                <th>Status</th>
                                <th>Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($visitors) > 0): 
                                foreach ($visitors as $visitor): 
                                    $duration = 'N/A';
                                    if ($visitor['CheckOutTime']) {
                                        $checkin = new DateTime($visitor['CheckInTime']);
                                        $checkout = new DateTime($visitor['CheckOutTime']);
                                        $interval = $checkin->diff($checkout);
                                        $duration = $interval->format('%hh %im');
                                    }
                            ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($visitor['CheckInTime'])); ?></td>
                                <td>
                                    <strong><?php echo $visitor['BadgeNumber']; ?></strong>
                                    <?php if ($visitor['HasLuggage']): ?>
                                    <br><small style="color: var(--warning-yellow);"><i class="fas fa-suitcase"></i></small>
                                    <?php endif; ?>
                                    <?php if ($visitor['PWDStatus']): ?>
                                    <br><small style="color: var(--info-blue);"><i class="fas fa-wheelchair"></i></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitize($visitor['FullName']); ?></td>
                                <td><?php echo sanitize($visitor['Organization']); ?></td>
                                <td><?php echo strlen($visitor['PurposeOfVisit']) > 30 ? substr(sanitize($visitor['PurposeOfVisit']), 0, 30) . '...' : sanitize($visitor['PurposeOfVisit']); ?></td>
                                <td><?php echo sanitize($visitor['HostName']); ?></td>
                                <td>
                                    <?php if ($visitor['HostAvailable']): ?>
                                        <span class="status-badge status-success">Yes</span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">No</span>
                                        <?php if (!empty($visitor['VisitorMessage'])): ?>
                                        <br><small title="<?php echo sanitize($visitor['VisitorMessage']); ?>">Has Message</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('H:i', strtotime($visitor['CheckInTime'])); ?></td>
                                <td><?php echo $visitor['CheckOutTime'] ? date('H:i', strtotime($visitor['CheckOutTime'])) : 'N/A'; ?></td>
                                <td><?php echo $duration; ?></td>
                                <td>
                                    <span style="color: var(--primary-brown); font-weight: 600;">
                                        <?php echo sanitize($visitor['AdmittingOfficer']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($visitor['CheckOutOfficer']): ?>
                                        <span style="color: var(--info-blue); font-weight: 600;">
                                            <?php echo sanitize($visitor['CheckOutOfficer']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="color: <?php echo $visitor['Status'] == 'Checked In' ? 'var(--success-green)' : 'var(--info-blue)'; ?>; font-weight: 600;">
                                        <?php echo $visitor['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($visitor['Feedback'])): ?>
                                        <span title="<?php echo sanitize($visitor['Feedback']); ?>">
                                            <i class="fas fa-comment" style="color: var(--info-blue);"></i>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">No feedback</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="14" class="text-center">No visitors found for the selected period</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Analytics Sections -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
                <!-- Organizations -->
                <div class="section">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Top Organizations</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Visits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($organizations) > 0): ?>
                                <?php foreach ($organizations as $org): ?>
                                <tr>
                                    <td><?php echo sanitize($org['Organization']); ?></td>
                                    <td><?php echo $org['visit_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">No organization data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Top Officers -->
                <div class="section">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Top Admitting Officers</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Visits Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($top_officers) > 0): ?>
                                <?php foreach ($top_officers as $officer): ?>
                                <tr>
                                    <td><?php echo sanitize($officer['AdmittingOfficer']); ?></td>
                                    <td><?php echo $officer['visit_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">No officer data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- More Analytics Sections -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Peak Hours -->
                <div class="section">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Peak Visiting Hours</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Hour</th>
                                <th>Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($peak_hours) > 0): ?>
                                <?php foreach ($peak_hours as $hour): ?>
                                <tr>
                                    <td><?php echo date('g A', strtotime($hour['hour'] . ':00:00')); ?></td>
                                    <td><?php echo $hour['visit_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">No peak hour data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Daily Trends -->
                <div class="section">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Daily Visitor Trends</h3>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitor Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($daily_trends) > 0): ?>
                                    <?php foreach ($daily_trends as $trend): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($trend['visit_date'])); ?></td>
                                        <td><?php echo $trend['visitor_count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No daily trend data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-success {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.8rem;
}

@media print {
    .sidebar, .nav-menu, .btn, .stats-container {
        display: none !important;
    }
    
    .main-content {
        grid-template-columns: 1fr !important;
        margin-left: 0 !important;
    }
    
    .content {
        width: 100% !important;
    }
    
    table {
        font-size: 10px !important;
    }
}
</style>

<script>
function toggleTableVisibility() {
    const table = document.getElementById('visitorTable');
    if (table.style.display === 'none') {
        table.style.display = 'block';
    } else {
        table.style.display = 'none';
    }
}

// Print report function
function printReport() {
    window.print();
}
</script>

<?php require_once 'includes/footer.php'; ?>