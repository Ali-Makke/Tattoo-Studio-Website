<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';

$name = $email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "INSERT INTO customers (name, email) VALUES ('$name', '$email')";

    if (mysqli_query($conn, $sql)) {
        $name = $email = '';
    } else {
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
    <title>Add New Customer</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
            <h2 class="heading">Add New Customer</h2>
        </header>
        <?php
        if (!empty($error)) {
            echo "<p>" . htmlspecialchars($error) . "</p>";
        }
        ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <br>
            <button type="submit">Add Customer</button>
        </form>
        <br>
        <a href="admin.php">Back to Admin Dashboard</a>
    </div>

</body>

</html>