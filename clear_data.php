<?php

session_start();



// Check if user is logged in

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {

    echo "User not logged in.";

    exit();

}



// Database connection details
$servername = "sql110.infinityfree.com";
$username = "if0_37153447";
$password = "phrz4sWrOhx";
$dbname = "if0_37153447_kyliebabe";



// Create connection

$conn = new mysqli($servername, $username, $password, $dbname);



// Check connection

if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

}



// SQL query to delete all data

$sql = "DELETE FROM page_stats";



if ($conn->query($sql) === TRUE) {

    echo "All data has been cleared successfully.";

} else {

    echo "Error clearing data: " . $conn->error;

}



$conn->close();

?>

