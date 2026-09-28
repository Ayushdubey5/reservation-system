<?php
// customer_view.php
$pageTitle = "View Customer";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: customers.php"); exit(); }

$customer = $conn->query("SELECT * FROM customers WHERE customer_id = $id")->fetch_assoc();
if (!$customer) { setFlash('danger', 'Customer not found.'); header("Location: customers.php"); exit(); }

// Customer orders
$orders = $conn->query("SELECT * FROM orders WHERE customer_id = $id ORDER BY order_id DESC");

// Customer sales
$sales = $conn->query("SELECT s.*, p.ticket_pnr, p.source, p.destination, p.mode FROM sales s JOIN purchases p ON s.purchase_id = p.purchase_id WHERE s.customer_id = $id ORDER BY s.sales_id DESC");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Customer Details</h2><p>CUST-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></p></div>
        <div class="header-right">
            <a href="customer_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Edit</a>
            <a href="customers.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h3>Customer Information</h3></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="d-label">Customer ID</div><div class="d-value">CUST-<?php echo str_pad($customer['customer_id'], 4, '0', STR_PAD_LEFT); ?></div></div>
                    <div class="detail-item"><div class="d-label">Name</div><div class="d-value"><?php echo htmlspecialchars($customer['customer_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Contact</div><div class="d-value"><?php echo htmlspecialchars($customer['contact'] ?? 'N/A'); ?></div></div>
                    <div class="detail-item"><div class="d-label">Email</div><div class="d-value"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></div></div>
                    <div class="detail-item"><div class="d-label">Address</div><div class="d-value"><?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></div></div>
                    <div class="detail-item"><div class="d-label">Registered On</div><div class="d-value"><?php echo date('d M Y', strtotime($customer['created_at'])); ?></div></div>
                </div>
            </div>
        </div>

        <!-- Customer Orders -->
        <div class="card">
            <div class="card-header"><h3>Booking Orders</h3></div>
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead><tr><th>Order ID</th><th>Route</th><th>Mode</th><th>Journey Date</th><th>Cost</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while($o = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><a href="order_view.php?id=<?php echo $o['order_id']; ?>">#<?php echo $o['order_id']; ?></a></td>
                                <td><?php echo htmlspecialchars($o['source'] . ' → ' . $o['destination']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($o['mode']); ?>"><?php echo $o['mode']; ?></span></td>
                                <td><?php echo $o['journey_date']; ?></td>
                                <td>₹<?php echo number_format($o['order_cost'], 2); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($o['status']); ?>"><?php echo $o['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:20px;">No orders found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Sales -->
        <div class="card">
            <div class="card-header"><h3>Sales History</h3></div>
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead><tr><th>Sale ID</th><th>PNR</th><th>Route</th><th>Sales Date</th><th>Sales Cost</th><th>Commission</th></tr></thead>
                    <tbody>
                        <?php if ($sales->num_rows > 0): ?>
                            <?php while($s = $sales->fetch_assoc()): ?>
                            <tr>
                                <td><a href="sale_view.php?id=<?php echo $s['sales_id']; ?>">#<?php echo $s['sales_id']; ?></a></td>
                                <td><?php echo htmlspecialchars($s['ticket_pnr'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['source'] . ' → ' . $s['destination']); ?></td>
                                <td><?php echo $s['sales_date']; ?></td>
                                <td>₹<?php echo number_format($s['sales_cost'], 2); ?></td>
                                <td>₹<?php echo number_format($s['commission'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:20px;">No sales found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>