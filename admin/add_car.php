<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

//file size (100MB)
$allowedExtensions = array("jpg", "jpeg", "png", "gif");
$maxFileSize = 100 * 1024 * 1024; // 100 MB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // retrieve form inputs
    $name        = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $specs       = $conn->real_escape_string($_POST['specs']);
    $price       = $conn->real_escape_string($_POST['price']);
    $category    = $conn->real_escape_string($_POST['category']);

    // Insert car details into the "cars" table
    $sql = "INSERT INTO cars (name, description, specs, price, category) 
            VALUES ('$name', '$description', '$specs', '$price', '$category')";
    if ($conn->query($sql) === TRUE) {
        $car_id = $conn->insert_id; // Get the new car's ID

        // Process uploaded images, if any
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $total_files = count($_FILES['images']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                $imageName  = $_FILES['images']['name'][$i];
                $tmpName    = $_FILES['images']['tmp_name'][$i];
                $fileError  = $_FILES['images']['error'][$i];
                $fileSize   = $_FILES['images']['size'][$i];

                if ($fileError === UPLOAD_ERR_OK) {
                    $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
                    if (in_array($fileExtension, $allowedExtensions)) {
                        if ($fileSize <= $maxFileSize) {
                            //store files in the "images" folder
                            $target_dir = "../images/";
                            // Generate a unique filename to avoid collisions
                            $uniqueFileName = time() . "_" . basename($imageName);
                            $target_file = $target_dir . $uniqueFileName;

                            // Move the uploaded file
                            if (move_uploaded_file($tmpName, $target_file)) {
                                // Store the image path relative to the site root, e.g., "images/filename.jpg"
                                $relativePath = "images/" . $uniqueFileName;
                                $conn->query("INSERT INTO car_images (car_id, image_path) VALUES ('$car_id', '$relativePath')");
                            } else {
                                $error = "Failed to move uploaded file: $imageName";
                            }
                        } else {
                            $error = "File $imageName exceeds the maximum allowed size (100MB)";
                        }
                    } else {
                        $error = "File type not allowed for $imageName. Allowed types: jpg, jpeg, png, gif.";
                    }
                } else {
                    $error = "Error uploading file $imageName (Error code: $fileError)";
                }
            }
        }
        $success = "Car added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Car</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include("../header.php"); ?>
    <div class="container">
        <h2>Add Car</h2>
        <?php 
        if (isset($success)) {
            echo "<p class='success'>$success</p>";
        }
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="name">Car Name:</label>
            <input type="text" id="name" name="name" placeholder="Car Name" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" placeholder="Car Description" required></textarea>
            
            <label for="specs">Specifications:</label>
            <textarea id="specs" name="specs" placeholder="Car Specifications" required></textarea>
            
            <label for="price">Price:</label>
            <input type="text" id="price" name="price" placeholder="Price" required>
            
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="rental">Rental</option>
                <option value="purchase">Purchase</option>
                <option value="both">Both</option>
            </select>
            
            <label for="images">Upload Images (select multiple files):</label>
            <input type="file" id="images" name="images[]" multiple>
            
            <button type="submit">Add Car</button>
        </form>
    </div>
</body>
</html>
