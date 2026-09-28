<?php
// purchase_view.php
$pageTitle = "View Purchase";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: purchases.php"); exit(); }

$purchase = $conn->query("
    SELECT p.*, v.vendor_name, v.contact as vendor_contact, o.customer_id, c.customer_name, u.name as created_by_name
    FROM purchases p
    JOIN vendors v ON p.vendor_id = v.vendor_id
    JOIN orders o ON p.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    JOIN users u ON p.created_by = u.user_id
    WHERE p.purchase_id = $id
")->fetch_assoc();

if (!$purchase) { setFlash('danger', 'Purchase not found.'); header("Location: purchases.php"); exit(); }

$passengers = $conn->query("SELECT * FROM purchase_passengers WHERE purchase_id = $id");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Purchase #<?php echo $id; ?></h2></div>
        <div class="header-right">
            <?php
            $soldCheck = $conn->query("SELECT COUNT(*) as c FROM sales WHERE purchase_id = $id");
            if ($soldCheck->fetch_assoc()['c'] == 0 && $purchase['status'] == 'Purchased'):
            ?>
                <a href="sale_add.php?purchase_id=<?php echo $id; ?>" class="btn btn-success">💰 Sell Ticket</a>
            <?php endif; ?>
            <a href="purchases.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h3>Purchase Information</h3><span class="badge badge-<?php echo strtolower($purchase['status']); ?>"><?php echo $purchase['status']; ?></span></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="d-label">Purchase ID</div><div class="d-value">#<?php echo $purchase['purchase_id']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Purchase Date</div><div class="d-value"><?php echo $purchase['purchase_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Order ID</div><div class="d-value"><a href="order_view.php?id=<?php echo $purchase['order_id']; ?>">#<?php echo $purchase['order_id']; ?></a></div></div>
                    <div class="detail-item"><div class="d-label">Customer</div><div class="d-value"><?php echo htmlspecialchars($purchase['customer_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Vendor</div><div class="d-value"><?php echo htmlspecialchars($purchase['vendor_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Source</div><div class="d-value"><?php echo htmlspecialchars($purchase['source']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Destination</div><div class="d-value"><?php echo htmlspecialchars($purchase['destination']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Mode</div><div class="d-value"><span class="badge badge-<?php echo strtolower($purchase['mode']); ?>"><?php echo $purchase['mode']; ?></span></div></div>
                    <div class="detail-item"><div class="d-label">Journey Date</div><div class="d-value"><?php echo $purchase['journey_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Ticket/PNR</div><div class="d-value"><strong><?php echo htmlspecialchars($purchase['ticket_pnr'] ?? 'N/A'); ?></strong></div></div>
                    <div class="detail-item"><div class="d-label">Purchase Cost</div><div class="d-value" style="color:var(--primary);font-size:18px;">₹<?php echo number_format($purchase['purchase_cost'], 2); ?></div></div>
                    <div class="detail-item"><div class="d-label">Processed By</div><div class="d-value"><?php echo htmlspecialchars($purchase['created_by_name']); ?></div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Passenger Details</h3></div>
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Name</th><th>Age</th><th>Sex</th><th>Seat</th><th>Food</th><th>Senior</th><th>Doctor</th></tr></thead>
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