<?php
// ajax_get_purchase.php
require_once 'config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$purchase_id = (int)($_GET['purchase_id'] ?? 0);
if ($purchase_id <= 0) {
    echo json_encode(['success' => false]);
    exit();
}

$purchase = $conn->query("
    SELECT p.*, v.vendor_name, o.customer_id, c.customer_name
    FROM purchases p
    JOIN vendors v ON p.vendor_id = v.vendor_id
    JOIN orders o ON p.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE p.purchase_id = $purchase_id
")->fetch_assoc();

if (!$purchase) {
    echo json_encode(['success' => false]);
    exit();
}

echo json_encode(['success' => true, 'purchase' => $purchase]);
?>