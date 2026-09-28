<?php
// customers.php
$pageTitle = "Customer Management";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$search = $_GET['search'] ?? '';
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = " WHERE customer_name LIKE '%$s%' OR contact LIKE '%$s%' OR email LIKE '%$s%' ";
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as c FROM customers $where")->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$customers = $conn->query("SELECT * FROM customers $where ORDER BY customer_id DESC LIMIT $limit OFFSET $offset");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Customer Management</h2><p>Manage customer records</p></div>
        <div class="header-right"><a href="customer_add.php" class="btn btn-primary">➕ Add Customer</a></div>
    </div>

    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>All Customers (<?php echo $total; ?>)</h3>
                <form method="GET" class="filter-bar" style="margin:0">
                    <input type="text" name="search" class="form-control" placeholder="Search customers..."
                           value="<?php echo htmlspecialchars($search); ?>" style="max-width:250px">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
                    <?php if (!empty($search)): ?><a href="customers.php" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
                </form>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Address</th><th>Contact</th><th>Email</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($customers->num_rows > 0): ?>
                                <?php while($row = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td>CUST-<?php echo str_pad($row['customer_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="customer_view.php?id=<?php echo $row['customer_id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="customer_edit.php?id=<?php echo $row['customer_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="customer_delete.php?id=<?php echo $row['customer_id']; ?>" class="btn btn-sm btn-danger btn-delete">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="empty-state">No customers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                           class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>