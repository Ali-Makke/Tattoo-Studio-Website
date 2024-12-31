<?php
require 'authentication_check.php';
require_admin_access();
require 'common_functions.php';
require 'db_connect.php';


// Fetch bookings with filters
$artistFilter = htmlspecialchars(isset($_GET['artist_id']) ? $_GET['artist_id'] : '');

$sqlBookings = "SELECT bookings.id AS booking_id,
    users.fname AS customer_fname,
    users.lname AS customer_lname,
    artists.id AS artist_id,
    users2.fname AS artist_fname,
    users2.lname AS artist_lname
FROM bookings
LEFT JOIN customers ON bookings.customer_id = customers.id
LEFT JOIN users AS users ON customers.user_id = users.id
LEFT JOIN artists ON bookings.artist_id = artists.id
LEFT JOIN users AS users2 ON artists.user_id = users2.id
WHERE bookings.status = 'done'";

if ($artistFilter) {
    $sqlBookings .= " AND bookings.artist_id = $artistFilter";
}

$resultBookings = mysqli_query($conn, $sqlBookings);

// Fetch all artists for filter
$artists = [];
$sqlArtists = "SELECT artists.id AS artist_id, users.fname, users.lname 
               FROM artists 
               JOIN users ON artists.user_id = users.id";
$resultArtists = mysqli_query($conn, $sqlArtists);
while ($row = mysqli_fetch_assoc($resultArtists)) {
    $artists[] = $row;
}
$sqlCategories = "SELECT id, name FROM categories";
$resultCategories = mysqli_query($conn, $sqlCategories);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $booking_id = test_input($_POST['booking_id']);
        $artist_id = test_input($_POST['artist_id']);
        $description = test_input($_POST['description']);
        $categoryId = test_input($_POST['category_id']);
        $image_url = uploadImage(fileKey: 'image', target_dir: 'images/tattoo_uploads/');

        if (str_contains($image_url, 'images/tattoo_uploads/')) {
            $message = "Image uploaded successfully: " . $image_url;
            $sqltattoo = "INSERT INTO tattoos (description, booking_id, artist_id, category_id, finished_tattoo_url)
                VALUES ('$description', '$booking_id', '$artist_id', '$categoryId', '$image_url')";
            if (!mysqli_query($conn, $sqltattoo)) {
                $message = "Error: " . mysqli_error($conn);
            } else {
                $message = 'Image uploaded successfully ' . $image_url;
            }
        } else {
            $message = "Error: " . $image_url;
        }
    } else {
        $message = "No file uploaded or an error occurred";
    }

    $_SESSION['message'] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tattoo</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h1 class="heading">Add New Tattoo</h1>
        </header>

        <!-- Filters Section -->
        <form method="GET" action="">
            <div>
                <label for="artist_id">Filter by Artist:</label>
                <select name="artist_id" id="artist_id">
                    <option value="">--Select Artists--</option>
                    <?php foreach ($artists as $artist) { ?>
                        <option value="<?php echo $artist['artist_id']; ?>"
                            <?php echo (isset($_GET['artist_id']) && $_GET['artist_id'] == $artist['artist_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($artist['fname'] . ' ' . $artist['lname']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit">Filter</button>
            </div>
        </form>

        <!-- Display Bookings Table -->
        <?php if (mysqli_num_rows($resultBookings) > 0) { ?>
            <h2>Bookings</h2>
            <table border="1" class="table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Artist</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($resultBookings)) : ?>
                        <tr>
                            <td data-label="Booking ID"><?php echo $row['booking_id']; ?></td>
                            <td data-label="Customer"><?php echo htmlspecialchars($row['customer_fname']) . ' ' . htmlspecialchars($row['customer_lname']); ?></td>
                            <td data-label="Artist"><?php echo htmlspecialchars($row['artist_fname']) . ' ' . htmlspecialchars($row['artist_lname']); ?></td>
                            <td data-label="Actions">
                                <!-- Add Tattoo Form for this Booking -->
                                <form method="POST" enctype="multipart/form-data" action="">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <input type="hidden" name="artist_id" value="<?php echo $row['artist_id']; ?>">

                                    <label for="image">Image:</label>
                                    <input type="file" id="image" name="image" required>
                                    <br>
                                    <label for="category_id">Category:</label>
                                    <select name="category_id" required>
                                        <option value="">--Select Category--</option>
                                        <?php
                                        mysqli_data_seek($resultCategories, 0);
                                        while ($cat = mysqli_fetch_assoc($resultCategories)) {
                                            echo "<option value=\"" . $cat['id'] . "\">" . $cat['name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <br>
                                    <label for="description">Description (optional):</label>
                                    <textarea id="description" name="description"></textarea>
                                    <br>
                                    <button type="submit">Add Tattoo</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php } else {
            echo "<br>No bookings have been completed for selected artist<br>";
        } ?>

        <br>
        <a class="back-link" href="dashboard_admin.php">Back to Admin Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>