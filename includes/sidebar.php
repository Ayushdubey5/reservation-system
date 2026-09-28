<?php
// includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="s-logo">🚌</div>
        <div class="s-title">
            <h2>Reservation</h2>
            <p>Management System</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-section">
            <a href="dashboard.php" class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
        </div>

        <!-- Masters -->
        <div class="nav-section">
            <div class="nav-section-title">Masters</div>
            <a href="users.php" class="nav-link <?php echo in_array($currentPage, ['users.php','user_add.php','user_edit.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Users
            </a>
            <a href="vendors.php" class="nav-link <?php echo in_array($currentPage, ['vendors.php','vendor_add.php','vendor_edit.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">🏢</span> Vendors
            </a>
            <a href="customers.php" class="nav-link <?php echo in_array($currentPage, ['customers.php','customer_add.php','customer_edit.php','customer_view.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Customers
            </a>
        </div>

        <!-- Reservations -->
        <div class="nav-section">
            <div class="nav-section-title">Reservations</div>
            <a href="order_add.php" class="nav-link <?php echo $currentPage == 'order_add.php' ? 'active' : ''; ?>">
                <span class="nav-icon">➕</span> New Order
            </a>
            <a href="orders.php" class="nav-link <?php echo in_array($currentPage, ['orders.php','order_view.php','order_edit.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> Orders
            </a>
        </div>

        <!-- Ticket Operations -->
        <div class="nav-section">
            <div class="nav-section-title">Ticket Operations</div>
            <a href="purchase_add.php" class="nav-link <?php echo $currentPage == 'purchase_add.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🎫</span> Purchase Ticket
            </a>
            <a href="purchases.php" class="nav-link <?php echo in_array($currentPage, ['purchases.php','purchase_view.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span> Purchased Tickets
            </a>
            <a href="sale_add.php" class="nav-link <?php echo $currentPage == 'sale_add.php' ? 'active' : ''; ?>">
                <span class="nav-icon">💰</span> Sell Ticket
            </a>
            <a href="sales.php" class="nav-link <?php echo in_array($currentPage, ['sales.php','sale_view.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Sales History
            </a>
        </div>

        <!-- Reports -->
        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <a href="report_customers.php" class="nav-link <?php echo $currentPage == 'report_customers.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📄</span> Customer Report
            </a>
            <a href="report_orders.php" class="nav-link <?php echo $currentPage == 'report_orders.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📄</span> Order Report
            </a>
            <a href="report_purchases.php" class="nav-link <?php echo $currentPage == 'report_purchases.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📄</span> Purchase Report
            </a>
            <a href="report_sales.php" class="nav-link <?php echo $currentPage == 'report_sales.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📄</span> Sales Report
            </a>
        </div>

        <!-- Account -->
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="profile.php" class="nav-link <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span> Profile
            </a>
            <a href="change_password.php" class="nav-link <?php echo $currentPage == 'change_password.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🔒</span> Change Password
            </a>
            <a href="logout.php" class="nav-link">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </div>
    </nav>
</aside>