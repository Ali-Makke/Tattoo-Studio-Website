<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

// editing tattoos
if (isset($_POST['edit_image'])) {
    $tattooId = $_POST['image_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $categoryId = $_POST['category_id'];
    $sqlEditImage = "UPDATE tattoos SET description = '$description', category_id = $categoryId WHERE id = $tattooId";
    mysqli_query($conn, $sqlEditImage);
}

if(isset($_POST['delete_image'])){
    $tattooId = $_POST['image_id'];

    $sqlDeleteTattoo = "DELETE FROM tattoos WHERE id = $tattooId";
    if (mysqli_query($conn, $sqlDeleteTattoo)) {
        $success = "Tattoo deleted successfully.";
    } else {
        echo "Error deleting tattoo: " . mysqli_error($conn);
    }
}

// Handle visibility toggle (Show/Hide)
if (isset($_POST['toggle_visibility'])) {
    $tattooId = $_POST['image_id'];
    $newStatus = $_POST['current_status'] == 'yes' ? 'no' : 'yes';  // Toggle status
    $sqlToggleStatus = "UPDATE tattoos SET is_gallery_image = '$newStatus' WHERE id = $tattooId";
    mysqli_query($conn, $sqlToggleStatus);
}

// Handle filters
$visibilityFilter = isset($_GET['visibility']) ? $_GET['visibility'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

// Build the query with filtering
$sqlTattoos = "SELECT tattoos.*, categories.name AS category_name 
               FROM tattoos 
               LEFT JOIN categories ON tattoos.category_id = categories.id
               WHERE 1";

// Apply visibility filter if set
if ($visibilityFilter != '') {
    $sqlTattoos .= " AND tattoos.is_gallery_image = '$visibilityFilter'";
}

// Apply category filter if set
if ($categoryFilter != '') {
    $sqlTattoos .= " AND tattoos.category_id = $categoryFilter";
}

$sqlTattoos .= " ORDER BY tattoos.date_finished DESC";

$resultTattoos = mysqli_query($conn, $sqlTattoos);

// Fetch categories for the filter dropdown
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
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Manage Tattoo Gallery</h2>

        <!-- Filter Form -->
        <div class="form-section">
            <h3>Filters</h3>
            <form method="GET" action="">
                <label for="visibility"></label>
                <select id="visibility" name="visibility">
                    <option value="">-- Select Visibility --</option>
                    <option value="yes" <?php echo ($visibilityFilter == 'yes' ? 'selected' : ''); ?>>Visible</option>
                    <option value="no" <?php echo ($visibilityFilter == 'no' ? 'selected' : ''); ?>>Hidden</option>
                </select>

                <label for="category"></label>
                <select id="category" name="category">
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = mysqli_fetch_assoc($resultCategories)) : ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryFilter == $cat['id'] ? 'selected' : ''); ?>><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Edit/Delete Tattoos -->
        <div class="form-section">
            <h3>Manage Tattoos</h3>
            <table border="1" class="table">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Visibility</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultTattoos)) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><img src="<?php echo $row['finished_tattoo_url']; ?>" alt="Tattoo Image" width="100"></td>
                        <td><?php echo trim(htmlspecialchars($row['description'])) ? htmlspecialchars($row['description']) : "Not Set"; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td>
                            <?php if ($row['is_gallery_image'] == 'yes'): ?>
                                <span style="color: green;">Visible</span>
                            <?php else: ?>
                                <span style="color: red;">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Toggle Visibility (Show/Hide) -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="image_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $row['is_gallery_image']; ?>">
                                <button type="submit" name="toggle_visibility" onclick="return confirm('Are you sure you want to toggle visibility?');">
                                    <?php echo $row['is_gallery_image'] == 'yes' ? 'Hide' : 'Show'; ?>
                                </button>
                            </form>
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
                                <button type="submit" name="delete_image" onclick="return confirm('Are you sure you want to completely delete the image?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>
