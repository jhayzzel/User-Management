<?php
require 'functions.php';
check_session('admin');

$conn = db_connect();

// Validate ID and Action
if (!isset($_GET['id']) || !is_numeric($_GET['id']) ||
    !isset($_GET['action'])) {
    die("Invalid request.");
}

$id = (int) $_GET['id'];
$action = $_GET['action'];

// Prevent admin from changing their own status
if ($id == $_SESSION['user_id']) {
    die("You cannot change your own status.");
}

// Validate action
if ($action === "activate") {
    $status = "active";
} elseif ($action === "deactivate") {
    $status = "inactive";
} else {
    die("Invalid action.");
}

// Update status safely
$stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit;
?>