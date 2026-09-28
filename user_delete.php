<?php
// user_delete.php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0 || $id == $_SESSION['user_id']) {
    setFlash('danger', 'Invalid operation.');
    header("Location: users.php");
    exit();
}

// Check if user has created any orders
$check = $conn->query("SELECT COUNT(*) as c FROM orders WHERE created_by = $id");
$count = $check->fetch_assoc()['c'];

if ($count > 0) {
    // Deactivate instead of delete
    $conn->query("UPDATE users SET status = 'Inactive' WHERE user_id = $id");
    setFlash('warning', 'User has existing records. Account deactivated instead of deleted.');
} else {
    $conn->query("DELETE FROM users WHERE user_id = $id");
    setFlash('success', 'User deleted successfully.');
}

header("Location: users.php");
exit();
?>