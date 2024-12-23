<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

// Update Booking Status
// if (isset($_POST['update_status'])) {
//     $bookingId = $_POST['booking_id'];
//     $newStatus = $_POST['status'];
//     $sqlUpdateStatus = "UPDATE bookings SET status = '$newStatus' WHERE id = $bookingId";
//     mysqli_query($conn, $sqlUpdateStatus);
// }
// Assign Artist with schedule
if (isset($_POST['assign_artist'])) {
    $bookingId = $_POST['booking_id'];
    $artistId = $_POST['artist_id'];

    if (isset($_POST['status']) && $_POST['status'] == 'pending') {
        $newDate = $_POST['reschedule_date'];
        $newTime = $_POST['reschedule_time'];
        $newDateTime = $newDate . ' ' . $newTime;
        $currentDateTime = new DateTime();
        $selectedDateTime = new DateTime($newDateTime);

        if ($selectedDateTime <= $currentDateTime) {
            $message = "The selected date and time must be in the future.";
            $_SESSION['message'] = $message;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        
        $newSchedule = "INSERT INTO artist_schedules (`date`,`time`,`session_status`,`artist_id`,`booking_id`) 
                                VALUES ('$newDate', '$newTime', 'scheduled', '$artistId', '$bookingId')";
        mysqli_query($conn, $newSchedule);
    }
    $sqlReassignArtist = "UPDATE bookings SET artist_id = '$artistId', status = 'approved' WHERE id = '$bookingId'";
    mysqli_query($conn, $sqlReassignArtist);
}

// Cancel Booking
if (isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];
    $sqlcancelBooking = "UPDATE bookings SET status = 'canceled' WHERE id = $bookingId";
    mysqli_query($conn, $sqlcancelBooking);
}

// Restore Booking
// if (isset($_POST['restore_booking'])) {
//     $bookingId = $_POST['booking_id'];
//     $date = $_POST['date'];
//     $time = $_POST['time'];
//     $sqlRestoreBooking = "UPDATE bookings
//                           SET status = 'pending' 
//                           WHERE id = '$bookingId'";
//     mysqli_query($conn, $sqlRestoreBooking);
// }

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
LEFT JOIN users AS users2 ON artists.user_id = users2.id
WHERE 1";

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
$sqlCustomers = "SELECT customers.id, users.fname, users.lname 
                 FROM customers
                 JOIN users ON customers.user_id = users.id";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

if (isset($_SESSION['message'])) {
    $escapedMessage = htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8');
    echo '<script>alert("' . $escapedMessage . '");</script>';
    unset($_SESSION['message']);
}

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
                            <?php if ($row['status'] != 'done' && $row['status'] != 'canceled') { ?>
                                <!-- Assign To Artist -->
                                <?php if ($row['status'] == 'pending' || $row['status'] == 'approved') { ?>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <select name="artist_id" required>
                                            <option value="">-- Assign Booking --</option>
                                            <?php
                                            mysqli_data_seek($resultArtists, 0);
                                            while ($artist = mysqli_fetch_assoc($resultArtists)) {
                                                echo "<option value='{$artist['id']}'>{$artist['fname']} {$artist['lname']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <br>
                                        <!-- Schedule Booking -->
                                        <?php if ($row['status'] == 'pending') { ?>
                                            <input type="date" name="reschedule_date" min="<?php echo date('Y-m-d'); ?>" required>
                                            <input type="time" name="reschedule_time" required>
                                        <?php } ?>
                                        <br>
                                        <button type="submit" name="assign_artist" onclick="return confirm('Are you sure?');">Assign</button>
                                    </form>
                                <?php } ?>

                                <br><br>

                                <!-- Cancel Booking -->
                                <?php if ($row['status'] == 'pending') { ?>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="cancel_booking" onclick="return confirm('Are you sure?');">Cancel Booking</button>
                                    </form>
                                <?php } ?>
                            <?php } else {
                                echo "This booking has been" . '<br>' . "completed or canceled.";
                            } ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
</body>

</html>