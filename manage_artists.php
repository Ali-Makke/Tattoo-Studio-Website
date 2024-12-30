<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

$successMessage = $errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artist_permissions'])) {
    foreach ($_POST['artist_permissions'] as $artistId => $permissions) {
        $canViewEarnings = isset($permissions['can_view_earnings']) ? 1 : 0;
        $canUpdateBookingStatus = isset($permissions['can_update_booking_status']) ? 1 : 0;
        $canManageSchedules = isset($permissions['can_manage_schedules']) ? 1 : 0;

        $sqlUpdatePermissions = "UPDATE artist_permissions 
            SET 
                can_view_earnings = $canViewEarnings,
                can_update_booking_status = $canUpdateBookingStatus,
                can_manage_schedules = $canManageSchedules
            WHERE artist_id = $artistId";
        mysqli_query($conn, $sqlUpdatePermissions);
    }

    $successMessage = "Permissions updated successfully!";
}

// Fetch all artists and their permissions
$sqlArtists = "SELECT 
        artists.id AS artist_id, 
        users.fname, 
        users.email, 
        ap.can_view_earnings, 
        ap.can_update_booking_status, 
        ap.can_manage_schedules
    FROM artists
    JOIN users ON artists.user_id = users.id
    JOIN artist_permissions ap ON ap.artist_id = artists.id";
$resultArtistsPermissions = mysqli_query($conn, $sqlArtists);

// Fetch artists
$sqlArtists = "SELECT artists.*, users.fname as artist_fname
               FROM artists
               JOIN users ON artists.user_id = users.id;";
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
        $userId = uniqid();
        // Collect and sanitize inputs
        $fname = test_input($_POST["fname"] ?? "");
        $lname = test_input($_POST["lname"] ?? "");
        $email = test_input($_POST["email"] ?? "");
        $password = test_input($_POST["password"] ?? "");

        if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
            $errorMessage = "All fields are required.";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $fname) || !preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
            $errorMessage = "Only letters and white space allowed in names.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format.";
        } elseif ($passwordErr = isValidPassword($password)) {
            $errorMessage = implode("<br>", $passwordErr);
        } else {
            // Hash the password
            $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
            $sqlAddUserArtist = "INSERT INTO users (id, fname, lname, email, password, role_id) VALUES ('$userId', '$fname', '$lname', '$email', '$passwordHashed', 3)";
            $sqlAddUserArtist = "INSERT INTO artists (user_id) VALUES ('$userId')";
            if (mysqli_query($conn, $sqlAddUserArtist) && mysqli_query($conn, $sqlAddArtist)) {
                $successMessage = "Artist account added successfully.";
            } else {
                $errorMessage = "Error: " . mysqli_error($conn);
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
    <script defer src="scripts/included.js"></script>
    <script>
        window.onload = function() {
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        };
    </script>
    <style>
        section {
            margin-bottom: 3%;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>
        <h2 class="heading">Manage Artists</h2>

        <!-- Artist List -->
        <section>
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

        <!-- Manage artist_reviews -->
        <section>
            <h3>Reviews</h3>
            <table border="1" class="table">
                <tr>
                    <th>Artist</th>
                    <th>Customer</th>
                    <th>Comment</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resultartist_reviews)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['artist_fname']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_fname']); ?></td>
                        <td><?php echo $row['comment']; ?></td>
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

        <!-- Manage artist permissions -->
        <section>
            <h3>Manage Artist Permissions</h3>
            <form method="post" action="">
                <table border=1 class="table">
                    <thead>
                        <tr>
                            <th>Artist</th>
                            <th>Email</th>
                            <th>Can View Earnings</th>
                            <th>Can Update Bookings</th>
                            <th>Can Manage Schedules</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($artist = mysqli_fetch_assoc($resultArtistsPermissions)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($artist['fname']); ?></td>
                                <td><?php echo htmlspecialchars($artist['email']); ?></td>
                                <td>
                                    <input type="checkbox" name="artist_permissions[<?php echo $artist['artist_id']; ?>][can_view_earnings]"
                                        <?php echo $artist['can_view_earnings'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <input type="checkbox" name="artist_permissions[<?php echo $artist['artist_id']; ?>][can_update_booking_status]"
                                        <?php echo $artist['can_update_booking_status'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <input type="checkbox" name="artist_permissions[<?php echo $artist['artist_id']; ?>][can_manage_schedules]"
                                        <?php echo $artist['can_manage_schedules'] ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <button type="submit">Update Permissions</button>
            </form>
        </section>

        <!-- Add Artist -->
        <section>
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
                <input type="password" name="password" id="password" placeholder="Password" required>
                <button type="submit" name="add_artist_account">Add Artist Account</button>
            </form>
        </section>
        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>