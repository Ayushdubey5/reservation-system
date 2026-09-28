<?php
// dashboard.php
$pageTitle = "Dashboard";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Fetch stats
$totalCustomers = $conn->query("SELECT COUNT(*) as c FROM customers")->fetch_assoc()['c'];
$totalVendors = $conn->query("SELECT COUNT(*) as c FROM vendors WHERE status='Active'")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$pendingOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
$totalPurchases = $conn->query("SELECT COUNT(*) as c FROM purchases")->fetch_assoc()['c'];
$totalSales = $conn->query("SELECT COUNT(*) as c FROM sales")->fetch_assoc()['c'];

// Recent orders
$recentOrders = $conn->query("SELECT o.*, c.customer_name FROM orders o JOIN customers c ON o.customer_id = c.customer_id ORDER BY o.order_id DESC LIMIT 5");

// Recent purchases
$recentPurchases = $conn->query("SELECT p.*, v.vendor_name FROM purchases p JOIN vendors v ON p.vendor_id = v.vendor_id ORDER BY p.purchase_id DESC LIMIT 5");

// Recent sales
$recentSales = $conn->query("SELECT s.*, c.customer_name FROM sales s JOIN customers c ON s.customer_id = c.customer_id ORDER BY s.sales_id DESC LIMIT 5");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left">
            <h2>Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        <div class="header-right">
            <div class="header-user">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
        </div>
    </div>

    <div class="page-content">
        <?php
        $flash = getFlash();
        if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blue">👥</div>
                </div>
                <div class="stat-label">Total Customers</div>
                <div class="stat-value"><?php echo $totalCustomers; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">🏢</div>
                </div>
                <div class="stat-label">Total Vendors</div>
                <div class="stat-value"><?php echo $totalVendors; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon orange">📋</div>
                </div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $totalOrders; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon red">⏳</div>
                </div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo $pendingOrders; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon cyan">🎫</div>
                </div>
                <div class="stat-label">Total Purchases</div>
                <div class="stat-value"><?php echo $totalPurchases; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">💰</div>
                </div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value"><?php echo $totalSales; ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header"><h3>Quick Actions</h3></div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="customer_add.php" class="quick-action-btn">
                        <div class="qa-icon">👤</div>
                        <span>New Customer</span>
                    </a>
                    <a href="order_add.php" class="quick-action-btn">
                        <div class="qa-icon">📝</div>
                        <span>New Order</span>
                    </a>
                    <a href="purchase_add.php" class="quick-action-btn">
                        <div class="qa-icon">🎫</div>
                        <span>Purchase Ticket</span>
                    </a>
                    <a href="sale_add.php" class="quick-action-btn">
                        <div class="qa-icon">💰</div>
                        <span>Sell Ticket</span>
                    </a>
                    <a href="report_sales.php" class="quick-action-btn">
                        <div class="qa-icon">📊</div>
                        <span>Reports</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Data -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body" style="padding:0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Route</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentOrders->num_rows > 0): ?>
                                    <?php while($row = $recentOrders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['source'] . ' → ' . $row['destination']); ?></td>
                                        <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--secondary);">No orders yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Purchases</h3>
                    <a href="purchases.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body" style="padding:0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Vendor</th>
                                    <th>PNR</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentPurchases->num_rows > 0): ?>
                                    <?php while($row = $recentPurchases->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['purchase_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['vendor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ticket_pnr'] ?? 'N/A'); ?></td>
                                        <td>₹<?php echo number_format($row['purchase_cost'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--secondary);">No purchases yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="card" style="margin-top:25px;">
            <div class="card-header">
                <h3>Recent Sales</h3>
                <a href="sales.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Sales Date</th>
                                <th>Purchase Cost</th>
                                <th>Commission</th>
                                <th>Sales Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentSales->num_rows > 0): ?>
                                <?php while($row = $recentSales->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['sales_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo $row['sales_date']; ?></td>
                                    <td>₹<?php echo number_format($row['purchase_cost'], 2); ?></td>
                                    <td>₹<?php echo number_format($row['commission'], 2); ?></td>
                                    <td>₹<?php echo number_format($row['sales_cost'], 2); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--secondary);">No sales yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>