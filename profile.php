<?php
session_start();

// Assuming you have a database connection established
$mysqli = new mysqli("localhost", "root", "", "Ambulance_Service");

// Check the connection
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
$sql = "SELECT * FROM Users WHERE UserID = $userID";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    // User found, retrieve information
    $row = $result->fetch_assoc();
    $userName = $row['FirstName'] . ' ' . $row['LastName'];
    $userEmail = $row['Email'];
    $userPhoneNumber = $row['PhoneNumber'];
    $userAddress = $row['Address'];
    $isDriver = $row['IsDriver'];
    if($isDriver==1){
        $driversql = "SELECT * FROM drivers WHERE UserID = $userID";
        $driverResult = $mysqli->query($driversql);
        if ($driverResult->num_rows > 0) {
            $driverInfo = $driverResult->fetch_assoc();
            $driverID = $driverInfo["DriverID"];
        }
        $vehiclesql = "SELECT * FROM vehicles WHERE driverID = $driverID";
        $vehicleResult = $mysqli->query($vehiclesql);
        if ($vehicleResult->num_rows > 0) {
            $vehicleInfo = $vehicleResult->fetch_assoc();
        }
    }
} else {
    // User not found, handle accordingly
    echo "User not found.";
    exit();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <!-- Bootstrap CSS link -->
    <link rel="stylesheet" href="css/all.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Open+Sans:wght@300&family=Oswald:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  
    <!-- Custom styles -->
    <style>
       .banner_logo{
  display: flex;
  align-items: center;
}
.banner_logo h4{
  font-family: 'Dancing Script', cursive;
  font-weight: bold;
  margin-right: 10px; 
  color: red;
  font-size: 30px;
}
.banner_logo i{
  color: red;
}
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px 0;
        }

        main {
            padding: 30px 0;
        }

        .user-info {
            margin-bottom: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 20px;
        }

        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 10px 0;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        h2, p {
            color: #333;
        }

        .card {
            border-radius: 10px;
        }

        footer {
            margin-top: 50px;
        }

.button-container {
  display: flex;
  justify-content: center;
  align-items: center;
   /* Centers vertically within the viewport */
}

.button {
  display: inline-block;
  padding: 10px 20px;
  text-align: center;
  text-decoration: none;
  background-color: #007bff; /* Change this to your preferred button color */
  color: #fff; /* Text color */
  border-radius: 5px;
  border: none;
  transition: background-color 0.3s ease;
  width: 200px; /* Adjust the width to your desired value */
}

.button:hover {
  background-color: #0056b3; /* Change this to the hover color you prefer */
  color: white;
}

a.button {
  text-decoration: none; }

  .driver_status p{
    text-align: center;
  }

  .driver_status{
    margin-bottom: 50px;
  }


  .navbar-nav .nav-item .nav-link{

margin-left: 5px;
color: black;
font-family: 'Open Sans', sans-serif;
font-size: 18px;
}

.navbar-nav .dropdown-menu .dropdown-item:hover{

 background-color: #04a590;
 color: white;
 text-align: center;
 font-weight: medium;
}

.navbar-nav .dropdown-menu .dropdown-item{
text-align: center;
font-weight: medium;
font-family: 'Open Sans', sans-serif;
}

