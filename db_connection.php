<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emergency_ambulance_service";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
