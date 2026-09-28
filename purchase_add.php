<?php
// purchase_add.php
$pageTitle = "Purchase Ticket";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Get orders that are Pending or Processing (not yet purchased)
$pendingOrders = $conn->query("
    SELECT o.*, c.customer_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.status IN ('Pending', 'Processing')
    AND o.order_id NOT IN (SELECT order_id FROM purchases WHERE status = 'Purchased')
    ORDER BY o.order_id DESC
");

$vendors = $conn->query("SELECT vendor_id, vendor_name FROM vendors WHERE status = 'Active' ORDER BY vendor_name");

$preselectedOrder = (int)($_GET['order_id'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $vendor_id = (int)$_POST['vendor_id'];
    $ticket_pnr = trim($_POST['ticket_pnr'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $mode = $_POST['mode'] ?? '';
    $journey_date = $_POST['journey_date'] ?? '';
    $purchase_cost = floatval($_POST['purchase_cost'] ?? 0);
    $purchase_date = date('Y-m-d');

    $p_names = $_POST['p_name'] ?? [];
    $p_ages = $_POST['p_age'] ?? [];
    $p_sexes = $_POST['p_sex'] ?? [];
    $p_seats = $_POST['p_seat'] ?? [];
    $p_foods = $_POST['p_food'] ?? [];
    $p_seniors = $_POST['p_senior'] ?? [];
    $p_doctors = $_POST['p_doctor'] ?? [];

    if ($order_id <= 0) $errors[] = "Please select an order.";
    if ($vendor_id <= 0) $errors[] = "Please select a vendor.";
    if (empty($source)) $errors[] = "Source is required.";
    if (empty($destination)) $errors[] = "Destination is required.";
    if ($purchase_cost <= 0) $errors[] = "Purchase cost must be greater than zero.";

    // Check if already purchased
    if ($order_id > 0) {
        $existCheck = $conn->query("SELECT COUNT(*) as c FROM purchases WHERE order_id = $order_id AND status = 'Purchased'");
        if ($existCheck->fetch_assoc()['c'] > 0) {
            $errors[] = "This order already has a purchased ticket.";
        }
    }

    if (empty($errors)) {
        $passenger_count = count(array_filter($p_names, function($n) { return !empty(trim($n)); }));
        $created_by = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO purchases (purchase_date, order_id, vendor_id, ticket_pnr, source, destination, mode, journey_date, passenger_count, purchase_cost, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Purchased', ?)");
        $stmt->bind_param("siisssssidi", $purchase_date, $order_id, $vendor_id, $ticket_pnr, $source, $destination, $mode, $journey_date, $passenger_count, $purchase_cost, $created_by);

        if ($stmt->execute()) {
            $purchase_id = $stmt->insert_id;
            $stmt->close();

            // Insert purchase passengers
            $pStmt = $conn->prepare("INSERT INTO purchase_passengers (purchase_id, passenger_name, age, sex, seat, food_preference, senior_citizen, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            for ($i = 0; $i < count($p_names); $i++) {
                if (!empty(trim($p_names[$i]))) {
                    $pName = trim($p_names[$i]);
                    $pAge = (int)$p_ages[$i];
                    $pSex = $p_sexes[$i] ?? 'Male';
                    $pSeat = trim($p_seats[$i] ?? '');
                    $pFood = $p_foods[$i] ?? 'No Food';
                    $pSenior = $p_seniors[$i] ?? 'No';
                    $pDoctor = $p_doctors[$i] ?? 'No';
                    $pStmt->bind_param("isisssss", $purchase_id, $pName, $pAge, $pSex, $pSeat, $pFood, $pSenior, $pDoctor);
                    $pStmt->execute();
                }
            }
            $pStmt->close();

            // Update order status
            $conn->query("UPDATE orders SET status = 'Purchased' WHERE order_id = $order_id");

            setFlash('success', 'Ticket purchased successfully! Purchase ID: #' . $purchase_id);
            header("Location: purchases.php");
            exit();
        } else {
            $errors[] = "Error: " . $conn->error;
        }
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Purchase Ticket</h2><p>Purchase ticket from vendor for a customer's order</p></div>
        <div class="header-right"><a href="purchases.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>

        <form method="POST" id="purchase_form">
            <div class="card">
                <div class="card-header"><h3>Purchase Details</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Order <span class="required">*</span></label>
                            <select name="order_id" id="order_id" class="form-control" required>
                                <option value="">-- Select Pending Order --</option>
                                <?php while($o = $pendingOrders->fetch_assoc()): ?>
                                    <option value="<?php echo $o['order_id']; ?>" <?php echo $preselectedOrder == $o['order_id'] ? 'selected' : ''; ?>>
                                        Order #<?php echo $o['order_id'] . ' - ' . htmlspecialchars($o['customer_name']) . ' (' . $o['source'] . ' → ' . $o['destination'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Vendor <span class="required">*</span></label>
                            <select name="vendor_id" class="form-control" required>
                                <option value="">-- Select Vendor --</option>
                                <?php while($v = $vendors->fetch_assoc()): ?>
                                    <option value="<?php echo $v['vendor_id']; ?>"><?php echo htmlspecialchars($v['vendor_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Source <span class="required">*</span></label>
                            <input type="text" name="source" id="source" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Destination <span class="required">*</span></label>
                            <input type="text" name="destination" id="destination" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Mode <span class="required">*</span></label>
                            <select name="mode" id="mode" class="form-control" required>
                                <option value="Bus">Bus</option>
                                <option value="Railway">Railway</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Journey Date <span class="required">*</span></label>
                            <input type="date" name="journey_date" id="journey_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ticket / PNR Number</label>
                            <input type="text" name="ticket_pnr" class="form-control" placeholder="Enter PNR or ticket number">
                        </div>
                        <div class="form-group">
                            <label>Purchase Cost (₹) <span class="required">*</span></label>
                            <input type="number" name="purchase_cost" class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <input type="hidden" id="passenger_count" name="passenger_count" value="1">
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Passenger Details</h3>
                    <button type="button" id="addPassenger" class="btn btn-sm btn-success">➕ Add Passenger</button>
                </div>
                <div class="card-body" style="padding:0; overflow-x:auto;">
                    <table class="passenger-table">
                        <thead><tr><th>#</th><th>Name *</th><th>Age *</th><th>Sex</th><th>Seat</th><th>Food</th><th>Senior</th><th>Doctor</th><th>Remove</th></tr></thead>
                        <tbody id="passengerBody">
                            <tr>
                                <td>1</td>
                                <td><input type="text" name="p_name[]" required></td>
                                <td><input type="number" name="p_age[]" min="1" max="120" required style="width:60px"></td>
                                <td><select name="p_sex[]"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></td>
                                <td><input type="text" name="p_seat[]" style="width:70px"></td>
                                <td><select name="p_food[]"><option value="No Food">No Food</option><option value="Veg">Veg</option><option value="Non-Veg">Non-Veg</option></select></td>
                                <td><select name="p_senior[]"><option value="No">No</option><option value="Yes">Yes</option></select></td>
                                <td><select name="p_doctor[]"><option value="No">No</option><option value="Yes">Yes</option></select></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-passenger">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:10px;">🎫 Purchase Ticket</button>
            <a href="purchases.php" class="btn btn-secondary" style="margin-top:10px;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>