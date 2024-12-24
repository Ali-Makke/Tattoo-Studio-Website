<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

$currentMonth = date('m');

// Total Users
$sqlUsers = "SELECT
                (SELECT COUNT(*) FROM users WHERE role_id = 2) AS customers,
                (SELECT COUNT(*) FROM users WHERE role_id = 3) AS artists";
$resultUsers = mysqli_query($conn, $sqlUsers);
$rowUsers = mysqli_fetch_assoc($resultUsers);
$totalArtists = $rowUsers['artists'];
$totalCustomers = $rowUsers['customers'];

// Total Bookings
$sqlBookings = "SELECT COUNT(*) AS total_bookings FROM bookings";
$resultBookings = mysqli_query($conn, $sqlBookings);
$rowBookings = mysqli_fetch_assoc($resultBookings);
$totalBookings = $rowBookings['total_bookings'];

// Total Payments
$sqlPayments = "SELECT SUM(total_price) AS total_payments FROM payments WHERE status = 'paid'";
$resultPayments = mysqli_query($conn, $sqlPayments);
$rowPayments = mysqli_fetch_assoc($resultPayments);
$totalPayments = $rowPayments['total_payments'] ?? 0;

// Recent Bookings
$sqlRecentBookings = "SELECT bookings.*, users.fname AS artist_name 
FROM bookings
JOIN artists ON bookings.artist_id = artists.id
JOIN users ON artists.user_id = users.id
ORDER BY created_at DESC LIMIT 10";
$resultRecentBookings = mysqli_query($conn, $sqlRecentBookings);

// Recent artist_artist_reviews
$sqlRecentartist_reviews = "SELECT artist_reviews.*, users.fname AS artist_name, customers.user_id AS customer_user_id
                     FROM artist_reviews
                     JOIN artists ON artist_reviews.artist_id = artists.id
                     JOIN users ON artists.user_id = users.id
                     JOIN customers ON artist_reviews.customer_id = customers.id
                     ORDER BY created_at DESC LIMIT 10";
$resultRecentartist_reviews = mysqli_query($conn, $sqlRecentartist_reviews);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Admin Dashboard</h2>
        <p class="welcome">Welcome, <?php echo $_SESSION['fname']; ?>. You are logged in as an admin.</p>

        <!-- Overview Statistics -->
        <div class="statistics">
            <h3>Overview Statistics</h3>
            <ul>
                <li>Total Artists: <?php echo $totalArtists; ?></li>
                <li>Total Customers: <?php echo $totalCustomers; ?></li>
                <li>Total Bookings: <?php echo $totalBookings; ?></li>
                <li>Total Payments: $<?php echo number_format($totalPayments, 2); ?></li>
            </ul>
        </div>

        <!-- Controls Section -->
        <div class="controls">
            <h3>Controls</h3>
            <ul>
                <li><a href="manage_artists.php">Manage Artists</a></li>
                <li><a href="manage_customers.php">Manage Customers</a></li>
                <li><a href="manage_bookings.php">Manage Bookings</a></li>
                <li><a href="manage_artist_schedules.php">Manage Artist Schedules</a></li>
                <li><a href="manage_categories.php">Manage Categories</a></li>
                <li><a href="manage_tattoos.php">Manage Completed Tattoos</a></li>
                <li><a href="manage_tattoo_gallery.php">Manage Gallery</a></li>
                <li><a href="manage_payments.php">Manage Payments</a></li>
            </ul>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h3>Recent Bookings</h3>
            <table border="1" class="table">
                <tr>
                    <th>ID</th>
                    <th>Style</th>
                    <th>Placement</th>
                    <th>Artist</th>
                    <th>Booked At</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultRecentBookings)) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['style']; ?></td>
                        <td><?php echo $row['placement']; ?></td>
                        <td><?php echo $row['artist_name']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <h3>Recent Reviews</h3>
            <table border="1" class="table">
                <tr>
                    <th>Customer</th>
                    <th>Artist</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultRecentartist_reviews)) : ?>
                    <tr>
                        <td><?php echo $row['customer_user_id']; ?></td>
                        <td><?php echo $row['artist_name']; ?></td>
                        <td><?php echo $row['rating']; ?>/5</td>
                        <td><?php echo $row['comment']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <a class="logout-link" href="logout.php">Logout</a>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>