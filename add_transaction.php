<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date']);

    $sql = "INSERT INTO transactions (type, description, amount, transaction_date) VALUES ('$type', '$description', '$amount', '$transaction_date')";

    if (!mysqli_query($conn, $sql)) {
        $error = "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Transaction</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h2 class="heading">Add New Transaction</h2>
        </header>
        <?php
        if (!empty($error)) {
            echo "<p>" . htmlspecialchars($error) . "</p>";
        }
        ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="revenue">Revenue</option>
                <option value="expense">Expense</option>
            </select>
            <br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <br>
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.50" min="0" required>
            <br>
            <label for="transaction_date">Date:</label>
            <input type="date" id="transaction_date" name="transaction_date" required>
            <br>
            <button type="submit">Add Transaction</button>
        </form>
        <br>
        <a href="admin.php">Back to Admin Dashboard</a>
    </div>
</body>

</html>