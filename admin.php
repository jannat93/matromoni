<?php
session_start();
include_once("includes/dbconn.php");

// Security: Check admin login and use password verification
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];

        $query = "SELECT id, password FROM users WHERE is_admin = 1 AND username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['is_admin'] = 1;
                header("Location: admin.php");
            } else {
                $login_error = "<p class='message error'>Invalid username or password!</p>";
            }
        } else {
            $login_error = "<p class='message error'>Invalid username or password!</p>";
        }
        $stmt->close();
    }
    if (!isset($_SESSION['admin_id'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
            <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
            <style>
                body {
                    margin: 0;
                    font-family: 'Ubuntu', sans-serif;
                    background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
                    background-size: 400% 400%;
                    animation: gradient 15s ease infinite;
                    color: #333;
                    background-image: url('images/pic1.avif');
                    background-repeat: no-repeat;
                    background-size: cover;
                    background-position: center center;
                }
                @keyframes gradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
                .login-section { padding: 3em 0; background-color: rgba(255, 255, 255, 0.9); text-align: center; }
                .login-section h1 { color: #c32143; font-size: 2.5em; margin-bottom: 1em; font-family: 'Oswald', sans-serif; }
                .login-form { max-width: 500px; margin: 0 auto; }
                .form-control { border-radius: 5px; border: 1px solid #ccc; padding: 0.8em; font-size: 1em; background-color: #f9f9f9; }
                .form-control:focus { border-color: #f1b458; box-shadow: 0 0 5px rgba(241, 180, 88, 0.5); }
                .btn-submit { background-color: #c32143; color: #fff; padding: 0.8em 1.5em; border: none; border-radius: 5px; font-size: 1em; cursor: pointer; transition: background-color 0.3s ease; }
                .btn-submit:hover { background-color: #f1b458; color: #333; }
                .message.error { color: red; background-color: #ffe0e0; padding: 10px; border-radius: 5px; margin-bottom: 1em; }
            </style>
        </head>
        <body>
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">MatchMingle</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="nav navbar-nav">
                            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa fa-home"></i> Home</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="login-section">
                <h1>Admin Login</h1>
                <?php if (isset($login_error)) echo $login_error; ?>
                <div class="login-form">
                    <form method="POST">
                        <div class="mb-3"><label for="username" class="form-label">Username <span class="text-danger">*</span></label><input type="text" id="username" name="username" class="form-control" required></div>
                        <div class="mb-3"><label for="password" class="form-label">Password <span class="text-danger">*</span></label><input type="password" id="password" name="password" class="form-control" required></div>
                        <button type="submit" name="admin_login" class="btn-submit">Login</button>
                    </form>
                </div>
            </div>
            <div class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col_2">
                            <h4>About Us</h4>
                            <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships...</p>
                        </div>
                        <div class="col-md-2 col_2">
                            <h4>Help & Support</h4>
                            <ul class="footer_links">
                                <li><a href="#">24x7 Live help</a></li>
                                <li><a href="contact.php">Contact us</a></li>
                                <li><a href="#">Feedback</a></li>
                                <li><a href="faq.php">FAQs</a></li>
                            </ul>
                        </div>
                        <div class="col-md-2 col_2">
                            <h4>Quick Links</h4>
                            <ul class="footer_links">
                                <li><a href="privacy.php">Privacy Policy</a></li>
                                <li><a href="terms.php">Terms and Conditions</a></li>
                                <li><a href="services.php">Services</a></li>
                            </ul>
                        </div>
                        <div class="col-md-2 col_2">
                            <h4>Social</h4>
                            <ul class="footer_social">
                                <li><a href="#"><i class="fa fa-facebook fa1"></i></a></li>
                                <li><a href="#"><i class="fa fa-twitter fa1"></i></a></li>
                                <li><a href="#"><i class="fa fa-google-plus fa1"></i></a></li>
                                <li><a href="#"><i class="fa fa-youtube fa1"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="copy"><p>Copyright © 2025 Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p></div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit();
    }
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_user'])) {
        $user_id = intval($_POST['user_id']);
        $query = "UPDATE users SET status = 'banned' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['unban_user'])) {
        $user_id = intval($_POST['user_id']);
        $query = "UPDATE users SET status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['approve_payment'])) {
        $request_id = intval($_POST['request_id']);
        $query = "SELECT user_id, plan_type FROM payment_requests WHERE id = ? AND status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $request = $result->fetch_assoc();
            $expiry_date = date('Y-m-d', strtotime('+1 month'));
            $update_query = "UPDATE payment_requests SET status = 'approved' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('i', $request_id);
            $update_stmt->execute();

            $user_update_query = "UPDATE users SET plan_type = ?, subscription_status = 'active', subscription_expiry = ? WHERE id = ?";
            $user_stmt = $conn->prepare($user_update_query);
            $user_stmt->bind_param('ssi', $request['plan_type'], $expiry_date, $request['user_id']);
            $user_stmt->execute();
            $success = "<p class='message success'>Payment request approved successfully!</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['reject_payment'])) {
        $request_id = intval($_POST['request_id']);
        $query = "UPDATE payment_requests SET status = 'rejected' WHERE id = ? AND status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $success = "<p class='message error'>Payment request rejected.</p>";
        $stmt->close();
    } elseif (isset($_POST['add_team_member'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $experience = mysqli_real_escape_string($conn, $_POST['experience']);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        $photo_path = 'images/default.jpg';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['photo'];
            $photo_name = time() . '_' . basename($photo['name']);
            $target_dir = "images/";
            $target_file = $target_dir . $photo_name;
            move_uploaded_file($photo['tmp_name'], $target_file);
            $photo_path = $target_file;
        }

        $query = "INSERT INTO team (name, role, experience, photo_path, is_admin) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssii', $name, $role, $experience, $photo_path, $is_admin);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch data
$user_count = $conn->query("SELECT COUNT(*) as count FROM users") ? $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] : 0;
$msg_count = $conn->query("SELECT COUNT(*) as count FROM messages") ? $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'] : 0; // Temporary: Count all messages
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM payment_requests WHERE status = 'pending'") ? $conn->query("SELECT COUNT(*) as count FROM payment_requests WHERE status = 'pending'")->fetch_assoc()['count'] : 0;
$users = $conn->query("SELECT id, username, email, status FROM users") ?: [];
$messages = $conn->query("SELECT id, name, email, subject, sent_date FROM messages") ?: []; // Removed status from query
$media = $conn->query("SELECT id, file_path, caption, approved FROM media") ?: [];
$events = $conn->query("SELECT id, title, date, location, status FROM events") ?: [];
$team = $conn->query("SELECT id, name, role, experience, photo_path, is_admin FROM team") ?: [];
$payments = $conn->query("SELECT pr.id, u.username, pr.plan_type, pr.bkash_number, pr.transaction_id, pr.amount, pr.status, pr.created_at FROM payment_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.status = 'pending'") ?: [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MatchMingle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            margin: 0;
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #333;
            background-image: url('images/pic1.avif');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }
        @keyframes gradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .navbar { background-color: rgba(0, 0, 0, 0.7); }
        .navbar-brand { font-family: 'Oswald', sans-serif; font-size: 1.8em; color: rgb(240, 63, 23) !important; transition: color 0.3s ease; }
        .navbar-brand:hover { color: #c32143 !important; }
        .navbar-nav .nav-link { color: #fff !important; font-size: 1.1em; margin-left: 1em; transition: color 0.3s ease; }
        .navbar-nav .nav-link:hover { color: #f1b458 !important; }
        .navbar-toggler { border-color: #f1b458; }
        .navbar-toggler-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(241, 180, 88, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e"); }
        .admin-section { padding: 3em 0; background-color: rgba(255, 255, 255, 0.9); }
        .admin-section h1 { color: #c32143; font-size: 2.5em; margin-bottom: 1.5em; font-family: 'Oswald', sans-serif; }
        .table-container { max-width: 1200px; margin: 0 auto; padding: 0 1em; }
        .table th, .table td { text-align: center; vertical-align: middle; padding: 1em; }
        .btn-action { margin: 0.2em; padding: 0.5em 1em; }
        .form-group { margin-bottom: 1em; }
        .message.success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 1em; }
        .message.error { color: red; background-color: #ffe0e0; padding: 10px; border-radius: 5px; margin-bottom: 1em; }
        .footer { background-color: #333; color: #fff; padding: 2em 0; font-size: 0.9em; }
        .footer .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .footer h4 { color: #f1b458; font-size: 1.2em; margin-bottom: 1em; font-family: 'Oswald', sans-serif; }
        .footer p { font-size: 0.9em; line-height: 1.6; color: #ccc; }
        .footer_links, .footer_social { list-style: none; padding: 0; }
        .footer_links li, .footer_social li { margin-bottom: 0.5em; }
        .footer_links li a, .footer_social li a { color: #fff; text-decoration: none; font-size: 0.9em; transition: color 0.3s ease; }
        .footer_links li a:hover, .footer_social li a:hover { color: #f1b458; }
        .footer_social .fa { font-size: 1.2em; margin-right: 0.5em; }
        .copy { text-align: center; margin-top: 2em; padding-top: 1em; border-top: 1px solid #555; }
        .copy p { margin: 0; color: #ccc; }
        .copy a { color: #f1b458; text-decoration: none; }
        .copy a:hover { color: #c32143; }
        @media (max-width: 768px) { .admin-section { padding: 2em 0; } .table-container { padding: 0; } .navbar-nav { text-align: center; } .navbar-nav .nav-link { margin: 0.5em 0; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin.php">MatchMingle Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#subscriptions">Subscription Approval</a></li>
                    <li class="nav-item"><a class="nav-link" href="#users">User Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="#messages">Message Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="#media">Media Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="#events">Event Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Team Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="admin-section">
        <h1>Admin Panel</h1>

        <!-- Dashboard -->
        <section id="dashboard">
            <h2>Dashboard</h2>
            <div class="row">
                <div class="col-md-4"><div class="card p-3"><h4>Total Users</h4><p><?php echo $user_count; ?></p></div></div>
                <div class="col-md-4"><div class="card p-3"><h4>Total Messages</h4><p><?php echo $msg_count; ?></p></div></div> <!-- Changed to Total Messages -->
                <div class="col-md-4"><div class="card p-3"><h4>Pending Payments</h4><p><?php echo $pending_payments; ?></p></div></div>
            </div>
        </section>

        <!-- Subscription Approval -->
        <section id="subscriptions">
            <h2>Subscription Approval</h2>
            <?php if (isset($success)) echo $success; ?>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Username</th><th>Plan</th><th>bKash Number</th><th>Transaction ID</th><th>Amount (BDT)</th><th>Requested At</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $payment['id']; ?></td>
                            <td><?php echo $payment['username']; ?></td>
                            <td><?php echo ucfirst($payment['plan_type']); ?> Plan</td>
                            <td><?php echo $payment['bkash_number']; ?></td>
                            <td><?php echo $payment['transaction_id']; ?></td>
                            <td><?php echo $payment['amount']; ?></td>
                            <td><?php echo $payment['created_at']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;"><input type="hidden" name="request_id" value="<?php echo $payment['id']; ?>"><button type="submit" name="approve_payment" class="btn btn-success btn-action">Approve</button></form>
                                <form method="POST" style="display:inline;"><input type="hidden" name="request_id" value="<?php echo $payment['id']; ?>"><button type="submit" name="reject_payment" class="btn btn-danger btn-action">Reject</button></form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- User Management -->
        <section id="users">
            <h2>User Management</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo isset($user['status']) ? $user['status'] : 'active'; ?></td>
                            <td>
                                <?php if (isset($user['status']) && $user['status'] != 'banned'): ?>
                                    <form method="POST" style="display:inline;"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><button type="submit" name="ban_user" class="btn btn-danger btn-action">Ban</button></form>
                                <?php elseif (isset($user['status']) && $user['status'] == 'banned'): ?>
                                    <form method="POST" style="display:inline;"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><button type="submit" name="unban_user" class="btn btn-success btn-action">Unban</button></form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><button type="submit" name="ban_user" class="btn btn-danger btn-action">Ban</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Message Management -->
        <section id="messages">
            <h2>Message Management</h2>
            <?php if (isset($success)) echo $success; ?>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $msg['id']; ?></td>
                            <td><?php echo $msg['name']; ?></td>
                            <td><?php echo $msg['email']; ?></td>
                            <td><?php echo $msg['subject']; ?></td>
                            <td><?php echo $msg['sent_date']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" name="delete_message" class="btn btn-danger btn-action" onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Media Management -->
        <section id="media">
            <h2>Media Management</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>File</th><th>Caption</th><th>Approved</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($media_item = $media->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $media_item['id']; ?></td>
                            <td><img src="<?php echo $media_item['file_path']; ?>" width="50"></td>
                            <td><?php echo $media_item['caption']; ?></td>
                            <td><?php echo $media_item['approved'] ? 'Yes' : 'No'; ?></td>
                            <td><a href="approve_media.php?id=<?php echo $media_item['id']; ?>" class="btn btn-success btn-action">Approve</a> <a href="delete_media.php?id=<?php echo $media_item['id']; ?>" class="btn btn-danger btn-action" onclick="return confirm('Are you sure?')">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Event Management -->
        <section id="events">
            <h2>Event Management</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $event['id']; ?></td>
                            <td><?php echo $event['title']; ?></td>
                            <td><?php echo $event['date']; ?></td>
                            <td><?php echo $event['location']; ?></td>
                            <td><?php echo $event['status']; ?></td>
                            <td><a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger btn-action" onclick="return confirm('Are you sure?')">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <form method="POST" class="mt-3">
                <div class="form-group"><input type="text" name="event_title" class="form-control" placeholder="Event Title" required></div>
                <div class="form-group"><input type="date" name="event_date" class="form-control" required></div>
                <div class="form-group"><input type="text" name="event_location" class="form-control" placeholder="Location" required></div>
                <button type="submit" name="add_event" class="btn btn-primary btn-action">Add Event</button>
            </form>
        </section>

        <!-- Team Management -->
        <section id="team">
            <h2>Team Management</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Experience</th><th>Photo</th><th>Is Admin</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($member = $team->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $member['id']; ?></td>
                            <td><?php echo $member['name']; ?></td>
                            <td><?php echo $member['role']; ?></td>
                            <td><?php echo $member['experience']; ?></td>
                            <td><img src="<?php echo $member['photo_path']; ?>" width="50"></td>
                            <td><?php echo $member['is_admin'] ? 'Yes' : 'No'; ?></td>
                            <td><a href="delete_team.php?id=<?php echo $member['id']; ?>" class="btn btn-danger btn-action" onclick="return confirm('Are you sure?')">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <form method="POST" enctype="multipart/form-data" class="mt-3">
                <div class="form-group"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                <div class="form-group"><input type="text" name="role" class="form-control" placeholder="Role" required></div>
                <div class="form-group"><textarea name="experience" class="form-control" placeholder="Experience" required></textarea></div>
                <div class="form-group"><input type="file" name="photo" class="form-control" accept="image/*"></div>
                <div class="form-group"><input type="checkbox" name="is_admin" value="1"> Make Admin</div>
                <button type="submit" name="add_team_member" class="btn btn-primary btn-action">Add Member</button>
            </form>
        </section>
    </div>

    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col_2">
                    <h4>About Us</h4>
                    <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships...</p>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Help & Support</h4>
                    <ul class="footer_links">
                        <li><a href="#">24x7 Live help</a></li>
                        <li><a href="contact.php">Contact us</a></li>
                        <li><a href="#">Feedback</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Quick Links</h4>
                    <ul class="footer_links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms and Conditions</a></li>
                        <li><a href="services.php">Services</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Social</h4>
                    <ul class="footer_social">
                        <li><a href="#"><i class="fa fa-facebook fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-youtube fa1"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="copy"><p>Copyright © 2025 Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    // Handle message operations
    if (isset($_POST['delete_message'])) {
        $message_id = intval($_POST['message_id']);
        $query = "DELETE FROM messages WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $message_id);
        if ($stmt->execute()) {
            $success = "<p class='message success'>Message deleted successfully!</p>";
        }
        $stmt->close();
    }
    ?>
</body>
</html>

<?php
$conn->close();
?>