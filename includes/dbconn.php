<?php
// includes/dbconn.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matrimonial1";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Twilio configuration
define('TWILIO_SID', 'your-twilio-sid'); // Replace with your Twilio SID
define('TWILIO_AUTH_TOKEN', 'your-twilio-token'); // Replace with your Twilio Auth Token
define('TWILIO_PHONE_NUMBER', '+1234567890'); // Replace with your Twilio phone number

// Use relative path to vendor/autoload.php from includes/
require_once __DIR__ . '/../vendor/autoload.php'; // Move up to project root

use Twilio\Rest\Client;

$twilio = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);
?>