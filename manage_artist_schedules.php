<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

$artists = [];
$sqlArtists = "SELECT artists.id AS artist_id, users.fname, users.lname 
               FROM artists 
               JOIN users ON artists.user_id = users.id";
$resultArtists = mysqli_query($conn, $sqlArtists);
while ($row = mysqli_fetch_assoc($resultArtists)) {
    $artists[] = $row;
}

if (isset($_GET['artist_id'])) {
    $artistId = $_GET['artist_id'];
    $sqlBookings = "SELECT bookings.id AS booking_id, bookings.status AS status, users.fname AS customer_fname, users.lname AS customer_lname
                           FROM bookings
                           JOIN customers ON bookings.customer_id = customers.id
                           JOIN users ON customers.user_id = users.id
                           WHERE bookings.artist_id = $artistId";
    $resultBookings = mysqli_query($conn, $sqlBookings);
}

$pendingBookings = [];
if (isset($_GET['artist_id'])) {
    $artistId = $_GET['artist_id'];
    $sqlPendingBookings = "SELECT bookings.id AS booking_id, users.fname AS customer_fname, users.lname AS customer_lname
                           FROM bookings
                           JOIN customers ON bookings.customer_id = customers.id
                           JOIN users ON customers.user_id = users.id
                           WHERE bookings.artist_id = $artistId AND bookings.status = 'pending'";
    $resultPendingBookings = mysqli_query($conn, $sqlPendingBookings);
    while ($row = mysqli_fetch_assoc($resultPendingBookings)) {
        $pendingBookings[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $artistId = $_POST['artist_id'];
    $bookingId = $_POST['booking_id'];
    $scheduleDate = $_POST['schedule_date'];
    $scheduleTime = $_POST['schedule_time'];

    if (empty($artistId) || empty($bookingId) || empty($scheduleDate) || empty($scheduleTime)) {
        $error = "All fields are required.";
    } else {
        $sqlAddSchedule = "INSERT INTO artist_schedules (artist_id, booking_id, date, time) VALUES ('$artistId', '$bookingId', '$scheduleDate', '$scheduleTime')";
        $approveBooking = "UPDATE bookings SET status = 'approved' WHERE id = '$bookingId'";
        if (mysqli_query($conn, $sqlAddSchedule) && mysqli_query($conn, $approveBooking)) {
            $success = "Schedule added successfully.";
        } else {
            $error = "Error adding schedule: " . mysqli_error($conn);
        }
    }
}

// Reschedule Booking
if (isset($_POST['reschedule_booking'])) {
    $bookingId = $_POST['booking_id'];
    $newDate = $_POST['reschedule_date'];
    $newTime = $_POST['reschedule_time'];
    $sqlReschedule = "UPDATE bookings SET dates = '$newDate', times = '$newTime' WHERE id = $bookingId";
    mysqli_query($conn, $sqlReschedule);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Artist Scheduling</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Artist Scheduling</h2>

        <!-- Error/Success Messages -->
        <?php if (!empty($error)) {
            echo "<div class='alert alert-error'>$error</div>";
        } ?>
        <?php if (!empty($success)) {
            echo "<div class='alert alert-success'>$success</div>";
        } ?>

        <!-- Artist Selection -->
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
        </form>

        <!-- Pending Bookings for Selected Artist -->
        <?php if (isset($_GET['artist_id'])) { ?>
            <section>
                <h3>All Bookings</h3>
                <?php if (mysqli_num_rows($resultBookings) > 0) { ?>
                    <table border="1" class="table">
                        <tr>
                            <th style="width: 20%;">Booking ID</th>
                            <th>Customer Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        <?php while ($booking = mysqli_fetch_assoc($resultBookings)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_fname'] . ' ' . $booking['customer_lname']); ?></td>
                                <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                <td>
                                    <!-- Reschedule Booking -->
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <input type="date" name="reschedule_date" required>
                                        <input type="time" name="reschedule_time" required>
                                        <button type="submit" name="reschedule_booking">Reschedule</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } else { ?>
                    <p>No pending bookings found for this artist.</p>
                <?php } ?>
            </section>

            <!-- Add Schedule Form -->
            <section>
                <h3>Add Schedule</h3>
                <form method="POST" action="">
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

                    <label for="schedule_date">Schedule Date:</label>
                    <input type="date" name="schedule_date" id="schedule_date" required>

                    <label for="schedule_time">Schedule Time:</label>
                    <input type="time" name="schedule_time" id="schedule_time" required>

                    <button type="submit" name="add_schedule">Add Schedule</button>
                </form>
            </section>
        <?php } ?>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>