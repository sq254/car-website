<?php
include "config/database.php";

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Process Rental Request Form Submission
$rentalSuccess = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rent_car_id'])) {
    $car_id = intval($_POST['rent_car_id']);
    $user_name = $conn->real_escape_string($_POST['name']);
    $user_email = $conn->real_escape_string($_POST['email']);
    $user_contact = $conn->real_escape_string($_POST['contact']);
    $user_location = $conn->real_escape_string($_POST['location']);
    $user_message = $conn->real_escape_string($_POST['message']);
    $request_type = 'rental';
    
    // Process Driving License Upload
    $licensePath = "";
    if (isset($_FILES['driving_license']) && $_FILES['driving_license']['error'] == 0) {
        $target_dir = "uploads/license/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["driving_license"]["name"]);
        if (move_uploaded_file($_FILES["driving_license"]["tmp_name"], $target_file)) {
            $licensePath = $target_file;
        }
    }
    
    // Insert request including the driving license path
    $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message, license_path) 
            VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message', '$licensePath')";
    if ($conn->query($sql)) {
        $rentalSuccess = "Your rental request has been submitted!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Rental Cars - Car Rental & Hire Hub</title>
  <link rel="stylesheet" href="css/style.css">
  <script>
    function toggleRentForm(carId) {
      var form = document.getElementById("rentForm_" + carId);
      if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
      } else {
        form.style.display = "none";
      }
    }
  </script>
</head>
<body>
  <?php include "header.php"; ?>
  <div class="container">
    <h1>Rental Cars</h1>
    <?php if ($rentalSuccess) { echo "<p class='success'>$rentalSuccess</p>"; } ?>
    <div class="car-list">
      <?php
      // Query cars where category is 'rental' or 'both'
      $query = "SELECT * FROM cars WHERE category IN ('rental','both') ORDER BY created_at DESC";
      $result = $conn->query($query);
      while ($car = $result->fetch_assoc()):
          // Retrieve one image for the car (if available)
          $imgResult = $conn->query("SELECT image_path FROM car_images WHERE car_id = " . $car['id'] . " LIMIT 1");
          $imgData = $imgResult->fetch_assoc();
          $imgSrc = ($imgData) ? $imgData['image_path'] : 'images/default_car.jpg';
      ?>
        <div class="car-item">
          <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="cursor:pointer;" onclick="window.location='car_details.php?id=<?php echo $car['id']; ?>&action=rent'">
          <h3><?php echo htmlspecialchars($car['name']); ?></h3>
          <p><?php echo substr(htmlspecialchars($car['description']), 0, 100) . '...'; ?></p>
          <a href="car_details.php?id=<?php echo $car['id']; ?>&action=rent" class="btn">View Details</a>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- If user is not logged in, prompt to register -->
            <a href="/carrental/register.php" class="btn">Please register or login to rent</a>
          <?php else: ?>
            <!-- If logged in, allow rental -->
            <button onclick="toggleRentForm(<?php echo $car['id']; ?>)" class="btn">Rent</button>
            <div id="rentForm_<?php echo $car['id']; ?>" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px;">
              <form method="POST" action="rental_cars.php" enctype="multipart/form-data">
                <input type="hidden" name="rent_car_id" value="<?php echo $car['id']; ?>">
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
                <input type="file" name="driving_license" required><br>
                <button type="submit" class="btn">Submit Rental Request</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
