<?php
// user-management.php
$page_title = "User Management";
require_once 'includes/header.php';

if (!canManageUsers()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $fullname = sanitize($_POST['FullName']);
        $pfnumber = sanitize($_POST['PFNumber']);
        $idnumber = sanitize($_POST['IDNumber']);
        $role = $_POST['Role'];
        $department = sanitize($_POST['Department']);
        $phone = sanitize($_POST['PhoneNumber']);
        $email = sanitize($_POST['Email']);
        $password = password_hash('password', PASSWORD_DEFAULT); // Default password
        
        try {
            $query = "INSERT INTO users (FullName, PFNumber, IDNumber, Role, Department, PhoneNumber, Email, PasswordHash) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$fullname, $pfnumber, $idnumber, $role, $department, $phone, $email, $password]);
            
            logActivity($_SESSION['user_id'], 'User Added', "Added user: $fullname");
            $message = "User added successfully! Default password: 'password'";
            
        } catch (Exception $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_user'])) {
        // Update user
        $user_id = $_POST['user_id'];
        $fullname = sanitize($_POST['FullName']);
        $pfnumber = sanitize($_POST['PFNumber']);
        $idnumber = sanitize($_POST['IDNumber']);
        $role = $_POST['Role'];
        $department = sanitize($_POST['Department']);
        $phone = sanitize($_POST['PhoneNumber']);
        $email = sanitize($_POST['Email']);
        $is_active = isset($_POST['IsActive']) ? 1 : 0;
        
        try {
            $query = "UPDATE users SET FullName = ?, PFNumber = ?, IDNumber = ?, Role = ?, 
                     Department = ?, PhoneNumber = ?, Email = ?, IsActive = ? 
                     WHERE UserID = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$fullname, $pfnumber, $idnumber, $role, $department, $phone, $email, $is_active, $user_id]);
            
            logActivity($_SESSION['user_id'], 'User Updated', "Updated user: $fullname");
            $message = "User updated successfully!";
            
        } catch (Exception $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['reset_password'])) {
        // Reset password
        $user_id = $_POST['user_id'];
        $password = password_hash('password', PASSWORD_DEFAULT);
        
        try {
            $query = "UPDATE users SET PasswordHash = ? WHERE UserID = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$password, $user_id]);
            
            logActivity($_SESSION['user_id'], 'Password Reset', "Reset password for user ID: $user_id");
            $message = "Password reset successfully! New password: 'password'";
            
        } catch (Exception $e) {
            $error = "Error resetting password: " . $e->getMessage();
        }
    }
}

// Get all users
$users = getAllUsers();
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">System Management</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="user-management.php" class="active"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="system-settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">User Management</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add User Form -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">Add New User</h2>
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Full Name</label>
                            <input type="text" name="FullName" required>
                        </div>
                        <div class="form-group">
                            <label class="required">PF Number</label>
                            <input type="text" name="PFNumber" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">ID Number</label>
                            <input type="text" name="IDNumber" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Role</label>
                            <select name="Role" required>
                                <option value="">Select Role</option>
                                <option value="System Administrator">System Administrator</option>
                                <option value="Main Front Desk Officer">Main Front Desk Officer</option>
                                <option value="Secondary Front Desk Officer">Secondary Front Desk Officer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="Department">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="PhoneNumber">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="Email">
                    </div>
                    
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </form>
            </div>
            
            <!-- Users List -->
            <div class="section">
                <h2 style="margin-bottom: 15px; color: var(--primary-brown);">System Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>PF Number</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo sanitize($user['FullName']); ?></td>
                                <td><?php echo $user['PFNumber']; ?></td>
                                <td><?php echo $user['Role']; ?></td>
                                <td><?php echo sanitize($user['Department']); ?></td>
                                <td><?php echo $user['PhoneNumber']; ?></td>
                                <td>
                                    <span style="color: <?php echo $user['IsActive'] ? 'var(--success-green)' : 'var(--error-red)'; ?>; font-weight: 600;">
                                        <?php echo $user['IsActive'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        Edit
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                        <button type="submit" name="reset_password" class="btn btn-warning btn-sm"
                                                onclick="return confirm('Reset password to default?')">
                                            Reset Password
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit User Modal -->
            <div id="editUserModal" class="modal" style="display: none;">
                <div class="modal-content" style="max-width: 600px;">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Edit User</h2>
                    <form method="POST" id="editUserForm">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <input type="hidden" name="update_user" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">Full Name</label>
                                <input type="text" name="FullName" id="edit_fullname" required>
                            </div>
                            <div class="form-group">
                                <label class="required">PF Number</label>
                                <input type="text" name="PFNumber" id="edit_pfnumber" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">ID Number</label>
                                <input type="text" name="IDNumber" id="edit_idnumber" required>
                            </div>
                            <div class="form-group">
                                <label class="required">Role</label>
                                <select name="Role" id="edit_role" required>
                                    <option value="">Select Role</option>
                                    <option value="System Administrator">System Administrator</option>
                                    <option value="Main Front Desk Officer">Main Front Desk Officer</option>
                                    <option value="Secondary Front Desk Officer">Secondary Front Desk Officer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="Department" id="edit_department">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="PhoneNumber" id="edit_phone">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="Email" id="edit_email">
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" name="IsActive" id="edit_active">
                            <label for="edit_active">Active User</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
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

.btn-sm {
    padding: 5px 10px;
    font-size: 0.8rem;
}
</style>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.UserID;
    document.getElementById('edit_fullname').value = user.FullName;
    document.getElementById('edit_pfnumber').value = user.PFNumber;
    document.getElementById('edit_idnumber').value = user.IDNumber;
    document.getElementById('edit_role').value = user.Role;
    document.getElementById('edit_department').value = user.Department || '';
    document.getElementById('edit_phone').value = user.PhoneNumber || '';
    document.getElementById('edit_email').value = user.Email || '';
    document.getElementById('edit_active').checked = user.IsActive == 1;
    
    document.getElementById('editUserModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editUserModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Close modal with escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>