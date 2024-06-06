<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ambulance_Service";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the login data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Fetch user data from the database based on the entered email
    $sql = "SELECT UserID, FirstName, LastName, Email, PasswordHash FROM Users WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // User found, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['PasswordHash'])) {
            // Password is correct, user is authenticated

            // Start a PHP session
            session_start();

            // Store user information in the session
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['user_name'] = $row['FirstName'] . " " . $row['LastName'];
            $_SESSION['user_email'] = $row['Email'];

            // Redirect to profile.html or any other page after successful login
            header("Location: profile.php");
            exit();
        } else {
            echo "Incorrect password. Please try again.";
        }
    } else {
        echo "User not found. Please check your email and try again.";
    }
}

// Close the database connection
$conn->close();
?>