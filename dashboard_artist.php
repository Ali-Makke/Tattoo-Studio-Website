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
$sqlEarnings = "SELECT payments.*, SUM(sub_payments.amount) AS total_earnings, tattoos.id, tattoos.artist_id, artists.id
                FROM payments
                JOIN sub_payments ON payments.id = sub_payments.payment_id
                JOIN tattoos ON payments.tattoos_id = tattoos.id
                JOIN artists ON tattoos.artist_id = artists.id";
$resultEarnings = mysqli_query($conn, $sqlEarnings);
$rowEarnings = mysqli_fetch_assoc($resultEarnings);
$totalEarnings = $rowEarnings['total_earnings'] ?? 0;

// Fetch all approved bookings
$sqlAssignedBookings = "SELECT bookings.*, users.fname AS customer_fname, users.email AS customer_email 
                        FROM bookings
                        JOIN customers ON bookings.customer_id = customers.id
                        JOIN users ON customers.user_id = users.id
                        WHERE bookings.artist_id = $artistId AND bookings.status = 'approved'";
$resultAssignedBookings = mysqli_query($conn, $sqlAssignedBookings);

// Fetch finished tattoos
$sqlFinishedTattoos = "SELECT * FROM tattoos WHERE artist_id = '$artistId'";
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

echo "fix status to schedule status, not booking status";
echo "fix so that i get schedule things not booking things so that i can match booking id";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Artist Dashboard</title>
    <link rel="stylesheet" href="styles/included.css">
    <link rel="stylesheet" href="styles/admin.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h2 class="heading">Artist Dashboard</h2>
        </header>

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

        <!-- Controls: Assigned Bookings -->
        <h3>Assigned Bookings</h3>
        <?php if ($canUpdateBookingStatus) { ?>
            <?php if (mysqli_num_rows($resultAssignedBookings) > 0) { ?>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Idea</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        <?php while ($row = mysqli_fetch_assoc($resultAssignedBookings)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_fname']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['idea']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <label>Mark As Done?</label>
                                        <button type="submit" name="mark_as_done" onclick="return confirm('Are you sure?');">Done</button>
                                        <br><br>
                                        <label>Mark As Canceled?</label>
                                        <button type="submit" name="mark_as_canceled" onclick="return confirm('Are you sure?');">Canceled</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php } else { ?>
                <p>No bookings assigned yet.</p>
            <?php } ?>
        <?php } else { ?>
            <p>You do not have permission to manage bookings.</p>
        <?php } ?>

        <!-- Schedule Management -->
        <h3>Manage Schedules</h3>
        <a href="view_schedule.php">View Schedules</a>
        <?php if ($canManageSchedules) { ?>
            <a href="edit_schedule.php">Edit Schedules</a>
        <?php } else { ?>
            <p>You do not have permission to manage schedules.</p>
        <?php } ?>

        <!-- Finished Tattoos -->
        <h3>Finished Tattoos</h3>
        <?php if (mysqli_num_rows($resultFinishedTattoos) > 0) { ?>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Finished Tattoo ID</th>
                        <th>Description</th>
                        <th>Date Finished</th>
                        <th>Image</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($resultFinishedTattoos)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_finished']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['finished_tattoo_url']); ?>" alt="Tattoo Image" width="100">
                            </td>
                        </tr>
                    <?php } ?>
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