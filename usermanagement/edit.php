<?php
require 'functions.php';
check_session('admin'); // Only admin access

$conn = db_connect();

$id = $first_name = $last_name = $email = $gender = $role = $address = "";
$errors = [];
$successMessage = "";

// GET request: fetch user data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id'])) {
        header("location: admin_dashboard.php");
        exit;
    }

    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT first_name,last_name,email,gender,role,address FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        header("location: admin_dashboard.php");
        exit;
    }

    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $email = $user['email'];
    $gender = $user['gender'];
    $role = $user['role'];
    $address = $user['address'];
}

// POST request: update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $address = trim($_POST['address']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($gender) || empty($role) || empty($address)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, gender=?, role=?, address=? WHERE id=?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $gender, $role, $address, $id);
        if ($stmt->execute()) {
            $successMessage = "User updated successfully.";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            max-width: 550px;
            border: none;
        }

        .form-title {
            color: #d63384;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 0.85rem;
            color: #000000;
            margin-left: 15px;
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 50px;
            padding: 12px 25px;
            border: 1px solid #f0f0f0;
            background-color: #fdfdfd;
            font-size: 0.95rem;
            color: #555;
            transition: border-color 0.2s;
        }

        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #ff9a9e;
            box-shadow: 0 0 0 4px rgba(255, 154, 158, 0.1);
            outline: none;
        }

        .btn-update {
            background-color: #ff9a9e;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s ease; 
        }

        .btn-update:hover {
            background-color: #d63384;
            color: white;
        }

        .btn-cancel {
            color: #aaa;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .btn-cancel:hover {
            color: #d63384;
        }

        .alert {
            border-radius: 15px;
            border: none;
            font-size: 0.9rem;
        }
        .alert-danger { background-color: #ffeef0; color: #d63384; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 class="form-title">Edit User Details</h2>

        <?php if (!empty($errors)): ?>
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <?php foreach($errors as $e) echo "<strong>".htmlspecialchars($e)."</strong><br>"; ?>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                <strong><?= htmlspecialchars($successMessage) ?></strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="Male" <?= $gender=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $gender=='Female'?'selected':'' ?>>Female</option>
                        <option value="Other" <?= $gender=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="admin" <?= $role=='admin'?'selected':'' ?>>Admin</option>
                        <option value="user" <?= $role=='user'?'selected':'' ?>>User</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>">
            </div>

            <div class="d-flex flex-column align-items-center gap-2 mt-4">
                <button type="submit" class="btn btn-update">Update User</button>
                <a class="btn-cancel" href="admin_dashboard.php">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>