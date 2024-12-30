<?php
require 'authentication_check.php';
require_customer_access();
require 'db_connect.php';
require 'common_functions.php';

$email = $_SESSION['email'];
$errorMessage = $successMessage = '';

$sqlCustomerBookings = "SELECT bookings.*,
    artists.id AS artist_id,
    users2.fname AS artist_fname,
    users2.lname AS artist_lname
FROM bookings
LEFT JOIN customers ON bookings.customer_id = customers.id
LEFT JOIN users AS users ON customers.user_id = users.id
LEFT JOIN artists ON bookings.artist_id = artists.id
LEFT JOIN users AS users2 ON artists.user_id = users2.id
WHERE users.email = '$email'
ORDER BY bookings.created_at ASC;";

$resultCustomerBookings = mysqli_query($conn, $sqlCustomerBookings);

if (isset($_POST['review_artist'])) {
    if (empty($fname) || empty($lname) || empty($femail) || empty($fpassword)) {
        $errorMessage = "All fields are required.";
    }else {
        $rating = test_input($_POST['rating']);
        $comment = test_input($_POST['review']);
        $artistId = test_input($_POST['artist_id']);
        $userId = test_input($_SESSION['user_id']);
    
        $sqlCustomerId = "SELECT customers.id AS customer_id
                                 FROM customers
                                 JOIN users ON customers.user_id = users.id
                                 WHERE customers.user_id = '$userId'";
        $resultCustomers = mysqli_query($conn, $sqlCustomerId);
        $customerId = mysqli_fetch_assoc($resultCustomers)['customer_id'];
    
        $sqlAddReview = "INSERT INTO `artist_reviews`(`customer_id`, `artist_id`, `rating`, `comment`) 
                         VALUES ('$customerId','$artistId','$rating','$comment')";
    
        if (mysqli_query($conn, $sqlAddReview)) {
            $successMessage = "Review has been added successfully";
        } else {
            $errorMessage = "Error adding review";
        }
        $sqlGetRatingAvg = "SELECT ROUND(AVG(rating), 2) AS avg
                            FROM `artist_reviews`
                            WHERE artist_id = $artistId";
        $resultAvg = mysqli_query($conn, $sqlGetRatingAvg);
        $ratingAvg = mysqli_fetch_assoc($resultAvg)['avg'];
    
        $sqlUpdateRating = "UPDATE artists set rating = '$ratingAvg' WHERE artists.id = $artistId";
        $resultUpdate = mysqli_query($conn, $sqlUpdateRating);
    }
}
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
    <script>
        window.onload = function() {
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        }
    </script>
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
                    <th>Artist</th>
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
                        <td>
                            <?php echo $row['artist_id'] . $row['artist_fname'] . ' ' . $row['artist_lname']; ?>
                            <!-- make it so that they can only review once -->
                            <br><br>
                            <form method="post">
                                <input type="hidden" name="artist_id" value="<?php echo $row['artist_id']; ?>">

                                <label for="rating">Rating:</label>
                                <input type="number" name="rating" id="rating" min="1" max="5" step="0.1" required>
                                <br><br>
                                <label for="review">Comment:</label>
                                <textarea id="review" name="review" required></textarea>
                                <br>
                                <button type="submit" name="review_artist">Add Review</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <a href="logout.php">Logout</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>