<?php
// sales.php
$pageTitle = "Sales History";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as c FROM sales")->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$sales = $conn->query("
    SELECT s.*, c.customer_name, p.ticket_pnr, p.source, p.destination, p.mode, p.journey_date, v.vendor_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.customer_id
    JOIN purchases p ON s.purchase_id = p.purchase_id
    JOIN vendors v ON p.vendor_id = v.vendor_id
    ORDER BY s.sales_id DESC LIMIT $limit OFFSET $offset
");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Sales History</h2><p>View all ticket sales</p></div>
        <div class="header-right"><a href="sale_add.php" class="btn btn-success">💰 Sell Ticket</a></div>
    </div>
    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>All Sales (<?php echo $total; ?>)</h3></div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>PNR</th>
                                <th>Route</th>
                                <th>Mode</th>
                                <th>Purchase Cost</th>
                                <th>Commission</th>
                                <th>Sales Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sales->num_rows > 0): ?>
                                <?php while($row = $sales->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['sales_id']; ?></td>
                                    <td><?php echo $row['sales_date']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ticket_pnr'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['source'] . ' → ' . $row['destination']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['mode']); ?>"><?php echo $row['mode']; ?></span></td>
                                    <td>₹<?php echo number_format($row['purchase_cost'], 2); ?></td>
                                    <td>₹<?php echo number_format($row['commission'], 2); ?></td>
                                    <td><strong>₹<?php echo number_format($row['sales_cost'], 2); ?></strong></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td><a href="sale_view.php?id=<?php echo $row['sales_id']; ?>" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="empty-state">No sales found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>