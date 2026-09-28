<?php
// report_sales.php
$pageTitle = "Sales Report";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$customerFilter = $_GET['customer_id'] ?? '';

$where = " WHERE 1=1 ";
if (!empty($dateFrom)) $where .= " AND s.sales_date >= '" . $conn->real_escape_string($dateFrom) . "' ";
if (!empty($dateTo)) $where .= " AND s.sales_date <= '" . $conn->real_escape_string($dateTo) . "' ";
if (!empty($customerFilter)) $where .= " AND s.customer_id = " . (int)$customerFilter;

$sales = $conn->query("
    SELECT s.*, c.customer_name, p.ticket_pnr, p.source, p.destination, p.mode, p.journey_date
    FROM sales s
    JOIN customers c ON s.customer_id = c.customer_id
    JOIN purchases p ON s.purchase_id = p.purchase_id
    $where ORDER BY s.sales_id DESC
");

$summary = $conn->query("
    SELECT COUNT(*) as total,
    COALESCE(SUM(s.purchase_cost),0) as total_purchase,
    COALESCE(SUM(s.commission),0) as total_commission,
    COALESCE(SUM(s.sales_cost),0) as total_sales
    FROM sales s $where
")->fetch_assoc();

$customersList = $conn->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Sales Report</h2></div>
        <div class="header-right"><button onclick="window.print()" class="btn btn-primary">🖨️ Print</button></div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="filter-bar">
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>" style="max-width:160px">
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>" style="max-width:160px">
                    <select name="customer_id" class="form-control" style="max-width:200px">
                        <option value="">All Customers</option>
                        <?php while($c = $customersList->fetch_assoc()): ?>
                            <option value="<?php echo $c['customer_id']; ?>" <?php echo $customerFilter == $c['customer_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['customer_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                    <a href="report_sales.php" class="btn btn-secondary btn-sm">Clear</a>
                </form>
            </div>
        </div>

        <div class="report-summary">
            <div class="report-summary-item"><div class="rs-value"><?php echo $summary['total']; ?></div><div class="rs-label">Total Sales</div></div>
            <div class="report-summary-item"><div class="rs-value">₹<?php echo number_format($summary['total_purchase'], 2); ?></div><div class="rs-label">Total Purchase Cost</div></div>
            <div class="report-summary-item"><div class="rs-value" style="color:var(--success);">₹<?php echo number_format($summary['total_commission'], 2); ?></div><div class="rs-label">Total Commission</div></div>
            <div class="report-summary-item"><div class="rs-value" style="color:var(--primary);">₹<?php echo number_format($summary['total_sales'], 2); ?></div><div class="rs-label">Total Sales Revenue</div></div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead>
                        <tr><th>Sale ID</th><th>Date</th><th>Customer</th><th>PNR</th><th>Route</th><th>Mode</th><th>Journey</th><th>Purchase Cost</th><th>Commission</th><th>Sales Cost</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $sales->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['sales_id']; ?></td>
                            <td><?php echo $row['sales_date']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['ticket_pnr'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['source'] . ' → ' . $row['destination']); ?></td>
                            <td><span class="badge badge-<?php echo strtolower($row['mode']); ?>"><?php echo $row['mode']; ?></span></td>
                            <td><?php echo $row['journey_date']; ?></td>
                            <td>₹<?php echo number_format($row['purchase_cost'], 2); ?></td>
                            <td>₹<?php echo number_format($row['commission'], 2); ?></td>
                            <td><strong>₹<?php echo number_format($row['sales_cost'], 2); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>