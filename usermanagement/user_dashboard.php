<?php
require_once "functions.php";

// Check session for user role
check_session("user");

// Connect to database
$connection = db_connect();

// Get logged-in user ID
$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user data
$user = get_user($user_id);

// Update profile logic
if (isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);

    if (!empty($first_name) && !empty($last_name) && !empty($address)) {
        $stmt = $connection->prepare("UPDATE users SET first_name=?, last_name=?, address=? WHERE id=?");
        $stmt->bind_param("sssi", $first_name, $last_name, $address, $user_id);
        $stmt->execute();

        $_SESSION['user_name'] = $first_name;
        $message = "Profile updated successfully.";

        $user = get_user($user_id);
    } else {
        $message = "All profile fields are required.";
    }
}

// Change password logic
if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $hashed = hash_password($new_password);
        $stmt = $connection->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $message = "Password changed successfully.";
    }
}

// Delete account logic
if (isset($_POST['delete_account'])) {
    $stmt = $connection->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    session_destroy();
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* Navbar with Hamburger */
        .navbar {
            background: linear-gradient(90deg, #ff9a9e, #fecfef) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .navbar .navbar-brand { color: white !important; font-weight: 700; }
        .navbar-toggler { border: none; filter: invert(1); }
        .navbar-toggler:focus { box-shadow: none; }

        .dashboard-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .section-title { color: #d63384; font-weight: 700; margin-bottom: 25px; }

        /* Grid Setup */
        .profile-grid {
            display: flex;
            gap: 25px;
            align-items: stretch;
        }

        .left-side { flex: 1; }
        .right-side {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card {
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            background: #ffffff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .left-side .card { height: 100%; }
        
        /* Fixed Overlap: Set height auto and use margin for spacing */
        .right-side .card { height: auto; margin-bottom: 20px; }
        .right-side .card:last-child { margin-bottom: 0; }

        .card-header {
            background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 100%) !important;
            color: white !important;
            font-weight: 600;
            padding: 15px 20px;
            border: none;
        }

        .card-body { padding: 25px; }

        .form-control {
            background-color: #fcfcfc;
            border: 2px solid #f2f2f2;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }
        .form-control:focus {
            border-color: #ffb7c5;
            box-shadow: 0 0 0 4px rgba(255, 183, 197, 0.15);
        }

        .input-wrapper { position: relative; }
        .input-wrapper .toggle-eye {
            position: absolute;
            right: 20px;
            top: 40%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        .btn-theme {
            border: none;
            border-radius: 50px;
            padding: 12px;
            background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 99%);
            color: white;
            font-weight: 600;
            transition: 0.3s;
            width: 100%;
        }
        .btn-theme:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,154,158,0.3); color: white; }

        .btn-delete-outline {
            background: transparent;
            color: #d63384;
            border: 2px solid #ff9a9e;
            border-radius: 50px;
            padding: 10px;
            font-weight: 600;
            width: 100%;
        }

        /* Sidebar Styling */
        .offcanvas-header { background: #fcfcfc; border-bottom: 1px solid #eee; }
        .offcanvas-title { color: #d63384; font-weight: 700; }

        @media (max-width: 850px) {
            .profile-grid { flex-direction: column; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <span class="navbar-brand">User Dashboard</span>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="mb-4 p-3 bg-light rounded text-center">
      <small class="text-muted">Logged in as:</small><br>
      <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
    </div>
    <a href="logout.php" class="btn btn-theme">Logout</a>
  </div>
</div>

<div class="dashboard-container">
    <h2 class="section-title">My Profile</h2>
    
    <?php if($message): ?>
        <div class="alert mb-4 py-2" style="border-radius: 12px; background:#ffeef0; color:#d63384; text-align:center;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="left-side">
            <div class="card">
                <div class="card-header">Update Information</div>
                <div class="card-body">
                    <form method="post">
                        <label class="small text-muted mb-1 ms-2">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        
                        <label class="small text-muted mb-1 ms-2">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        
                        <label class="small text-muted mb-1 ms-2">Address</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                        
                        <button name="update_profile" class="btn btn-theme mt-2">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="right-side">
            <div class="card">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="post">
                        <div class="input-wrapper">
                            <input type="password" name="new_password" class="form-control" placeholder="New Password" id="new_p" required>
                            <span class="toggle-eye" onclick="tPass('new_p', this)"><i class="bi bi-eye"></i></span>
                        </div>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" id="conf_p" required>
                            <span class="toggle-eye" onclick="tPass('conf_p', this)"><i class="bi bi-eye"></i></span>
                        </div>
                        <button name="change_password" class="btn btn-theme">Update Password</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Account Deletion</div>
                <div class="card-body">
                    <form method="post" onsubmit="return confirm('Permanently delete account?');">
                        <p class="text-muted small mb-3">All your data will be permanently removed to our system.</p>
                        <button name="delete_account" class="btn btn-delete-outline">Delete My Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function tPass(id, el) {
    const input = document.getElementById(id);
    const icon = el.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>