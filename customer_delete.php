<?php
// customer_delete.php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: customers.php"); exit(); }

$check = $conn->query("SELECT COUNT(*) as c FROM orders WHERE customer_id = $id");
$count = $check->fetch_assoc()['c'];

if ($count > 0) {
    setFlash('danger', 'Cannot delete customer. They have existing orders. Remove associated orders first.');
} else {
    $conn->query("DELETE FROM customers WHERE customer_id = $id");
    setFlash('success', 'Customer deleted successfully.');
}

header("Location: customers.php");
exit();
?>