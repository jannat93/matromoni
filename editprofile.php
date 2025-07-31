<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

// Check if user is logged in
if (!isloggedin()) {
    error_log("User not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$uploadMessage = '';
$updateMessage = '';

// Get list of existing columns in users table
$columnsResult = mysqli_query($conn, "SHOW COLUMNS FROM users");
$existingColumns = [];
while ($column = mysqli_fetch_assoc($columnsResult)) {
    $existingColumns[] = $column['Field'];
}

// Define expected fields (based on current table)
$expectedFields = [
    'username', 'email', 'birth_date', 'gender', 'religion', 'photo',
    'age', 'maritalstatus', 'caste', 'district', 'state', 'country',
    'education', 'occupation', 'aboutme'
];

// Filter fields to only those that exist in the database
$availableFields = array_intersect($expectedFields, $existingColumns);
$selectFields = $availableFields ? implode(', ', $availableFields) : 'id'; // Fallback to 'id' if no fields exist

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = 'uploads/profiles/';
    $file = $_FILES['profile_photo'];

    // Validate file
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $userId . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Update database
                $sql = "UPDATE users SET photo = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "si", $filename, $userId);
                    if (mysqli_stmt_execute($stmt)) {
                        $uploadMessage = "<p style='color: green;'>Profile photo updated successfully!</p>";
                    } else {
                        $uploadMessage = "<p style='color: red;'>Failed to update profile photo in database.</p>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    error_log("Prepare failed for photo update: " . mysqli_error($conn));
                    $uploadMessage = "<p style='color: red;'>Database error.</p>";
                }
            } else {
                $uploadMessage = "<p style='color: red;'>Failed to upload file.</p>";
            }
        } else {
            $uploadMessage = "<p style='color: red;'>Invalid file type or size (max 2MB, JPEG/PNG/GIF only).</p>";
        }
    } else {
        $uploadMessage = "<p style='color: red;'>File upload error.</p>";
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['profile_photo'])) {
    $params = [];
    $types = '';
    $sql = "UPDATE users SET ";
    $setClauses = [];

    foreach ($availableFields as $field) {
        if ($field === 'username' || $field === 'photo' || $field === 'password' || $field === 'created_at') continue; // Skip read-only or system fields
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : '';
        $params[] = $value;
        $types .= 's'; // Assume all fields are strings for simplicity
        $setClauses[] = "$field = ?";
    }

    if ($setClauses) {
        $sql .= implode(", ", $setClauses) . " WHERE id = ?";
        $params[] = $userId;
        $types .= 'i';

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if (mysqli_stmt_execute($stmt)) {
                $updateMessage = "<p style='color: green;'>Profile updated successfully!</p>";
            } else {
                $updateMessage = "<p style='color: red;'>Failed to update profile.</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Prepare failed for profile update: " . mysqli_error($conn));
            $updateMessage = "<p style='color: red;'>Database error.</p>";
        }
    } else {
        $updateMessage = "<p style='color: red;'>No fields to update.</p>";
    }
}

