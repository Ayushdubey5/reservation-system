<?php
// users.php - User List
$pageTitle = "User Management";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Search
$search = $_GET['search'] ?? '';
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = " WHERE name LIKE '%$s%' OR username LIKE '%$s%' OR email LIKE '%$s%' ";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) as c FROM users $where");
$total = $totalResult->fetch_assoc()['c'];
$totalPages = ceil($total / $limit);

$users = $conn->query("SELECT * FROM users $where ORDER BY user_id DESC LIMIT $limit OFFSET $offset");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left">
            <h2>User Management</h2>
            <p>Manage system users and their roles</p>
        </div>
        <div class="header-right">
            <a href="user_add.php" class="btn btn-primary">➕ Add User</a>
        </div>
    </div>

    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>All Users (<?php echo $total; ?>)</h3>
                <form method="GET" class="filter-bar" style="margin:0">
                    <input type="text" name="search" class="form-control" placeholder="Search users..."
                           value="<?php echo htmlspecialchars($search); ?>" style="max-width:250px">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="users.php" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows > 0): ?>
                                <?php while($row = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['user_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><span class="badge badge-<?php echo $row['role'] == 'Admin' ? 'purchased' : 'processing'; ?>"><?php echo $row['role']; ?></span></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="user_edit.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="user_delete.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-danger btn-delete">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="empty-state">No users found.</td></tr>
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