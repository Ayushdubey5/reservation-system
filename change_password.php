<?php
// change_password.php
$pageTitle = "Change Password";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword)) {
        $errors[] = "Current password is required.";
    }
    if (empty($newPassword)) {
        $errors[] = "New password is required.";
    } elseif (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New password and password confirmation do not match.";
    }

    if (empty($errors)) {
        // Fetch current password from DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Verify old password match
            if (password_verify($currentPassword, $user['password'])) {
                // Securely hash the new password using modern PHP hashing algorithms
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update in database
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->bind_param("si", $hashedNewPassword, $userId);
                
                if ($updateStmt->execute()) {
                    $success = "Password updated successfully.";
                } else {
                    $errors[] = "Database update error. Please try again.";
                }
                $updateStmt->close();
            } else {
                $errors[] = "Incorrect current password.";
            }
        } else {
            $errors[] = "User record not found.";
        }
        $stmt->close();
    }
}
?>

<!-- Change Password View Layout -->
<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="h3 mb-0 text-gray-800">Change Password</h1>
                <p class="text-muted">Update your account password to maintain system security</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary text-uppercase">Security Settings</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="change_password.php" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label font-weight-bold text-secondary">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label for="new_password" class="form-label font-weight-bold text-secondary">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label font-weight-bold text-secondary">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type new password" required>
                            </div>

                            <div class="d-flex justify-content-end border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>