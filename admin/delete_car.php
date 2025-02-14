<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include "../config/database.php";

// Check if a delete request was made
if (isset($_GET['delete_id'])) {
    $car_id = intval($_GET['delete_id']);
    
    // Delete associated images from the file system
    $sqlImages = "SELECT image_path FROM car_images WHERE car_id = $car_id";
    $resultImages = $conn->query($sqlImages);
    while ($row = $resultImages->fetch_assoc()) {
        $imagePath = $row['image_path'];
        // Ensure the file exists before attempting to delete it
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Delete image records and then the car record
    $conn->query("DELETE FROM car_images WHERE car_id = $car_id");
    $conn->query("DELETE FROM cars WHERE id = $car_id");
    $success = "Car deleted successfully!";
}

$sql = "SELECT * FROM cars ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Car</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include("../header.php"); ?>
    <div class="container">
        <h2>Delete Car</h2>
        <?php 
        if (isset($success)) { 
           echo "<p class='success'>$success</p>"; 
        }
        ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                        <a href="delete_car.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this car?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
