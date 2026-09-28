<?php
// report_orders.php
$pageTitle = "Order Report";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Filters
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$modeFilter = $_GET['mode'] ?? '';

$where = " WHERE 1=1 ";
if (!empty($dateFrom)) $where .= " AND o.order_date >= '" . $conn->real_escape_string($dateFrom) . "' ";
if (!empty($dateTo)) $where .= " AND o.order_date <= '" . $conn->real_escape_string($dateTo) . "' ";
if (!empty($statusFilter)) $where .= " AND o.status = '" . $conn->real_escape_string($statusFilter) . "' ";
if (!empty($modeFilter)) $where .= " AND o.mode = '" . $conn->real_escape_string($modeFilter) . "' ";

$orders = $conn->query("
    SELECT o.*, c.customer_name
    FROM orders o JOIN customers c ON o.customer_id = c.customer_id
    $where ORDER BY o.order_id DESC
");

// Summary
$summary = $conn->query("
    SELECT COUNT(*) as total, COALESCE(SUM(order_cost),0) as total_cost
    FROM orders o $where
")->fetch_assoc();
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Order Report</h2></div>
        <div class="header-right"><button onclick="window.print()" class="btn btn-primary">🖨️ Print</button></div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="filter-bar">
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>" style="max-width:160px" placeholder="From">
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>" style="max-width:160px" placeholder="To">
                    <select name="status" class="form-control" style="max-width:140px">
                        <option value="">All Status</option>
                        <?php foreach(['Pending','Processing','Purchased','Sold','Completed','Cancelled'] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $statusFilter == $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="mode" class="form-control" style="max-width:120px">
                        <option value="">All Modes</option>
                        <option value="Bus" <?php echo $modeFilter == 'Bus' ? 'selected' : ''; ?>>Bus</option>
                        <option value="Railway" <?php echo $modeFilter == 'Railway' ? 'selected' : ''; ?>>Railway</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                    <a href="report_orders.php" class="btn btn-secondary btn-sm">Clear</a>
                </form>
            </div>
        </div>

        <div class="report-summary">
            <div class="report-summary-item"><div class="rs-value"><?php echo $summary['total']; ?></div><div class="rs-label">Total Orders</div></div>
            <div class="report-summary-item"><div class="rs-value">₹<?php echo number_format($summary['total_cost'], 2); ?></div><div class="rs-label">Total Order Cost</div></div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Date</th><th>Customer</th><th>Source</th><th>Destination</th><th>Mode</th><th>Journey Date</th><th>Passengers</th><th>Cost</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['order_date']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['source']); ?></td>
                            <td><?php echo htmlspecialchars($row['destination']); ?></td>
                            <td><span class="badge badge-<?php echo strtolower($row['mode']); ?>"><?php echo $row['mode']; ?></span></td>
                            <td><?php echo $row['journey_date']; ?></td>
                            <td><?php echo $row['passenger_count']; ?></td>
                            <td>₹<?php echo number_format($row['order_cost'], 2); ?></td>
                            <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>