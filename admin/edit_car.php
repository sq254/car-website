<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include "../config/database.php";

// If a delete request is made:
if (isset($_GET['delete_id'])) {
    $car_id = intval($_GET['delete_id']);
    
    // Delete associated images from the file system
    $sqlImages = "SELECT image_path FROM car_images WHERE car_id = $car_id";
    $resultImages = $conn->query($sqlImages);
    while ($row = $resultImages->fetch_assoc()) {
        $imagePath = $row['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Delete image records and then the car record
    $conn->query("DELETE FROM car_images WHERE car_id = $car_id");
    $conn->query("DELETE FROM cars WHERE id = $car_id");
    $success = "Car deleted successfully!";
}

// If a specific car is selected for editing:
if (isset($_GET['id'])) {
    $car_id = intval($_GET['id']);
    $sql = "SELECT * FROM cars WHERE id = $car_id";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        echo "Car not found.";
        exit();
    }
    $car = $result->fetch_assoc();

    // Process form submission to update car details
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $specs = $conn->real_escape_string($_POST['specs']);
        $price = $conn->real_escape_string($_POST['price']);
        $category = $conn->real_escape_string($_POST['category']);

        $sql_update = "UPDATE cars SET name='$name', description='$description', specs='$specs', price='$price', category='$category' WHERE id=$car_id";
        if ($conn->query($sql_update) === TRUE) {
            $success = "Car updated successfully!";
            // Refresh car data
            $sql = "SELECT * FROM cars WHERE id = $car_id";
            $result = $conn->query($sql);
            $car = $result->fetch_assoc();
        } else {
            $error = "Error updating car: " . $conn->error;
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
       <meta charset="UTF-8">
       <title>Edit Car</title>
       <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
       <?php include("../header.php"); ?>
       <div class="container">
           <h2>Edit Car</h2>
           <?php 
           if (isset($success)) {
               echo "<p class='success'>$success</p>";
           }
           if (isset($error)) {
               echo "<p class='error'>$error</p>";
           }
           ?>
           <form method="POST" action="">
              <label for="name">Car Name:</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" required>
              
              <label for="description">Description:</label>
              <textarea id="description" name="description" required><?php echo htmlspecialchars($car['description']); ?></textarea>
              
              <label for="specs">Specifications:</label>
              <textarea id="specs" name="specs" required><?php echo htmlspecialchars($car['specs']); ?></textarea>
              
              <label for="price">Price:</label>
              <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($car['price']); ?>" step="0.01" required>
              
              <label for="category">Category:</label>
              <select id="category" name="category" required>
                <option value="rental" <?php if($car['category']=='rental') echo 'selected'; ?>>Rental</option>
                <option value="purchase" <?php if($car['category']=='purchase') echo 'selected'; ?>>Purchase</option>
                <option value="both" <?php if($car['category']=='both') echo 'selected'; ?>>Both</option>
              </select>
              
              <button type="submit">Update Car</button>
           </form>
           <br>
           <!-- Delete Car button -->
           <a href="edit_car.php?delete_id=<?php echo $car['id']; ?>" onclick="return confirm('Are you sure you want to delete this car?');" class="btn">Delete Car</a>
           <br><br>
           <a href="edit_car.php">Back to Car List</a>
       </div>
       <?php include("../footer.php"); ?>
    </body>
    </html>
    <?php
    exit();
} else {
    // No specific car selected – list all cars with both Edit and Delete options
    $sql = "SELECT * FROM cars ORDER BY created_at DESC";
    $result = $conn->query($sql);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
       <meta charset="UTF-8">
       <title>Edit/Delete Car</title>
       <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
       <?php include("../header.php"); ?>
       <div class="container">
          <h2>Select a Car to Edit/Delete</h2>
          <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
          <table border="1" cellpadding="5" cellspacing="0">
             <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Action</th>
             </tr>
             <?php while($row = $result->fetch_assoc()){ ?>
                <tr>
                   <td><?php echo $row['id']; ?></td>
                   <td><?php echo htmlspecialchars($row['name']); ?></td>
                   <td><?php echo htmlspecialchars($row['category']); ?></td>
                   <td>
                      <a href="edit_car.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                      <a href="edit_car.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this car?');">Delete</a>
                   </td>
                </tr>
             <?php } ?>
          </table>
       </div>
       <?php include("../footer.php"); ?>
    </body>
    </html>
    <?php
}
?>
