<?php
// vendor_edit.php
$pageTitle = "Edit Vendor";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: vendors.php"); exit(); }

$vendor = $conn->query("SELECT * FROM vendors WHERE vendor_id = $id")->fetch_assoc();
if (!$vendor) { setFlash('danger', 'Vendor not found.'); header("Location: vendors.php"); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendor['vendor_name'] = trim($_POST['vendor_name'] ?? '');
    $vendor['address'] = trim($_POST['address'] ?? '');
    $vendor['contact'] = trim($_POST['contact'] ?? '');
    $vendor['email'] = trim($_POST['email'] ?? '');
    $vendor['status'] = $_POST['status'] ?? 'Active';

    if (empty($vendor['vendor_name'])) $errors[] = "Vendor name is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE vendors SET vendor_name=?, address=?, contact=?, email=?, status=? WHERE vendor_id=?");
        $stmt->bind_param("sssssi", $vendor['vendor_name'], $vendor['address'], $vendor['contact'], $vendor['email'], $vendor['status'], $id);
        if ($stmt->execute()) {
            setFlash('success', 'Vendor updated successfully!');
            header("Location: vendors.php");
            exit();
        } else {
            $errors[] = "Error updating vendor.";
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="top-header">
        <div class="header-left"><h2>Edit Vendor</h2><p>Update vendor information</p></div>
        <div class="header-right"><a href="vendors.php" class="btn btn-secondary">← Back</a></div>
    </div>
    <div class="page-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>⚠️ " . htmlspecialchars($e) . "</div>"; ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h3>Edit Vendor #<?php echo $id; ?></h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Vendor Name <span class="required">*</span></label>
                            <input type="text" name="vendor_name" class="form-control" value="<?php echo htmlspecialchars($vendor['vendor_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($vendor['contact']); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($vendor['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo $vendor['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $vendor['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($vendor['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Update Vendor</button>
                    <a href="vendors.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>