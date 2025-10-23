<?php
// visitor-management.php
$page_title = "Manage Visitors";
require_once 'includes/header.php';

if (!canRegisterVisitors()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle checkout with optional QR code and message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout_visitor'])) {
    $visitor_id = $_POST['visitor_id'];
    $feedback = sanitize($_POST['feedback'] ?? '');
    $generate_qr = isset($_POST['generate_qr']) ? 1 : 0;
    
    try {
        // Generate feedback token only if QR code is requested
        $feedback_token = null;
        if ($generate_qr) {
            $feedback_token = bin2hex(random_bytes(16));
        }
        
        $query = "UPDATE visitors SET Status = 'Checked Out', CheckOutTime = NOW(), 
                  CheckOutOfficer = ?, Feedback = ?";
        
        // Add FeedbackToken only if QR code is generated
        if ($generate_qr) {
            $query .= ", FeedbackToken = ?";
            $params = [$_SESSION['user_name'], $feedback, $feedback_token, $visitor_id];
        } else {
            $params = [$_SESSION['user_name'], $feedback, $visitor_id];
        }
        
        $query .= " WHERE VisitorID = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $message = "Visitor checked out successfully!";
        
        if ($generate_qr) {
            $message .= " QR code generated for feedback.";
            logActivity($_SESSION['user_id'], 'QR Code Generated', "Feedback QR code generated for visitor ID: $visitor_id");
        }
        
        if (!empty($feedback)) {
            $message .= " Visitor comments saved.";
        }
        
        logActivity($_SESSION['user_id'], 'Visitor Checkout', "Checked out visitor ID: $visitor_id");
        
    } catch (Exception $e) {
        $error = "Error checking out visitor: " . $e->getMessage();
    }
}

// Handle simple actions
if (isset($_GET['checkout'])) {
    $visitor_id = $_GET['checkout'];
    
    // Show checkout modal instead of immediate checkout
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showCheckoutModal($visitor_id);
        });
    </script>";
}

if (isset($_GET['return_badge'])) {
    $visitor_id = $_GET['return_badge'];
    $query = "UPDATE visitors SET BadgeReturned = TRUE WHERE VisitorID = ?";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([$visitor_id])) {
        logActivity($_SESSION['user_id'], 'Badge Return', "Badge returned for visitor ID: $visitor_id");
        $message = "Badge marked as returned";
    } else {
        $message = "Error updating badge status";
    }
}

// Build query based on filters - DEFAULT TO ACTIVE VISITORS ONLY
$filter = $_GET['filter'] ?? 'active'; // Default to 'active'
$search = $_GET['search'] ?? '';

$filters = [];
// Only apply status filter if not set to 'all'
if ($filter == 'active') {
    $filters['status'] = 'Checked In';
} elseif ($filter == 'history') {
    $filters['status'] = 'Checked Out';
}
// If filter is 'all', no status filter is applied

if (!empty($search)) {
    $filters['search'] = $search;
}

$visitors = getVisitors($filters);

// Get counts for filter badges
$active_count_query = "SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked In'";
$active_count = $pdo->query($active_count_query)->fetch(PDO::FETCH_ASSOC)['count'];

$history_count_query = "SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked Out'";
$history_count = $pdo->query($history_count_query)->fetch(PDO::FETCH_ASSOC)['count'];

$all_count_query = "SELECT COUNT(*) as count FROM visitors";
$all_count = $pdo->query($all_count_query)->fetch(PDO::FETCH_ASSOC)['count'];

// Get feedback statistics
$feedback_stats_query = "SELECT 
    COUNT(*) as total_checked_out,
    COUNT(CASE WHEN Feedback IS NOT NULL AND Feedback != '' THEN 1 END) as feedback_received,
    COUNT(CASE WHEN FeedbackToken IS NOT NULL THEN 1 END) as qr_generated
    FROM visitors 
    WHERE Status = 'Checked Out'";
$feedback_stats = $pdo->query($feedback_stats_query)->fetch(PDO::FETCH_ASSOC);

