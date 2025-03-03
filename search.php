<?php
include "config/database.php";

// Process form submissions for rental or purchase requests (from search results)
$formSuccess = "";
$formError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['rent_car_id'])) {
        $car_id = intval($_POST['rent_car_id']);
        $user_name = $conn->real_escape_string($_POST['name']);
        $user_email = $conn->real_escape_string($_POST['email']);
        $user_contact = $conn->real_escape_string($_POST['contact']);
        $user_location = $conn->real_escape_string($_POST['location']);
        $user_message = $conn->real_escape_string($_POST['message']);
        $request_type = 'rental';

        // Process driving license upload
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

        $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message, license_path) 
                VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message', '$licensePath')";
        if ($conn->query($sql)) {
            $formSuccess = "Your rental request has been submitted!";
        } else {
            $formError = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['purchase_car_id'])) {
        $car_id = intval($_POST['purchase_car_id']);
        $user_name = $conn->real_escape_string($_POST['name']);
        $user_email = $conn->real_escape_string($_POST['email']);
        $user_contact = $conn->real_escape_string($_POST['contact']);
        $user_location = $conn->real_escape_string($_POST['location']);
        $user_message = $conn->real_escape_string($_POST['message']);
        $request_type = 'purchase';
        $sql = "INSERT INTO requests (car_id, request_type, name, email, contact, location, message) 
                VALUES ('$car_id', '$request_type', '$user_name', '$user_email', '$user_contact', '$user_location', '$user_message')";
        if ($conn->query($sql)) {
            $formSuccess = "Your purchase request has been submitted!";
        } else {
            $formError = "Error: " . $conn->error;
        }
    }
}

// Retrieve search parameters from GET
$searchName = isset($_GET['name']) ? trim($_GET['name']) : "";
$searchCategory = isset($_GET['category']) ? trim($_GET['category']) : "any";
$minPrice = isset($_GET['min_price']) ? trim($_GET['min_price']) : "";
$maxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : "";

// Build SQL query based on criteria
$whereClauses = [];
if ($searchName !== "") {
    $whereClauses[] = "name LIKE '%" . $conn->real_escape_string($searchName) . "%'";
}
if ($searchCategory !== "" && $searchCategory != "any") {
    $whereClauses[] = "category = '" . $conn->real_escape_string($searchCategory) . "'";
}
if ($minPrice !== "") {
    $whereClauses[] = "price >= '" . $conn->real_escape_string($minPrice) . "'";
}
if ($maxPrice !== "") {
    $whereClauses[] = "price <= '" . $conn->real_escape_string($maxPrice) . "'";
}

$query = "SELECT * FROM cars";
if (count($whereClauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}
$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);

// Determine default action for "View Details" based on category filter
$defaultAction = "";
if ($searchCategory == "rental") {
    $defaultAction = "rent";
} elseif ($searchCategory == "purchase") {
    $defaultAction = "purchase";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Search Cars - Car Rental & Hire</title>
  <link rel="stylesheet" href="css/style.css">
  <script>
    function toggleForm(formId) {
      var form = document.getElementById(formId);
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
    <h1>Search Cars</h1>
    <!-- Search Form -->
    <form method="GET" action="search.php">
      <label for="name">Car Name:</label>
      <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($searchName); ?>">
      
      <label for="category">Category:</label>
      <select id="category" name="category">
        <option value="any" <?php if($searchCategory=="any") echo "selected"; ?>>Any</option>
        <option value="rental" <?php if($searchCategory=="rental") echo "selected"; ?>>Rental</option>
        <option value="purchase" <?php if($searchCategory=="purchase") echo "selected"; ?>>Purchase</option>
        <option value="both" <?php if($searchCategory=="both") echo "selected"; ?>>Both</option>
      </select>
      
      <label for="min_price">Min Price:</label>
      <input type="text" id="min_price" name="min_price" value="<?php echo htmlspecialchars($minPrice); ?>">
      
      <label for="max_price">Max Price:</label>
      <input type="text" id="max_price" name="max_price" value="<?php echo htmlspecialchars($maxPrice); ?>">
      
      <button type="submit" class="btn">Search</button>
    </form>
    
    <?php 
      if ($formSuccess) { echo "<p class='success'>$formSuccess</p>"; }
      if ($formError) { echo "<p class='error'>$formError</p>"; }
    ?>
    
    <h2>Search Results</h2>
    <?php if ($result && $result->num_rows > 0): ?>
      <div class="car-list">
        <?php while ($car = $result->fetch_assoc()): ?>
          <div class="car-item">
            <?php
              $imgResult = $conn->query("SELECT image_path FROM car_images WHERE car_id = " . $car['id'] . " LIMIT 1");
              $imgData = $imgResult->fetch_assoc();
              $imgSrc = ($imgData) ? $imgData['image_path'] : 'images/default_car.jpg';
            ?>
            <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="cursor:pointer;" onclick="window.location='car_details.php?id=<?php echo $car['id']; ?><?php echo ($defaultAction != '') ? '&action=' . $defaultAction : ''; ?>'">
            <h3><?php echo htmlspecialchars($car['name']); ?></h3>
            <p><?php echo substr(htmlspecialchars($car['description']), 0, 100) . '...'; ?></p>
            <a href="car_details.php?id=<?php echo $car['id']; ?><?php echo ($defaultAction != '') ? '&action=' . $defaultAction : ''; ?>" class="btn">View Details</a>
            
            <!-- Rental Option  -->
            <?php if ($defaultAction == "rental" || $defaultAction == ""): ?>
              <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- If not logged in, prompt to register/login instead of renting -->
                <a href="/carrental/register.php" class="btn">Please register or login to rent</a>
              <?php else: ?>
                <button onclick="toggleForm('rentForm_<?php echo $car['id']; ?>')" class="btn">Rent</button>
                <div id="rentForm_<?php echo $car['id']; ?>" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px;">
                  <form method="POST" action="search.php?<?php echo http_build_query($_GET); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="rent_car_id" value="<?php echo $car['id']; ?>">
                    <label>Your Name:</label><br><input type="text" name="name" required><br>
                    <label>Your Email:</label><br><input type="email" name="email" required><br>
                    <label>Contact Number:</label><br><input type="text" name="contact" required><br>
                    <label>Location:</label><br><input type="text" name="location" required><br>
                    <label>Additional Details:</label><br><textarea name="message"></textarea><br>
                    <label>Upload Driving License:</label><br><input type="file" name="driving_license" accept="image/*" required><br>
                    <button type="submit" class="btn">Submit Rental Request</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php endif; ?>
            
            <!-- Purchase Option  -->
            <?php if ($defaultAction == "purchase" || $defaultAction == ""): ?>
              <button onclick="toggleForm('purchaseForm_<?php echo $car['id']; ?>')" class="btn">Purchase</button>
              <div id="purchaseForm_<?php echo $car['id']; ?>" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px;">
                <form method="POST" action="search.php?<?php echo http_build_query($_GET); ?>">
                  <input type="hidden" name="purchase_car_id" value="<?php echo $car['id']; ?>">
                  <label>Your Name:</label><br><input type="text" name="name" required><br>
                  <label>Your Email:</label><br><input type="email" name="email" required><br>
                  <label>Contact Number:</label><br><input type="text" name="contact" required><br>
                  <label>Location:</label><br><input type="text" name="location" required><br>
                  <label>Additional Details:</label><br><textarea name="message"></textarea><br>
                  <button type="submit" class="btn">Submit Purchase Request</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p>No cars match your search criteria. Please try different options.</p>
    <?php endif; ?>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
