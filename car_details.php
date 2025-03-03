<?php
include "config/database.php";

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure a car ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$car_id = intval($_GET['id']);
$car_query = $conn->query("SELECT * FROM cars WHERE id = $car_id");
if ($car_query->num_rows != 1) {
    echo "Car not found!";
    exit();
}
$car = $car_query->fetch_assoc();

// Determine action (rent or purchase)
$action = isset($_GET['action']) ? $_GET['action'] : "";

// Process form submission if action is set and form is posted
$requestSuccess = "";
$requestError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == "rent" || $action == "purchase")) {
    $user_name     = $conn->real_escape_string($_POST['name']);
    $user_email    = $conn->real_escape_string($_POST['email']);
    $user_contact  = $conn->real_escape_string($_POST['contact']);
    $user_location = $conn->real_escape_string($_POST['location']);
    $user_message  = $conn->real_escape_string($_POST['message']);
    $request_type  = $action;  // "rent" or "purchase"
    
    if ($action == "rent") {
        // Process driving license upload
        $licensePath = "";
        if (isset($_FILES['driving_license']) && $_FILES['driving_license']['error'] == 0) {
            $target_dir = "uploads/license/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            // Create a unique filename to avoid collisions
            $filename = time() . '_' . basename($_FILES["driving_license"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["driving_license"]["tmp_name"], $target_file)) {
                $licensePath = $target_file;
            }
        }
        // Insert rental request including driving license path
        $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message, license_path) 
                VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message', '$licensePath')";
    } else {
        // Insert purchase request (no license upload)
        $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message) 
                VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message')";
    }
    
    if ($conn->query($sql)) {
        $requestSuccess = "Your " . htmlspecialchars($action) . " request has been submitted!";
    } else {
        $requestError = "Error: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($car['name']); ?> - Details</title>
  <link rel="stylesheet" href="css/style.css">
  <script>
    function toggleForm(type) {
      var rentalForm = document.getElementById('rentalForm');
      var purchaseForm = document.getElementById('purchaseForm');
      if (type === 'rent') {
          if (rentalForm.style.display === 'none' || rentalForm.style.display === '') {
              rentalForm.style.display = 'block';
              purchaseForm.style.display = 'none';
          } else {
              rentalForm.style.display = 'none';
          }
      } else if (type === 'purchase') {
          if (purchaseForm.style.display === 'none' || purchaseForm.style.display === '') {
              purchaseForm.style.display = 'block';
              rentalForm.style.display = 'none';
          } else {
              purchaseForm.style.display = 'none';
          }
      }
    }
  </script>
</head>
<body>
  <?php include "header.php"; ?>
  <div class="container" style="text-align: center;">
      <h2><?php echo htmlspecialchars($car['name']); ?></h2>
      
      <!-- Display all images in a gallery -->
      <div class="car-images">
          <?php 
          $images_query = $conn->query("SELECT image_path FROM car_images WHERE car_id = $car_id");
          while ($img = $images_query->fetch_assoc()) {
              echo "<img src='" . htmlspecialchars($img['image_path']) . "' alt='" . htmlspecialchars($car['name']) . "'>";
          }
          ?>
      </div>
      
      <!-- Car Information -->
      <div class="car-info">
          <p><?php echo htmlspecialchars($car['description']); ?></p>
          <p><?php echo htmlspecialchars($car['specs']); ?></p>
          <p>Price: <?php echo htmlspecialchars($car['price']); ?></p>
      </div>
      
      <!-- Toggle Buttons for Rental and Purchase --> 
      <div class="car-actions" style="margin-top:20px;">
          <button class="btn" onclick="toggleForm('rent')">Rent</button>
          <button class="btn" onclick="toggleForm('purchase')">Purchase</button>
      </div>
      
      <!-- Rental Form with Driving License Upload --> 
      <div id="rentalForm" style="display:none; margin-top:20px;">
          <?php if (!isset($_SESSION['user_id'])): ?>
              <div class="form-container">
                  <h3>Rent</h3>
                  <p class="error">Please <a href="register.php" style="color: #fff;">register</a> or <a href="login.php" style="color: #fff;">login</a> to rent this car.</p>
              </div>
          <?php else: ?>
              <div class="form-container">
                  <h3>Rent</h3>
                  <form method="POST" action="car_details.php?id=<?php echo $car_id; ?>&action=rent" enctype="multipart/form-data">
                      <input type="hidden" name="rent_car_id" value="<?php echo $car_id; ?>">
                      <label>Your Name:</label><br>
                      <input type="text" name="name" required><br>
                      <label>Your Email:</label><br>
                      <input type="email" name="email" required><br>
                      <label>Contact Number:</label><br>
                      <input type="text" name="contact" required><br>
                      <label>Location:</label><br>
                      <input type="text" name="location" required><br>
                      <label>Additional Details:</label><br>
                      <textarea name="message"></textarea><br>
                      <label>Upload Driving License:</label><br>
                      <input type="file" name="driving_license" accept="image/*" required><br>
                      <button type="submit" class="btn">Submit Rental Request</button>
                  </form>
              </div>
          <?php endif; ?>
      </div>
      
      <!-- Purchase Form --> 
      <div id="purchaseForm" style="display:none; margin-top:20px;">
          <div class="form-container">
              <h3>Purchase</h3>
              <form method="POST" action="car_details.php?id=<?php echo $car_id; ?>&action=purchase">
                  <input type="hidden" name="purchase_car_id" value="<?php echo $car_id; ?>">
                  <label>Your Name:</label><br>
                  <input type="text" name="name" required><br>
                  <label>Your Email:</label><br>
                  <input type="email" name="email" required><br>
                  <label>Contact Number:</label><br>
                  <input type="text" name="contact" required><br>
                  <label>Location:</label><br>
                  <input type="text" name="location" required><br>
                  <label>Additional Details:</label><br>
                  <textarea name="message"></textarea><br>
                  <button type="submit" class="btn">Submit Purchase Request</button>
              </form>
          </div>
      </div>
      
      <?php 
          if ($requestSuccess) { echo "<p class='success'>$requestSuccess</p>"; }
          if ($requestError) { echo "<p class='error'>$requestError</p>"; }
      ?>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
