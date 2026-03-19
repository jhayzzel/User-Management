<?php
require 'functions.php';
check_session('admin'); 

$conn = db_connect();
$id = $_GET['id'] ?? "";
$user_name = "";

// SECURITY: Hard block to prevent you from deleting yourself
if ($id == $_SESSION['user_id']) {
    header("Location: admin_dashboard.php?error=self_delete");
    exit;
}

if (!empty($id)) {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        header("Location: admin_dashboard.php");
        exit;
    }
    $user_name = $row['first_name'] . " " . $row['last_name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $target_id = $_POST['id'];

    // Double check it's not you
    if ($target_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
    }

    header("Location: admin_dashboard.php?msg=deleted");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Deletion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%); 
            font-family: 'Poppins', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .confirm-card { 
            background: white; 
            padding: 40px; 
            border-radius: 30px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: #e84393;
            border: 2px solid #faaca8;
        }
        .btn-delete { 
            background: linear-gradient(45deg, #e84393, #ff7675); 
            border: none; 
            color: white; 
            border-radius: 50px; 
            padding: 12px; 
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(232, 67, 147, 0.3);
            color: white;
        }
        .btn-cancel {
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="confirm-card">
        <div class="user-avatar">👤</div>
        <h3 class="fw-bold mb-2" style="color: #d63384;">Delete User</h3>
        <p class="text-muted mb-4">Are you sure you want to delete <br><strong class="text-dark"><?= htmlspecialchars($user_name) ?></strong>?<br><small>This action cannot be undone.</small></p>
        
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit" name="confirm_delete" class="btn btn-delete w-100">Yes, Delete User</button>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-cancel w-100">No, Go Back</a>
        </form>
    </div>
</body>
</html>