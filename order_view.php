<?php
// order_view.php
$pageTitle = "View Order";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: orders.php"); exit(); }

$order = $conn->query("SELECT o.*, c.customer_name, c.contact as customer_contact, u.name as created_by_name FROM orders o JOIN customers c ON o.customer_id = c.customer_id JOIN users u ON o.created_by = u.user_id WHERE o.order_id = $id")->fetch_assoc();
if (!$order) { setFlash('danger', 'Order not found.'); header("Location: orders.php"); exit(); }

$passengers = $conn->query("SELECT * FROM order_passengers WHERE order_id = $id");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Order #<?php echo $id; ?></h2><p>View order details</p></div>
        <div class="header-right">
            <?php if ($order['status'] == 'Pending'): ?>
                <a href="purchase_add.php?order_id=<?php echo $id; ?>" class="btn btn-success">🎫 Purchase Ticket</a>
                <a href="order_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Edit</a>
            <?php endif; ?>
            <a href="orders.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h3>Order Information</h3><span class="badge badge-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="d-label">Order ID</div><div class="d-value">#<?php echo $order['order_id']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Order Date</div><div class="d-value"><?php echo $order['order_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Customer</div><div class="d-value"><?php echo htmlspecialchars($order['customer_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Source</div><div class="d-value"><?php echo htmlspecialchars($order['source']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Destination</div><div class="d-value"><?php echo htmlspecialchars($order['destination']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Mode</div><div class="d-value"><span class="badge badge-<?php echo strtolower($order['mode']); ?>"><?php echo $order['mode']; ?></span></div></div>
                    <div class="detail-item"><div class="d-label">Journey Date</div><div class="d-value"><?php echo $order['journey_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Passengers</div><div class="d-value"><?php echo $order['passenger_count']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Order Cost</div><div class="d-value">₹<?php echo number_format($order['order_cost'], 2); ?></div></div>
                    <div class="detail-item"><div class="d-label">Created By</div><div class="d-value"><?php echo htmlspecialchars($order['created_by_name']); ?></div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Passenger Details</h3></div>
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Name</th><th>Age</th><th>Sex</th><th>Seat</th><th>Food</th><th>Senior Citizen</th><th>Doctor</th></tr></thead>
                    <tbody>
                        <?php $i = 1; while($p = $passengers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($p['passenger_name']); ?></td>
                            <td><?php echo $p['age']; ?></td>
                            <td><?php echo $p['sex']; ?></td>
                            <td><?php echo htmlspecialchars($p['seat'] ?? '-'); ?></td>
                            <td><?php echo $p['food_preference']; ?></td>
                            <td><?php echo $p['senior_citizen']; ?></td>
                            <td><?php echo $p['doctor']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>