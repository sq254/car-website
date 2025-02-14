<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include("../config/database.php");

$sql = "SELECT r.*, c.name AS car_name 
        FROM requests r 
        JOIN cars c ON r.car_id = c.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Requests - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
      table { width: 100%; border-collapse: collapse; }
      th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
      th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <?php include("../header.php"); ?>
    <div class="container">
        <h2>Customer Requests</h2>
        <?php if ($result->num_rows > 0): ?>
          <table>
              <tr>
                  <th>ID</th>
                  <th>Car Name</th>
                  <th>Request Type</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Location</th>
                  <th>Message</th>
                  <th>Submitted On</th>
              </tr>
              <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['id']; ?></td>
                  <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['request_type']); ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['contact']); ?></td>
                  <td><?php echo htmlspecialchars($row['location']); ?></td>
                  <td><?php echo htmlspecialchars($row['message']); ?></td>
                  <td><?php echo $row['created_at']; ?></td>
                </tr>
              <?php endwhile; ?>
          </table>
        <?php else: ?>
          <p>No requests found.</p>
        <?php endif; ?>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
