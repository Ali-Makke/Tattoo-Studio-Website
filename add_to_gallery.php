<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

$sqlCategories = "SELECT id, name FROM categories";
$resultCategories = mysqli_query($conn, $sqlCategories);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $categoryQuery = "SELECT name FROM categories WHERE id = '$category_id'";
    $categoryResult = mysqli_query($conn, $categoryQuery);
    $category = mysqli_fetch_assoc($categoryResult);
    $categoryName = $category['name'];
    $target_dir = "images/" . $categoryName . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid('', true) . '.' . $imageFileType;
    while (file_exists($target_file)) {
        $target_file = $target_dir . uniqid('', true) . '.' . $imageFileType;
    }

    $uploadOk = 1;
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    if ($_FILES["image"]["size"] > 500000) {
        echo "File is too large!";
        $uploadOk = 0;
    }
    if (!in_array($imageFileType, array('jpg', 'jpeg', 'png', 'gif','webp'))) {
        echo "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
        $uploadOk = 0;
    }
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
            $sql = "INSERT INTO gallery (category_id, image_url, description) VALUES ('$category_id', '$image_url', '$description')";
            if (mysqli_query($conn, $sql)) {
                echo "The file " . basename($_FILES["image"]["name"]) . " has been uploaded.";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gallery Image</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h1 class="heading">Add New Tattoo to Gallery</h1>
        </header>
        <h2>Add New Gallery Image</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php
                while ($row = mysqli_fetch_assoc($resultCategories)) {
                    echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <br>
            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" required>
            <br>
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
            <br>
            <button type="submit">Add Image</button>
            <br><br>
            <a href="admin.php">Back to Admin Dashboard</a>
        </form>
    </div>
</body>

</html>