<?php
// order_add.php
$pageTitle = "New Order";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$customers = $conn->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $source = trim($_POST['source'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $mode = $_POST['mode'] ?? '';
    $journey_date = $_POST['journey_date'] ?? '';
    $order_cost = floatval($_POST['order_cost'] ?? 0);
    $order_date = date('Y-m-d');

    // Passenger data
    $p_names = $_POST['p_name'] ?? [];
    $p_ages = $_POST['p_age'] ?? [];
    $p_sexes = $_POST['p_sex'] ?? [];
    $p_seats = $_POST['p_seat'] ?? [];
    $p_foods = $_POST['p_food'] ?? [];
    $p_seniors = $_POST['p_senior'] ?? [];
    $p_doctors = $_POST['p_doctor'] ?? [];

    if ($customer_id <= 0) $errors[] = "Please select a customer.";
    if (empty($source)) $errors[] = "Source is required.";
    if (empty($destination)) $errors[] = "Destination is required.";
    if (empty($mode)) $errors[] = "Mode of journey is required.";
    if (empty($journey_date)) $errors[] = "Journey date is required.";
    if ($journey_date < date('Y-m-d')) $errors[] = "Journey date cannot be in the past.";
    if ($order_cost < 0) $errors[] = "Order cost cannot be negative.";
    if (empty($p_names) || empty($p_names[0])) $errors[] = "At least one passenger is required.";

    if (empty($errors)) {
        $passenger_count = count($p_names);
        $created_by = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO orders (order_date, customer_id, source, destination, mode, journey_date, passenger_count, order_cost, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
        $stmt->bind_param("sisssssdi", $order_date, $customer_id, $source, $destination, $mode, $journey_date, $passenger_count, $order_cost, $created_by);

        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Insert passengers
            $pStmt = $conn->prepare("INSERT INTO order_passengers (order_id, passenger_name, age, sex, seat, food_preference, senior_citizen, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 0; $i < count($p_names); $i++) {
                if (!empty(trim($p_names[$i]))) {
                    $pName = trim($p_names[$i]);
                    $pAge = (int)($p_ages[$i] ?? 0);
                    $pSex = $p_sexes[$i] ?? 'Male';
                    $pSeat = trim($p_seats[$i] ?? '');
                    $pFood = $p_foods[$i] ?? 'No Food';
                    $pSenior = $p_seniors[$i] ?? 'No';
                    $pDoctor = $p_doctors[$i] ?? 'No';

                    $pStmt->bind_param("isissss", $order_id, $pName, $pAge, $pSex, $pSeat, $pFood, $pSenior);
                    // Fix: doctor is the 8th parameter
                    $pStmt = $conn->prepare("INSERT INTO order_passengers (order_id, passenger_name, age, sex, seat, food_preference, senior_citizen, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $pStmt->bind_param("isisssss", $order_id, $pName, $pAge, $pSex, $pSeat, $pFood, $pSenior, $pDoctor);
                    $pStmt->execute();
                }
            }
            $pStmt->close();

            setFlash('success', 'Order #' . $order_id . ' created successfully!');
            header("Location: orders.php");
            exit();
        } else {
            $errors[] = "Error creating order: " . $conn->error;
        }
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Create New Order</h2><p>Create a booking order for a customer</p></div>
        <div class="header-right"><a href="orders.php" class="btn btn-secondary">← Back</a></div>
    </div>

    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="card">
                <div class="card-header"><h3>Order Details</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Customer <span class="required">*</span></label>
                            <select name="customer_id" class="form-control" required>
                                <option value="">-- Select Customer --</option>
                                <?php while($c = $customers->fetch_assoc()): ?>
                                    <option value="<?php echo $c['customer_id']; ?>" <?php echo (isset($customer_id) && $customer_id == $c['customer_id']) ? 'selected' : ''; ?>>
                                        CUST-<?php echo str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT) . ' - ' . htmlspecialchars($c['customer_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mode of Journey <span class="required">*</span></label>
                            <select name="mode" class="form-control" required>
                                <option value="">-- Select Mode --</option>
                                <option value="Bus" <?php echo (isset($mode) && $mode == 'Bus') ? 'selected' : ''; ?>>Bus</option>
                                <option value="Railway" <?php echo (isset($mode) && $mode == 'Railway') ? 'selected' : ''; ?>>Railway</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Source <span class="required">*</span></label>
                            <input type="text" name="source" class="form-control" value="<?php echo htmlspecialchars($source ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Destination <span class="required">*</span></label>
                            <input type="text" name="destination" class="form-control" value="<?php echo htmlspecialchars($destination ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Journey Date <span class="required">*</span></label>
                            <input type="date" name="journey_date" class="form-control" value="<?php echo $journey_date ?? ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Expected Order Cost (₹) <span class="required">*</span></label>
                            <input type="number" name="order_cost" class="form-control" step="0.01" min="0" value="<?php echo $order_cost ?? 0; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Number of Passengers</label>
                        <input type="number" id="passenger_count" class="form-control" value="1" readonly style="max-width:100px; background:#f1f5f9;">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Passenger Details</h3>
                    <button type="button" id="addPassenger" class="btn btn-sm btn-success">➕ Add Passenger</button>
                </div>
                <div class="card-body" style="padding:0; overflow-x:auto;">
                    <table class="passenger-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name *</th>
                                <th>Age *</th>
                                <th>Sex</th>
                                <th>Seat</th>
                                <th>Food</th>
                                <th>Senior</th>
                                <th>Doctor</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
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

            <button type="submit" class="btn btn-primary" style="margin-top:10px;">💾 Create Order</button>
            <a href="orders.php" class="btn btn-secondary" style="margin-top:10px;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>