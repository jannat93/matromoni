<?php
function getDBConnection() {
    $host = "localhost"; // Host name
    $username = "root"; // Mysql username
    $password = ""; // Mysql password
    $db_name = "matrimonial1"; // Database name

    $conn = mysqli_connect($host, $username, $password, $db_name) or die("Cannot connect: " . mysqli_connect_error());
    return $conn;
}

function mysqlexec($conn, $sql) {
    if ($result = mysqli_query($conn, $sql)) {
        return $result;
    } else {
        echo "Error: " . mysqli_error($conn);
        return false;
    }
}

function searchid() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['profid'])) {
        $profid = intval($_POST['profid']);
        $conn = getDBConnection();
        $sql = "SELECT * FROM customer WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $profid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    return false;
}

function search() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agemin'], $_POST['agemax'], $_POST['maritalstatus'], $_POST['country'], $_POST['state'], $_POST['religion'], $_POST['mothertounge'], $_POST['sex'])) {
        $agemin = intval($_POST['agemin']);
        $agemax = intval($_POST['agemax']);
        $maritalstatus = mysqli_real_escape_string(getDBConnection(), $_POST['maritalstatus']);
        $country = mysqli_real_escape_string(getDBConnection(), $_POST['country']);
        $state = mysqli_real_escape_string(getDBConnection(), $_POST['state']);
        $religion = mysqli_real_escape_string(getDBConnection(), $_POST['religion']);
        $mothertounge = mysqli_real_escape_string(getDBConnection(), $_POST['mothertounge']);
        $sex = mysqli_real_escape_string(getDBConnection(), $_POST['sex']);

        $conn = getDBConnection();
        $sql = "SELECT * FROM customer WHERE 
                sex = ? 
                AND age >= ? 
                AND age <= ? 
                AND maritalstatus = ? 
                AND country = ? 
                AND state = ? 
                AND religion = ? 
                AND mothertounge = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'siisssss', $sex, $agemin, $agemax, $maritalstatus, $country, $state, $religion, $mothertounge);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    return false;
}

function writepartnerprefs($id) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agemin'], $_POST['agemax'], $_POST['maritalstatus'], $_POST['colour'], $_POST['height'], $_POST['diet'], $_POST['religion'], $_POST['caste'], $_POST['mothertounge'], $_POST['education'], $_POST['occupation'], $_POST['country'], $_POST['descr'])) {
        $id = intval($id);
        $agemin = intval($_POST['agemin']);
        $agemax = intval($_POST['agemax']);
        $maritalstatus = mysqli_real_escape_string(getDBConnection(), $_POST['maritalstatus']);
        $complexion = mysqli_real_escape_string(getDBConnection(), $_POST['colour']);
        $height = mysqli_real_escape_string(getDBConnection(), $_POST['height']);
        $diet = mysqli_real_escape_string(getDBConnection(), $_POST['diet']);
        $religion = mysqli_real_escape_string(getDBConnection(), $_POST['religion']);
        $caste = mysqli_real_escape_string(getDBConnection(), $_POST['caste']);
        $mothertounge = mysqli_real_escape_string(getDBConnection(), $_POST['mothertounge']);
        $education = mysqli_real_escape_string(getDBConnection(), $_POST['education']);
        $occupation = mysqli_real_escape_string(getDBConnection(), $_POST['occupation']);
        $country = mysqli_real_escape_string(getDBConnection(), $_POST['country']);
        $descr = mysqli_real_escape_string(getDBConnection(), $_POST['descr']);

        $conn = getDBConnection();
        $sql = "UPDATE partnerprefs 
                SET agemin = ?, agemax = ?, maritalstatus = ?, complexion = ?, height = ?, diet = ?, religion = ?, caste = ?, mothertounge = ?, education = ?, descr = ?, occupation = ?, country = ? 
                WHERE custId = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iiissssssssss', $agemin, $agemax, $maritalstatus, $complexion, $height, $diet, $religion, $caste, $mothertounge, $education, $descr, $occupation, $country, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if ($result) {
            echo "<script>alert(\"Successfully updated Partner Preference\")</script>";
            echo "<script>window.location=\"userhome.php?id=$id\"</script>";
        } else {
            echo "Error updating partner preferences";
        }
    }
}

