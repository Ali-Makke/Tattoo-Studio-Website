<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

if (!is_admin()) {
    header("Location: user.php");
    exit();
}

$currentMonth = date('m');
$sqlCustomers = "SELECT COUNT(*) AS num_customers FROM customers WHERE MONTH(created_at) = '$currentMonth'";
$resultCustomers = mysqli_query($conn, $sqlCustomers);
$rowCustomers = mysqli_fetch_assoc($resultCustomers);
$numCustomers = $rowCustomers['num_customers'];

$sqlRevenue = "SELECT SUM(amount) AS total_revenue FROM transactions WHERE type = 'revenue'";
$resultRevenue = mysqli_query($conn, $sqlRevenue);
$rowRevenue = mysqli_fetch_assoc($resultRevenue);
$totalRevenue = $rowRevenue['total_revenue'];

$sqlExpenses = "SELECT SUM(amount) AS total_expenses FROM transactions WHERE type = 'expense'";
$resultExpenses = mysqli_query($conn, $sqlExpenses);
$rowExpenses = mysqli_fetch_assoc($resultExpenses);
$totalExpenses = $rowExpenses['total_expenses'];

$sqlRecentTattoos = "SELECT tattoos.*, customers.name AS customer_name, users.fname AS artist_name 
                     FROM tattoos 
                     JOIN customers ON tattoos.customer_id = customers.id 
                     JOIN users ON tattoos.user_id = users.id 
                     ORDER BY tattoos.date DESC LIMIT 10";
$resultRecentTattoos = mysqli_query($conn, $sqlRecentTattoos);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Admin</title>
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
        <div class="statistics">
            <h3>Statistics</h3>
            <ul>
                <li>Number of Customers this Month: <?php echo $numCustomers; ?></li>
                <li>Total Revenue: $<?php echo number_format($totalRevenue, 2); ?></li>
                <li>Total Expenses: $<?php echo number_format($totalExpenses, 2); ?></li>
                <li>Net Income: $<?php echo number_format(($totalRevenue - $totalExpenses), 2); ?></li>
            </ul>
        </div>
        <div class="controls">
            <h3>Controls</h3>
            <ul>
                <li><a href="add_tattoo.php">Add Tattoo</a></li>
                <li><a href="add_customer.php">Add New Customer</a></li>
                <li><a href="add_transaction.php">Add New Transaction</a></li>
                <li><a href="add_to_gallery.php">Add new work to gallery</a></li>
                <li><a href="available_bookings.php">Check available bookings</a></li>
                <li><a href="remove_from_gallery.php">Remove Tattoo from Gallery</a></li>
            </ul>
        </div>
        <div class="recent-transactions">
            <h3>Recent Transactions</h3>
            <div class="table-responsive">
                <table border="1" class="table">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    $sqlRecentTransactions = "SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT 10";
                    $resultRecentTransactions = mysqli_query($conn, $sqlRecentTransactions);
                    while ($row = mysqli_fetch_assoc($resultRecentTransactions)) {
                        echo "<tr>";
                        echo "<td>" . $row['transaction_date'] . "</td>";
                        echo "<td>" . ucfirst($row['type']) . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            <h3>Recent Tattoos</h3>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Artist</th>
                        <th>Description</th>
                        <th>Price</th>
                    </tr>
                    <?php
                    while ($row = mysqli_fetch_assoc($resultRecentTattoos)) {
                        echo "<tr>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . $row['customer_name'] . "</td>";
                        echo "<td>" . $row['artist_name'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>$" . number_format($row['price'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
        <a class="logout-link" href="logout.php">Logout</a>

    </div>
    <?php include 'footer.php'; ?>
</body>

</html>