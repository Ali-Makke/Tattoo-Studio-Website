<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

if (!is_admin()) {
    header("Location: user.php");
    exit();
}

// Update Booking Status
if (isset($_POST['update_status'])) {
    $bookingId = $_POST['booking_id'];
    $newStatus = $_POST['status'];
    $sqlUpdateStatus = "UPDATE bookings SET status = '$newStatus' WHERE id = $bookingId";
    mysqli_query($conn, $sqlUpdateStatus);
}

// Reassign Artist
if (isset($_POST['reassign_artist'])) {
    $bookingId = $_POST['booking_id'];
    $artistId = $_POST['artist_id'];
    $sqlReassignArtist = "UPDATE bookings SET artist_id = $artistId WHERE id = $bookingId";
    mysqli_query($conn, $sqlReassignArtist);
}

// Reschedule Booking
if (isset($_POST['reschedule_booking'])) {
    $bookingId = $_POST['booking_id'];
    $newDate = $_POST['reschedule_date'];
    $newTime = $_POST['reschedule_time'];
    $sqlReschedule = "UPDATE bookings SET dates = '$newDate', times = '$newTime' WHERE id = $bookingId";
    mysqli_query($conn, $sqlReschedule);
}

// Delete Booking
if (isset($_POST['delete_booking'])) {
    $bookingId = $_POST['booking_id'];
    $sqlDeleteBooking = "DELETE FROM bookings WHERE id = $bookingId";
    mysqli_query($conn, $sqlDeleteBooking);
}

// Fetch Bookings
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$artistFilter = isset($_GET['artist']) ? $_GET['artist'] : '';
$customerFilter = isset($_GET['customer']) ? $_GET['customer'] : '';

$sqlFetchBookings = "SELECT
    bookings.*,
    customers.id AS customer_id,
    users.fname AS customer_fname,
    users.lname AS customer_lname,
    artists.id AS artist_id,
    users2.fname AS artist_fname,
    users2.lname AS artist_lname
FROM
    bookings
LEFT JOIN customers ON bookings.customer_id = customers.id
LEFT JOIN users AS users ON customers.user_id = users.id
LEFT JOIN artists ON bookings.artist_id = artists.id
LEFT JOIN users AS users2 ON artists.user_id = users2.id";

if ($statusFilter) {
    $sqlFetchBookings .= " AND bookings.status = '$statusFilter'";
}
if ($artistFilter) {
    $sqlFetchBookings .= " AND bookings.artist_id = $artistFilter";
}
if ($customerFilter) {
    $sqlFetchBookings .= " AND bookings.customer_id = $customerFilter";
}

$sqlFetchBookings .= " ORDER BY bookings.created_at DESC";
$resultBookings = mysqli_query($conn, $sqlFetchBookings);

// Fetch Artists for Filters
$sqlArtists = "SELECT artists.id, users.fname, users.lname 
               FROM artists 
               JOIN users ON artists.user_id = users.id";
$resultArtists = mysqli_query($conn, $sqlArtists);

// Fetch Customers for Filters
$sqlCustomers = "SELECT users.id, users.fname, users.lname FROM users WHERE role_id = 2";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Bookings</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Manage Bookings</h2>

        <!-- Filters -->
        <div class="filter-section">
            <h3>Filter Bookings</h3>
            <form method="GET">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" <?php if ($statusFilter == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if ($statusFilter == 'approved') echo 'selected'; ?>>Approved</option>
                    <option value="done" <?php if ($statusFilter == 'done') echo 'selected'; ?>>Done</option>
                    <option value="canceled" <?php if ($statusFilter == 'canceled') echo 'selected'; ?>>Canceled</option>
                </select>

                <label for="artist">Artist:</label>
                <select id="artist" name="artist">
                    <option value="">All</option>
                    <?php while ($row = mysqli_fetch_assoc($resultArtists)) {
                        $selected = ($artistFilter == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['fname']} {$row['lname']}</option>";
                    } ?>
                </select>

                <label for="customer">Customer:</label>
                <select id="customer" name="customer">
                    <option value="">All</option>
                    <?php while ($row = mysqli_fetch_assoc($resultCustomers)) {
                        $selected = ($customerFilter == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['fname']} {$row['lname']}</option>";
                    } ?>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Display Bookings -->
        <div class="form-section">
            <h3>Booking Details</h3>
            <table border="1" class="table">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Artist</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultBookings)) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo "{$row['customer_fname']} {$row['customer_lname']}"; ?></td>
                        <td><?php echo $row['artist_id'] ? "{$row['artist_fname']} {$row['artist_lname']}" : "Unassigned"; ?></td>
                        <td>
                            <strong>Idea:</strong> <?php echo $row['idea']; ?><br>
                            <strong>Size:</strong> <?php echo $row['size']; ?><br>
                            <strong>Color:</strong> <?php echo ucfirst($row['color']); ?><br>
                            <strong>Budget:</strong> <?php echo $row['budget']; ?><br>
                            <strong>Placement:</strong> <?php echo $row['placement']; ?><br>
                        </td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td>
                            <!-- Update Status -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <select name="status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="done">Done</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                            <br>
                            <!-- Reassign Artist -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <select name="artist_id">
                                    <?php
                                    mysqli_data_seek($resultArtists, 0);
                                    while ($artist = mysqli_fetch_assoc($resultArtists)) {
                                        echo "<option value='{$artist['id']}'>{$artist['fname']} {$artist['lname']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="reassign_artist">Reassign</button>
                            </form>
                            <br>
                            <!-- Reschedule Booking -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <input type="date" name="reschedule_date" required>
                                <input type="time" name="reschedule_time" required>
                                <button type="submit" name="reschedule_booking">Reschedule</button>
                            </form>
                            <br>
                            <!-- Delete Booking -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_booking" onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a class="back-link" href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>

</html>