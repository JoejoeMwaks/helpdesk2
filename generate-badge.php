<?php
// generate-badge.php
$page_title = "Generate Badge";
require_once 'includes/header.php';

if (!canRegisterVisitors()) {
    header("Location: dashboard.php");
    exit();
}

$visitor_id = $_GET['id'] ?? 0;
$success = $_GET['success'] ?? 0;

if (!$visitor_id) {
    header("Location: visitor-registration.php");
    exit();
}

// Get visitor details
$visitor = getVisitorById($visitor_id);
if (!$visitor) {
    header("Location: visitor-registration.php");
    exit();
}

if ($success) {
    $message = "Visitor registered successfully! Badge Number: " . $visitor['BadgeNumber'];
}

// Calculate duration if checked out
$duration = '';
if ($visitor['CheckOutTime']) {
    $checkin = new DateTime($visitor['CheckInTime']);
    $checkout = new DateTime($visitor['CheckOutTime']);
    $interval = $checkin->diff($checkout);
    $duration = $interval->format('%hh %im');
}
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Manage Visitors</a></li>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">Visitor Status</h4>
                <div style="font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <strong>Status:</strong> 
                        <span style="color: <?php echo $visitor['Status'] == 'Checked In' ? 'var(--success-green)' : 'var(--info-blue)'; ?>; font-weight: 600;">
                            <?php echo $visitor['Status']; ?>
                        </span>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Check-in:</strong> <?php echo date('M j, Y H:i', strtotime($visitor['CheckInTime'])); ?>
                    </div>
                    <?php if ($visitor['CheckOutTime']): ?>
                    <div style="margin-bottom: 8px;">
                        <strong>Check-out:</strong> <?php echo date('M j, Y H:i', strtotime($visitor['CheckOutTime'])); ?>
                    </div>
                    <div>
                        <strong>Duration:</strong> <?php echo $duration; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Visitor Badge & Details</h1>
            
            <!-- Printable Badge -->
            <div class="badge-preview" id="printableBadge" style="max-width: 400px; margin: 0 auto; border: 2px solid var(--primary-brown);">
                <div class="badge-header">
                    <h3 style="margin: 0; font-size: 1.2rem;">VISITOR PASS</h3>
                    <div style="font-size: 0.9rem;">KENYA NATIONAL BUREAU OF STATISTICS</div>
                </div>
                <div class="badge-name" style="font-size: 1.4rem; margin: 15px 0; font-weight: bold;">
                    <?php echo sanitize($visitor['FullName']); ?>
                </div>
                <div class="badge-organization" style="font-size: 1.1rem; margin-bottom: 10px;">
                    <?php echo sanitize($visitor['Organization']); ?>
                </div>
                <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; text-align: left;">
                    <div style="margin-bottom: 5px;"><strong>Purpose:</strong> <?php echo sanitize($visitor['PurposeOfVisit']); ?></div>
                    <?php if (!empty($visitor['HostName'])): ?>
                    <div style="margin-bottom: 5px;"><strong>Host:</strong> <?php echo sanitize($visitor['HostName']); ?></div>
                    <?php endif; ?>
                    <div><strong>Admitted by:</strong> <?php echo sanitize($visitor['AdmittingOfficer']); ?></div>
                </div>
                
                <?php if (!$visitor['HostAvailable'] && !empty($visitor['VisitorMessage'])): ?>
                <div style="margin: 10px 0; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                    <strong>Message for Host:</strong> <?php echo sanitize($visitor['VisitorMessage']); ?>
                </div>
                <?php endif; ?>
                
                <div class="badge-number" style="font-size: 1.3rem; font-weight: bold; color: var(--primary-brown); margin: 15px 0;">
                    <?php echo $visitor['BadgeNumber']; ?>
                </div>
                <div class="badge-date" style="margin-bottom: 10px;">
                    <?php echo date('F j, Y'); ?>
                </div>
                <div style="margin-top: 15px; font-size: 0.8rem; color: var(--text-muted); padding: 8px; background: #fff3cd; border-radius: 4px;">
                    <strong>Important:</strong> Valid for today only • Must be returned at exit • Must be visible at all times
                </div>
                <?php if ($visitor['HasLuggage']): ?>
                <div style="margin-top: 10px; padding: 8px; background: #d1ecf1; border-radius: 4px; font-size: 0.9rem;">
                    <strong>Luggage Tag:</strong> <?php echo $visitor['LuggageNumber']; ?>
                </div>
                <?php endif; ?>
                <?php if ($visitor['PWDStatus']): ?>
                <div style="margin-top: 10px; padding: 8px; background: #e2e3e5; border-radius: 4px; font-size: 0.9rem;">
                    <strong><i class="fas fa-wheelchair"></i> Person with Disability</strong>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div style="text-align: center; margin-top: 30px;">
                <button onclick="printBadge()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Badge
                </button>
                <a href="visitor-registration.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Register Another Visitor
                </a>
                <a href="dashboard.php" class="btn btn-info">
                    <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                </a>
                <?php if ($visitor['Status'] == 'Checked In'): ?>
                <button onclick="showCheckoutModal(<?php echo $visitor['VisitorID']; ?>)" class="btn btn-warning">
                    <i class="fas fa-sign-out-alt"></i> Check Out Visitor
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Visitor Details -->
            <div class="section" style="margin-top: 40px;">
                <h3 style="margin-bottom: 15px; color: var(--primary-brown);">
                    <i class="fas fa-info-circle"></i> Complete Visitor Details
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <h4 style="color: var(--primary-brown); margin-bottom: 15px;">Personal Information</h4>
                        <div style="background: var(--bg-card); padding: 20px; border-radius: 8px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Full Name:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo sanitize($visitor['FullName']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Gender:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo $visitor['Gender']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>ID Type:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo $visitor['IDType']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>ID Number:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo $visitor['IDNumber']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Phone:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo $visitor['PhoneNumber'] ?: 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>PWD Status:</strong></td>
                                    <td style="padding: 8px 0;"><?php echo $visitor['PWDStatus'] ? 'Yes' : 'No'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: var(--primary-brown); margin-bottom: 15px;">Visit Information</h4>
                        <div style="background: var(--bg-card); padding: 20px; border-radius: 8px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Organization:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo sanitize($visitor['Organization']) ?: 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Purpose:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo sanitize($visitor['PurposeOfVisit']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Host:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo sanitize($visitor['HostName']) ?: 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Host Available:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                                        <?php if ($visitor['HostAvailable']): ?>
                                            <span class="status-badge status-success">Yes</span>
                                        <?php else: ?>
                                            <span class="status-badge status-warning">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><strong>Check-in Time:</strong></td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid var(--border-color);"><?php echo date('M j, Y H:i', strtotime($visitor['CheckInTime'])); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Admitted by:</strong></td>
                                    <td style="padding: 8px 0;">
                                        <span style="color: var(--primary-brown); font-weight: 600;"><?php echo sanitize($visitor['AdmittingOfficer']); ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <?php if ($visitor['CheckOutTime']): ?>
                        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <h5 style="color: var(--success-green); margin-bottom: 10px;">Check-out Information</h5>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Check-out Time:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo date('M j, Y H:i', strtotime($visitor['CheckOutTime'])); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Checked out by:</strong></td>
                                    <td style="padding: 5px 0;">
                                        <span style="color: var(--info-blue); font-weight: 600;"><?php echo sanitize($visitor['CheckOutOfficer']); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Visit Duration:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo $duration; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Badge Returned:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo $visitor['BadgeReturned'] ? 'Yes' : 'No'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div style="margin-top: 20px;">
                    <h4 style="color: var(--primary-brown); margin-bottom: 15px;">Additional Information</h4>
                    <div style="background: var(--bg-card); padding: 20px; border-radius: 8px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <strong>Has Luggage:</strong> <?php echo $visitor['HasLuggage'] ? 'Yes' : 'No'; ?>
                                <?php if ($visitor['HasLuggage']): ?>
                                <br><strong>Luggage Number:</strong> <?php echo $visitor['LuggageNumber']; ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong>Badge Number:</strong> <?php echo $visitor['BadgeNumber']; ?>
                                <br><strong>Record ID:</strong> VIS<?php echo str_pad($visitor['VisitorID'], 5, '0', STR_PAD_LEFT); ?>
                            </div>
                        </div>
                        
                        <?php if (!$visitor['HostAvailable'] && !empty($visitor['VisitorMessage'])): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <strong>Message for Host:</strong>
                            <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-top: 5px;">
                                <?php echo sanitize($visitor['VisitorMessage']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($visitor['Feedback'])): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <strong>Visitor Feedback:</strong>
                            <div style="background: #e8f5e8; padding: 10px; border-radius: 4px; margin-top: 5px;">
                                <?php echo sanitize($visitor['Feedback']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <span class="close" onclick="closeCheckoutModal()">&times;</span>
        <h2>Check Out Visitor</h2>
        <form method="POST" action="visitor-management.php" id="checkoutForm">
            <input type="hidden" name="visitor_id" id="checkout_visitor_id">
            <input type="hidden" name="checkout_visitor" value="1">
            
            <div class="form-group">
                <label for="feedback">Visitor Feedback (Optional)</label>
                <textarea name="feedback" id="feedback" rows="4" 
                          placeholder="Enter any feedback from the visitor about their experience"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sign-out-alt"></i> Confirm Check Out
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()">Cancel</button>
            </div>
        </form>
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

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: var(--shadow);
    position: relative;
}

