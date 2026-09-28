<?php
// profile.php
$pageTitle = "Profile";
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

// Fetch user data securely
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Username/Email is required.";
    }

    if (empty($errors)) {
        // Check email/username uniqueness, excluding the current user
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "This username/email is already taken by another user.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Update profile details
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $name, $email, $userId);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            // Update current user info in active session
            $_SESSION['user_name'] = $name;
            $_SESSION['username'] = $email;
            
            // Refresh local user data for page display
            $user['name'] = $name;
            $user['username'] = $email;
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!-- Profile View/Edit Layout -->
<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
                <p class="text-muted">Manage your personal account details</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary text-uppercase">Account Details</h6>
                        <span class="badge bg-primary text-white p-2">Role: <?php echo htmlspecialchars($user['role']); ?></span>
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

                        <form method="POST" action="profile.php" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="userIdField" class="form-label font-weight-bold text-secondary">User ID</label>
                                    <input type="text" class="form-control" id="userIdField" value="<?php echo htmlspecialchars($user['user_id']); ?>" readonly disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="statusField" class="form-label font-weight-bold text-secondary">Account Status</label>
                                    <input type="text" class="form-control" id="statusField" value="<?php echo htmlspecialchars(ucfirst($user['status'])); ?>" readonly disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label font-weight-bold text-secondary">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label font-weight-bold text-secondary">Username / Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-secondary d-block">Account Created On</label>
                                <small class="text-muted"><?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></small>
                            </div>

                            <div class="d-flex justify-content-end border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>