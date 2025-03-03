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
                  <th>Driving License</th>
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
                  <td>
                      <?php 
                        if (!empty($row['license_path'])) {
                            $licenseFile = "../" . $row['license_path'];
                            $ext = strtolower(pathinfo($licenseFile, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                // The image is clickable; the external JS opens it in a modal.
                                echo "<img src='" . htmlspecialchars($licenseFile) . "' alt='Driving License' style='max-width:100px;cursor:pointer;' onclick='openModal(\"" . htmlspecialchars($licenseFile) . "\")'>";
                            } else {
                                echo "<a href='" . htmlspecialchars($licenseFile) . "' target='_blank'>" . basename($licenseFile) . "</a>";
                            }
                        } 
                      ?>
                  </td>
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
    
    <!-- Modal Structure -->
    <div id="myModal" class="modal">
      <span class="close" onclick="closeModal()">&times;</span>
      <img class="modal-content" id="modalImg">
    </div>
    
    <?php include("../footer.php"); ?>
    <script src="../js/script.js"></script>
</body>
</html>
