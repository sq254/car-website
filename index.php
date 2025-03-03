<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Car Rental & Hire Hub - Home</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include "header.php"; ?>
  <div class="container" style="text-align: center;">
    <!-- Website title -->
    <h1 class="site-title">Car Rental & Hire Hub</h1>
    <?php
      // Start the session if not already started
      if (session_status() == PHP_SESSION_NONE) {
          session_start();
      }
      // If a user is logged in, display the welcome note below the title
      if (isset($_SESSION['user_id'])) {
          echo '<p class="welcome-note" style="color: white;">Welcome, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
      }
    ?>
    <p style="color: white;">Please choose one of the following options:</p>
    <a href="/carrental/rental_cars.php" class="btn" >Rental</a>
    <a href="/carrental/purchase_cars.php" class="btn" >Purchase</a>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
