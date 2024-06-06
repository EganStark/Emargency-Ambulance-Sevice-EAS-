<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ambulance_Service";


// Assuming you have a database connection established
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.html");
    exit();
}

// Assuming you have stored the user ID in the session variable 'user_id'
$userID = $_SESSION['user_id'];

// Fetch user information from the database
$sql = "SELECT * FROM Users WHERE UserID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User found, retrieve information
    $row = $result->fetch_assoc();
} else {
    // User not found, handle accordingly
    echo "User not found.";
    exit();
}

// Close the prepared statement
$stmt->close();

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve updated values from the form
    $updatedName = $_POST['editName'];
    $updatedEmail = $_POST['editEmail'];
    $updatedPhone = $_POST['editPhone'];
    $updatedAddress = $_POST['editAddress'];

    // Update user details in the database
    $updateSql = "UPDATE Users SET FirstName = ?, Email = ?, PhoneNumber = ?, Address = ? WHERE UserID = ?";
    $updateStmt = $mysqli->prepare($updateSql);
    $updateStmt->bind_param("ssssi", $updatedName, $updatedEmail, $updatedPhone, $updatedAddress, $userID);

    if ($updateStmt->execute()) {
        // Redirect back to the profile page after successful update
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating record: " . $mysqli->error;
    }

    // Close the prepared statement
    $updateStmt->close();
}

$mysqli->close();
?>
