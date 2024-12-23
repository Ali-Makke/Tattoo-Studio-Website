<?php
require 'authentication_check.php';
require_customer_access();
require 'db_connect.php';

$username = $_SESSION['fname'];

$sqlUser = "SELECT * FROM users WHERE fname = '$username'";
$resultUser = mysqli_query($conn, $sqlUser);
$user = mysqli_fetch_assoc($resultUser);
$userId = $user['id'];

$sqlAssignedBookings = "SELECT * 
                        FROM bookings
                        WHERE bookings.artist_id = $userId";
$resultAssignedBookings = mysqli_query($conn, $sqlAssignedBookings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Customer Dashboard</title>
    <link rel="stylesheet" href="styles/included.css">
    <link rel="stylesheet" href="styles/admin.css">
    <script defer src="scripts/included.js"></script>
</head>
<body>
    
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h2 class="heading">Customer Dashboard</h2>
        </header>
        <p>Welcome, <?php echo $_SESSION['fname']; ?>.</p>
        
        <h3>Profile Information</h3>
        <ul>
            <li>Username: <?php echo $user['fname']; ?></li>
            <li>Email: <?php echo $user['email']; ?></li>
        </ul>
        

        <h3>Your Bookings</h3>
        
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>Email</th>
                    <th>Style</th>
                    <th>Placement</th>
                    <th>Idea</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Budget</th>
                    <th>Dates</th>
                    <th>Times</th>
                    <th>Additional Info</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultAssignedBookings)) { ?>
                    <tr>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['style']; ?></td>
                        <td><?php echo $row['placement']; ?></td>
                        <td><?php echo $row['idea']; ?></td>
                        <td><?php echo $row['color']; ?></td>
                        <td><?php echo $row['size']; ?></td>
                        <td><?php echo $row['budget']; ?></td>
                        <td><?php echo $row['dates']; ?></td>
                        <td><?php echo $row['times']; ?></td>
                        <td><?php echo $row['additional_info']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <a href="logout.php">Logout</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
