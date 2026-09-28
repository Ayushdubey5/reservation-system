<?php
// vendor_add.php
$pageTitle = "Add Vendor";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$errors = [];
$data = ['vendor_name' => '', 'address' => '', 'contact' => '', 'email' => '', 'status' => 'Active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['vendor_name'] = trim($_POST['vendor_name'] ?? '');
    $data['address'] = trim($_POST['address'] ?? '');
    $data['contact'] = trim($_POST['contact'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');
    $data['status'] = $_POST['status'] ?? 'Active';

    if (empty($data['vendor_name'])) $errors[] = "Vendor name is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO vendors (vendor_name, address, contact, email, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $data['vendor_name'], $data['address'], $data['contact'], $data['email'], $data['status']);
        if ($stmt->execute()) {
            setFlash('success', 'Vendor added successfully!');
            header("Location: vendors.php");
            exit();
        } else {
            $errors[] = "Error adding vendor.";
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Add New Vendor</h2><p>Register a new ticket vendor</p></div>
        <div class="header-right"><a href="vendors.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h3>Vendor Information</h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Vendor Name <span class="required">*</span></label>
                            <input type="text" name="vendor_name" class="form-control" value="<?php echo htmlspecialchars($data['vendor_name']); ?>" required>
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
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo $data['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $data['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($data['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Vendor</button>
                    <a href="vendors.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>