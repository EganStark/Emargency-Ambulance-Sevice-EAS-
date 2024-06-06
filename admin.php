<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "ambulance_service";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to delete user or driver
// Function to delete driver and related vehicle
function deleteDriverAndUser($conn, $userIdToDelete)
{
    // Get the driver's associated vehicle ID and user ID
    $getDriverInfoQuery = "SELECT * FROM drivers WHERE UserID = $userIdToDelete";
    $getDriverresult = mysqli_query($conn, $getDriverInfoQuery);
    if ($getDriverresult->num_rows > 0) {
    $getDriverRow = mysqli_fetch_assoc($getDriverresult);
    $driverId = $getDriverRow['DriverID'];

    // Delete from the vehicles table first
    // if ($driverId) {
    //     $deleteVehicleQuery = "DELETE FROM vehicles WHERE VehicleID = $vehicleId";
    //     mysqli_query($conn, $deleteVehicleQuery);
    // }
    $deleteVehicleQuery = "DELETE FROM vehicles WHERE DriverID = $driverId";
    mysqli_query($conn, $deleteVehicleQuery);
    // Then delete from the drivers table
    $deleteDriverQuery = "DELETE FROM drivers WHERE DriverID = $driverId";
    mysqli_query($conn, $deleteDriverQuery);

    // Finally, delete from the users table
    }
    
}

// Check if the delete button is clicked
if (isset($_GET['delete'])) {
    $userIdToDelete = $_GET['delete'];
    deleteDriverAndUser($conn, $userIdToDelete);
    $deleteUserQuery = "DELETE FROM users WHERE UserID = $userIdToDelete";
    mysqli_query($conn, $deleteUserQuery);
    // Optionally, you can perform additional actions, if needed
    // ...

    header("Location: admin.php");
    exit();
}

// Fetch user information
$userQuery = "SELECT * FROM users";
$userResult = mysqli_query($conn, $userQuery);

// Fetch driver information with related user details
$driverQuery = "SELECT drivers.DriverID, drivers.LicenseNumber, users.* FROM drivers JOIN users ON drivers.UserID = users.UserID";
$driverResult = mysqli_query($conn, $driverQuery);

// Check if the delete button is clicked
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Custom styles -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px 0;
        }

        main {
            padding: 30px 0;
        }

        .admin-table {
            max-width: 1000px;
            margin: auto;
        }

        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 10px 0;
        }
        /*footer css start*/

.common_head h4{
  text-transform: uppercase;
  text-align: center;
  margin-top: 50px;
  font-family: 'Open Sans', sans-serif;
  font-weight: bold;
  margin-bottom: 10px;

}
.common_head{
  margin-bottom: 50px;;
}

.footer_common_head{
    margin-top: 30px;
}

footer{
  margin-top: 50px;
  background-color: #010101;
}

.footer_common_head h4{
  color: #01ecce;
  
}
.footer_text p{
  color: white;
}

.footer_form{
  text-align: center;
  margin-bottom: 20px;
}

/* Apply styles to the form container */
.footer_form form {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Style the input box */
.footer_form input {
  padding: 10px;
  width: 90%;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-right: 5px;
  outline: none;
}

/* Style the submit button */
.footer_form button {
  padding: 10px 15px;
  background-color: #01ecce; /* Change the color as needed */
  color: black;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  width: 10%;
  text-transform: uppercase;
}

.footer_icon{
  margin-bottom: 130px;
}

.footer_icon ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center; /* Adjust as needed */
}

.footer_icon li {
  margin: 0 10px;
  padding: 0;
}

.footer_icon a {
  display: inline-block;
  padding: 5px; /* Adjust padding as needed */
  text-decoration: none;
  color: #fff; /* Adjust text color as needed */
  background-color: #01ecce; /* Replace with your desired background color */
  border-radius: 5px;
  font-size: 30px;
  color: black; /* Adjust border-radius for the rectangle shape */
}



.footer_item_one p{
  color: white;
}

.footer_item_two ul li {
  color: white;
}


.footer_item_three ul li {
  color: white;
}

.footer_item_four ul li {
  color: white;
}

.footer_item_fifth ul li {
  color: white;
}

.about_text{
  text-align: center;
  font-size: 20px;
  margin-bottom: 95px;
}


/*footer css end*/

.common_head .line {
  
  border: 0;
  border-top: 2px solid black;
  margin-top: 5px; /* Adjust as needed */
  width: 300px;
  margin: 0 auto;
}

