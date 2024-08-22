<?php
// Connect to database
$servername = "sql110.infinityfree.com";
$username = "if0_37153447";
$password = "phrz4sWrOhx";
$dbname = "if0_37153447_kyliebabe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Track view or click
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $page = $_POST['page'];
    $action = $_POST['action'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $date = date("Y-m-d");

    // Update page_statistics table
    if ($action == 'view') {
        $sql = "INSERT INTO page_statistics (page_name, date, views, clicks) 
                VALUES ('$page', '$date', 1, 0) 
                ON DUPLICATE KEY UPDATE views = views + 1";
    } elseif ($action == 'click') {
        $sql = "INSERT INTO page_statistics (page_name, date, views, clicks) 
                VALUES ('$page', '$date', 0, 1) 
                ON DUPLICATE KEY UPDATE clicks = clicks + 1";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
