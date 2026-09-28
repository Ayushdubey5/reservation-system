<?php
// purchases.php
$pageTitle = "Purchased Tickets";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as c FROM purchases")->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$purchases = $conn->query("
    SELECT p.*, v.vendor_name, o.customer_id, c.customer_name
    FROM purchases p
    JOIN vendors v ON p.vendor_id = v.vendor_id
    JOIN orders o ON p.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY p.purchase_id DESC LIMIT $limit OFFSET $offset
");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Purchased Tickets</h2><p>View all purchased tickets</p></div>
        <div class="header-right"><a href="purchase_add.php" class="btn btn-primary">🎫 Purchase Ticket</a></div>
    </div>
    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>All Purchases (<?php echo $total; ?>)</h3></div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Purchase ID</th>
                                <th>Date</th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Vendor</th>
                                <th>Route</th>
                                <th>Mode</th>
                                <th>PNR</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($purchases->num_rows > 0): ?>
                                <?php while($row = $purchases->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['purchase_id']; ?></td>
                                    <td><?php echo $row['purchase_date']; ?></td>
                                    <td><a href="order_view.php?id=<?php echo $row['order_id']; ?>">#<?php echo $row['order_id']; ?></a></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['vendor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['source'] . ' → ' . $row['destination']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['mode']); ?>"><?php echo $row['mode']; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['ticket_pnr'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($row['purchase_cost'], 2); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="purchase_view.php?id=<?php echo $row['purchase_id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <?php
                                            // Check if not yet sold
                                            $soldCheck = $conn->query("SELECT COUNT(*) as c FROM sales WHERE purchase_id = " . $row['purchase_id']);
                                            if ($soldCheck->fetch_assoc()['c'] == 0 && $row['status'] == 'Purchased'):
                                            ?>
                                                <a href="sale_add.php?purchase_id=<?php echo $row['purchase_id']; ?>" class="btn btn-sm btn-success">Sell</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="empty-state">No purchases found.</td></tr>
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