<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once("includes/dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $attachment = $_FILES['attachment']['name'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
        header("Location: contact.php");
        exit();
    }

    $attachmentPath = '';
    if (!empty($attachment)) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($attachment);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        if (in_array($imageFileType, $allowedTypes) && move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
            $attachmentPath = $targetFile;
        } else {
            header("Location: contact.php");
            exit();
        }
    }

    $userId = $_SESSION['id'];
    $sql = "INSERT INTO messages (user_id, name, email, phone, subject, message, attachment, sent_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssss", $userId, $name, $email, $phone, $subject, $message, $attachmentPath);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: success.php");
            exit();
        } else {
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
    header("Location: contact.php");
    exit();
}
?>



