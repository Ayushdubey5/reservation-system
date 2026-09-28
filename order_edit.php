<?php
// order_edit.php
$pageTitle = "Edit Order";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: orders.php"); exit(); }

$order = $conn->query("SELECT * FROM orders WHERE order_id = $id")->fetch_assoc();
if (!$order) { setFlash('danger', 'Order not found.'); header("Location: orders.php"); exit(); }
if ($order['status'] !== 'Pending') { setFlash('danger', 'Only pending orders can be edited.'); header("Location: orders.php"); exit(); }

$customers = $conn->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order['customer_id'] = (int)$_POST['customer_id'];
    $order['source'] = trim($_POST['source']);
    $order['destination'] = trim($_POST['destination']);
    $order['mode'] = $_POST['mode'];
    $order['journey_date'] = $_POST['journey_date'];
    $order['order_cost'] = floatval($_POST['order_cost']);
    $order['status'] = $_POST['status'];

    $p_names = $_POST['p_name'] ?? [];
    $p_ages = $_POST['p_age'] ?? [];
    $p_sexes = $_POST['p_sex'] ?? [];
    $p_seats = $_POST['p_seat'] ?? [];
    $p_foods = $_POST['p_food'] ?? [];
    $p_seniors = $_POST['p_senior'] ?? [];
    $p_doctors = $_POST['p_doctor'] ?? [];

    if ($order['customer_id'] <= 0) $errors[] = "Select a customer.";
    if (empty($order['source'])) $errors[] = "Source is required.";
    if (empty($order['destination'])) $errors[] = "Destination is required.";
    if ($order['order_cost'] < 0) $errors[] = "Cost cannot be negative.";

    if (empty($errors)) {
        $passenger_count = count(array_filter($p_names, function($n) { return !empty(trim($n)); }));

        $stmt = $conn->prepare("UPDATE orders SET customer_id=?, source=?, destination=?, mode=?, journey_date=?, passenger_count=?, order_cost=?, status=? WHERE order_id=?");
        $stmt->bind_param("issssidsi", $order['customer_id'], $order['source'], $order['destination'], $order['mode'], $order['journey_date'], $passenger_count, $order['order_cost'], $order['status'], $id);

        if ($stmt->execute()) {
            // Delete old passengers, insert new
            $conn->query("DELETE FROM order_passengers WHERE order_id = $id");

            $pStmt = $conn->prepare("INSERT INTO order_passengers (order_id, passenger_name, age, sex, seat, food_preference, senior_citizen, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            for ($i = 0; $i < count($p_names); $i++) {
                if (!empty(trim($p_names[$i]))) {
                    $pName = trim($p_names[$i]);
                    $pAge = (int)$p_ages[$i];
                    $pSex = $p_sexes[$i] ?? 'Male';
                    $pSeat = trim($p_seats[$i] ?? '');
                    $pFood = $p_foods[$i] ?? 'No Food';
                    $pSenior = $p_seniors[$i] ?? 'No';
                    $pDoctor = $p_doctors[$i] ?? 'No';
                    $pStmt->bind_param("isisssss", $id, $pName, $pAge, $pSex, $pSeat, $pFood, $pSenior, $pDoctor);
                    $pStmt->execute();
                }
            }
            $pStmt->close();

            setFlash('success', 'Order updated successfully!');
            header("Location: orders.php");
            exit();
        }
        $stmt->close();
    }
}

$orderPassengers = $conn->query("SELECT * FROM order_passengers WHERE order_id = $id");
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Edit Order #<?php echo $id; ?></h2></div>
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
                                <option value="">-- Select --</option>
                                <?php while($c = $customers->fetch_assoc()): ?>
                                    <option value="<?php echo $c['customer_id']; ?>" <?php echo $order['customer_id'] == $c['customer_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['customer_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mode <span class="required">*</span></label>
                            <select name="mode" class="form-control" required>
                                <option value="Bus" <?php echo $order['mode'] == 'Bus' ? 'selected' : ''; ?>>Bus</option>
                                <option value="Railway" <?php echo $order['mode'] == 'Railway' ? 'selected' : ''; ?>>Railway</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Source <span class="required">*</span></label>
                            <input type="text" name="source" class="form-control" value="<?php echo htmlspecialchars($order['source']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Destination <span class="required">*</span></label>
                            <input type="text" name="destination" class="form-control" value="<?php echo htmlspecialchars($order['destination']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Journey Date <span class="required">*</span></label>
                            <input type="date" name="journey_date" class="form-control" value="<?php echo $order['journey_date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Order Cost (₹)</label>
                            <input type="number" name="order_cost" class="form-control" step="0.01" min="0" value="<?php echo $order['order_cost']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <?php foreach(['Pending','Processing','Cancelled'] as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo $order['status'] == $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" id="passenger_count" name="passenger_count" value="<?php echo $order['passenger_count']; ?>">
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
                            <?php $i = 1; while($p = $orderPassengers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><input type="text" name="p_name[]" value="<?php echo htmlspecialchars($p['passenger_name']); ?>" required></td>
                                <td><input type="number" name="p_age[]" value="<?php echo $p['age']; ?>" min="1" max="120" required style="width:60px"></td>
                                <td><select name="p_sex[]">
                                    <option value="Male" <?php echo $p['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $p['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $p['sex'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select></td>
                                <td><input type="text" name="p_seat[]" value="<?php echo htmlspecialchars($p['seat']); ?>" style="width:70px"></td>
                                <td><select name="p_food[]">
                                    <option value="No Food" <?php echo $p['food_preference'] == 'No Food' ? 'selected' : ''; ?>>No Food</option>
                                    <option value="Veg" <?php echo $p['food_preference'] == 'Veg' ? 'selected' : ''; ?>>Veg</option>
                                    <option value="Non-Veg" <?php echo $p['food_preference'] == 'Non-Veg' ? 'selected' : ''; ?>>Non-Veg</option>
                                </select></td>
                                <td><select name="p_senior[]"><option value="No" <?php echo $p['senior_citizen'] == 'No' ? 'selected' : ''; ?>>No</option><option value="Yes" <?php echo $p['senior_citizen'] == 'Yes' ? 'selected' : ''; ?>>Yes</option></select></td>
                                <td><select name="p_doctor[]"><option value="No" <?php echo $p['doctor'] == 'No' ? 'selected' : ''; ?>>No</option><option value="Yes" <?php echo $p['doctor'] == 'Yes' ? 'selected' : ''; ?>>Yes</option></select></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-passenger">✕</button></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">💾 Update Order</button>
            <a href="orders.php" class="btn btn-secondary" style="margin-top:10px;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>