function register() {
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['pass'], $_POST['email'], $_POST['day'], $_POST['month'], $_POST['year'], $_POST['gender'])) {
        $uname = trim($_POST['name']);
        $pass = trim($_POST['pass']);
        $email = trim($_POST['email']);
        $day = $_POST['day'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $dob = "$year-$month-$day";
        $gender = $_POST['gender'];

        $conn = getDBConnection();

        if (empty($uname) || empty($pass) || empty($email) || empty($day) || empty($month) || empty($year) || empty($gender)) {
            $message = "<p style='color: red; text-align: center;'>All fields are required!</p>";
            mysqli_close($conn);
            return $message;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<p style='color: red; text-align: center;'>Invalid email format!</p>";
            mysqli_close($conn);
            return $message;
        }

        if (!checkdate($month, $day, $year)) {
            $message = "<p style='color: red; text-align: center;'>Invalid date of birth!</p>";
            mysqli_close($conn);
            return $message;
        }

        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check_stmt, "ss", $uname, $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        if (mysqli_num_rows($check_result) > 0) {
            $message = "<p style='color: red; text-align: center;'>Username or email already exists!</p>";
            mysqli_stmt_close($check_stmt);
            mysqli_close($conn);
            return $message;
        }
        mysqli_stmt_close($check_stmt);

        $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, birth_date, gender, status) VALUES (?, ?, ?, ?, ?, 'active')");
        mysqli_stmt_bind_param($insert_stmt, "sssss", $uname, $hashed_password, $email, $dob, $gender);

        if (mysqli_stmt_execute($insert_stmt)) {
            $message = "<p style='color: green; text-align: center;'>Successfully Registered. <a href=\"login.php\">Login to your account</a></p>";
        } else {
            $message = "<p style='color: red; text-align: center;'>Error registering user: " . mysqli_error($conn) . "</p>";
        }

        mysqli_stmt_close($insert_stmt);
        mysqli_close($conn);
    }
    return $message;
}

function isloggedin() {
    if (!isset($_SESSION['id'])) {
        return false;
    }
    $conn = getDBConnection();
    $user_id = $_SESSION['id'];
    $query = "SELECT status FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'banned') {
            session_destroy();
            header("Location: banned.php");
            exit();
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return true;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return false;
}

function processprofile_form($id) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fname'], $_POST['lname'], $_POST['sex'], $_POST['email'], $_POST['day'], $_POST['month'], $_POST['year'], $_POST['religion'], $_POST['caste'], $_POST['subcaste'], $_POST['country'], $_POST['state'], $_POST['district'], $_POST['age'], $_POST['maritalstatus'], $_POST['profileby'], $_POST['education'], $_POST['edudescr'], $_POST['bodytype'], $_POST['physicalstatus'], $_POST['drink'], $_POST['smoke'], $_POST['mothertounge'], $_POST['bloodgroup'], $_POST['weight'], $_POST['height'], $_POST['colour'], $_POST['diet'], $_POST['occupation'], $_POST['occupationdescr'], $_POST['fatheroccupation'], $_POST['motheroccupation'], $_POST['income'], $_POST['bros'], $_POST['sis'], $_POST['aboutme'])) {
        $id = intval($id);
        $fname = mysqli_real_escape_string(getDBConnection(), $_POST['fname']);
        $lname = mysqli_real_escape_string(getDBConnection(), $_POST['lname']);
        $sex = mysqli_real_escape_string(getDBConnection(), $_POST['sex']);
        $email = mysqli_real_escape_string(getDBConnection(), $_POST['email']);
        $day = $_POST['day'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $dob = "$year-$month-$day";
        $religion = mysqli_real_escape_string(getDBConnection(), $_POST['religion']);
        $caste = mysqli_real_escape_string(getDBConnection(), $_POST['caste']);
        $subcaste = mysqli_real_escape_string(getDBConnection(), $_POST['subcaste']);
        $country = mysqli_real_escape_string(getDBConnection(), $_POST['country']);
        $state = mysqli_real_escape_string(getDBConnection(), $_POST['state']);
        $district = mysqli_real_escape_string(getDBConnection(), $_POST['district']);
        $age = intval($_POST['age']);
        $maritalstatus = mysqli_real_escape_string(getDBConnection(), $_POST['maritalstatus']);
        $profileby = mysqli_real_escape_string(getDBConnection(), $_POST['profileby']);
        $education = mysqli_real_escape_string(getDBConnection(), $_POST['education']);
        $edudescr = mysqli_real_escape_string(getDBConnection(), $_POST['edudescr']);
        $bodytype = mysqli_real_escape_string(getDBConnection(), $_POST['bodytype']);
        $physicalstatus = mysqli_real_escape_string(getDBConnection(), $_POST['physicalstatus']);
        $drink = mysqli_real_escape_string(getDBConnection(), $_POST['drink']);
        $smoke = mysqli_real_escape_string(getDBConnection(), $_POST['smoke']);
        $mothertounge = mysqli_real_escape_string(getDBConnection(), $_POST['mothertounge']);
        $bloodgroup = mysqli_real_escape_string(getDBConnection(), $_POST['bloodgroup']);
        $weight = mysqli_real_escape_string(getDBConnection(), $_POST['weight']);
        $height = mysqli_real_escape_string(getDBConnection(), $_POST['height']);
        $colour = mysqli_real_escape_string(getDBConnection(), $_POST['colour']);
        $diet = mysqli_real_escape_string(getDBConnection(), $_POST['diet']);
        $occupation = mysqli_real_escape_string(getDBConnection(), $_POST['occupation']);
        $occupationdescr = mysqli_real_escape_string(getDBConnection(), $_POST['occupationdescr']);
        $fatheroccupation = mysqli_real_escape_string(getDBConnection(), $_POST['fatheroccupation']);
        $motheroccupation = mysqli_real_escape_string(getDBConnection(), $_POST['motheroccupation']);
        $income = mysqli_real_escape_string(getDBConnection(), $_POST['income']);
        $bros = intval($_POST['bros']);
        $sis = intval($_POST['sis']);
        $aboutme = mysqli_real_escape_string(getDBConnection(), $_POST['aboutme']);

        $conn = getDBConnection();
        $sql = "SELECT cust_id FROM customer WHERE cust_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($result) >= 1) {
            $sql = "UPDATE customer 
                    SET email = ?, age = ?, sex = ?, religion = ?, caste = ?, subcaste = ?, district = ?, state = ?, country = ?, maritalstatus = ?, profilecreatedby = ?, education = ?, education_sub = ?, firstname = ?, lastname = ?, body_type = ?, physical_status = ?, drink = ?, mothertounge = ?, colour = ?, weight = ?, height = ?, blood_group = ?, diet = ?, smoke = ?, dateofbirth = ?, occupation = ?, occupation_descr = ?, annual_income = ?, fathers_occupation = ?, mothers_occupation = ?, no_bro = ?, no_sis = ?, aboutme = ? 
                    WHERE cust_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sissssssssssssssssssssssssssssi', $email, $age, $sex, $religion, $caste, $subcaste, $district, $state, $country, $maritalstatus, $profileby, $education, $edudescr, $fname, $lname, $bodytype, $physicalstatus, $drink, $mothertounge, $colour, $weight, $height, $bloodgroup, $diet, $smoke, $dob, $occupation, $occupationdescr, $income, $fatheroccupation, $motheroccupation, $bros, $sis, $aboutme, $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $sql = "INSERT INTO customer (cust_id, email, age, sex, religion, caste, subcaste, district, state, country, maritalstatus, profilecreatedby, education, education_sub, firstname, lastname, body_type, physical_status, drink, mothertounge, colour, weight, height, blood_group, diet, smoke, dateofbirth, occupation, occupation_descr, annual_income, fathers_occupation, mothers_occupation, no_bro, no_sis, aboutme, profilecreationdate) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'isissssssssssssssssssssssssssss', $id, $email, $age, $sex, $religion, $caste, $subcaste, $district, $state, $country, $maritalstatus, $profileby, $education, $edudescr, $fname, $lname, $bodytype, $physicalstatus, $drink, $mothertounge, $colour, $weight, $height, $bloodgroup, $diet, $smoke, $dob, $occupation, $occupationdescr, $income, $fatheroccupation, $motheroccupation, $bros, $sis, $aboutme);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($result) {
                echo "Successfully Created profile";
                echo "<a href=\"userhome.php?id={$id}\">Back to home</a>";
                $sql2 = "INSERT INTO partnerprefs (id, custId) VALUES('', ?)";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, 'i', $id);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                $sql2 = "UPDATE users SET profilestat = 1 WHERE id = ?";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, 'i', $id);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            } else {
                echo "Error executing query: " . mysqli_error($conn);
            }
        }
        mysqli_close($conn);
    }
}