// Fetch current user data
$sql = "SELECT $selectFields FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for user fetch: " . mysqli_error($conn));
    die("Database error");
}
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if (!$user) {
    error_log("No user found with ID: $userId");
    header("Location: login.php");
    exit();
}
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #333;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
            padding-top: 0;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            font-family: 'Ubuntu', sans-serif;
        }

        .navbar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8em;
            color: rgb(240, 63, 23) !important;
            transition: color 0.3s ease;
        }

        .navbar-brand:hover {
            color: #c32143 !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-size: 1.1em;
            margin-left: 1em;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #f1b458 !important;
        }

        .navbar-nav .nav-link.active {
            color: #f1b458 !important;
            font-weight: bold;
        }

        .navbar-toggler {
            border-color: #f1b458;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(241, 180, 88, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .header {
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 2em 0;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 0.5em;
            font-family: 'Oswald', sans-serif;
        }

        .edit-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .edit-section h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .edit-form {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .edit-form .form-group {
            margin-bottom: 1.5em;
        }

        .edit-form label {
            font-weight: bold;
            color: #333;
        }

        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="number"],
        .edit-form input[type="date"],
        .edit-form select,
        .edit-form textarea {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .edit-form input[type="file"] {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .edit-form button {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.2em;
            border-radius: 5px;
            cursor: pointer;
        }

        .edit-form button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1em;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .form-section {
            margin-bottom: 2em;
        }

        .form-section h3 {
            color: #c32143;
            margin-bottom: 1em;
        }

        .footer {
            background-color: #333;
            color: #fff;
            padding: 2em 0;
            font-size: 0.9em;
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .footer h4 {
            color: #f1b458;
            font-size: 1.2em;
            margin-bottom: 1em;
            font-family: 'Oswald', sans-serif;
        }

        .footer p {
            font-size: 0.9em;
            line-height: 1.6;
            color: #ccc;
        }

        .footer_links,
        .footer_social {
            list-style: none;
            padding: 0;
        }

        .footer_links li,
        .footer_social li {
            margin-bottom: 0.5em;
        }

        .footer_links li a,
        .footer_social li a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .footer_links li a:hover,
        .footer_social li a:hover {
            color: #f1b458;
        }

        .footer_social .fa {
            font-size: 1.2em;
            margin-right: 0.5em;
        }

        .copy {
            text-align: center;
            margin-top: 2em;
            padding-top: 1em;
            border-top: 1px solid #555;
        }

        .copy p {
            margin: 0;
            color: #ccc;
        }

        .copy a {
            color: #f1b458;
            text-decoration: none;
        }

        .copy a:hover {
            color: #c32143;
        }

        .clearfix {
            clear: both;
        }

        @media (max-width: 768px) {
            .edit-form {
                padding: 20px;
            }

            .profile-img {
                width: 100px;
                height: 100px;
            }

            .footer .col-md-4,
            .footer .col-md-2 {
                margin-bottom: 1.5em;
                text-align: center;
            }

            .footer_social {
                display: flex;
                justify-content: center;
                gap: 1em;
            }

            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-link {
                margin: 0.5em 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MatchMingle</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">View Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Your Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Back</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Edit Your Profile</h1>
    </div>

    <!-- Edit Profile Section -->
    <div class="edit-section">
        <div class="container">
            <div class="edit-form">
                <h2>Update Profile</h2>
                <?php echo $uploadMessage; ?>
                <?php echo $updateMessage; ?>

                <!-- Profile Photo -->
                <?php if (in_array('photo', $availableFields)): ?>
                <div class="form-section">
                    <h3>Profile Photo</h3>
                    <?php
                    $currentPhoto = !empty($user['photo']) ? "uploads/profiles/" . htmlspecialchars($user['photo']) : "images/default_profile.jpg";
                    ?>
                    <img src="<?php echo $currentPhoto; ?>" alt="Current Profile Photo" class="profile-img">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="profile_photo">Upload New Profile Photo</label>
                            <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/gif">
                        </div>
                        <button type="submit">Upload Photo</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Personal Details -->
                <?php if (array_intersect(['username', 'email', 'birth_date', 'gender', 'age', 'maritalstatus'], $availableFields)): ?>
                <div class="form-section">
                    <h3>Personal Details</h3>
                    <form method="POST">
                        <div class="row">
                            <?php if (in_array('username', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('email', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('birth_date', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="birth_date">Date of Birth</label>
                                <input type="date" name="birth_date" id="birth_date" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>" required>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('age', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="age">Age</label>
                                <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" min="18" required>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('gender', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" required>
                                    <option value="" disabled>Select</option>
                                    <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo ($user['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('maritalstatus', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="maritalstatus">Marital Status</label>
                                <select name="maritalstatus" id="maritalstatus" required>
                                    <option value="" disabled>Select</option>
                                    <option value="Single" <?php echo ($user['maritalstatus'] ?? '') == 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Divorced" <?php echo ($user['maritalstatus'] ?? '') == 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo ($user['maritalstatus'] ?? '') == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit">Update Personal Details</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Religious Details -->
                <?php if (in_array('religion', $availableFields) || in_array('caste', $availableFields)): ?>
                <div class="form-section">
                    <h3>Religious Details</h3>
                    <form method="POST">
                        <div class="row">
                            <?php if (in_array('religion', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="religion">Religion</label>
                                <input type="text" name="religion" id="religion" value="<?php echo htmlspecialchars($user['religion'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('caste', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="caste">Caste</label>
                                <input type="text" name="caste" id="caste" value="<?php echo htmlspecialchars($user['caste'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit">Update Religious Details</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Location Details -->
                <?php if (array_intersect(['district', 'state', 'country'], $availableFields)): ?>
                <div class="form-section">
                    <h3>Location Details</h3>
                    <form method="POST">
                        <div class="row">
                            <?php if (in_array('district', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="district">District</label>
                                <input type="text" name="district" id="district" value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('state', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="state">State</label>
                                <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('country', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="country">Country</label>
                                <input type="text" name="country" id="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit">Update Location Details</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Education and Occupation -->
                <?php if (array_intersect(['education', 'occupation'], $availableFields)): ?>
                <div class="form-section">
                    <h3>Education and Occupation</h3>
                    <form method="POST">
                        <div class="row">
                            <?php if (in_array('education', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="education">Education</label>
                                <input type="text" name="education" id="education" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('occupation', $availableFields)): ?>
                            <div class="col-md-6 form-group">
                                <label for="occupation">Occupation</label>
                                <input type="text" name="occupation" id="occupation" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit">Update Education and Occupation</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- About Me -->
                <?php if (in_array('aboutme', $availableFields)): ?>
                <div class="form-section">
                    <h3>About Me</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="aboutme">About Me</label>
                            <textarea name="aboutme" id="aboutme" rows="5" style="width: 100%;"><?php echo htmlspecialchars($user['aboutme'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit">Update About Me</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col_2">
                    <h4>About Us</h4>
                    <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships. We connect verified profiles from diverse backgrounds to make partner search easy, safe, and efficient. Our goal is to simplify matchmaking using smart tools and a secure environment. Whether you're looking for love or a life partner, we're here to support your journey. Join MatchMingle — where meaningful connections begin.</p>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Help & Support</h4>
                    <ul class="footer_links">
                        <li><a href="livehelp.php">24x7 Live help</a></li>
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
            <div class="clearfix"></div>
            <div class="copy">
                <p>Copyright © 2025 Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>