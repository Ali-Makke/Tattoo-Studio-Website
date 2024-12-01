<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_id = mysqli_real_escape_string($conn, $_POST['image_id']);

    $imageQuery = "SELECT * FROM gallery WHERE id = '$image_id'";
    $imageResult = mysqli_query($conn, $imageQuery);
    $image = mysqli_fetch_assoc($imageResult);
    if ($image) {
        $image_url = $image['image_url'];
        if (file_exists($image_url)) {
            unlink($image_url);
        }
        
        $deleteQuery = "DELETE FROM gallery WHERE id = '$image_id'";
        if (mysqli_query($conn, $deleteQuery)) {
            echo "The image has been deleted.";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Image not found.";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Gallery Image</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
    <script>
        function previewImage() {
            const select = document.getElementById('image_id');
            const preview = document.getElementById('image_preview');
            const selectedOption = select.options[select.selectedIndex];
            const imageUrl = selectedOption.getAttribute('data-url');
            preview.src = imageUrl;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('image_id');
            select.addEventListener('change', previewImage);
        });
    </script>
</head>

<body>
    <div class="container img_remove">
        <header>
            <?php include 'navbar.php'; ?>
            <h1 class="heading">Remove Tattoo from Gallery</h1>
        </header>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <h2>Remove Gallery Image</h2>
            <label for="image_id">Select Image:</label>
            <select id="image_id" name="image_id" required>
                <?php
                $sqlImages = "SELECT id, image_url FROM gallery";
                $resultImages = mysqli_query($conn, $sqlImages);
                while ($row = mysqli_fetch_assoc($resultImages)) {
                    echo "<option value=\"" . $row['id'] . "\" data-url=\"" . htmlspecialchars($row['image_url']) . "\">" . htmlspecialchars($row['image_url']) . "</option>";
                }
                ?>
            </select>
            <br>
            <button type="submit">Remove Image</button>
            <br><br>
            <a href="admin.php">Back to Admin Dashboard</a>
        </form>
        <div>
            <h3>Image Preview:</h3>
            <div class="preview_img">
                <img id="image_preview" src="" style="max-width: 100%;">
            </div>
        </div>
    </div>
</body>

</html>