<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "ambulance_service";

$conn = mysqli_connect($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate user input to prevent SQL injection (you can use prepared statements)
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);
    
    // Check if the user exists in the database
    $sql = "SELECT * FROM admins WHERE Email='$email' AND PasswordHash='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // User exists, set session variables or redirect to a dashboard
        $_SESSION['loggedin'] = true;
        $_SESSION['Email'] = $email;
        header('Location: admin.php'); // Redirect to the dashboard or any authenticated page
        exit;
    } else {
        echo "Invalid email or password";
    }
}

$conn->close();
?>
