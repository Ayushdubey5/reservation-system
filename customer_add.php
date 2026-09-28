<?php
// customer_add.php
$pageTitle = "Add Customer";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$errors = [];
$data = ['customer_name' => '', 'address' => '', 'contact' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['customer_name'] = trim($_POST['customer_name'] ?? '');
    $data['address'] = trim($_POST['address'] ?? '');
    $data['contact'] = trim($_POST['contact'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');

    if (empty($data['customer_name'])) $errors[] = "Customer name is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO customers (customer_name, address, contact, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['customer_name'], $data['address'], $data['contact'], $data['email']);
        if ($stmt->execute()) {
            setFlash('success', 'Customer added successfully! Customer ID: CUST-' . str_pad($stmt->insert_id, 4, '0', STR_PAD_LEFT));
            header("Location: customers.php");
            exit();
        } else {
            $errors[] = "Error adding customer.";
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Add New Customer</h2><p>Register a new customer</p></div>
        <div class="header-right"><a href="customers.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h3>Customer Information</h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Customer Name <span class="required">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($data['customer_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($data['contact']); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['email']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($data['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Customer</button>
                    <a href="customers.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>