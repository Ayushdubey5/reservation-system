<?php
// ajax_get_order.php
require_once 'config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    echo json_encode(['success' => false]);
    exit();
}

$order = $conn->query("SELECT * FROM orders WHERE order_id = $order_id")->fetch_assoc();
if (!$order) {
    echo json_encode(['success' => false]);
    exit();
}

$passengers = [];
$result = $conn->query("SELECT * FROM order_passengers WHERE order_id = $order_id");
while ($row = $result->fetch_assoc()) {
    $passengers[] = $row;
}

echo json_encode(['success' => true, 'order' => $order, 'passengers' => $passengers]);
?>