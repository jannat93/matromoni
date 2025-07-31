<?php
header('Content-Type: application/json');
$apiKey = 'xai-dPQ2lGf1K3n8EyFWTqrEUymRYLCCI2CtkvrlmmiVNvfsNwlEq1oIR2dhPA264owl3lpdxEMhWDDiOK'; // Replace with your actual API key
$message = $_POST['message'] ?? '';

if ($message) {
    $ch = curl_init('https://api.x.ai/v1/grok');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
} else {
    echo json_encode(['error' => 'No message provided']);
}
?>