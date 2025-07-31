<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "matrimonial1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$receiverId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if interest already sent
$checkInterest = $conn->prepare("SELECT id FROM interests WHERE sender_id = ? AND receiver_id = ?");
$checkInterest->bind_param("ii", $userId, $receiverId);
$checkInterest->execute();
$result = $checkInterest->get_result();

if ($result->num_rows == 0) {
    // Insert interest request
    $insertInterest = $conn->prepare("INSERT INTO interests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
    $insertInterest->bind_param("iis", $userId, $receiverId);
    $insertInterest->execute();
    $insertInterest->close();
}

$checkInterest->close();
$conn->close();

// Redirect back to matchright.php
header("Location: matchright.php");
exit();
?>