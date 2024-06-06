<?php
// Assuming you have a database connection established
// Replace 'your_host', 'your_username', 'your_password', and 'your_database' with your actual credentials

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ambulance_Service";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form data if it's submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $userID = $_POST['userId'];
    $driverID = $_POST['driverId'];
    $vehicleID = $_POST['vehicleId'];
    $pickupLocation = $_POST['pickupLocation'];
    $dropoffLocation = $_POST['dropoffLocation'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $status = "1";

    // Prepare and execute the SQL statement to insert data into your database table
    $sql = "INSERT INTO services (UserID, DriverID, VehicleID, PickupLocation, DropoffLocation, StartTime, EndTime, Status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters to the prepared statement
        $stmt->bind_param("ssssssss", $userID, $driverID, $vehicleID, $pickupLocation, $dropoffLocation, $startTime, $endTime, $status);

        // Execute the prepared statement
        $stmt->execute();

        // Check if the insertion was successful
        if ($stmt->affected_rows > 0) {

            header("Location: search.html");
            
        } else {
            echo "Error: Unable to insert data.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: Unable to prepare statement.";
    }
}

// Close the database connection
$conn->close();
?>
