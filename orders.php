<?php
// orders.php
$pageTitle = "Orders";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$modeFilter = $_GET['mode'] ?? '';

$where = " WHERE 1=1 ";
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (c.customer_name LIKE '%$s%' OR o.source LIKE '%$s%' OR o.destination LIKE '%$s%') ";
}
if (!empty($statusFilter)) {
    $sf = $conn->real_escape_string($statusFilter);
    $where .= " AND o.status = '$sf' ";
}
if (!empty($modeFilter)) {
    $mf = $conn->real_escape_string($modeFilter);
    $where .= " AND o.mode = '$mf' ";
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as c FROM orders o JOIN customers c ON o.customer_id = c.customer_id $where")->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$orders = $conn->query("SELECT o.*, c.customer_name FROM orders o JOIN customers c ON o.customer_id = c.customer_id $where ORDER BY o.order_id DESC LIMIT $limit OFFSET $offset");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Order Management</h2><p>View and manage booking orders</p></div>
        <div class="header-right"><a href="order_add.php" class="btn btn-primary">➕ New Order</a></div>
    </div>

    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>All Orders (<?php echo $total; ?>)</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:200px">
                    <select name="status" class="form-control" style="max-width:150px">
                        <option value="">All Status</option>
                        <?php foreach(['Pending','Processing','Purchased','Sold','Completed','Cancelled'] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $statusFilter == $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="mode" class="form-control" style="max-width:150px">
                        <option value="">All Modes</option>
                        <option value="Bus" <?php echo $modeFilter == 'Bus' ? 'selected' : ''; ?>>Bus</option>
                        <option value="Railway" <?php echo $modeFilter == 'Railway' ? 'selected' : ''; ?>>Railway</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                    <a href="orders.php" class="btn btn-secondary btn-sm">Clear</a>
                </form>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Source</th>
                                <th>Destination</th>
                                <th>Mode</th>
                                <th>Journey Date</th>
                                <th>Passengers</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders->num_rows > 0): ?>
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
                                    <td>
                                        <div class="action-btns">
                                            <a href="order_view.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <?php if ($row['status'] == 'Pending'): ?>
                                                <a href="order_edit.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="empty-state">No orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&mode=<?php echo urlencode($modeFilter); ?>"
                           class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>