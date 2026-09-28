<?php
require_once 'config/database.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, username, password, role, status) VALUES (?, ?, ?, 'admin', 'active')");
$name = 'Admin User';
$username = 'admin@admin.com';
$stmt->bind_param("sss", $name, $username, $hash);
$stmt->execute();

echo "Admin created successfully!<br>";
echo "Username: admin@admin.com<br>";
echo "Password: admin123<br>";
echo "<br><b>DELETE THIS FILE AFTER USE!</b>";
?>