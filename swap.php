<?php
// swap.php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

if (!isloggedin()) {
    error_log("User not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$receiverId = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$response = ['success' => false, 'message' => ''];

// Subscription tier swap limits
$swapLimits = [
    'free' => 5,
    'basic' => 5,
    'premium' => 20,
    'gold' => PHP_INT_MAX
];

// Fetch user details
$sql = "SELECT plan_type, swap_requests_sent, username FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    $response['message'] = "User not found.";
    echo json_encode($response);
    exit();
}

if ($action === 'send' && $receiverId > 0 && $receiverId != $userId) {
    // Check swap limit
    if ($user['swap_requests_sent'] >= $swapLimits[strtolower($user['plan_type'])]) {
        $response['message'] = "Daily swap limit reached. Upgrade your plan!";
        echo json_encode($response);
        exit();
    }

    // Check if swap request already exists
    $sql = "SELECT id FROM swap_requests WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $receiverId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $response['message'] = "Swap request already sent.";
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_close($stmt);

    // Create swap request
    $sql = "INSERT INTO swap_requests (sender_id, receiver_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $receiverId);
    if (mysqli_stmt_execute($stmt)) {
        // Update swap_requests_sent
        $sql = "UPDATE users SET swap_requests_sent = swap_requests_sent + 1 WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt2, "i", $userId);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        // Fetch receiver's details
        $sql = "SELECT username, phone FROM users WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt2, "i", $receiverId);
        mysqli_stmt_execute($stmt2);
        $result = mysqli_stmt_get_result($stmt2);
        $receiver = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt2);

        // Send SMS notification to receiver
        if ($receiver['phone']) {
            try {
                $twilio->messages->create(
                    $receiver['phone'],
                    [
                        'from' => TWILIO_PHONE_NUMBER,
                        'body' => "You have a new swap request from {$user['username']} on MatchMingle! Awaiting admin approval."
                    ]
                );
            } catch (Exception $e) {
                error_log("Twilio SMS failed: " . $e->getMessage());
            }
        }

        $response['success'] = true;
        $response['message'] = "Swap request sent for admin approval!";
    } else {
        $response['message'] = "Failed to send swap request.";
    }
    mysqli_stmt_close($stmt);
}

echo json_encode($response);
mysqli_close($conn);
?>