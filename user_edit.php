<?php
// user_edit.php
$pageTitle = "Edit User";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: users.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) { setFlash('danger', 'User not found.'); header("Location: users.php"); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user['name'] = trim($_POST['name'] ?? '');
    $user['username'] = trim($_POST['username'] ?? '');
    $user['email'] = trim($_POST['email'] ?? '');
    $user['role'] = $_POST['role'] ?? 'Staff';
    $user['status'] = $_POST['status'] ?? 'Active';
    $password = trim($_POST['password'] ?? '');

    if (empty($user['name'])) $errors[] = "Name is required.";
    if (empty($user['username'])) $errors[] = "Username is required.";
    if (empty($user['email'])) $errors[] = "Email is required.";

    // Check uniqueness excluding current user
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt->bind_param("ssi", $user['username'], $user['email'], $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username or email already in use by another user.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, password=?, role=?, status=? WHERE user_id=?");
                $stmt->bind_param("ssssssi", $user['name'], $user['username'], $user['email'], $hashedPassword, $user['role'], $user['status'], $id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, role=?, status=? WHERE user_id=?");
            $stmt->bind_param("sssssi", $user['name'], $user['username'], $user['email'], $user['role'], $user['status'], $id);
        }

        if (empty($errors)) {
            if ($stmt->execute()) {
                setFlash('success', 'User updated successfully!');
                header("Location: users.php");
                exit();
            } else {
                $errors[] = "Error updating user.";
            }
            $stmt->close();
        }
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left">
            <h2>Edit User</h2>
            <p>Update user information</p>
        </div>
        <div class="header-right">
            <a href="users.php" class="btn btn-secondary">← Back to Users</a>
        </div>
    </div>

    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div>⚠️ <?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>Edit User #<?php echo $id; ?></h3></div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username <span class="required">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role <span class="required">*</span></label>
                            <select name="role" class="form-control">
                                <option value="Staff" <?php echo $user['role'] == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                                <option value="Admin" <?php echo $user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo $user['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $user['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Update User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>