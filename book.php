<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book an Ambulance</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .header h4{
        text-align: center;
        padding-top: 30px;
        font-size: 30px;
        color: white;
        text-transform: uppercase;
        
    }
    .header{
        margin-top: 10px;
        background-color: green;
        height: 100px;
    }
  </style>
</head>
<body>


<div class="container">
    <div class="row">
        <div class="col-md-12">
        <div class="header">
        <h4>Book Your Ambulance</h4>
        </div>
        </div>
    </div>
</div>

<?php
if (isset($_GET['driverPhoneNumber']) && isset($_GET['userId']) && isset($_GET['vehicleId']) && isset($_GET['driverId'])) { 
    
    $userId = $_GET['userId'];
    $vehicleId = $_GET['vehicleId'];
    $driverId = $_GET['driverId'];

    // Form for additional input fields using Bootstrap classes

    echo '
    <div class="container mt-3">
        <form action="process_form.php" method="POST">
            <div class="mb-3">
                <label for="userId" class="form-label">UserID:</label>
                <input type="number" class="form-control" id="userId" name="userId" value="' . $userId . '">
            </div>
            
            <div class="mb-3">
                <label for="driverId" class="form-label">DriverID:</label>
                <input type="number" class="form-control" id="driverId" name="driverId" value="' . $driverId . '">
            </div>
            
            <div class="mb-3">
                <label for="vehicleId" class="form-label">VehicleID:</label>
                <input type="number" class="form-control" id="vehicleId" name="vehicleId" value="' . $vehicleId . '">
            </div>
            
            <div class="mb-3">
                <label for="pickupLocation" class="form-label">PickupLocation:</label>
                <input type="text" class="form-control" id="pickupLocation" name="pickupLocation">
            </div>
            
            <div class="mb-3">
                <label for="dropoffLocation" class="form-label">DropoffLocation:</label>
                <input type="text" class="form-control" id="dropoffLocation" name="dropoffLocation">
            </div>
            
            <div class="mb-3">
                <label for="startTime" class="form-label">StartTime:</label>
                <input type="datetime-local" class="form-control" id="startTime" name="startTime">
            </div>
            
            <div class="mb-3">
                <label for="endTime" class="form-label">EndTime:</label>
                <input type="datetime-local" class="form-control" id="endTime" name="endTime">
            </div>
            
            
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>';
} else {
    echo "Parameters not provided.";
}
?>

<!-- Bootstrap JS (Optional for certain Bootstrap features like dropdowns, modals, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Get the current time
    const now = new Date();

    // Format the time to match the input's required format (HH:MM)
    const formattedTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    // Set the value of the input field with id 'startTime' to the current time
    document.getElementById('startTime').value = `${new Date().toISOString().split('T')[0]}T${formattedTime}`;
</script>
</body>
</html>
