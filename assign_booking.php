<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bookingId = intval($_POST['booking_id']);
    $artistId = intval($_POST['artist_id']);

    $sqlAssign = "UPDATE bookings SET artist_id = $artistId WHERE id = $bookingId";
    if (mysqli_query($conn, $sqlAssign)) {
        header('Location: available_bookings.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else if (isset($_GET['id'])) {
    $bookingId = intval($_GET['id']);
    
    $sqlBooking = "SELECT * FROM bookings WHERE id = $bookingId";
    $resultBooking = mysqli_query($conn, $sqlBooking);
    $booking = mysqli_fetch_assoc($resultBooking);

    $sqlArtists = "SELECT id, fname, lname FROM users WHERE role = 'user'";
    $resultArtists = mysqli_query($conn, $sqlArtists);
} else {
    header('Location: available_bookings.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Assign Booking</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Assign Booking to Artist</h2>
        <?php if ($booking && $resultArtists) { ?>
            <form method="post" action="assign_booking.php">
                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                <div>
                    <label for="artist_id">Select Artist:</label>
                    <select name="artist_id" id="artist_id" required>
                        <?php while ($artist = mysqli_fetch_assoc($resultArtists)) { ?>
                            <option value="<?php echo $artist['id']; ?>">
                                <?php echo $artist['fname'] . ' ' . $artist['lname']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit">Assign Artist</button>
            </form>
        <?php } else { ?>
            <p>No booking found or no artists available.</p>
        <?php } ?>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>
