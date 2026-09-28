<?php
// sale_add.php
$pageTitle = "Sell Ticket";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Get purchased tickets not yet sold
$availablePurchases = $conn->query("
    SELECT p.*, v.vendor_name, o.customer_id, c.customer_name
    FROM purchases p
    JOIN vendors v ON p.vendor_id = v.vendor_id
    JOIN orders o ON p.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE p.status = 'Purchased'
    AND p.purchase_id NOT IN (SELECT purchase_id FROM sales WHERE status = 'Completed')
    ORDER BY p.purchase_id DESC
");

// Get commission percentage
$commPct = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'commission_percentage'")->fetch_assoc()['setting_value'] ?? 10;

$preselectedPurchase = (int)($_GET['purchase_id'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_id = (int)$_POST['purchase_id'];
    $order_id = (int)$_POST['order_id'];
    $customer_id = (int)$_POST['customer_id'];
    $commission = floatval($_POST['commission'] ?? 0);
    $sales_cost = floatval($_POST['sales_cost'] ?? 0);
    $sales_date = date('Y-m-d');

    if ($purchase_id <= 0) $errors[] = "Select a purchased ticket.";
    if ($customer_id <= 0) $errors[] = "Customer is required.";
    if ($sales_cost <= 0) $errors[] = "Sales cost must be positive.";

    // Check not already sold
    if ($purchase_id > 0) {
        $soldCheck = $conn->query("SELECT COUNT(*) as c FROM sales WHERE purchase_id = $purchase_id AND status = 'Completed'");
        if ($soldCheck->fetch_assoc()['c'] > 0) {
            $errors[] = "This ticket has already been sold.";
        }
    }

    if (empty($errors)) {
        // Get purchase cost
        $pData = $conn->query("SELECT purchase_cost FROM purchases WHERE purchase_id = $purchase_id")->fetch_assoc();
        $purchase_cost = $pData['purchase_cost'];

        $created_by = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO sales (purchase_id, order_id, customer_id, sales_date, purchase_cost, commission, sales_cost, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, 'Completed', ?)");
        $stmt->bind_param("iiisdddi", $purchase_id, $order_id, $customer_id, $sales_date, $purchase_cost, $commission, $sales_cost, $created_by);

        if ($stmt->execute()) {
            $sales_id = $stmt->insert_id;
            $stmt->close();

            // Copy purchase passengers to sales passengers
            $pp = $conn->query("SELECT * FROM purchase_passengers WHERE purchase_id = $purchase_id");
            $spStmt = $conn->prepare("INSERT INTO sales_passengers (sales_id, passenger_name, age, sex, seat, food_preference, senior_citizen, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            while ($sp = $pp->fetch_assoc()) {
                $spStmt->bind_param("isisssss", $sales_id, $sp['passenger_name'], $sp['age'], $sp['sex'], $sp['seat'], $sp['food_preference'], $sp['senior_citizen'], $sp['doctor']);
                $spStmt->execute();
            }
            $spStmt->close();

            // Update order status
            $conn->query("UPDATE orders SET status = 'Sold' WHERE order_id = $order_id");

            setFlash('success', 'Ticket sold successfully! Sale ID: #' . $sales_id . ' | Commission: ₹' . number_format($commission, 2));
            header("Location: sales.php");
            exit();
        } else {
            $errors[] = "Error: " . $conn->error;
        }
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Sell Ticket</h2><p>Sell/forward purchased ticket to customer</p></div>
        <div class="header-right"><a href="sales.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>

        <form method="POST" id="sale_form">
            <div class="card">
                <div class="card-header"><h3>Sale Details</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Purchased Ticket <span class="required">*</span></label>
                            <select name="purchase_id" id="purchase_id" class="form-control" required>
                                <option value="">-- Select Purchased Ticket --</option>
                                <?php while($p = $availablePurchases->fetch_assoc()): ?>
                                    <option value="<?php echo $p['purchase_id']; ?>" <?php echo $preselectedPurchase == $p['purchase_id'] ? 'selected' : ''; ?>>
                                        Purchase #<?php echo $p['purchase_id'] . ' | PNR: ' . ($p['ticket_pnr'] ?? 'N/A') . ' | ' . htmlspecialchars($p['customer_name']) . ' | ₹' . number_format($p['purchase_cost'], 2); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Order ID</label>
                            <input type="text" id="order_id_display" class="form-control" readonly style="background:#f1f5f9;">
                            <input type="hidden" name="order_id" id="order_id">
                        </div>
                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" id="customer_display" class="form-control" readonly style="background:#f1f5f9;">
                            <input type="hidden" name="customer_id" id="customer_id">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Vendor</label>
                            <input type="text" id="vendor_display" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                        <div class="form-group">
                            <label>Journey</label>
                            <input type="text" id="journey_display" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>PNR Number</label>
                            <input type="text" id="pnr_display" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                    </div>

                    <hr style="margin:20px 0; border-color:var(--border);">
                    <h4 style="margin-bottom:15px;">💰 Commission & Sales Calculation</h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Purchase Cost (₹)</label>
                            <input type="number" id="purchase_cost_display" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                        <div class="form-group">
                            <label>Commission % </label>
                            <input type="number" id="commission_pct" class="form-control" value="<?php echo $commPct; ?>" step="0.1" min="0" max="100">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Commission Amount (₹)</label>
                            <input type="number" name="commission" id="commission" class="form-control" step="0.01" readonly style="background:#dcfce7;">
                        </div>
                        <div class="form-group">
                            <label>Sales Cost (₹) <span class="required">*</span></label>
                            <input type="number" name="sales_cost" id="sales_cost" class="form-control" step="0.01" readonly style="background:#dbeafe; font-weight:700; font-size:16px;">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success" style="margin-top:10px;">💰 Confirm Sale</button>
            <a href="sales.php" class="btn btn-secondary" style="margin-top:10px;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>