<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

// Fetch customers and their associated data
$sqlCustomers = "SELECT customers.*, users.fname, users.lname, users.email, users.account_status as status
                 FROM customers
                 JOIN users ON customers.user_id = users.id";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

// Fetch customer bookings and history
if (isset($_GET['customer_id'])) {
    $customerId = intval($_GET['customer_id']);
    $sqlBookings = "SELECT bookings.*, tattoos.description AS tattoo_description
                                FROM bookings
                                LEFT JOIN tattoos ON bookings.id = tattoos.booking_id
                                WHERE bookings.customer_id = $customerId";
    $resultBookings = mysqli_query($conn, $sqlBookings);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_account_status'])) {
        $userId = $_POST['user_id'];
        $status = $_POST['status'] === 'active' ? 'inactive' : 'active';
        $sqlUpdateStatus = "UPDATE users SET account_status = '$status' WHERE id = '$userId'";
        if (mysqli_query($conn, $sqlUpdateStatus)) {
            echo "<script>alert('Account status updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating account status: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Customers</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>

        <h2 class="heading">Manage Customers</h2>

        <!-- Customer List -->
        <section class="customer-list">
            <h3>Customers</h3>
            <table border="1" class="table">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultCustomers)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $row['status']; ?>">
                                <button type="submit" name="toggle_account_status">
                                    <?php echo $row['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>

        <!-- Customer Booking History -->
        <section class="customer-bookings">
            <h3>Customer Booking History</h3>
            <form method="GET">
                <label for="customer_id">Select Customer:</label>
                <select name="customer_id" id="customer_id" required>
                    <option value="">-- Select a Customer --</option>
                    <?php
                    mysqli_data_seek($resultCustomers, 0);
                    while ($row = mysqli_fetch_assoc($resultCustomers)) { ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit">View History</button>
            </form>

            <?php
            if (isset($_GET['customer_id'])) {
                if (mysqli_num_rows($resultBookings) > 0) {
                    echo '<table border="1" class="table">';
                    echo '<tr><th>Status</th><th>Preferred Dates</th><th>Preferred Times</th><th>Tattoo Description</th></tr>';
                    while ($booking = mysqli_fetch_assoc($resultBookings)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($booking['status']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['preferred_dates']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['preferred_times']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['tattoo_description'] ?? 'N/A') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>No bookings found for this customer.</p>';
                }
            }
            ?>
        </section>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>