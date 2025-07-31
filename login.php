<?php include_once("functions.php"); ?>
<?php
session_start();

include_once("includes/dbconn.php");

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_submit'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($email) || empty($password)) {
            $message = "<p style='color: red; text-align: center;'>Username, email, and password are required!</p>";
        } else {
            $sql = "SELECT id, password FROM users WHERE username = ? AND email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                error_log("Login successful, session ID set: " . $_SESSION['id']);
                if (!isloggedin()) {
                    $message = "<p style='color: red; text-align: center;'>Your account has been banned. Contact support.</p>";
                } else {
                    header("Location: userhome.php?id=" . $user['id']);
                    exit();
                }
            } else {
                $message = "<p style='color: red; text-align: center;'>Invalid username, email, or password!</p>";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['forgot_password'])) {
        $email = trim($_POST['forgot_email'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        echo "<!-- Debug: Forgot Password submitted, email = $email, new_password = $new_password -->";
        if (empty($email) || empty($new_password)) {
            $message = "<p style='color: red; text-align: center;'>Email and new password are required!</p>";
        } else {
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($result)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $message = "<p style='color: green; text-align: center;'>Password updated successfully. You can now <a href='login.php'>login</a>.</p>";
            } else {
                $message = "<p style='color: red; text-align: center;'>Email not found!</p>";
            }
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE HTML>
<html>
<head>
<title>Makemy Love</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="application/x-javascript"> 
    addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); 
    function hideURLbar(){ window.scrollTo(0,1); } 
</script>

<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<link href="css/font-awesome.css" rel="stylesheet">

<!-- Custom style for background and layout -->
<style>
.grid_3 {
    background-image: url('images/pic12.png'); /* Ensure this path matches your register.php */
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center center;
    padding: 20px 0; /* Reduced padding to minimize gap */
}

.breadcrumb1 {
    background-color: rgba(247, 246, 250, 0.98);
    padding: 10px;
    border-radius: 2px;
    margin-bottom: 10px; /* Reduced margin to close gap */
}

.login_left {
    background-color: rgba(255, 182, 193, 0.8); /* Light pink similar to red-box effect */
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.login_left .form-group {
    margin-bottom: 15px;
}

.login_left label {
    color: #333;
    font-weight: bold;
}

.login_left input[type="text"],
.login_left input[type="email"],
.login_left input[type="password"] {
    padding: 0.8em;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    width: 100%;
    box-sizing: border-box;
}

.login_left .btn_1 {
    background-color: #d9534f; /* Red button to match register.php */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s ease;
    width: 100%;
}

.login_left .btn_1:hover {
    background-color: #c9302c;
}

.sharing {
    background-color: rgba(255, 182, 193, 0.8); /* Light pink to match */
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.sharing li a {
    color: #fff;
}

.forgot-password {
    text-align: right;
    margin-bottom: 1em;
}
.forgot-password a {
    color: #d9534f;
    text-decoration: underline;
    font-size: 0.9em;
}

#forgotPasswordModal .modal-content {
    background-color: rgba(255, 182, 193, 0.8);
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

#forgotPasswordModal .modal-header {
    border-bottom: none;
    padding: 15px;
}

#forgotPasswordModal .modal-title {
    color: #333;
    font-weight: bold;
}

#forgotPasswordModal .modal-body {
    padding: 20px;
}

#forgotPasswordModal .form-group {
    margin-bottom: 15px;
}

#forgotPasswordModal label {
    color: #333;
    font-weight: bold;
}

#forgotPasswordModal input[type="email"],
#forgotPasswordModal input[type="password"] {
    padding: 0.8em;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    width: 100%;
    box-sizing: border-box;
}

#forgotPasswordModal .btn-primary {
    background-color: #d9534f;
    border: none;
    border-radius: 5px;
    padding: 10px;
    font-size: 1em;
    transition: background-color 0.3s ease;
    width: 100%;
}

