<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

if (!is_admin()) {
    header("Location: user.php");
    exit();
}

$fname = $lname = $email = $password = "";
$ferr = $nameErr = $emailErr = $passwordErr = $passErr = "";
$successMessage = $errorMessage = "";

// Fetch artists
$sqlArtists = "SELECT artists.*, users.fname as artist_fname
               FROM artists
               JOIN users ON artists.id = users.id;";
$resultArtists = mysqli_query($conn, $sqlArtists);

// Fetch artist_reviews
$sqlartist_reviews = "SELECT artist_reviews.*, customers.id AS customer_id, users.fname AS customer_fname, artists.id AS artist_id, users2.fname AS artist_fname
               FROM artist_reviews
               JOIN customers ON artist_reviews.customer_id = customers.id
               JOIN users AS users ON customers.user_id = users.id
               JOIN artists ON artist_reviews.artist_id = artists.id
               JOIN users AS users2 ON artists.user_id = users2.id
               ORDER BY artist_reviews.created_at DESC";
$resultartist_reviews = mysqli_query($conn, $sqlartist_reviews);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_artist_account'])) {
        if (empty(trim($_POST["fname"])) || empty(trim($_POST["lname"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"]))) {
            $errorMessage = "All fields are required.";
        } else {
            $fname = test_input($_POST["fname"]);
            $lname = test_input($_POST["lname"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $fname) || !preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
                $errorMessage = "Only letters and white space allowed in names.";
            }
            $email = test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
            }
            $password = test_input($_POST["password"]);
            $passwordErrors = isValidPassword($password);
            if (!empty($passwordErrors)) {
                $errorMessage = implode(" ", $passwordErrors);
            }
            if (empty($errorMessage)) {
                $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
                $sqlAddUserArtist = "INSERT INTO users (fname, lname, email, password, role_id) VALUES ('$fname', '$lname', '$email', '$passwordHashed', 3)";
                if (mysqli_query($conn, $sqlAddUserArtist)) {
                    $successMessage = "Artist account added successfully.";
                } else {
                    $errorMessage = "Database error: " . mysqli_error($conn);
                }
            }
        }
    } elseif (isset($_POST['edit_artist_bio'])) {
        $artistId = $_POST['artist_id'];
        $bio = test_input($_POST['bio']);
        $sqlUpdateBio = "UPDATE artists SET bio = '$bio' WHERE id = '$artistId'";
        if (mysqli_query($conn, $sqlUpdateBio)) {
            $successMessage = "Biography updated successfully.";
        } else {
            $errorMessage = "Database error: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['delete_review'])) {
        $reviewId = $_POST['review_id'];
        $sqlDeleteReview = "DELETE FROM artist_reviews WHERE id = $reviewId";
        if (mysqli_query($conn, $sqlDeleteReview)) {
            $successMessage = "Review deleted successfully.";
        } else {
            $errorMessage = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Artists</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script>
        // Display alerts for errors or success messages
        window.onload = function () {
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        };
    </script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>
        <h2 class="heading">Manage Artists</h2>

        <!-- Artist List -->
        <section class="artist-list">
            <h3>Artists</h3>
            <table border="1" class="table">
                <tr>
                    <th>Name</th>
                    <th>Bio</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultArtists)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['artist_fname']); ?></td>
                        <td><?php echo htmlspecialchars($row['bio']); ?></td>
                        <td><?php echo number_format($row['rating'], 1); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="artist_id" value="<?php echo $row['id']; ?>">
                                <input type="text" name="bio" placeholder="Edit Biography" required>
                                <button type="submit" name="edit_artist_bio">Edit Bio</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>

        <!-- Add Artist -->
        <section class="add-artist">
            <h3>Add Artist Account</h3>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="fname">First name:</label>
                <input type="text" name="fname" id="fname" placeholder="Firstname" required>
                <label for="lname">Last name:</label>
                <input type="text" name="lname" id="lname" placeholder="Lastname" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <br>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Password" autocomplete="current-password" required>
                <button type="submit" name="add_artist_account">Add Artist Account</button>
            </form>
        </section>

        <!-- Manage artist_reviews -->
        <section class="manage-artist_reviews">
            <h3>Reviews</h3>
            <table border="1" class="table">
                <tr>
                    <th>Artist</th>
                    <th>Customer</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultartist_reviews)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['artist_fname']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_fname']); ?></td>
                        <td><?php echo htmlspecialchars($row['content']); ?></td>
                        <td><?php echo number_format($row['rating'], 1); ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="review_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_review" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>
        <a class="back-link" href="admin_dashboard.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>