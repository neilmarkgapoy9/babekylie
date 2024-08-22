<?php
// Database connection details
$servername = "sql110.infinityfree.com";
$username = "if0_37153447";
$password = "phrz4sWrOhx";
$dbname = "if0_37153447_kyliebabe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
$page = $_POST['page'];
$action = $_POST['action'];
$country = $_POST['country'] ?? 'Unknown';
$city = $_POST['city'] ?? 'Unknown';
$ip = $_SERVER['REMOTE_ADDR'];
$os = $_POST['os'] ?? 'Unknown OS';
$userAgent = $_POST['userAgent'] ?? 'Unknown User Agent';
$family = $_POST['family'] ?? 'Unknown Family';
$device = $_POST['device'] ?? 'Unknown Device';

// Insert data into the tracking table
$sql = "INSERT INTO page_stats (ip, country, city, action_datetime, page, os, user_agent, family, device, action) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $ip, $country, $city, $page, $os, $userAgent, $family, $device, $action);

if ($stmt->execute()) {
    echo "Tracking data inserted successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>