#forgotPasswordModal .btn-primary:hover {
    background-color: #c9302c;
}

.close {
    background: none;
    border: none;
    font-size: 1.5em;
    color: #333;
}

.message {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 5px;
}

.message.success {
    color: green;
    background-color: #e0ffe0;
}

.message.error {
    color: red;
    background-color: #ffe0e0;
}

.debug {
    display: none;
    color: red;
    font-size: 12px;
}
</style>

<script>
$(document).ready(function(){
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).stop(true, true).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).stop(true, true).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );

    $('[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        console.log('Modal trigger clicked');
        var modalId = $(this).data('target');
        $(modalId).modal('show');
    });

    $('#forgotPasswordModal form').on('submit', function(e) {
        console.log('Forgot Password form submitted');
        // e.preventDefault(); // Uncomment to debug, then comment back
    });
});
</script>
</head>
<body>

<!-- Navigation -->
<?php include_once("includes/navigation.php");?>

<div class="grid_3">
  <div class="container">
   <div class="breadcrumb1">
     <ul>
        <a href="index.php"><i class="fa fa-home home_1"></i></a>
        <span class="divider"> | </span>
        <li class="current-page">Login</li>
     </ul>
   </div>
   <?php if (!empty($message)): ?>
       <div class="message <?php echo $message['type']; ?>">
           <?php echo $message['text']; ?>
       </div>
   <?php endif; ?>
   <div class="services">
   	  <div class="col-sm-6 login_left">
	     <form action="" method="post">
		     <div class="form-group">
                <label for="edit-name">Username <span class="form-required" title="This field is required.">*</span></label>
                <input type="text" id="edit-name" name="username" value="" size="60" maxlength="60" class="form-text">
            </div>
            <div class="form-group">
                <label for="edit-email">Email <span class="form-required" title="This field is required.">*</span></label>
                <input type="email" id="edit-email" name="email" value="" size="60" maxlength="255" class="form-text">
            </div>
		    <div class="form-group">
		      <label for="edit-pass">Password <span class="form-required" title="This field is required.">*</span></label>
		      <input type="password" id="edit-pass" name="password" size="60" maxlength="128" class="form-text">
		    </div>
            <div class="forgot-password">
                <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot Password?</a>
            </div>
            <div class="form-actions">
                <input type="submit" id="edit-submit" name="login_submit" value="Log in" class="btn_1 submit">
                <a href="admin.php" class="btn_1" style="margin-top: 10px; display: block;">Admin Login</a>
            </div>
         </form>
	  </div>
	  <div class="col-sm-6">
	     <ul class="sharing">
			<li><a href="#" class="facebook" title="Facebook"><i class="fa fa-boxed fa-fw fa-facebook"></i> Share on Facebook</a></li>
		  	<li><a href="#" class="twitter" title="Twitter"><i class="fa fa-boxed fa-fw fa-twitter"></i> Tweet</a></li>
		  	<li><a href="#" class="google" title="Google"><i class="fa fa-boxed fa-fw fa-google-plus"></i> Share on Google+</a></li>
		  	<li><a href="#" class="linkedin" title="Linkedin"><i class="fa fa-boxed fa-fw fa-linkedin"></i> Share on LinkedIn</a></li>
		  	<li><a href="#" class="mail" title="Email"><i class="fa fa-boxed fa-fw fa-envelope-o"></i> E-mail</a></li>
		 </ul>
	  </div>
	  <div class="clearfix"> </div>
   </div>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="forgot-email" class="form-label">Email <span class="form-required" title="This field is required.">*</span></label>
                        <input type="email" class="form-control" id="forgot-email" name="forgot_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="new-password" class="form-label">New Password <span class="form-required" title="This field is required.">*</span></label>
                        <input type="password" class="form-control" id="new-password" name="new_password" required>
                    </div>
                    <input type="hidden" name="forgot_password" value="1">
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php include_once("footer.php");?>
</body>
</html>