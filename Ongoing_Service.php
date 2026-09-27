<?php
session_start();

// Include your database connection code here
$mysqli = new mysqli("localhost", "root", "", "Ambulance_Service");

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get user ID from the session
$userID = $_SESSION['user_id'];

// Process Reached Destination button press
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reached_destination'])) {
    $serviceID = $_POST['service_id'];

    // Update status, end time, and calculate travel time
    $updateSql = "UPDATE services SET Status = 0, EndTime = NOW() WHERE ServiceID = ?";
    $updateStmt = $mysqli->prepare($updateSql);
    $updateStmt->bind_param("i", $serviceID);
    $updateStmt->execute();
}

// Retrieve ongoing services for the current user with user and driver details
$sql = "SELECT services.*, 
               users.FirstName AS UserName, 
               users.PhoneNumber AS UserPhoneNumber, 
               drivers.LicenseNumber,
               driverUser.PhoneNumber AS DriverPhoneNumber,
               sessionUser.PhoneNumber AS SessionUserPhoneNumber
        FROM services
        INNER JOIN users ON services.UserID = users.UserID
        INNER JOIN drivers ON services.DriverID = drivers.DriverID
        INNER JOIN users AS driverUser ON drivers.UserID = driverUser.UserID
        INNER JOIN users AS sessionUser ON services.UserID = sessionUser.UserID
        WHERE services.UserID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Services</title>
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
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px 0;
        }

        main {
            padding: 30px 0;
        }

        .service-list {
            max-width: 600px;
            margin: auto;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-body {
            padding: 20px;
        }

        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 10px 0;
        }

        .reached-destination-btn {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .time-left {
            color: #007bff;
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
    <h1>Ongoing Services</h1>
</header>

<!-- Main content section -->
<main class="container">

    <!-- Ongoing Services List section -->
    <section class="service-list">
        <?php
        while ($row = $result->fetch_assoc()) {
            $startTime = new DateTime($row['StartTime']);
            $currentTime = new DateTime();
            $timeLeft = $currentTime->diff($startTime)->format('%H:%I:%S');
            ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['UserName']; ?></h5>
                    
                    <p class="card-text">User Phone Number: <?php echo $row['UserPhoneNumber']; ?></p>
                    <p class="card-text">PickUp Location: <?php echo $row['PickupLocation']; ?></p>
                    <p class="card-text">DropOff Location: <?php echo $row['DropoffLocation']; ?></p>
                    <p class="card-text">Driver License Number: <?php echo $row['LicenseNumber']; ?></p>
                    <p class="card-text">Driver Phone Number: <?php echo $row['DriverPhoneNumber']; ?></p>
                    

                    <?php if ($row['Status']) { ?>
                        <!-- Add a button to indicate reaching destination -->
                        <p class="time-left">Time Left to Start: <?php echo $timeLeft; ?></p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="service_id" value="<?php echo $row['ServiceID']; ?>">
                            <button type="submit" name="reached_destination" class="btn btn-success reached-destination-btn">
                                Reached Destination
                            </button>
                        </form>
                    <?php } else { ?>
                        <p class="text-muted">Destination Reached.</p>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
        ?>

        <?php
        // Check if no ongoing services found
        if ($result->num_rows === 0) {
            ?>
            <div class="alert alert-info" role="alert">
                No ongoing services found for the current user.
            </div>
            <?php
        }
        ?>
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

<!-- Bootstrap JS and Popper.js links (required for Bootstrap components) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
