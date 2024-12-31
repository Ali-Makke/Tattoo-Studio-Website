<?php
require 'authentication_check.php';
require_artist_access();
require 'db_connect.php';

$userId = $_SESSION['user_id'];
// Fetch logged-in artist's details
$sqlUser = "SELECT artists.id AS artist_id, artists.user_id, users.fname, users.lname, users.email
            FROM artists
            JOIN users ON artists.user_id = users.id
            where users.id = '$userId'";
$resultUser = mysqli_query($conn, $sqlUser);
$user = mysqli_fetch_assoc($resultUser);
$artistId = $user['artist_id'];
$_SESSION['artist_id'] = $artistId; //this session is used in different pages

// Fetch permissions for the artist
$sqlPermissions = "SELECT * FROM artist_permissions WHERE artist_id = '$artistId'";
$resultPermissions = mysqli_query($conn, $sqlPermissions);
$permissions = mysqli_fetch_assoc($resultPermissions);
$canViewEarnings = $permissions['can_view_earnings'] ?? 0;
$canUpdateBookingStatus = $permissions['can_update_booking_status'] ?? 0;
$canManageSchedules = $permissions['can_manage_schedules'] ?? 0;

// number of tattoos done by the artist, completed bookings, Upcoming bookings, Earnings statistics
$sqlTattoos = "SELECT COUNT(*) AS num_tattoos FROM tattoos WHERE artist_id = '$artistId'";
$resultTattoos = mysqli_query($conn, $sqlTattoos);
$rowTattoos = mysqli_fetch_assoc($resultTattoos);
$numTattoos = $rowTattoos['num_tattoos'];
$sqlCompletedBookings = "SELECT COUNT(*) AS completed_bookings 
                         FROM bookings 
                         WHERE artist_id = '$artistId' AND status = 'done'";
$resultCompletedBookings = mysqli_query($conn, $sqlCompletedBookings);
$rowCompletedBookings = mysqli_fetch_assoc($resultCompletedBookings);
$numCompletedBookings = $rowCompletedBookings['completed_bookings'];
$sqlUpcomingBookings = "SELECT COUNT(*) AS upcoming_bookings 
                        FROM bookings 
                        WHERE artist_id = '$artistId' AND status = 'approved'";
$resultUpcomingBookings = mysqli_query($conn, $sqlUpcomingBookings);
$rowUpcomingBookings = mysqli_fetch_assoc($resultUpcomingBookings);
$numUpcomingBookings = $rowUpcomingBookings['upcoming_bookings'];
$sqlEarnings = "SELECT payments.*, SUM(sub_payments.amount) AS total_earnings, bookings.id, bookings.artist_id, artists.id
                FROM payments
                JOIN sub_payments ON payments.id = sub_payments.payment_id
                JOIN bookings ON payments.booking_id = bookings.id
                JOIN artists ON bookings.artist_id = artists.id";
$resultEarnings = mysqli_query($conn, $sqlEarnings);
$rowEarnings = mysqli_fetch_assoc($resultEarnings);
$totalEarnings = $rowEarnings['total_earnings'] ?? 0;

// Fetch finished tattoos
$sqlFinishedTattoos = "SELECT * FROM tattoos
WHERE artist_id = '$artistId'
ORDER BY finished_tattoo_url DESC
LIMIT 5";
$resultFinishedTattoos = mysqli_query($conn, $sqlFinishedTattoos);

// Mark schedule as done:
if (isset($_POST['mark_as_done'])) {
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Artist Dashboard</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Artist Dashboard</h2>
        <p>Welcome, <?php echo ucfirst(htmlspecialchars($_SESSION['fname'])); ?>. You are logged in as an artist.</p>

        <!-- Profile Information -->
        <h3>Profile Information</h3>
        <ul>
            <li>Name: <?php echo htmlspecialchars(ucfirst($_SESSION['fname']) . " " . ucfirst($_SESSION['lname'])); ?></li>
            <li>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></li>
        </ul>

        <!-- Statistics -->
        <h3>Statistics</h3>
        <ul>
            <li>Total Tattoos Done: <?php echo $numTattoos; ?></li>
            <li>Completed Bookings: <?php echo $numCompletedBookings; ?></li>
            <li>Upcoming Bookings: <?php echo $numUpcomingBookings; ?></li>
            <?php if ($canViewEarnings) { ?>
                <li>Total Earnings: $<?php echo number_format($totalEarnings, 2); ?></li>
            <?php } else { ?>
                <p>You do not have permission to view earnings.</p>
            <?php } ?>
        </ul>


        <!-- Controls Section -->
        <div class="controls">
            <h3>Controls</h3>
            <ul>
                <li><a class="link" href="manage_bookings.php">View Assigned Bookings</a></li>
                <li><a class="link" href="manage_artist_schedules.php">Manage Schedules</a></li>
            </ul>
        </div>

        <!-- Finished Tattoos -->
        <h3>Finished Tattoos</h3>
        <?php if (mysqli_num_rows($resultFinishedTattoos) > 0) { ?>
            <div class="table-responsive">
                <table border="1" class="table">
                    <thead>
                        <tr>
                            <th>Finished Tattoo ID</th>
                            <th>Description</th>
                            <th>Date Finished</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultFinishedTattoos)) { ?>
                            <tr>
                                <td data-label="Finished Tattoo ID"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td data-label="Description"><?php echo trim(htmlspecialchars($row['description'])) ? htmlspecialchars($row['description']) : "Not Set"; ?></td>
                                <td data-label="Date Finished"><?php echo htmlspecialchars($row['date_finished']); ?></td>
                                <td data-label="Image">
                                    <img src="<?php echo htmlspecialchars($row['finished_tattoo_url']); ?>" alt="Tattoo Image" width="100">
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p>No tattoos completed yet.</p>
        <?php } ?>

        <!-- Logout -->
        <a href="logout.php">Logout</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>