.close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 1.5rem;
    cursor: pointer;
}
</style>

<script>
function printBadge() {
    const badgeElement = document.getElementById('printableBadge');
    const originalContents = document.body.innerHTML;
    
    // Create a print-friendly version
    const printContents = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Visitor Badge - <?php echo $visitor['FullName']; ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .badge-preview { 
                    border: 2px solid #663300; 
                    border-radius: 8px; 
                    max-width: 400px; 
                    margin: 0 auto; 
                    text-align: center;
                }
                .badge-header { 
                    background: #663300; 
                    color: white; 
                    padding: 15px; 
                    border-radius: 6px 6px 0 0; 
                }
                .badge-name { font-size: 1.4rem; margin: 15px 0; font-weight: bold; }
                .badge-organization { font-size: 1.1rem; margin-bottom: 10px; }
                .badge-number { font-size: 1.3rem; font-weight: bold; color: #663300; margin: 15px 0; }
                @media print {
                    body { margin: 0; }
                    .badge-preview { border: none; box-shadow: none; }
                }
            </style>
        </head>
        <body>
            ${badgeElement.innerHTML}
            <div style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #666;">
                Generated by KNBS Visitor System on <?php echo date('M j, Y H:i'); ?>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContents);
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

function showCheckoutModal(visitorId) {
    document.getElementById('checkout_visitor_id').value = visitorId;
    document.getElementById('checkoutModal').style.display = 'flex';
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('checkoutModal');
    if (event.target === modal) {
        closeCheckoutModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>