<?php
require 'authentication_check.php';
require_customer_access();
require 'db_connect.php';

$email = $_SESSION['email'];

$sqlCustomerBookings = "SELECT bookings.*
                        FROM bookings
                        JOIN customers ON customers.id = bookings.customer_id
                        JOIN users ON users.id = customers.user_id
                        WHERE users.email = '$email'
                        ORDER BY bookings.created_at ASC;";
$resultCustomerBookings = mysqli_query($conn, $sqlCustomerBookings);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Customer Dashboard</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>

    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Customer Dashboard</h2>

        <h3>Profile Information</h3>
        <ul>
            <li>Username: <?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></li>
            <li>Email: <?php echo $_SESSION['email']; ?></li>
        </ul>

        <h3>Your Bookings</h3>

        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>Image</th>
                    <th>Details</th>
                    <th>Dates</th>
                    <th>Times</th>
                    <th>Additional Info</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultCustomerBookings)) { ?>
                    <tr>
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
                        <td><?php echo $row['preferred_dates']; ?></td>
                        <td><?php echo $row['preferred_times']; ?></td>
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