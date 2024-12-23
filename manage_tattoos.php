<?php
require 'authentication_check.php';
require_admin_access();
require 'common_functions.php';
require 'db_connect.php';

$artists = [];
$sqlArtists = "SELECT artists.id AS artist_id, users.fname, users.lname 
               FROM artists 
               JOIN users ON artists.user_id = users.id";
$resultArtists = mysqli_query($conn, $sqlArtists);
while ($row = mysqli_fetch_assoc($resultArtists)) {
    $artists[] = $row;
}

$pendingBookings = [];
if (isset($_GET['artist_id'])) {
    $artistId = $_GET['artist_id'];
    $sqlPendingBookings = "SELECT bookings.id AS booking_id, users.fname AS customer_fname, users.lname AS customer_lname
                           FROM bookings
                           JOIN customers ON bookings.customer_id = customers.id
                           JOIN users ON customers.user_id = users.id
                           WHERE bookings.artist_id = $artistId AND bookings.status = 'done'";
    $resultPendingBookings = mysqli_query($conn, $sqlPendingBookings);
    while ($row = mysqli_fetch_assoc($resultPendingBookings)) {
        $pendingBookings[] = $row;
    }
}

$sqlCategories = "SELECT id, name FROM categories";
$resultCategories = mysqli_query($conn, $sqlCategories);
$message = 'test';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $booking_id = test_input($_POST['booking_id']);
        $artist_id = test_input($_POST['artist_id']);
        $description = test_input($_POST['description']);
        $categoryId = test_input($_POST['category_id']);
        $image_url = uploadImage(fileKey: 'image');

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
if (isset($_SESSION['message'])) {
    $escapedMessage = htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8');
    echo '<script>alert("' . $escapedMessage . '");</script>';
    unset($_SESSION['message']);
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

        <form method="GET" action="">
            <label for="artist_id">Select Artist:</label>
            <select name="artist_id" id="artist_id" onchange="if(value != '') {this.form.submit()}" required>
                <option value="">--Select Artist--</option>
                <?php foreach ($artists as $artist) { ?>
                    <option value="<?php echo $artist['artist_id']; ?>"
                        <?php echo (isset($_GET['artist_id']) && $_GET['artist_id'] == $artist['artist_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($artist['fname'] . ' ' . $artist['lname']); ?>
                    </option>
                <?php } ?>
            </select>
            <br><br>
        </form>

        <?php if (isset($_GET['artist_id'])) { ?>
            <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="artist_id" value="<?php echo $_GET['artist_id']; ?>">
                <label for="booking_id">Select Booking:</label>
                <select name="booking_id" id="booking_id" required>
                    <option value="">--Select Booking--</option>
                    <?php foreach ($pendingBookings as $booking) { ?>
                        <option value="<?php echo $booking['booking_id']; ?>">
                            <?php echo htmlspecialchars($booking['booking_id'] . ' - ' . $booking['customer_fname'] . ' ' . $booking['customer_lname']); ?>
                        </option>
                    <?php } ?>
                </select>
                <br> <br>
                <label for="image">Upload Image:</label>
                <input type="file" id="image" name="image" required>
                <br> <br>
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">--Select Category--</option>
                    <?php
                    while ($row = mysqli_fetch_assoc($resultCategories)) {
                        echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
                <br> <br>
                <label for="description">Description(optional):</label>
                <textarea id="description" name="description"> </textarea>
                <br> <br>
                <button type="submit">Add Tattoo</button>
            </form>
        <?php } ?>
        <br>
        <a href="dashboard_admin.php">Back to Admin Dashboard</a>
    </div>
</body>

</html>