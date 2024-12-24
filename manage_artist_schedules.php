<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

// fetch all artists
$artists = [];
$sqlArtists = "SELECT artists.id AS artist_id, users.fname, users.lname 
               FROM artists 
               JOIN users ON artists.user_id = users.id";
$resultArtists = mysqli_query($conn, $sqlArtists);
while ($row = mysqli_fetch_assoc($resultArtists)) {
    $artists[] = $row;
}

// fetch all schedules for an artist
if (isset($_GET['artist_id'])) {
    $artistId = $_GET['artist_id'];
    $sqlSchedules = "SELECT artist_schedules.*, artist_schedules.id AS session_id, DATE_FORMAT(artist_schedules.time, '%h:%i %p') AS time,
        users.fname AS customer_fname, 
        users.lname AS customer_lname
        FROM artist_schedules
        LEFT JOIN bookings ON artist_schedules.booking_id = bookings.id
        LEFT JOIN customers ON bookings.customer_id = customers.id
        LEFT JOIN users ON customers.user_id = users.id
        WHERE artist_schedules.artist_id = '$artistId';";
    $resultSchedules = mysqli_query($conn, $sqlSchedules);
}

// Reschedule Booking
if (isset($_POST['reschedule_booking'])) {
    $sessionId = $_POST['session_id'];
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

    $updateSchedule = "UPDATE artist_schedules SET `date` = '$newDate', `time` = '$newTime' WHERE artist_schedules.id = '$sessionId'";
    mysqli_query($conn, $updateSchedule);
}

// reassign session to another artist
if (isset($_POST['reassign_session'])) {
    $sessionId = $_POST['session_id'];
    $artistId = $_POST['artistid'];

    $sqlReassignArtist = "UPDATE artist_schedules SET artist_id = '$artistId' WHERE id = '$sessionId'";
    mysqli_query($conn, $sqlReassignArtist);
}


// show message
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

        <!-- Artist Selection -->
        <form method="GET" action="">
            <label for="artist_id" style="font-size: larger; font-weight: bolder;">All Schedules For:</label>
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

        <?php if (isset($_GET['artist_id'])) { ?>
            <section>
                <?php if (mysqli_num_rows($resultSchedules) > 0) { ?>
                    <table border="1" class="table">
                        <tr>
                            <th>Session Id</th>
                            <th>Booking Id</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        <?php while ($schedule = mysqli_fetch_assoc($resultSchedules)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['session_id']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['customer_fname'] . ' ' . $schedule['customer_lname']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['session_status']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['date']) . '<br>' . htmlspecialchars($schedule['time']); ?></td>
                                <td>
                                    <?php if ($schedule['session_status'] == 'scheduled') { ?>
                                        <!-- Assign schedule to artist -->
                                        <form method="post">
                                            <input type="hidden" name="session_id" value="<?php echo $schedule['session_id']; ?>">
                                            <select name="artistid" required>
                                                <option value="">-- Reassign Session --</option>
                                                <?php
                                                mysqli_data_seek($resultArtists, 0);
                                                while ($artist = mysqli_fetch_assoc($resultArtists)) {
                                                    echo "<option value='{$artist['artist_id']}'>{$artist['fname']} {$artist['lname']}</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" name="reassign_session">Reassign</button>
                                        </form>
                                        <br>
                                        <!-- Reschedule Booking -->
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="booking_id" value="<?php echo $schedule['booking_id']; ?>">
                                            <input type="hidden" name="session_id" value="<?php echo $schedule['session_id']; ?>">
                                            <input type="date" name="reschedule_date" min="<?php echo date('Y-m-d'); ?>" required>
                                            <input type="time" name="reschedule_time" required>
                                            <br>
                                            <button type="submit" name="reschedule_booking">Reschedule</button>
                                        </form>
                                    <?php } else {
                                        echo 'Booking not scheduled yet';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } else { ?>
                    <p>No bookings Approved found for this artist.</p>
                <?php } ?>
            </section>
        <?php } ?>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>