<?php
require 'authentication_check.php';
require_artist_access();
require 'db_connect.php';

$canEdit = false;
if (isset($_POST['edit'])) {
    $canEdit = true;
} else if (isset($_POST['cancel_edit'])) {
    $canEdit = false;
}
// Update Booking Status
// if (isset($_POST['update_status'])) {
//     $bookingId = $_POST['booking_id'];
//     $newStatus = $_POST['status'];
//     $sqlUpdateStatus = "UPDATE bookings SET status = '$newStatus' WHERE id = $bookingId";
//     mysqli_query($conn, $sqlUpdateStatus);
// }
// Assign Artist with schedule
if (isset($_POST['assign_artist']) || isset($_POST['create_session'])) {
    $bookingId = $_POST['booking_id'];
    $artistId = $_POST['artist_id'];
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
// $artistFilter = isset($_GET['artist']) ? $_GET['artist'] : '';
$customerFilter = isset($_GET['customer']) ? $_GET['customer'] : '';

$sqlFetchBookings = "SELECT
    bookings.*, 
    bookings.status AS booking_status,
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

// Check if the user is an artist, and if so, only show booking for that artist
if (is_artist()) {
    $user_id = $_SESSION['user_id'];
    $sqlFetchBookings .= " AND artists.user_id = '$user_id'";
}

if ($statusFilter) {
    $sqlFetchBookings .= " AND bookings.status = '$statusFilter'";
}
// if ($artistFilter) {
//     $sqlFetchBookings .= " AND bookings.artist_id = $artistFilter";
// }
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
$sqlCustomers = "SELECT c.*, users.fname, users.lname
FROM customers c
JOIN bookings b ON c.id = b.customer_id
JOIN users ON c.user_id = users.id
JOIN artists a ON b.artist_id = a.id
WHERE 1";

if (is_artist()) {
    $artistId = $_SESSION['artist_id'];
    $sqlCustomers .= " AND a.id = '$artistId';";
}

$resultCustomers = mysqli_query($conn, $sqlCustomers);

// check if booking is complete an update it's status
// $sqlCompleteBooking = "UPDATE bookings b
// SET b.status = 'approved'
// WHERE b.id IN (
//     SELECT booking_id
//     FROM artist_schedules
//     GROUP BY booking_id
//     HAVING SUM(CASE WHEN session_status IN ('done', 'canceled') THEN 1 ELSE 0 END) = COUNT(*));";
// if (!mysqli_query($conn, $sqlCompleteBooking)) {
//     echo "error";
// }

// fetch bookings when all their sessions done or canceled
$sqlCompleteBooking = "SELECT b.id
FROM bookings b
WHERE b.id IN (
    SELECT booking_id
    FROM artist_schedules
    GROUP BY booking_id
    HAVING SUM(CASE WHEN session_status IN ('done', 'canceled') THEN 1 ELSE 0 END) = COUNT(*));";
$resultCompleteBooking = mysqli_query($conn, $sqlCompleteBooking);

$completedBookings = [];
while ($row = mysqli_fetch_assoc($resultCompleteBooking)) {
    $completedBookings[] = $row['id'];
}

// can mark booking as complete only if all sessions are done and/or canceled
if (isset($_POST['mark_as_complete'])) {
    $bookingId = $_POST['booking_id'];

    $sqlReassignArtist = "UPDATE booking SET status = 'done' WHERE id = '$bookingId'";
    mysqli_query($conn, $sqlReassignArtist);
}

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
                <!-- <label for="artist">Artist:</label>
                <select id="artist" name="artist">
                    <option value="">All</option>
                    <?php //while ($row = mysqli_fetch_assoc($resultArtists)) 
                    {
                        //$selected = ($artistFilter == $row['id']) ? 'selected' : '';
                        // echo "<option value='{$row['id']}' $selected>{$row['fname']} {$row['lname']}</option>";
                    } ?>
                </select> -->

                <label for="customer">Customer:</label>
                <select id="customer" name="customer">
                    <option value="">All</option>
                    <?php while ($row = mysqli_fetch_assoc($resultCustomers)) {
                        $selected = ($customerFilter == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['fname']} {$row['lname']}</option>";
                    } ?>
                </select>
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" <?php if ($statusFilter == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if ($statusFilter == 'approved') echo 'selected'; ?>>Approved</option>
                    <option value="done" <?php if ($statusFilter == 'done') echo 'selected'; ?>>Done</option>
                    <option value="canceled" <?php if ($statusFilter == 'canceled') echo 'selected'; ?>>Canceled</option>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Display Bookings -->
        <div class="form-section">
            <?php if (mysqli_num_rows($resultBookings) > 0) { ?>
                <h3>Booking Details</h3>
                <?php if (is_artist()) { ?>
                    <!-- Enable Edit Sessions -->
                    <form method="POST" action="">
                        <label for="artist_id" style="font-size: large;">Mark Booking as complete: </label>
                        <button type="submit" name="edit">Edit</button>
                        <button type="submit" name="cancel_edit">Cancel Edit</button>
                    </form>
                <?php } ?>
                <table border="1" class="table">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Image</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th> Actions </th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($resultBookings)) : ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo "{$row['customer_fname']} {$row['customer_lname']}"; ?></td>
                            <td>
                                <?php
                                if ($row['image_url']) {
                                    echo '<img src="' . $row['image_url'] . '" alt="Artist Image" style="width:100px;height:auto;">';
                                } else {
                                    echo "No Image";
                                }
                                ?>
                            </td>
                            <td>
                                <strong>Idea:</strong> <?php echo $row['idea']; ?><br>
                                <strong>Size:</strong> <?php echo $row['size']; ?><br>
                                <strong>Color:</strong> <?php echo ucfirst($row['color']); ?><br>
                                <strong>Placement:</strong> <?php echo $row['placement']; ?><br>
                                <strong>Budget:</strong> $<?php echo number_format($row['budget'], 1); ?><br>
                            </td>
                            <td><?php echo ucfirst($row['booking_status']); ?></td>
                            <?php if ($row['booking_status'] === 'pending' && is_admin()) { ?>
                                <td>
                                    <!-- Assign Booking To Artist -->
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
                                        <input type="date" name="reschedule_date" min="<?php echo date('Y-m-d'); ?>" required>
                                        <input type="time" name="reschedule_time" required>
                                        <br>
                                        <button type="submit" name="assign_artist" onclick="return confirm('Are you sure?');">Assign</button>
                                    </form>
                                    <br><br>
                                    <!-- Cancel Booking -->
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="cancel_booking" onclick="return confirm('Are you sure?');">Cancel Booking</button>
                                    </form>
                                </td>
                            <?php } else if ($row['booking_status'] === 'approved' && is_artist()) { ?>
                                <td>
                                    <?php if (!$canEdit) { ?>
                                        <form method="post">
                                            <!-- Artist Schedule Booking -->
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $_SESSION['artist_id']; ?>">
                                            <input type="date" name="reschedule_date" min="<?php echo date('Y-m-d'); ?>" required>
                                            <input type="time" name="reschedule_time" required>
                                            <br>
                                            <button type="submit" name="create_session">Add Session</button>
                                        </form>
                                    <?php } ?>
                                    <!-- Add the button for bookings where all sessions are 'done' or 'canceled' -->
                                    <?php if (in_array($row['id'], $completedBookings) && $canEdit) { ?>
                                        <form method="post">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <button type="button" name="mark_as_complete">Set as complete</button>
                                        </form>
                                    <?php } else if ($canEdit) {
                                        echo "<p>Some sessions are not complete</p>";
                                    } ?>
                                </td>
                            <?php } else {
                                echo "<td>This booking is" . '<br>' . $row['booking_status'] . '</td>';
                            } ?>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php } else {
                echo '<br><h3>--- No Bookings<br><br></h3>';
            } ?>
        </div>

        <?php if (is_admin()) {
            echo "<a class='back-link' href='dashboard_admin.php'>Back to Dashboard</a>";
        } else {
            echo "<a class='back-link' href='dashboard_artist.php'>Back to Dashboard</a>";
        } ?>
    </div>
    <?php include 'footer.php' ?>
</body>

</html>