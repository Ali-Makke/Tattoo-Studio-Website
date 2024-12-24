<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';


// Add New Image to gallery page
if (isset($_POST['add_image'])) {
    $tattooId = $_POST['image_id'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $sqlAddImage = "UPDATE tattoos SET is_gallery_image = 'yes', description = '$description' WHERE id = '$tattooId'";
    mysqli_query($conn, $sqlAddImage);
}

// Edit Tattoo Description and Category
if (isset($_POST['edit_image'])) {
    $tattooId = $_POST['image_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $categoryId = $_POST['category_id'];
    $sqlEditImage = "UPDATE tattoos SET description = '$description', category_id = $categoryId WHERE id = $tattooId";
    mysqli_query($conn, $sqlEditImage);
}

// Delete Image from gallery page
if (isset($_POST['delete_image'])) {
    $tattooId = $_POST['image_id'];
    $sqlDeleteImage = "UPDATE tattoos SET is_gallery_image = 'no' WHERE id = $tattooId";
    mysqli_query($conn, $sqlDeleteImage);
}

// Fetch all tattoos
$sqlTattoos = "SELECT * FROM tattoos WHERE tattoos.is_gallery_image = 'no' ";
$resultTattos = mysqli_query($conn, $sqlTattoos);

// Fetch Tattoos for Gallery
$sqlGallery = "SELECT tattoos.*, categories.name AS category_name 
               FROM tattoos 
               LEFT JOIN categories ON tattoos.category_id = categories.id 
               WHERE tattoos.is_gallery_image = 'yes' 
               ORDER BY tattoos.date_finished DESC";
$resultGallery = mysqli_query($conn, $sqlGallery);

// Fetch Categories
$sqlCategories = "SELECT * FROM categories";
$resultCategories = mysqli_query($conn, $sqlCategories);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Tattoo Gallery</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
    <script>
        function previewImage() {
            const select = document.getElementById('image_url');
            const preview = document.getElementById('image_preview');
            const selectedOption = select.options[select.selectedIndex];
            const imageUrl = selectedOption.getAttribute('data-url');
            preview.src = imageUrl;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('image_url');
            select.addEventListener('change', previewImage);
        });
    </script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Manage Tattoo Gallery</h2>

        <!-- Add New Image -->
        <div class="form-section">
            <h3>Add Tattoo To Gallery</h3>
            <form method="POST">
                <label for="image_url">Tattoo image:</label>
                <select id="image_url" name="image_id" required>
                    <option value="">-- Select Tattoo --</option>
                    <?php while ($row = mysqli_fetch_assoc($resultTattos)) {
                        echo "<option value=\"" . $row['id'] . "\" data-url=\"" . htmlspecialchars($row['finished_tattoo_url']) . "\">" . $row['tattoo_id'] . htmlspecialchars($row['finished_tattoo_url']) . "</option>";
                    } ?>
                </select>
                <label for="description">Description(optional):</label>
                <textarea id="description" name="description"></textarea>
                <button type="submit" name="add_image">Add Image</button>
            </form>
        </div>
        <h3>Tattoo Preview:</h3>
        <div class="preview_img">
            <img id="image_preview" src="" style="max-width: 100%;">
        </div>

        <!-- Edit/Delete Gallery Images -->
        <div class="form-section">
            <h3>Manage Gallery Images</h3>
            <table border="1" class="table">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultGallery)) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><img src="<?php echo $row['finished_tattoo_url']; ?>" alt="Tattoo Image" width="100"></td>
                        <td><?php echo trim(htmlspecialchars($row['description'])) ? htmlspecialchars($row['description']) : "Not Set"; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td>
                            <!-- Edit category and description -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="image_id" value="<?php echo $row['id']; ?>">
                                <input type="text" name="description" placeholder="Edit Description" required>
                                <select name="category_id" required>
                                    <?php
                                    mysqli_data_seek($resultCategories, 0);
                                    while ($cat = mysqli_fetch_assoc($resultCategories)) {
                                        $selected = $row['category_id'] == $cat['id'] ? 'selected' : '';
                                        echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="edit_image" onclick="return confirm('Are you sure?');">Edit</button>
                            </form>
                            <!-- Delete Form -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="image_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_image" onclick="return confirm('Are you sure?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
</body>

</html>