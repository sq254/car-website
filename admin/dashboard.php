<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <?php include("../header.php"); ?>
  <div class="container">
    <h2>Welcome, <?php echo $_SESSION['admin']; ?></h2>
    <nav>
      <a href="add_car.php" class="btn">Add Car</a> |
      <a href="edit_car.php" class="btn">Edit Car</a> |
      <a href="delete_car.php" class="btn">Delete Car</a> |
      <a href="view_requests.php" class="btn">View Requests</a> |
      <a href="logout.php" class="btn">Logout</a>
    </nav>
  </div>
  <?php include("../footer.php"); ?>
</body>
</html>
