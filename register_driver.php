<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.html");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include your database connection code here

    // Assuming you have a database connection established
    $mysqli = new mysqli("localhost", "root", "", "Ambulance_Service");

    // Check the connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Get user ID from the session
    $userID = $_SESSION['user_id'];

    // Prepare and bind the parameters for Drivers table
    $stmt = $mysqli->prepare("INSERT INTO Drivers (UserID, LicenseNumber) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $licenseNumber);

    // Set the parameters and execute
    $licenseNumber = $_POST['licenseNumber'];

    if ($stmt->execute()) {
        // Driver registered successfully

        // Get the last inserted DriverID
        $driverID = $mysqli->insert_id;

        // Prepare and bind the parameters for Vehicles table
        $stmtVehicles = $mysqli->prepare("INSERT INTO Vehicles ( VehicleNumber, Type, CurrentLocation, Availability,DriverID) VALUES ( ?, ?, ?,?,?)");
        $stmtVehicles->bind_param("sssii", $vehicleNumber, $vehicleType, $currentLocation, $availability,$driverID);

        // Set the parameters and execute
        $vehicleNumber = $_POST['vehicleNumber'];
        $vehicleType = $_POST['vehicleType'];
        $currentLocation = $_POST['currentLocation'];
        $availability = $_POST['availability'];

        
        if ($stmtVehicles->execute()) {
            // Vehicle inserted successfully

            // Get the last inserted VehicleID
            $updateUserStmt = $mysqli->prepare("UPDATE Users SET isDriver = 1 WHERE UserID = ?");
            $updateUserStmt->bind_param("i", $userID);
            $updateUserStmt->execute();
            $updateUserStmt->close();
            echo "Driver registered successfully.";
        } else {
            // Error inserting Vehicle
            echo "Error inserting Vehicle.";
        }

        $stmtVehicles->close();
    } else {
        // Error registering driver
        echo "Error registering driver.";
    }

    $stmt->close();
    $mysqli->close();
}
?>
