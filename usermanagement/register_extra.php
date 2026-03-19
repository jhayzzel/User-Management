<?php
require 'functions.php';
session_start();

// FIXED: Only redirect to Step 1 if user is NOT an admin AND session data is missing
if (!isset($_SESSION['first_name']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header("Location: register.php");
    exit;
}

$error = "";
$gender = $role = $address = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $gender = $_POST['gender'] ?? '';
    $role = $_POST['role'] ?? '';
    $address = trim($_POST['address']);

    $errors = [];
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($role)) $errors[] = "Role is required.";
    if (empty($address)) $errors[] = "Address is required.";

    if (empty($errors)) {
        $conn = db_connect();
        $hashed_password = hash_password($_SESSION['password']);
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, gender, role, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['email'], $hashed_password, $gender, $role, $address);

        if ($stmt->execute()) {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                // Keep Admin logged in, just clear the form data
                unset($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['email'], $_SESSION['password']);
                header("Location: admin_dashboard.php?msg=registered");
            } else {
                session_destroy();
                header("Location: login.php?msg=registered");
            }
            exit;
        } else { 
            $error = "Database Error: " . $stmt->error; 
        }
        $stmt->close(); 
        $conn->close();
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Step 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%); min-height: 100vh; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .form-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 25px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); padding: 40px; width: 100%; max-width: 500px; }
        .form-title { color: #d63384; font-weight: 600; text-align: center; margin-bottom: 5px; }
        .form-subtitle { text-align: center; color: #888; font-size: 0.9rem; margin-bottom: 30px; }
        .custom-select-wrapper { position: relative; margin-bottom: 20px; }
        .select-trigger { background: #fdfdfd; border: 2px solid #f0f0f0; border-radius: 50px; padding: 12px 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; color: #555; font-size: 0.95rem; }
        .select-trigger.active, .select-trigger.selected { border-color: #ff9a9e; box-shadow: 0 0 0 4px rgba(255, 154, 158, 0.2); color: #d63384; }
        .custom-options { position: absolute; top: 110%; left: 0; right: 0; background: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none; z-index: 100; overflow: hidden; padding: 10px 0; border: 1px solid #f0f0f0; }
        .custom-options.show { display: block; }
        .option { padding: 10px 25px; cursor: pointer; transition: 0.2s; color: #555; }
        .option:hover { background-color: #ffeef0; color: #d63384; }
        .form-label { font-size: 0.85rem; color: #333; margin-left: 15px; font-weight: 500; }
        .form-control { border-radius: 50px; padding: 12px 25px; border: 2px solid #f0f0f0; }
        textarea.form-control { border-radius: 20px; resize: none; }
        .btn-finish { background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 99%); color: white; border: none; border-radius: 50px; padding: 12px 30px; font-weight: 600; width: 100%; transition: 0.3s; margin-top: 10px; }
        .btn-finish:hover { background: linear-gradient(45deg, #d63384 0%, #ff9a9e 99%); transform: translateY(-2px); }
        .cancel-link { display: block; text-align: center; margin-top: 20px; color: #aaa; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="form-card">
    <h2 class="form-title">Almost Done!</h2>
    <p class="form-subtitle">Step 2: Additional Profile Details</p>

    <?php if($error): ?>
        <div class="alert alert-danger" style="border-radius:15px; background:#ffeef0; color:#d63384; border:none;"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" id="regForm">
        <input type="hidden" name="gender" id="genderInput" value="<?= htmlspecialchars($gender) ?>">
        <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role) ?>">

        <label class="form-label">Gender</label>
        <div class="custom-select-wrapper">
            <div class="select-trigger" id="genderTrigger"><?= $gender ? $gender : 'Select Gender' ?> <i class="bi bi-chevron-down"></i></div>
            <div class="custom-options">
                <div class="option" data-value="Male">Male</div>
                <div class="option" data-value="Female">Female</div>
                <div class="option" data-value="Other">Other</div>
            </div>
        </div>

        <label class="form-label">Role</label>
        <div class="custom-select-wrapper">
            <div class="select-trigger" id="roleTrigger"><?= $role ? ucfirst($role) : 'Select Role' ?> <i class="bi bi-chevron-down"></i></div>
            <div class="custom-options">
                <div class="option" data-value="user">User</div>
                <div class="option" data-value="admin">Admin</div>
            </div>
        </div>

        <label class="form-label">Home Address</label>
        <textarea name="address" class="form-control mb-4" rows="3" placeholder="Enter your full address..."><?= htmlspecialchars($address) ?></textarea>

        <button type="submit" class="btn btn-finish">Finish Registration</button>
    </form>
    <a href="register.php" class="cancel-link">Go back to Step 1</a>
</div>

<script>
    function setupCustomSelect(triggerId, hiddenInputId) {
        const trigger = document.getElementById(triggerId);
        const optionsContainer = trigger.nextElementSibling;
        const hiddenInput = document.getElementById(hiddenInputId);
        const options = optionsContainer.querySelectorAll('.option');

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.custom-options.show').forEach(container => {
                if (container !== optionsContainer) container.classList.remove('show');
            });
            optionsContainer.classList.toggle('show');
            trigger.classList.toggle('active');
        });

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                const val = opt.getAttribute('data-value');
                trigger.innerHTML = opt.textContent + ' <i class="bi bi-chevron-down"></i>';
                hiddenInput.value = val;
                trigger.classList.add('selected');
                optionsContainer.classList.remove('show');
            });
        });

        document.addEventListener('click', () => {
            optionsContainer.classList.remove('show');
            trigger.classList.remove('active');
        });
    }
    setupCustomSelect('genderTrigger', 'genderInput');
    setupCustomSelect('roleTrigger', 'roleInput');
</script>
</body>
</html>