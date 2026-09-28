<?php
// vendors.php
$pageTitle = "Vendor Management";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$search = $_GET['search'] ?? '';
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = " WHERE vendor_name LIKE '%$s%' OR contact LIKE '%$s%' OR email LIKE '%$s%' ";
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as c FROM vendors $where")->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$vendors = $conn->query("SELECT * FROM vendors $where ORDER BY vendor_id DESC LIMIT $limit OFFSET $offset");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left">
            <h2>Vendor Management</h2>
            <p>Manage ticket vendors / providers</p>
        </div>
        <div class="header-right">
            <a href="vendor_add.php" class="btn btn-primary">➕ Add Vendor</a>
        </div>
    </div>

    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>All Vendors (<?php echo $total; ?>)</h3>
                <form method="GET" class="filter-bar" style="margin:0">
                    <input type="text" name="search" class="form-control" placeholder="Search vendors..."
                           value="<?php echo htmlspecialchars($search); ?>" style="max-width:250px">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="vendors.php" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vendor Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($vendors->num_rows > 0): ?>
                                <?php while($row = $vendors->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['vendor_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['vendor_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="vendor_edit.php?id=<?php echo $row['vendor_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="vendor_delete.php?id=<?php echo $row['vendor_id']; ?>" class="btn btn-sm btn-danger btn-delete">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="empty-state">No vendors found.</td></tr>
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