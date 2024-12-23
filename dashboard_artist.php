<?php
require 'authentication_check.php';
require_artist_access();
require 'db_connect.php';

$email = $_SESSION['email'];

// Fetch logged-in artist's details
$sqlUser = "SELECT artists.id, artists.user_id, users.fname, users.lname, users.email
            FROM artists
            JOIN users ON artists.user_id = users.id
            where users.email = 'alimakke@gmail.com';";
$resultUser = mysqli_query($conn, $sqlUser);
$user = mysqli_fetch_assoc($resultUser);
$artistId = $user['id'];

// Fetch permissions for the artist
$sqlPermissions = "SELECT * FROM artist_permissions WHERE artist_id = '$artistId'";
$resultPermissions = mysqli_query($conn, $sqlPermissions);
$permissions = mysqli_fetch_assoc($resultPermissions);

// Set default permission values in case they aren't defined
$canViewEarnings = $permissions['can_view_earnings'] ?? 0;
$canUpdateBookingStatus = $permissions['can_update_booking_status'] ?? 0;
$canManageSchedules = $permissions['can_manage_schedules'] ?? 0;

// Fetch statistics
// Total tattoos done by the artist
$sqlTattoos = "SELECT COUNT(*) AS num_tattoos FROM tattoos WHERE artist_id = '$artistId'";
$resultTattoos = mysqli_query($conn, $sqlTattoos);
$rowTattoos = mysqli_fetch_assoc($resultTattoos);
$numTattoos = $rowTattoos['num_tattoos'];

// Total completed bookings
$sqlCompletedBookings = "SELECT COUNT(*) AS completed_bookings 
                         FROM bookings 
                         WHERE artist_id = '$artistId' AND status = 'done'";
$resultCompletedBookings = mysqli_query($conn, $sqlCompletedBookings);
$rowCompletedBookings = mysqli_fetch_assoc($resultCompletedBookings);
$numCompletedBookings = $rowCompletedBookings['completed_bookings'];

// Upcoming bookings
$sqlUpcomingBookings = "SELECT COUNT(*) AS upcoming_bookings 
                        FROM bookings 
                        WHERE artist_id = '$artistId' AND status = 'approved'";
$resultUpcomingBookings = mysqli_query($conn, $sqlUpcomingBookings);
$rowUpcomingBookings = mysqli_fetch_assoc($resultUpcomingBookings);
$numUpcomingBookings = $rowUpcomingBookings['upcoming_bookings'];

// Earnings (Sum of paid sub-payments)
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
                        WHERE bookings.artist_id = $artistId AND bookings.status IN ('pending', 'approved')";
$resultAssignedBookings = mysqli_query($conn, $sqlAssignedBookings);

// Fetch finished tattoos
$sqlFinishedTattoos = "SELECT * FROM tattoos WHERE artist_id = '$artistId'";
$resultFinishedTattoos = mysqli_query($conn, $sqlFinishedTattoos);
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

        <p>Welcome, <?php echo ucfirst(htmlspecialchars($user['fname'])); ?>. You are logged in as an artist.</p>

        <!-- Profile Information -->
        <h3>Profile Information</h3>
        <ul>
            <li>Name: <?php echo htmlspecialchars(ucfirst($user['fname']) ." ". ucfirst($user['lname'])); ?></li>
            <li>Email: <?php echo htmlspecialchars($user['email']); ?></li>
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
            <h3>Assigned Bookings</h3>
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
                                    <a href="update_booking_status.php?id=<?php echo $row['id']; ?>">Mark as Done</a>
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

        <!-- Schedule Management Section -->
        <?php if ($canManageSchedules) { ?>
            <h3>Manage Schedules</h3>
            <a href="view_schedule.php">View Schedules</a>
            <a href="edit_schedule.php">Edit Schedules</a>
        <?php } else { ?>
            <p>You do not have permission to manage schedules.</p>
        <?php } ?>

        <!-- Controls: Finished Tattoos -->
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