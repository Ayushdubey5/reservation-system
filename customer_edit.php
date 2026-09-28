<?php
// customer_edit.php
$pageTitle = "Edit Customer";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: customers.php"); exit(); }

$customer = $conn->query("SELECT * FROM customers WHERE customer_id = $id")->fetch_assoc();
if (!$customer) { setFlash('danger', 'Customer not found.'); header("Location: customers.php"); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer['customer_name'] = trim($_POST['customer_name'] ?? '');
    $customer['address'] = trim($_POST['address'] ?? '');
    $customer['contact'] = trim($_POST['contact'] ?? '');
    $customer['email'] = trim($_POST['email'] ?? '');

    if (empty($customer['customer_name'])) $errors[] = "Customer name is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE customers SET customer_name=?, address=?, contact=?, email=? WHERE customer_id=?");
        $stmt->bind_param("ssssi", $customer['customer_name'], $customer['address'], $customer['contact'], $customer['email'], $id);
        if ($stmt->execute()) {
            setFlash('success', 'Customer updated successfully!');
            header("Location: customers.php");
            exit();
        } else {
            $errors[] = "Error updating customer.";
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Edit Customer</h2><p>Update customer information</p></div>
        <div class="header-right"><a href="customers.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h3>Edit Customer #CUST-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Customer Name <span class="required">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($customer['contact']); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Update Customer</button>
                    <a href="customers.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>