.common_head h4{
  font-family: 'Oswald', sans-serif;
  font-weight: bold;
  font-size: 30px;
}


    </style>
</head>
<body>

<!-- Header section -->
<header class="text-center">
    <h1>Admin Dashboard</h1>
</header>

<!-- Main content section -->
<main class="container">

    <!-- Users and Drivers Table section -->
    <section class="admin-table">
        <h2>Users Information</h2>
        <!-- Users Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Is Driver</th>
                <th>NID Number</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($user = mysqli_fetch_assoc($userResult)) : ?>
                <tr>
                    <td><?= $user['UserID'] ?></td>
                    <td><?= $user['FirstName'] ?></td>
                    <td><?= $user['LastName'] ?></td>
                    <td><?= $user['PhoneNumber'] ?></td>
                    <td><?= $user['Email'] ?></td>
                    <td><?= $user['Address'] ?></td>
                    <td><?= $user['IsDriver'] ? 'Yes' : 'No' ?></td>
                    <td><?= $user['NIDNumber'] ?></td>
                    <td>
                        <a href="?delete=<?= $user['UserID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Drivers Information</h2>
        <!-- Drivers Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>Driver ID</th>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>License Number</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($driver = mysqli_fetch_assoc($driverResult)) : ?>
                <tr>
                    <td><?= $driver['DriverID'] ?></td>
                    <td><?= $driver['UserID'] ?></td>
                    <td><?= $driver['FirstName'] ?></td>
                    <td><?= $driver['LastName'] ?></td>
                    <td><?= $driver['PhoneNumber'] ?></td>
                    <td><?= $driver['Email'] ?></td>
                    <td><?= $driver['Address'] ?></td>
                    <td><?= $driver['LicenseNumber'] ?></td>
                    <td>
                        <a href="?delete=<?= $driver['UserID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<!--footer start-->


<footer>

  <div class="container">

      <div class="footer_content">

          <!--about common head start-->

          <div class="row">

            <div class="col-md-12">

              


               <div class="common_head footer_common_head">

                   <h4>Emergency Ambulance Service</h4>
                     
               </div>


               

            </div>

          </div>

       <!--about common haed end-->


       <div class="row">

        <div class="col-md-12">


            <div class="about_text footer_text">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent vitae eros eget tellus tristique bibendum. Donec rutrum sed sem quis venenatis.</p>
            </div>

        </div>

      </div>


      <div class="row mt-2">

            <div class="col-md-12">

                <div class="footer_form">

                    <form>
                    
                        <input type="mail" placeholder="Enter your mail to update"> <button type="submit">Submit</button>
                      
                    </form>

                </div>
               
            </div>

      </div>


      <div class="row mt-2">


          <div class="footer_icon">

              <ul>
                  <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                  <li><a href="#"> <i class="fab fa-twitter"></i></a></li>
                  <li><a href="#"><i class="fab fa-google-plus"></i></a></li>
                  <li><a href="#"><i class="fab fa-pinterest"></i></a></li>

              </ul>
          </div>


      </div>



          
      </div>



      <!--footer item start-->


      <div class="footer_item">

          <div class="row">

              <div class="col-md-4">


                  <div class="footer_item_one">
                      <p>Emergency Ambulance Service<br>

                          Dhaka, Bangladesh</p>
                          
                  </div>



              </div>


              <div class="col-md-2">

                  <div class="footer_item_two">
                      <ul>
                           <li>Examples</li>
                           <li>Search</li>
                           <li>License</li>
                           <li>Terms</li>
                      </ul>
                  </div>

              </div>

              <div class="col-md-2">

                  <div class="footer_item_three">
                      <ul>
                           <li>Download</li>
                           <li>Support</li>
                           <li>Documents</li>

                      </ul>
                  </div>

                  
              </div>

              <div class="col-md-2">

                  <div class="footer_item_four">
                      <ul>
                           <li>Contract</li>
                           <li>About</li>
                           <li>Privacy</li>
                           
                      </ul>
                  </div>
                  
              </div>
              <div class="col-md-2">

                  <div class="footer_item_fifth">
                      <ul>
                           <li>Media</li>
                           <li>Blog</li>
                           <li>Forums</li>
                           
                      </ul>
                  </div>
                  
              </div>
          </div>



      </div>



      <!--footer item end-->


<!-- Bootstrap JS and Popper.js links (required for Bootstrap components) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
