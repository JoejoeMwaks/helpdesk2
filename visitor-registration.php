<?php
// visitor-registration.php
$page_title = "Register Visitor";
require_once 'includes/header.php';

if (!canRegisterVisitors()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = ['FullName', 'Gender', 'IDType', 'IDNumber', 'PurposeOfVisit'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Please fill in all required fields: " . implode(', ', $missing_fields));
        }

        // Generate badge number
        $badge_number = generateBadgeNumber();
        
        // Prepare the SQL query
        $query = "INSERT INTO visitors 
                 (FullName, Gender, PWDStatus, IDType, IDNumber, PhoneNumber, Organization, 
                  PurposeOfVisit, HostName, HostAvailable, VisitorMessage, BadgeNumber, HasLuggage, LuggageNumber, 
                  AdmittingOfficer, CheckInTime) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($query);
        
        // Execute with parameters
        $result = $stmt->execute([
            sanitize($_POST['FullName']),
            $_POST['Gender'],
            isset($_POST['PWDStatus']) ? 1 : 0,
            $_POST['IDType'],
            sanitize($_POST['IDNumber']),
            sanitize($_POST['PhoneNumber'] ?? ''),
            sanitize($_POST['Organization'] ?? ''),
            sanitize($_POST['PurposeOfVisit']),
            sanitize($_POST['HostName'] ?? ''),
            $_POST['HostAvailable'] ?? 1,
            sanitize($_POST['VisitorMessage'] ?? ''),
            $badge_number,
            isset($_POST['HasLuggage']) ? 1 : 0,
            isset($_POST['HasLuggage']) && !empty($_POST['LuggageNumber']) ? sanitize($_POST['LuggageNumber']) : NULL,
            $_SESSION['user_name']
        ]);
        
        if ($result) {
            $visitor_id = $pdo->lastInsertId();
            
            // Log activity
            logActivity($_SESSION['user_id'], 'Visitor Registration', 
                       "Registered visitor: " . sanitize($_POST['FullName']));
            
            // Success message
            $message = "Visitor registered successfully! Redirecting to badge...";
            
            // Redirect to generate badge
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'generate-badge.php?id=" . $visitor_id . "&success=1';
                }, 2000);
            </script>";
            
        } else {
            throw new Exception("Database insertion failed.");
        }
        
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
        error_log("Visitor Registration Error: " . $e->getMessage());
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php" class="active"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="visitor-management.php?filter=history"><i class="fas fa-history"></i> Visitor History</a></li>
            </ul>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Register New Visitor</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" id="visitorForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Full Name</label>
                            <input type="text" name="FullName" required 
                                   value="<?php echo isset($_POST['FullName']) ? $_POST['FullName'] : ''; ?>"
                                   placeholder="Enter visitor's full name">
                        </div>
                        <div class="form-group">
                            <label class="required">Gender</label>
                            <select name="Gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">ID Type</label>
                            <select name="IDType" required>
                                <option value="">Select ID Type</option>
                                <option value="ID" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'ID') ? 'selected' : ''; ?>>National ID</option>
                                <option value="Passport" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'Passport') ? 'selected' : ''; ?>>Passport</option>
                                <option value="Driving License" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'Driving License') ? 'selected' : ''; ?>>Driving License</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">ID Number</label>
                            <input type="text" name="IDNumber" required 
                                   value="<?php echo isset($_POST['IDNumber']) ? $_POST['IDNumber'] : ''; ?>"
                                   placeholder="Enter ID/Passport number">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="PhoneNumber" 
                                   value="<?php echo isset($_POST['PhoneNumber']) ? $_POST['PhoneNumber'] : ''; ?>"
                                   placeholder="Enter phone number">
                        </div>
                        <div class="form-group">
                            <label>Organization</label>
                            <input type="text" name="Organization" 
                                   value="<?php echo isset($_POST['Organization']) ? $_POST['Organization'] : ''; ?>"
                                   placeholder="Enter organization name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Purpose of Visit</label>
                        <textarea name="PurposeOfVisit" rows="3" required 
                                  placeholder="Enter purpose of visit"><?php echo isset($_POST['PurposeOfVisit']) ? $_POST['PurposeOfVisit'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Host Name (Person to Visit)</label>
                        <input type="text" name="HostName" 
                               value="<?php echo isset($_POST['HostName']) ? $_POST['HostName'] : ''; ?>"
                               placeholder="Enter host name">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Host Available?</label>
                            <select name="HostAvailable" id="HostAvailable">
                                <option value="1" selected>Yes</option>
                                <option value="0">No - Host Not Available</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" id="visitorMessageField" style="display: none;">
                        <label>Message for Host</label>
                        <textarea name="VisitorMessage" rows="3" placeholder="Leave a message for the host if they are not available"><?php echo isset($_POST['VisitorMessage']) ? $_POST['VisitorMessage'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group checkbox-group">
                            <input type="checkbox" name="PWDStatus" id="PWDStatus" value="1" <?php echo (isset($_POST['PWDStatus'])) ? 'checked' : ''; ?>>
                            <label for="PWDStatus">Person with Disability</label>
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" name="HasLuggage" id="HasLuggage" value="1" <?php echo (isset($_POST['HasLuggage'])) ? 'checked' : ''; ?>>
                            <label for="HasLuggage">Has Luggage</label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="luggageNumberField" style="display: <?php echo (isset($_POST['HasLuggage'])) ? 'block' : 'none'; ?>;">
                        <label>Luggage Number</label>
                        <input type="text" name="LuggageNumber" 
                               value="<?php echo isset($_POST['LuggageNumber']) ? $_POST['LuggageNumber'] : ''; ?>"
                               placeholder="Enter luggage tag number">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1rem;">
                        <i class="fas fa-id-card"></i> Register Visitor & Print Badge
                    </button>
                </form>
            </div>
            
            <!-- Badge Preview -->
            <div style="margin-top: 40px; text-align: center;">
                <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Visitor Badge Preview</h3>
                <div class="badge-preview">
                    <div class="badge-header">
                        <h3>VISITOR PASS</h3>
                        <div>KENYA NATIONAL BUREAU OF STATISTICS</div>
                    </div>
                    <div class="badge-name" id="previewName">Visitor Name</div>
                    <div class="badge-organization" id="previewOrganization">Organization</div>
                    <div class="badge-number"><?php echo generateBadgeNumber(); ?></div>
                    <div class="badge-date"><?php echo date('F j, Y'); ?></div>
                    <div style="margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                        Valid for today only • Must be returned at exit
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const luggageCheckbox = document.getElementById('HasLuggage');
        const luggageField = document.getElementById('luggageNumberField');
        const hostAvailableSelect = document.getElementById('HostAvailable');
        const messageField = document.getElementById('visitorMessageField');
        
        if (luggageCheckbox && luggageField) {
            luggageCheckbox.addEventListener('change', function() {
                luggageField.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    document.querySelector('input[name="LuggageNumber"]').value = '';
                }
            });
        }
        
        if (hostAvailableSelect && messageField) {
            hostAvailableSelect.addEventListener('change', function() {
                messageField.style.display = this.value === '0' ? 'block' : 'none';
            });
        }
        
        // Real-time badge preview
        const nameInput = document.querySelector('input[name="FullName"]');
        const orgInput = document.querySelector('input[name="Organization"]');
        const previewName = document.getElementById('previewName');
        const previewOrg = document.getElementById('previewOrganization');
        
        if (nameInput && previewName) {
            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value || 'Visitor Name';
            });
        }
        
        if (orgInput && previewOrg) {
            orgInput.addEventListener('input', function() {
                previewOrg.textContent = this.value || 'Organization';
            });
        }

        // Form validation
        const form = document.getElementById('visitorForm');
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields marked with *');
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>