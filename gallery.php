<?php
include 'authentication_check.php';
require 'db_connect.php';

$sqlTattooImages = "SELECT DISTINCT categories.id, categories.name
                  FROM categories 
                  JOIN tattoos ON categories.id = tattoos.category_id
                  WHERE is_gallery_image = 'yes'";
$resultTattooImages = mysqli_query($conn, $sqlTattooImages);
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
            if (mysqli_num_rows($resultTattooImages) > 0) {
                while ($category = mysqli_fetch_assoc($resultTattooImages)) {
                    echo "<h2>" . htmlspecialchars($category['name']) . "</h2>";
                    $categoryId = $category['id'];
                    $sqlGallery = "SELECT * FROM tattoos WHERE category_id = '$categoryId' && is_gallery_image = 'yes'";
                    $resultGallery = mysqli_query($conn, $sqlGallery);
                    
                    echo "<div class='gallery'>";
                    while ($image = mysqli_fetch_assoc($resultGallery)) {
                        echo "<div class='gallery-item'>";
                        echo "<img src='" . htmlspecialchars($image['finished_tattoo_url']) . "' alt='" . htmlspecialchars($image['description']) . "'>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<br><h1 style='text-align: center'>No Ink Here <br>\-(0-0)-/</h1><br><br>";
            }
            ?>
        </div>
    </div>
    <?php include 'footer.php' ?>
</body>

</html>