<?php
// Establish a connection to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ambulance_Service";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape special characters to prevent SQL injection
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $nid = mysqli_real_escape_string($conn, $_POST['nid']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Insert user data into the database
    $sql = "INSERT INTO Users (FirstName, LastName, PhoneNumber, Email, Address, PasswordHash, IsDriver, NIDNumber)
            VALUES ('$firstName', '$lastName', '$contactNumber', '$email', '$location', '$password', 0, '$nid')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login_form.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>