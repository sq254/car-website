<?php
include "config/database.php";

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
    $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message) 
            VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message')";
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
          <!-- View Details Link -->
          <a href="car_details.php?id=<?php echo $car['id']; ?>&action=rent" class="btn" style="background-color: black; color: white;">View Details</a>
          <!-- Toggle Rental Form -->
          <button onclick="toggleRentForm(<?php echo $car['id']; ?>)" class="btn" style="background-color: black; color: white;">Rent Now</button>
          <div id="rentForm_<?php echo $car['id']; ?>" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px;">
            <form method="POST" action="rental_cars.php">
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
              <button type="submit" class="btn">Submit Rental Request</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