// Calculate feedback rate
$feedback_rate = $feedback_stats['total_checked_out'] > 0 ? 
    round(($feedback_stats['feedback_received'] / $feedback_stats['total_checked_out']) * 100, 1) : 0;
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php" class="active"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="visitor-management.php?filter=history"><i class="fas fa-history"></i> Visitor History</a></li>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">Quick Stats</h4>
                <div style="font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <strong>Active Visitors:</strong> <?php echo $active_count; ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Checked Out Today:</strong> 
                        <?php 
                        $today_checked_out = $pdo->query("SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked Out' AND DATE(CheckOutTime) = CURDATE()")->fetch(PDO::FETCH_ASSOC)['count'];
                        echo $today_checked_out;
                        ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Pending Badges:</strong> 
                        <?php 
                        $pending_badges = $pdo->query("SELECT COUNT(*) as count FROM visitors WHERE Status = 'Checked Out' AND BadgeReturned = FALSE")->fetch(PDO::FETCH_ASSOC)['count'];
                        echo $pending_badges;
                        ?>
                    </div>
                    <div>
                        <strong>Feedback Rate:</strong> <?php echo $feedback_rate; ?>%
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">
                <?php 
                if ($filter == 'active') {
                    echo 'Active Visitors';
                } elseif ($filter == 'history') {
                    echo 'Visitor History';
                } else {
                    echo 'All Visitors';
                }
                ?>
            </h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Feedback Statistics -->
            <?php if ($filter == 'history' || $filter == 'all'): ?>
            <div class="stats-container" style="margin-bottom: 20px;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $feedback_stats['feedback_received']; ?></div>
                    <div class="stat-label">Feedback Received</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $feedback_rate; ?>%</div>
                    <div class="stat-label">Feedback Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $feedback_stats['qr_generated']; ?></div>
                    <div class="stat-label">QR Codes Generated</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $today_checked_out; ?></div>
                    <div class="stat-label">Checked Out Today</div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Filters and Search -->
            <div class="section">
                <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; align-items: end;">
                    <div style="flex: 1; min-width: 250px;">
                        <label>Search Visitors</label>
                        <input type="text" id="search" placeholder="Search by name, ID, or organization..." 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               style="width: 100%; padding: 10px;">
                    </div>
                    <div style="min-width: 200px;">
                        <label>Filter by Status</label>
                        <select id="statusFilter" style="width: 100%; padding: 10px;">
                            <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>
                                Active Visitors (<?php echo $active_count; ?>)
                            </option>
                            <option value="history" <?php echo $filter == 'history' ? 'selected' : ''; ?>>
                                Visitor History (<?php echo $history_count; ?>)
                            </option>
                            <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>
                                All Visitors (<?php echo $all_count; ?>)
                            </option>
                        </select>
                    </div>
                    <div>
                        <button onclick="applyFilters()" class="btn btn-primary">Apply Filters</button>
                        <a href="visitor-registration.php" class="btn btn-success">Register New</a>
                        <a href="test-qr.php" class="btn btn-info" target="_blank">Test QR System</a>
                    </div>
                </div>
                
                <!-- Quick Filter Buttons -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                    <a href="?filter=active" class="btn <?php echo $filter == 'active' ? 'btn-primary' : 'btn-outline'; ?>" 
                       style="text-decoration: none;">
                        <i class="fas fa-user-clock"></i> Active (<?php echo $active_count; ?>)
                    </a>
                    <a href="?filter=history" class="btn <?php echo $filter == 'history' ? 'btn-primary' : 'btn-outline'; ?>" 
                       style="text-decoration: none;">
                        <i class="fas fa-history"></i> History (<?php echo $history_count; ?>)
                    </a>
                    <a href="?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline'; ?>" 
                       style="text-decoration: none;">
                        <i class="fas fa-users"></i> All (<?php echo $all_count; ?>)
                    </a>
                </div>
                
                <?php if (count($visitors) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Badge No.</th>
                            <th>Visitor Name</th>
                            <th>ID Number</th>
                            <th>Organization</th>
                            <th>Host Name</th>
                            <th>Host Available</th>
                            <th>Check-in Time</th>
                            <th>Check-out Time</th>
                            <th>Status</th>
                            <th>Badge Returned</th>
                            <th>Feedback</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitors as $visitor): 
                            $duration = 'N/A';
                            if ($visitor['CheckOutTime']) {
                                $checkin = new DateTime($visitor['CheckInTime']);
                                $checkout = new DateTime($visitor['CheckOutTime']);
                                $interval = $checkin->diff($checkout);
                                $duration = $interval->format('%hh %im');
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $visitor['BadgeNumber']; ?></strong>
                                <?php if ($visitor['HasLuggage']): ?>
                                <br><small style="color: var(--warning-yellow);"><i class="fas fa-suitcase"></i> Luggage</small>
                                <?php endif; ?>
                                <?php if ($visitor['PWDStatus']): ?>
                                <br><small style="color: var(--info-blue);"><i class="fas fa-wheelchair"></i> PWD</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo sanitize($visitor['FullName']); ?></td>
                            <td><?php echo $visitor['IDNumber']; ?></td>
                            <td><?php echo sanitize($visitor['Organization']); ?></td>
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
                            <td><?php echo date('M j, Y H:i', strtotime($visitor['CheckInTime'])); ?></td>
                            <td>
                                <?php if ($visitor['CheckOutTime']): ?>
                                    <?php echo date('M j, Y H:i', strtotime($visitor['CheckOutTime'])); ?>
                                    <br><small><?php echo $duration; ?></small>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Still in facility</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($visitor['Status'] == 'Checked In'): ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-user-clock"></i> Checked In
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-user-check"></i> Checked Out
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($visitor['Status'] == 'Checked Out'): ?>
                                    <?php if ($visitor['BadgeReturned']): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check-circle"></i> Returned
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">
                                            <i class="fas fa-exclamation-circle"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($visitor['Status'] == 'Checked Out'): ?>
                                    <?php if (!empty($visitor['FeedbackToken'])): ?>
                                        <span class="status-badge status-info" title="QR code generated for feedback">
                                            <i class="fas fa-qrcode"></i> QR Generated
                                        </span>
                                    <?php elseif (!empty($visitor['Feedback'])): ?>
                                        <span class="status-badge status-success" title="Comments provided during checkout">
                                            <i class="fas fa-comment"></i> Comments
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">No feedback</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <?php if ($visitor['Status'] == 'Checked In'): ?>
                                        <button onclick="showCheckoutModal(<?php echo $visitor['VisitorID']; ?>)" class="btn btn-warning btn-sm">
                                            <i class="fas fa-sign-out-alt"></i> Check Out
                                        </button>
                                    <?php elseif (!$visitor['BadgeReturned']): ?>
                                        <a href="?return_badge=<?php echo $visitor['VisitorID']; ?>" class="btn btn-success btn-sm"
                                           onclick="return confirm('Mark badge as returned for <?php echo addslashes($visitor['FullName']); ?>?')">
                                            <i class="fas fa-id-card"></i> Mark Returned
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($visitor['Status'] == 'Checked Out' && !empty($visitor['FeedbackToken'])): ?>
                                        <a href="print-qr.php?id=<?php echo $visitor['VisitorID']; ?>" class="btn btn-info btn-sm" target="_blank">
                                            <i class="fas fa-qrcode"></i> Print QR
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="generate-badge.php?id=<?php echo $visitor['VisitorID']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-print"></i> View Badge
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: var(--bg-card); border-radius: 8px;">
                        <i class="fas fa-users" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                        <h3 style="color: var(--text-muted); margin-bottom: 10px;">
                            <?php if ($filter == 'active'): ?>
                                No Active Visitors
                            <?php elseif ($filter == 'history'): ?>
                                No Visitor History
                            <?php else: ?>
                                No Visitors Found
                            <?php endif; ?>
                        </h3>
                        <p style="color: var(--text-muted);">
                            <?php if ($filter == 'active'): ?>
                                There are currently no visitors checked into the facility.
                            <?php elseif ($filter == 'history'): ?>
                                No visitors have been checked out yet.
                            <?php else: ?>
                                No visitors match your search criteria.
                            <?php endif; ?>
                        </p>
                        <?php if ($filter != 'active'): ?>
                            <a href="?filter=active" class="btn btn-primary" style="margin-top: 15px;">
                                <i class="fas fa-list"></i> View Active Visitors
                            </a>
                        <?php endif; ?>
                        <a href="visitor-registration.php" class="btn btn-success" style="margin-top: 15px;">
                            <i class="fas fa-user-plus"></i> Register New Visitor
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <span class="close" onclick="closeCheckoutModal()">&times;</span>
        <h2><i class="fas fa-sign-out-alt"></i> Check Out Visitor</h2>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
            <strong><i class="fas fa-info-circle"></i> System Status:</strong>
            <span id="qrStatus">Ready to generate QR code</span>
        </div>
        
        <form method="POST" id="checkoutForm">
            <input type="hidden" name="visitor_id" id="checkout_visitor_id">
            <input type="hidden" name="checkout_visitor" value="1">
            
            <div class="form-group">
                <label for="feedback">
                    <i class="fas fa-comment"></i> Visitor Comments 
                    <small style="color: #666; font-weight: normal;">(Optional)</small>
                </label>
                <textarea name="feedback" id="feedback" rows="3" 
                          placeholder="Any comments or feedback from the visitor during checkout..."></textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="generate_qr" id="generate_qr" value="1">
                <label for="generate_qr">
                    <strong><i class="fas fa-qrcode"></i> Generate Feedback QR Code</strong>
                    <br><small style="color: #666;">Optional - Visitor can scan QR code later to provide feedback</small>
                </label>
            </div>
            
            <!-- QR Code Preview -->
            <div id="qrCodePreview" style="text-align: center; margin: 20px 0; display: none;">
                <h4 style="color: var(--primary-brown);">
                    <i class="fas fa-qrcode"></i> Feedback QR Code Preview
                </h4>
                <div id="qrCodeImage" style="min-height: 240px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <div style="padding: 30px; color: #666; text-align: center;">
                        <i class="fas fa-qrcode" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                        <div>QR Code will appear here when generated</div>
                    </div>
                </div>
                <p style="color: #666; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Scan this code to provide feedback about your visit
                </p>
                
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 15px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-info btn-sm" onclick="testCurrentQR()">
                        <i class="fas fa-bolt"></i> Test QR Code
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="printQRCode()">
                        <i class="fas fa-print"></i> Print QR Code
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="refreshQRCode()">
                        <i class="fas fa-sync-alt"></i> Refresh QR
                    </button>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <button type="submit" class="btn btn-warning" style="padding: 12px 30px; font-size: 1.1rem;">
                    <i class="fas fa-sign-out-alt"></i> Confirm Check Out
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()">Cancel</button>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;">
                <small style="color: #666;">
                    <strong><i class="fas fa-lightbulb"></i> Note:</strong> 
                    Both comments and QR code are optional. You can provide comments, generate a QR code, both, or neither.
                </small>
            </div>
        </form>
    </div>
</div>

<style>
.btn-outline {
    background: transparent;
    border: 2px solid var(--primary-brown);
    color: var(--primary-brown);
}

.btn-outline:hover {
    background: var(--primary-brown);
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-active {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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

.status-info {
    background: #e7f3ff;
    color: #0c63e4;
    border: 1px solid #b6d4fe;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
    white-space: nowrap;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.close {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 1.5rem;
    cursor: pointer;
    color: #aaa;
    background: #f8f9fa;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close:hover {
    color: #000;
    background: #e9ecef;
}

#qrCodePreview {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

#qrCodePreview img {
    max-width: 200px;
    border: 1px solid #ddd;
    padding: 10px;
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.checkbox-group {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s;
}

.checkbox-group:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.checkbox-group input[type="checkbox"] {
    transform: scale(1.2);
    margin-right: 10px;
}
</style>

<script>
// Filter functionality
function applyFilters() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('statusFilter').value;
    
    let url = 'visitor-management.php?';
    if (search) url += 'search=' + encodeURIComponent(search) + '&';
    if (status) url += 'filter=' + encodeURIComponent(status);
    
    window.location.href = url;
}

document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') applyFilters();
});

// Checkout Modal Functions
let currentQRUrl = '';
let currentVisitorId = '';

function showCheckoutModal(visitorId) {
    console.log('Opening checkout modal for visitor:', visitorId);
    
    currentVisitorId = visitorId;
    document.getElementById('checkout_visitor_id').value = visitorId;
    document.getElementById('feedback').value = '';
    document.getElementById('generate_qr').checked = false;
    document.getElementById('qrCodePreview').style.display = 'none';
    document.getElementById('qrStatus').innerHTML = 'Ready to generate QR code';
    currentQRUrl = '';
    
    // Reset QR preview
    document.getElementById('qrCodeImage').innerHTML = `
        <div style="padding: 30px; color: #666; text-align: center;">
            <i class="fas fa-qrcode" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
            <div>QR Code will appear here when generated</div>
        </div>
    `;
    
    document.getElementById('checkoutModal').style.display = 'flex';
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').style.display = 'none';
    currentQRUrl = '';
    currentVisitorId = '';
}

// QR Code Generation
document.getElementById('generate_qr').addEventListener('change', function() {
    if (this.checked && currentVisitorId) {
        console.log('Generating QR code for visitor:', currentVisitorId);
        document.getElementById('qrCodePreview').style.display = 'block';
        generateQRCode(currentVisitorId);
    } else {
        document.getElementById('qrCodePreview').style.display = 'none';
        currentQRUrl = '';
    }
});

function generateQRCode(visitorId) {
    const qrContainer = document.getElementById('qrCodeImage');
    const qrStatus = document.getElementById('qrStatus');
    
    // Show loading state
    qrContainer.innerHTML = `
        <div style="text-align: center; color: #666;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <div>Generating QR Code...</div>
            <small>Please wait</small>
        </div>
    `;
    qrStatus.innerHTML = '<span style="color: #17a2b8;"><i class="fas fa-sync fa-spin"></i> Generating QR code...</span>';
    
    // Generate QR code URL with cache busting
    currentQRUrl = 'generate-qr.php?id=' + visitorId + '&t=' + new Date().getTime();
    console.log('QR Generation URL:', currentQRUrl);
    
    // Create image element
    const img = new Image();
    img.src = currentQRUrl;
    img.alt = 'Feedback QR Code';
    img.style.maxWidth = '200px';
    img.style.maxHeight = '200px';
    img.style.border = '1px solid #ddd';
    img.style.padding = '10px';
    img.style.background = 'white';
    img.style.borderRadius = '5px';
    img.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    
    img.onload = function() {
        console.log('✅ QR code loaded successfully');
        qrContainer.innerHTML = '';
        qrContainer.appendChild(img);
        qrStatus.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> QR code generated successfully</span>';
        
        // Add success animation
        img.style.animation = 'fadeIn 0.5s';
    };
    
    img.onerror = function() {
        console.error('❌ QR code failed to load');
        qrContainer.innerHTML = `
            <div style="text-align: center; color: #dc3545; padding: 20px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div><strong>QR Code Generation Failed</strong></div>
                <small>Please try refreshing or check the console for errors</small>
                <div style="margin-top: 10px;">
                    <button type="button" class="btn btn-warning btn-sm" onclick="generateQRCode(${visitorId})">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            </div>
        `;
        qrStatus.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> QR generation failed - please try again</span>';
    };
}

function testCurrentQR() {
    if (currentQRUrl) {
        console.log('Testing QR URL:', currentQRUrl);
        
        // Open in new tab
        window.open(currentQRUrl, '_blank', 'width=400,height=400');
        
        // Test accessibility
        fetch(currentQRUrl)
            .then(response => {
                const status = `Status: ${response.status}\nType: ${response.headers.get('content-type')}\nSize: ${response.headers.get('content-length') || 'unknown'} bytes`;
                console.log('QR Test Result:', status);
                
                if (response.ok) {
                    alert('✅ QR Code Test Successful!\n\n' + status);
                } else {
                    alert('❌ QR Code Test Failed!\n\n' + status);
                }
            })
            .catch(error => {
                console.error('QR Test Error:', error);
                alert('❌ QR Test Failed!\n\nError: ' + error.message);
            });
    } else {
        alert('⚠️ No QR code generated yet.\n\nPlease check "Generate Feedback QR Code" first.');
    }
}

function refreshQRCode() {
    if (currentVisitorId && document.getElementById('generate_qr').checked) {
        console.log('Refreshing QR code for visitor:', currentVisitorId);
        generateQRCode(currentVisitorId);
    } else {
        alert('⚠️ Please select "Generate Feedback QR Code" first.');
    }
}

function printQRCode() {
    if (currentVisitorId) {
        const printUrl = 'print-qr.php?id=' + currentVisitorId;
        console.log('Opening print URL:', printUrl);
        const printWindow = window.open(printUrl, '_blank', 'width=600,height=700');
        if (!printWindow) {
            alert('⚠️ Popup blocked!\n\nPlease allow popups for this site to print QR codes.');
        }
    } else {
        alert('⚠️ No visitor selected for printing.');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('checkoutModal');
    if (event.target === modal) {
        closeCheckoutModal();
    }
}

// Close modal with escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCheckoutModal();
    }
});

// Auto-refresh for active visitors
<?php if ($filter == 'active'): ?>
setTimeout(function() {
    window.location.reload();
}, 30000); // Refresh every 30 seconds
<?php endif; ?>

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once 'includes/footer.php'; ?>