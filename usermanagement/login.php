<?php
require 'functions.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$success = "";
if (isset($_SESSION['logout_message'])) {
    $success = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$conn = db_connect();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Email and Password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, password, role, status, login_attempts 
                                FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($user['status'] == 'inactive') {
                $errors[] = "Your account is inactive. Contact admin.";
            } elseif (password_verify($password, $user['password'])) {
                $reset = $conn->prepare("UPDATE users SET login_attempts=0 WHERE id=?");
                $reset->bind_param("i", $user['id']);
                $reset->execute();

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php?t=" . time());
                } else {
                    header("Location: user_dashboard.php?t=" . time());
                }
                exit;
            } else {
                $attempts = $user['login_attempts'] + 1;
                if ($attempts >= 3) {
                    $lock = $conn->prepare("UPDATE users SET login_attempts=?, status='inactive' WHERE id=?");
                    $lock->bind_param("ii", $attempts, $user['id']);
                    $lock->execute();
                    $errors[] = "Account locked after 3 failed attempts.";
                } else {
                    $update = $conn->prepare("UPDATE users SET login_attempts=? WHERE id=?");
                    $update->bind_param("ii", $attempts, $user['id']);
                    $update->execute();
                    $errors[] = "Invalid password. Attempt $attempts of 3.";
                }
            }
        } else {
            $errors[] = "Email does not exist.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
/* BODY BACKGROUND - pastel gradient */
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%);
    position: relative;
}

/* Decorative circles */
body::before {
    content: "";
    position: absolute;
    width: 700px;
    height: 700px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    top: -150px;
    left: -200px;
    z-index: 0;
}
body::after {
    content: "";
    position: absolute;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    bottom: -100px;
    right: -150px;
    z-index: 0;
}

/* CARD CONTAINER */
.card-auth {
    display: flex;
    width: 900px;
    min-height: 500px;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    background: transparent;
    z-index: 1;
}

/* LEFT PANEL */
.left-panel {
    width: 50%;
    background: linear-gradient(160deg, #d63384, #f497b2);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 50px;
    text-align: center;
}
.left-panel h1 { font-size: 3rem; font-weight: 700; margin-bottom: 20px; }
.left-panel p { font-size: 1rem; margin-bottom: 15px; opacity: 0.9; }
.left-panel a.btn-create { 
    padding: 10px 22px; border-radius: 50px; border: 2px solid #fff;
    color: #fff; text-decoration: none; font-weight: 500; font-size: 0.95rem;
}
.left-panel a.btn-create:hover { background: #fff; color: #d63384; }

/* RIGHT PANEL - WHITE */
.right-panel {
    width: 50%;
    padding: 50px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #fff; /* solid white */
    box-shadow: -5px 0 30px rgba(0,0,0,0.05);
    border-top-right-radius: 25px;
    border-bottom-right-radius: 25px;
}

/* LOGIN FORM */
.login-title {
    text-align:center;
    font-weight:700;
    color:#d63384;
    margin-bottom:35px;
    font-size: 2rem;
}
.form-control {
    border-radius: 50px;
    padding: 14px 20px;
    border: 2px solid #f0f0f0;
    background-color: #fcfcfc;
    transition: all 0.3s ease;
    color: #555;
    font-size: 0.95rem;
}
.form-control:focus {
    background-color: #fff;
    border-color: #ffb7c5;
    box-shadow: 0 0 0 4px rgba(255,183,197,0.15);
}

/* Password toggle */
.input-wrapper {
    position: relative;
}
.input-wrapper input {
    width: 100%;
    border-radius: 50px;
    padding: 14px 45px 14px 20px;
    border: 2px solid #f0f0f0;
    background-color: #fcfcfc;
    transition: all 0.3s ease;
}
.input-wrapper input:focus {
    border-color: #ffb7c5;
    box-shadow: 0 0 0 4px rgba(255,183,197,0.15);
}
.input-wrapper .toggle-eye {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 1.2rem;
    color: #888;
}
.input-wrapper .toggle-eye:hover { color: #d63384; }

/* BUTTON */
.btn-login {
    width:100%;
    border:none;
    border-radius:50px;
    padding:14px;
    background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 99%);
    color:white;
    font-weight:500;
    letter-spacing:1px;
    margin-top:15px;
    font-size: 1rem;
}
.btn-login:hover {
    transform: translateY(-2px);
    box-shadow:0 5px 15px rgba(255,154,158,0.4);
}

/* ALERTS */
.alert-danger {
    background-color: #ffeef0;
    color: #d63384;
    border-radius:15px;
    text-align:center;
    font-size:0.9rem;
    border:1px solid #f5c6cb;
    margin-bottom:15px;
}
</style>
</head>
<body>

<div class="card-auth">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <h1>Welcome</h1>
        <p>Don't have an account?</p>
        <a href="register.php" class="btn-create">Create Account</a>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <h2 class="login-title">Login</h2>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-4 input-wrapper">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="toggle-eye"><i class="bi bi-eye"></i></span>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</div>

<script>
const toggle = document.querySelector('.toggle-eye');
const pass = document.querySelector('#password');
const icon = toggle.querySelector('i');
toggle.addEventListener('click', () => {
    const type = pass.getAttribute('type') === 'password' ? 'text' : 'password';
    pass.setAttribute('type', type);
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
});
</script>

</body>
</html>