<?php
session_start();

// Set logout message BEFORE destroying session
$_SESSION = [];
session_destroy();

// Start fresh session only for message
session_start();
$_SESSION['logout_message'] = "You have been logged out successfully.";

// Redirect to login
header("Location: login.php");
exit;