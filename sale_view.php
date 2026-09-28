<?php
// sale_view.php
$pageTitle = "View Sale";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: sales.php"); exit(); }

$sale = $conn->query("
    SELECT s.*, c.customer_name, c.contact as customer_contact,
    p.ticket_pnr, p.source, p.destination, p.mode, p.journey_date, p.vendor_id,
    v.vendor_name, u.name as created_by_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.customer_id
    JOIN purchases p ON s.purchase_id = p.purchase_id
    JOIN vendors v ON p.vendor_id = v.vendor_id
    JOIN users u ON s.created_by = u.user_id
    WHERE s.sales_id = $id
")->fetch_assoc();

if (!$sale) { setFlash('danger', 'Sale not found.'); header("Location: sales.php"); exit(); }

$passengers = $conn->query("SELECT * FROM sales_passengers WHERE sales_id = $id");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Sale #<?php echo $id; ?></h2></div>
        <div class="header-right">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
            <a href="sales.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h3>Sale Information</h3><span class="badge badge-<?php echo strtolower($sale['status']); ?>"><?php echo $sale['status']; ?></span></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="d-label">Sale ID</div><div class="d-value">#<?php echo $sale['sales_id']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Sale Date</div><div class="d-value"><?php echo $sale['sales_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">Order ID</div><div class="d-value"><a href="order_view.php?id=<?php echo $sale['order_id']; ?>">#<?php echo $sale['order_id']; ?></a></div></div>
                    <div class="detail-item"><div class="d-label">Purchase ID</div><div class="d-value"><a href="purchase_view.php?id=<?php echo $sale['purchase_id']; ?>">#<?php echo $sale['purchase_id']; ?></a></div></div>
                    <div class="detail-item"><div class="d-label">Customer</div><div class="d-value"><?php echo htmlspecialchars($sale['customer_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Vendor</div><div class="d-value"><?php echo htmlspecialchars($sale['vendor_name']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Route</div><div class="d-value"><?php echo htmlspecialchars($sale['source'] . ' → ' . $sale['destination']); ?></div></div>
                    <div class="detail-item"><div class="d-label">Mode</div><div class="d-value"><span class="badge badge-<?php echo strtolower($sale['mode']); ?>"><?php echo $sale['mode']; ?></span></div></div>
                    <div class="detail-item"><div class="d-label">Journey Date</div><div class="d-value"><?php echo $sale['journey_date']; ?></div></div>
                    <div class="detail-item"><div class="d-label">PNR</div><div class="d-value"><strong><?php echo htmlspecialchars($sale['ticket_pnr'] ?? 'N/A'); ?></strong></div></div>
                </div>

                <hr style="margin:25px 0; border-color:var(--border);">

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="d-label">Purchase Cost</div>
                        <div class="d-value" style="font-size:18px;">₹<?php echo number_format($sale['purchase_cost'], 2); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Commission</div>
                        <div class="d-value" style="font-size:18px; color:var(--success);">+ ₹<?php echo number_format($sale['commission'], 2); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Total Sales Cost</div>
                        <div class="d-value" style="font-size:22px; color:var(--primary); font-weight:700;">₹<?php echo number_format($sale['sales_cost'], 2); ?></div>
                    </div>
                    <div class="detail-item"><div class="d-label">Processed By</div><div class="d-value"><?php echo htmlspecialchars($sale['created_by_name']); ?></div></div>
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