<?php
require 'functions.php';
session_start();

$error = "";
$first_name = $last_name = $email = $password = $confirm_password = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email is invalid.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors) && user_exists($email)) {
        $errors[] = "An account with this email already exists.";
    }

    if (empty($errors)) {
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        header("Location: register_extra.php");
        exit;
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }

        .form-title { color: #d63384; font-weight: 600; text-align: center; margin-bottom: 5px; }
        .form-subtitle { text-align: center; color: #888; font-size: 0.9rem; margin-bottom: 30px; }

        .form-label { font-size: 0.85rem; color: #333; margin-left: 15px; font-weight: 500; }

        .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #f0f0f0;
            background-color: #fdfdfd;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #ff9a9e;
            box-shadow: 0 0 0 4px rgba(255, 154, 158, 0.1);
            outline: none;
        }

        /* Input Wrapper for the Eye Icon */
        .input-wrapper { position: relative; }
        .input-wrapper input { width: 100%; padding-right: 45px; }
        
        .toggle-eye {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.1rem;
            color: #888;
            z-index: 10;
        }
        .toggle-eye:hover { color: #d63384; }

        .btn-next {
            background-color: #ff9a9e;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-next:hover { background-color: #d63384; transform: translateY(-2px); }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .login-link:hover { color: #d63384; }

        .alert { border-radius: 15px; background-color: #ffeef0; color: #d63384; border: none; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="form-card">
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Step 1: Account Information</p>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label">First Name</label>
                <input class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>" placeholder="Carlo">
            </div>
            <div class="col-6 mb-3">
                <label class="form-label">Last Name</label>
                <input class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>" placeholder="Aquino">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="carlo@example.com">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-wrapper">
                <input type="password" class="form-control" name="password" id="password" placeholder="At least 8 character">
                <span class="toggle-eye" onclick="togglePass('#password', this)"><i class="bi bi-eye"></i></span>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <div class="input-wrapper">
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Repeat password">
                <span class="toggle-eye" onclick="togglePass('#confirm_password', this)"><i class="bi bi-eye"></i></span>
            </div>
        </div>

        <button class="btn btn-next">Next Step</button>
    </form>

    <a href="login.php" class="login-link">Already have an account? Login</a>
</div>

<script>
    function togglePass(inputId, iconEl) {
        const input = document.querySelector(inputId);
        const icon = iconEl.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>