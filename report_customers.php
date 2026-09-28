<?php
// report_customers.php
$pageTitle = "Customer Report";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$customers = $conn->query("
    SELECT c.*,
    (SELECT COUNT(*) FROM orders WHERE customer_id = c.customer_id) as total_orders,
    (SELECT COUNT(*) FROM sales WHERE customer_id = c.customer_id) as total_sales,
    (SELECT COALESCE(SUM(sales_cost), 0) FROM sales WHERE customer_id = c.customer_id) as total_sales_amount
    FROM customers c
    ORDER BY c.customer_id
");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Customer Report</h2><p>Overview of all customers and their transactions</p></div>
        <div class="header-right"><button onclick="window.print()" class="btn btn-primary">🖨️ Print</button></div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h3>Customer Report</h3></div>
            <div class="card-body" style="padding:0">
                <table class="data-table">
                    <thead>
                        <tr><th>Customer ID</th><th>Name</th><th>Contact</th><th>Email</th><th>Total Orders</th><th>Total Sales</th><th>Total Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td>CUST-<?php echo str_pad($row['customer_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><a href="customer_view.php?id=<?php echo $row['customer_id']; ?>"><?php echo htmlspecialchars($row['customer_name']); ?></a></td>
                            <td><?php echo htmlspecialchars($row['contact'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                            <td><?php echo $row['total_orders']; ?></td>
                            <td><?php echo $row['total_sales']; ?></td>
                            <td>₹<?php echo number_format($row['total_sales_amount'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>