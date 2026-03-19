<?php
// functions.php

function db_connect() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli("localhost", "root", "", "user_db", 3306);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}

function check_session($role = null) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    if ($role && $_SESSION['role'] != $role) {
        if ($_SESSION['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit;
    }
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// --- NEW FUNCTION ADDED HERE ---
function get_user($id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function increment_login_attempts($user_id) {
    $conn = db_connect();
    $stmt = $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare("SELECT login_attempts FROM users WHERE id=?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    if ($result['login_attempts'] >= 3) {
        $stmt3 = $conn->prepare("UPDATE users SET status='inactive' WHERE id=?");
        $stmt3->bind_param("i", $user_id);
        $stmt3->execute();
        $stmt3->close();
    }
}

function reset_login_attempts($user_id) {
    $conn = db_connect();
    $stmt = $conn->prepare("UPDATE users SET login_attempts=0 WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

function get_users($search = "") {
    $conn = db_connect();
    if ($search) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?");
        $like = "%$search%";
        $stmt->bind_param("sss", $like, $like, $like);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function user_exists($email) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}
?>