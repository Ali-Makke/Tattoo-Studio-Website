<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';


if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $sqlAddCategory = "INSERT INTO categories (name) VALUES ('$name')";
    mysqli_query($conn, $sqlAddCategory);
}

if (isset($_POST['edit_category'])) {
    $id = $_POST['category_id'];
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $sqlEditCategory = "UPDATE categories SET name = '$name' WHERE id = $id";
    mysqli_query($conn, $sqlEditCategory);
}

if (isset($_POST['assign_category'])) {
    $tattooId = $_POST['tattoo_id'];
    $categoryId = $_POST['category_id'];
    $sqlAssignCategory = "UPDATE tattoos SET category_id = $categoryId WHERE id = $tattooId";
    mysqli_query($conn, $sqlAssignCategory);
}

$sqlCategories = "SELECT * FROM categories";
$resultCategories = mysqli_query($conn, $sqlCategories);

$sqlTattoos = "SELECT tattoos.id, tattoos.description, categories.name AS category_name
               FROM tattoos
               LEFT JOIN categories ON tattoos.category_id = categories.id";
$resultTattoos = mysqli_query($conn, $sqlTattoos);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Categories</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Manage Categories</h2>

        <div class="form-section">
            <h3>Add New Category</h3>
            <form method="POST">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" required>
                <button type="submit" name="add_category">Add Category</button>
            </form>
        </div>

        <div class="form-section">
            <h3>Edit Categories</h3>
            <table border="1" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($resultCategories)) : ?>
                        <tr>
                            <td data-label="ID"><?php echo $row['id']; ?></td>
                            <td data-label="Name"><?php echo $row['name']; ?></td>
                            <td data-label="Actions">
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="category_id" value="<?php echo $row['id']; ?>">
                                    <input type="text" name="category_name" placeholder="Edit Name" required>
                                    <button type="submit" name="edit_category">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="form-section">
            <h3>Assign Categories to Tattoos</h3>
            <form method="POST">
                <label for="tattoo_id">Tattoo:</label>
                <select id="tattoo_id" name="tattoo_id" required>
                    <?php
                    mysqli_data_seek($resultTattoos, 0);
                    while ($row = mysqli_fetch_assoc($resultTattoos)) {
                        echo "<option value='{$row['id']}'>Tattoo #{$row['id']} - {$row['description']} (Category: " . ($row['category_name'] ?? 'None') . ")</option>";
                    }
                    ?>
                </select>
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php
                    mysqli_data_seek($resultCategories, 0);
                    while ($row = mysqli_fetch_assoc($resultCategories)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="assign_category">Assign Category</button>
            </form>
        </div>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>