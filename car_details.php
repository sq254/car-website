<?php
include "config/database.php";

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
    
    $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message) 
            VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message')";
    
    if ($conn->query($sql)) {
        $requestSuccess = "Your " . htmlspecialchars($action) . " request has been submitted!";
    } else {
        $requestError = "Error: " . $conn->error;
    }
}

// Fetch all images for the car
$images_query = $conn->query("SELECT image_path FROM car_images WHERE car_id = $car_id");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($car['name']); ?> - Details</title>
  <link rel="stylesheet" href="css/style.css">

</head>
<body>
  <?php include "header.php"; ?>
  <div class="container">
      <h2><?php echo htmlspecialchars($car['name']); ?></h2>
      
      <!-- Display all images in a gallery -->
      <div class="car-images">
          <?php 
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
      
      <?php if($action == "rent" || $action == "purchase"): ?>
          <?php 
          if ($requestSuccess) {
              echo "<p class='success'>$requestSuccess</p>";
          }
          if ($requestError) {
              echo "<p class='error'>$requestError</p>";
          }
          ?>
          <!-- Display only the relevant form based on the action -->
          <div class="form-container">
              <h3><?php echo ucfirst($action); ?></h3>
              <form method="POST" action="car_details.php?id=<?php echo $car_id; ?>&action=<?php echo $action; ?>">
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
                  <button type="submit" class="btn">Submit <?php echo ucfirst($action); ?> Request</button>
              </form>
          </div>
      <?php else: ?>
          <!-- If no action is provided, show buttons to choose the form -->
          <div class="car-actions">
              <a href="car_details.php?id=<?php echo $car_id; ?>&action=rent" class="btn">Rent</a>
              <a href="car_details.php?id=<?php echo $car_id; ?>&action=purchase" class="btn">Purchase</a>
          </div>
      <?php endif; ?>
      
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