function uploadphoto($id) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pic1'], $_FILES['pic2'], $_FILES['pic3'], $_FILES['pic4'])) {
        $id = intval($id);
        $target = "profile/" . $id . "/";
        if (!file_exists($target)) {
            mkdir($target, 0777, true);
        }

        $target1 = $target . basename($_FILES['pic1']['name']);
        $target2 = $target . basename($_FILES['pic2']['name']);
        $target3 = $target . basename($_FILES['pic3']['name']);
        $target4 = $target . basename($_FILES['pic4']['name']);

        $pic1 = mysqli_real_escape_string(getDBConnection(), $_FILES['pic1']['name']);
        $pic2 = mysqli_real_escape_string(getDBConnection(), $_FILES['pic2']['name']);
        $pic3 = mysqli_real_escape_string(getDBConnection(), $_FILES['pic3']['name']);
        $pic4 = mysqli_real_escape_string(getDBConnection(), $_FILES['pic4']['name']);

        $conn = getDBConnection();
        $sql = "SELECT id FROM photos WHERE cust_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($result) == 0) {
            $sql = "INSERT INTO photos (id, cust_id, pic1, pic2, pic3, pic4) VALUES ('', ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issss', $id, $pic1, $pic2, $pic3, $pic4);
            mysqli_stmt_execute($stmt);
        } else {
            $sql = "UPDATE photos SET pic1 = ?, pic2 = ?, pic3 = ?, pic4 = ? WHERE cust_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssi', $pic1, $pic2, $pic3, $pic4, $id);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if (move_uploaded_file($_FILES['pic1']['tmp_name'], $target1) && 
            move_uploaded_file($_FILES['pic2']['tmp_name'], $target2) && 
            move_uploaded_file($_FILES['pic3']['tmp_name'], $target3) && 
            move_uploaded_file($_FILES['pic4']['tmp_name'], $target4)) {
            echo "The files have been uploaded, and your information has been added to the directory";
        } else {
            echo "Sorry, there was a problem uploading your file.";
        }
    }
}
?>