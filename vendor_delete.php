<?php
// vendor_delete.php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: vendors.php"); exit(); }

$check = $conn->query("SELECT COUNT(*) as c FROM purchases WHERE vendor_id = $id");
$count = $check->fetch_assoc()['c'];

if ($count > 0) {
    $conn->query("UPDATE vendors SET status = 'Inactive' WHERE vendor_id = $id");
    setFlash('warning', 'Vendor has purchase records. Deactivated instead of deleted.');
} else {
    $conn->query("DELETE FROM vendors WHERE vendor_id = $id");
    setFlash('success', 'Vendor deleted successfully.');
}

header("Location: vendors.php");
exit();
?>