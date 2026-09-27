<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Driver</title>
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

        .driver-form {
            max-width: 600px;
            margin: auto;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 20px;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
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
    <h1>Become a Driver</h1>
</header>

<!-- Main content section -->
<main class="container">

    <!-- Driver Registration Form section -->
    <section class="driver-form">
        <div class="card">
            <div class="card-body">
                <!-- Driver Registration Form -->
                <form action="register_driver.php" method="post" enctype="multipart/form-data">
                    <!-- Hidden field to store the user ID -->
                    <input type="hidden" name="user_id" value="1"> <!-- Replace with the actual user ID -->

                    <!-- License Number Input -->
                    <div class="form-group">
                        <label for="licenseNumber">License Number</label>
                        <input type="text" class="form-control" id="licenseNumber" name="licenseNumber" placeholder="Enter your license number" required>
                    </div>

                    <!-- Vehicle Information Inputs -->
                    <div class="form-group">
                        <label for="vehicleNumber">Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicleNumber" name="vehicleNumber" placeholder="Enter your vehicle number" required>
                    </div>

                    <div class="form-group">
                        <label for="vehicleType">Vehicle Type</label>
                        <input type="text" class="form-control" id="vehicleType" name="vehicleType" placeholder="Enter your vehicle type" required>
                    </div>

                    <div class="form-group">
                        <label for="currentLocation">Current Location</label>
                        <input type="text" class="form-control" id="currentLocation" name="currentLocation" placeholder="Enter your current location" required>
                    </div>

                    <div class="form-group">
                        <label for="availability">Availability</label>
                        <select class="form-control" id="availability" name="availability" required>
                            <option value="1">Available</option>
                            <option value="0">Not Available</option>
                        </select>
                    </div>

                    <!-- Car Image Input -->
                    <div class="form-group">
                        <label for="carImage">Car Image</label>
                        <input type="file" class="form-control-file" id="carImage" name="carImage" accept="image/*" required>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-success btn-block">Submit</button>
                </form>
            </div>
        </div>
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