.navbar-nav .nav-item .nav-link:hover
{
color: #04a590; /* Change this to the desired hover color */
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

<!--nav bar html start-->
<div class="header_main">

<div class="container">
    <div class="row">

        <div class="col-md-2">
            <div class="banner_logo">
                <h4>SAVE LIFE</h4>
                <i class="fa-solid fa-heart"></i>
            </div>
        </div>

        <div class="col-md-10">

            <div class="navigation">

                <nav class="navbar navbar-expand-lg ">
                    <div class="container">
                    
                      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                      </button>
                      <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto">
                          <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="index.html">Home</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" href="#">About</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" href="#">Contact</a>
                          </li>
                          <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                              Service
                            </a>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="search.html">Search Ambulance</a></li>
                              <li><a class="dropdown-item" href="Ongoing_Service.php">Ongoing Services</a></li>
                              <li><hr class="dropdown-divider"></li>
                              <li><a class="dropdown-item" href="#">Ambulance Information</a></li>
                            </ul>
                          </li>

                          <li class="nav-item">
                            <a class="nav-link" href="signup_form.html">Register</a>
                          </li>   
                        
                        </ul>
                        <form class="d-flex" role="search">

                          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                          <button class="btn btn-outline-success" type="submit" style="font-family: 'Open Sans', sans-serif;">Search</button>

                        </form>

      
                      </div>
                    </div>
                  </nav>


            </div>

        </div>

    </div>
</div>

</div>




<!--nav bar html end-->


<!-- Header section -->
<header class="text-center">
    <h1>User Profile</h1>
</header>

<!-- Main content section -->
<main class="container">

    <!-- User Information section -->
    <section class="user-info">
        <h2 class="text-center mb-4">User Information</h2>
        <!-- Display user information using Bootstrap card -->
        <div class="card">
            <div class="card-body">
                <!-- Display user information retrieved from the database -->
                <img src="./images/about.jpg" alt="User Profile" class="profile-image mx-auto d-block mb-3">
                <p class="card-text"><strong>Name:</strong> <?php echo $userName; ?></p>
                <p class="card-text"><strong>Email:</strong> <?php echo $userEmail; ?></p>
                <p class="card-text"><strong>Phone Number:</strong> <?php echo $userPhoneNumber; ?></p>
                <p class="card-text"><strong>Address:</strong> <?php echo $userAddress; ?></p>
                <!-- Button to edit user details -->
                <button class="btn btn-secondary btn-block" data-toggle="modal" data-target="#editUserModal">Edit User Details</button>
                <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php endif; ?>
            </div>
        </div>
    </section>

</main>



<!-- Footer section -->
<!-- Become a Driver section -->
<?php if ($isDriver == 1): ?>
    <!-- Driver Information section -->
    <section class="driver-info">
        <h2 class="text-center mb-4">Driver Information</h2>
        <!-- Display driver information using Bootstrap card -->
        <div class="card">
            <div class="card-body">
                <!-- Display driver information retrieved from the database -->
                <p class="card-text"><strong>Car Type:</strong> <?php echo $vehicleInfo['Type']; ?></p>
                <p class="card-text"><strong>Location:</strong> <?php echo $vehicleInfo['CurrentLocation']; ?></p>
                <p class="card-text"><strong>License Plate:</strong> <?php echo $driverInfo['LicenseNumber']; ?></p>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Become a Driver section ... -->
    <section class="driver-option">
        <h2 class="text-center mb-4">Become a Driver</h2>
        <!-- Display the option to become a driver using Bootstrap card -->
        <div class="card">
            <div class="card-body">
                <div class="driver_status">
                <p class="card-text">If you want to become a driver and provide ambulance services, click the button below:</p>
                </div>
                <form action="update_driver_status.php" method="post">
                    <input type="hidden" name="user_id" value="1"> <!-- Replace with the actual user ID -->
                    
                    <div class="button-container">
                      <a href="./driver_form.php" class="button">Become a Driver</a>
                   </div>


                </form>
            </div>
        </div>
    </section>
<?php endif; ?>

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
                          1212, Soth Bannasree<br>
                          Dhaka, Bangladesh</p>
                          <p>aniklal2020@gmail.com</p>
                          <p>01537156605</p>
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add form elements for editing user details here -->
                <form action="update_user_details.php" method="post">
                    <!-- Replace with input fields for editing user details -->
                    <div class="form-group">
                        <label for="editName">Name</label>
                        <input type="text" class="form-control" id="editName" name="editName" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="editEmail" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="editPhone">Phone Number</label>
                        <input type="tel" class="form-control" id="editPhone" name="editPhone" placeholder="Enter your phone number">
                    </div>
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="editAddress" placeholder="Enter your address">
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js links (required for Bootstrap components) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>

</body>
</html>
