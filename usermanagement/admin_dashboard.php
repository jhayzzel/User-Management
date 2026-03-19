<?php
require 'functions.php';
check_session('admin'); 
$conn = db_connect();

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$users = get_users($search);
$total_registered = $users->num_rows;
$session_admin_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background: linear-gradient(19deg, #faaca8 0%, #ddd6f3 100%); font-family: 'Poppins', sans-serif; min-height: 100vh; margin: 0; }
        .navbar { background: linear-gradient(90deg, #ff9a9e, #fecfef) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .navbar .navbar-brand { color: white !important; font-weight: 700; }
        
        .main-card { width: 98%; max-width: 1600px; margin: 20px auto; background: #ffffff; border-radius: 30px; overflow: hidden; display: flex; min-height: 85vh; box-shadow: 0 25px 50px rgba(0,0,0,0.1); }
        .panel-left { width: 15%; background: linear-gradient(160deg, #ff9a9e, #d63384); color: white; padding: 30px 15px; display: flex; flex-direction: column; text-align: center; }
        .panel-right { width: 85%; padding: 40px; background: #ffffff; }
        
        .list-header { color: #d63384; font-weight: 700; margin-bottom: 25px; font-size: 1.5rem; }

        .badge-you { 
            background-color: #212529 !important; 
            color: white !important; 
            border-radius: 6px; 
            padding: 2px 10px; 
            font-size: 0.65rem; 
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 4px;
        }

        .btn-action { font-weight: 600; border-radius: 50px; font-size: 0.8rem; padding: 6px 16px; border: none; color: white !important; }
        .btn-edit { background: #a29bfe; } 
        .btn-delete { background: #e84393; } 
        .btn-deactivate { background: #636e72; } 
        
        .btn-theme { border: none; border-radius: 50px; padding: 10px 25px; background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 99%); color: white; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-theme:hover { opacity: 0.9; color: white; }

        .table thead th { font-weight: 700; color: #333; border-top: none; border-bottom: 2px solid #eee; white-space: nowrap; }
        .nowrap { white-space: nowrap; }

        @media (max-width: 1200px) { .main-card { flex-direction: column; } .panel-left, .panel-right { width: 100%; } }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Admin Dashboard</span>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 style="color:#d63384; font-weight:700;">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body text-center">
    <div class="mb-4 p-3 bg-light rounded shadow-sm">
      <p class="mb-1 text-muted small">Logged in as:</p>
      <h5 class="fw-bold" style="color: #d63384;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></h5>
    </div>
    <a class="btn btn-theme w-100" href="logout.php">Logout</a>
  </div>
</div>

<div class="main-card">
    <div class="panel-left">
        <h3 class="fw-bold mb-3">Admin Panel</h3>
        <p class="fw-bold mb-4"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
        <hr class="text-white-50">
        <div class="mt-auto">
             <p class="small opacity-75">Management Access</p>
        </div>
    </div>

    <div class="panel-right">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0" style="color: #d63384;">User Management</h4>
                <p class="text-muted mb-0 small">Total Registered Users: <strong><?= $total_registered ?></strong></p>
            </div>
            <a href="register.php" class="btn btn-theme">Add New User</a>
        </div>

        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="border-radius: 50px 0 0 50px; padding-left: 20px;">
                <button class="btn btn-theme" type="submit" style="border-radius: 0 50px 50px 0;">Search</button>
            </div>
        </form>

        <h3 class="list-header">Registered Users</h3>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center">Attempts</th>
                        <th>Created At</th>
                        <th>Address</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <?php $is_me = ($row['id'] == $session_admin_id); ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $row['id'] ?></td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></span>
                                <?php if ($is_me): ?>
                                    <div><span class="badge-you">You</span></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? 'Female') ?></td>
                        <td><span class="badge bg-info text-dark rounded-pill px-3"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td><span class="badge <?= $row['status']=='active' ? 'bg-success':'bg-secondary' ?> rounded-pill px-3"><?= ucfirst($row['status']) ?></span></td>
                        <td class="text-center"><?= $row['login_attempts'] ?? 0 ?></td>
                        <td class="nowrap fw-bold" style="font-size: 0.8rem;">
                            <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($row['address'] ?? 'N/A') ?></small></td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-action btn-edit">Edit</a>
                                <button class="btn btn-action btn-delete" onclick="handleSecurityAction('delete', <?= $row['id'] ?>, '<?= addslashes($row['first_name']) ?>', <?= $is_me ? 'true' : 'false' ?>)">Delete</button>
                                <button class="btn btn-action btn-deactivate" onclick="handleSecurityAction('deactivate', <?= $row['id'] ?>, '<?= addslashes($row['first_name']) ?>', <?= $is_me ? 'true' : 'false' ?>)">Deactivate</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// SUCCESS ALERT FOR REGISTRATION
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('msg') === 'registered') {
    Swal.fire({
        title: 'User Registered!',
        text: 'The new account was added successfully.',
        icon: 'success',
        confirmButtonColor: '#d63384'
    });
    // Remove the message from URL without refresh
    window.history.replaceState({}, document.title, window.location.pathname);
}

function handleSecurityAction(type, targetId, targetName, isMe) {
    if (isMe) {
        Swal.fire({
            title: 'Action Denied',
            text: 'You cannot ' + type + ' your own account.',
            icon: 'error',
            confirmButtonColor: '#d63384'
        });
        return;
    }

    Swal.fire({
        title: type === 'delete' ? 'Delete User?' : 'Confirm Action',
        text: `Are you sure you want to ${type} ${targetName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: type === 'delete' ? '#e84393' : '#a29bfe',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = (type === 'delete') ? 'delete.php?id=' + targetId : `toggle_status.php?id=${targetId}&action=${type}`;
        }
    });
}
</script>
</body>
</html>