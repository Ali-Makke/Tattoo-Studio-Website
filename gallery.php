<?php
include 'authentication_check.php';
require 'db_connect.php';

$sqlCategories = "SELECT DISTINCT categories.id, categories.name 
                  FROM categories 
                  JOIN gallery ON categories.id = gallery.category_id";
$resultCategories = mysqli_query($conn, $sqlCategories);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Gallery</title>
    <link rel="icon" type="image/x-icon" href="images/icons/t4.png">
    <link rel="stylesheet" href="styles/included.css">
    <link rel="stylesheet" href="styles/style_gallery.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="wrapper">
        <header>
            <?php include 'navbar.php' ?>
        </header>
        <div class="container">
            <?php
            while ($category = mysqli_fetch_assoc($resultCategories)) {
                echo "<h2>" . htmlspecialchars($category['name']) . "</h2>";
                $categoryId = $category['id'];
                $sqlGallery = "SELECT * FROM gallery WHERE category_id = '$categoryId'";
                $resultGallery = mysqli_query($conn, $sqlGallery);

                echo "<div class='gallery'>";
                while ($image = mysqli_fetch_assoc($resultGallery)) {
                    echo "<div class='gallery-item'>";
                    echo "<img src='" . htmlspecialchars($image['image_url']) . "' alt='" . htmlspecialchars($image['description']) . "'>";
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <?php include 'footer.php' ?>
</body>

</html>