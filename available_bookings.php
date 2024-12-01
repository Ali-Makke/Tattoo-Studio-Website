<?php
require 'authentication_check.php';
require 'db_connect.php';

authenticate_user();
$isAdmin = is_admin();

$sqlBookings = "SELECT bookings.*,
                       users.fname AS artist_fname,
                       users.lname AS artist_lname
                FROM bookings 
                LEFT JOIN users ON bookings.artist_id = users.id 
                ORDER BY bookings.created_at DESC";
$resultBookings = mysqli_query($conn, $sqlBookings);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Available Bookings</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Available Bookings</h2>
        <div class="table-responsive">
            <table border="1" class="table">
                <tr>
                    <th>Email</th>
                    <th>Style</th>
                    <th>Placement</th>
                    <th>Idea</th>
                    <th>Image</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Budget</th>
                    <th>Dates</th>
                    <th>Times</th>
                    <th>Additional Info</th>
                    <th>Artist</th>
                    <?php if ($isAdmin) { ?>
                        <th>Actions</th>
                    <?php } ?>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultBookings)) { ?>
                    <tr>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['style']; ?></td>
                        <td><?php echo $row['placement']; ?></td>
                        <td><?php echo $row['idea']; ?></td>
                        <td>
                            <?php if (!empty($row['image_url'])) { ?>
                                <img src="<?php echo $row['image_url']; ?>" alt="Tattoo Image" width="100">
                            <?php } else { ?>
                                No Image
                            <?php } ?>
                        </td>
                        <td><?php echo $row['color']; ?></td>
                        <td><?php echo $row['size']; ?></td>
                        <td><?php echo $row['budget']; ?></td>
                        <td><?php echo $row['dates']; ?></td>
                        <td><?php echo $row['times']; ?></td>
                        <td><?php echo $row['additional_info']; ?></td>
                        <td><?php echo isset($row['artist_fname']) ? $row['artist_fname'] . ' ' . $row['artist_lname'] : 'Unassigned'; ?></td>
                        <?php if ($isAdmin) { ?>
                            <td>
                                <a href="assign_booking.php?id=<?php echo $row['id']; ?>">Assign</a>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <a href="admin.php">Back to Admin Dashboard</a>
        <a class="logout-link" href="logout.php">Logout</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>