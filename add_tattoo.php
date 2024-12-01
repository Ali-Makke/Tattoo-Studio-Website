<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

$sqlCustomers = "SELECT id, name FROM customers";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

$sqlUsers = "SELECT id, fname FROM users WHERE role IN ('user', 'admin')";
$resultUsers = mysqli_query($conn, $sqlUsers);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    $sqltattoo = "INSERT INTO tattoos (customer_id, user_id, description, date, price) 
            VALUES ('$customer_id', '$user_id', '$description', '$date', '$price')";
    if (!mysqli_query($conn, $sqltattoo)) {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tattoo</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
        <?php include 'navbar.php'; ?>
        <h1 class="heading">Add New Tattoo</h1>
    </header>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="user_id">Artist:</label>
            <select id="user_id" name="user_id" required>
                <?php
                while ($row = mysqli_fetch_assoc($resultUsers)) {
                    echo "<option value=\"" . $row['id'] . "\">" . $row['fname'] . "</option>";
                }
                ?>
            </select>
            <br>
            <label for="customer_id">Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <?php
                while ($row = mysqli_fetch_assoc($resultCustomers)) {
                    echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <br>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
            <br>
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.5" min="0" required>
            <br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <br>
            <button type="submit">Add Tattoo</button>
        </form>
        <br>
        <a href="admin.php">Back to Admin Dashboard</a>
    </div>
</body>

</html>