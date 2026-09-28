<?php
// order_delete.php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: orders.php"); exit(); }

// Check if order has purchases
$check = $conn->query("SELECT COUNT(*) as c FROM purchases WHERE order_id = $id");
if ($check->fetch_assoc()['c'] > 0) {
    setFlash('danger', 'Cannot delete order with existing purchases.');
} else {
    $conn->query("DELETE FROM order_passengers WHERE order_id = $id");
    $conn->query("DELETE FROM orders WHERE order_id = $id");
    setFlash('success', 'Order deleted successfully.');
}

header("Location: orders.php");
exit();
?>