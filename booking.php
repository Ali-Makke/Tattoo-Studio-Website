<?php
include 'authentication_check.php';
require 'db_connect.php';
require 'common_functions.php';

$errorMessage = $successMessage = '';
$canBook = (!isset($_SESSION['email']) || (!is_customer() && !is_artist() && !is_admin()));
// only a customer that is logged in can create a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canBook) {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $sqlCustomerId = "SELECT customers.id AS customer_id
                             FROM customers
                             JOIN users ON customers.user_id = users.id
                             WHERE customers.user_id = '$userId'";
        $resultCustomers = mysqli_query($conn, $sqlCustomerId);
        // get a random artist
        $sqlartistId = "SELECT artists.id AS artist_id FROM artists";
        $resultArtist = mysqli_query($conn, $sqlartistId);
        $artistId = mysqli_fetch_assoc($resultArtist)['artist_id'];
        $customerId = mysqli_fetch_assoc($resultCustomers)['customer_id'];


        if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $age_confirmation = isset($_POST['age_confirmation']) ? 1 : 0;
            if ($age_confirmation = 0) {
                $errorMessage = "You must be older than 18";
            } else {
                $style = mysqli_real_escape_string($conn, $_POST['style']);
                $placement = mysqli_real_escape_string($conn, $_POST['placement']);
                $idea = mysqli_real_escape_string($conn, $_POST['idea']);
                $color = mysqli_real_escape_string($conn, $_POST['color']);
                $size = mysqli_real_escape_string($conn, $_POST['size']);
                $budget = mysqli_real_escape_string($conn, $_POST['budget']);
                $dates = implode(", ", $_POST['dates']);
                $times = implode(", ", $_POST['times']);
                $additional_info = isset($_POST['additional_info']) ? mysqli_real_escape_string($conn, $_POST['additional_info']) : '';

                $image_url = uploadImage(fileKey: 'image', target_dir: 'images/booking_uploads/');
                if (str_contains($image_url, 'images/booking_uploads/')) {
                    $sql = "INSERT INTO bookings (age_confirmation, style, placement, idea, image_url, color, size, budget, preferred_dates, preferred_times, additional_info, status, customer_id, artist_id) 
                            VALUES ('$age_confirmation', '$style', '$placement', '$idea', '$image_url', '$color', '$size', '$budget', '$dates', '$times', '$additional_info', 'pending', '$customerId', '$artistId')";
                    if (mysqli_query($conn, $sql)) {
                        $successMessage = "Your booking was added successfully";
                    } else {
                        $errorMessage = "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }
                } else {
                    $errorMessage = "Error: " . $image_url;
                }
            }
        } else {
            $errorMessage = "No file uploaded or an error occurred";
        }
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Booking</title>
    <link rel="icon" type="image/x-icon" href="images/icons/logo1.svg">
    <link rel="stylesheet" href="styles/style_booking.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
    <script defer src="scripts/drop_box.js"></script>
    <script>
        window.onload = function() {
            <?php if ($canBook) { ?>
                alert('You need to sign in before you can submit your booking.');
            <?php } ?>
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        }
    </script>
</head>

<body>
    <div class="header_wrapper">
        <header>
            <?php include 'navbar.php'; ?>
            <div class="header_text_container">
                <h1 class="header_text quotes" id="header_text">
                    <i>Wear your heart on your skin</i>
                </h1>
            </div>
        </header>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
        <section class="sectionf" id="section1">
            <h1>Book Your Tattoo</h1>
            <label>
                <input type="checkbox" name="age_confirmation" required>
                I understand that I must be 18 years or older to get tattooed
            </label>
        </section>
        <section class="sectionb" id="section2">
        </section>

        <section class="sectionf" id="section3">
            <h1>The good stuff:</h1>
            <div class="sibling">
                <div>
                    <label for="style">What's the style you're looking for?</label>
                    <select id="style" name="style" required>
                        <option value="Geometric">Geometric</option>
                        <option value="New-school">New-school</option>
                        <option value="Old-school">Old-school</option>
                        <option value="Japanese">Japanese</option>
                        <option value="Hyperrealism">Hyperrealism</option>
                        <option value="Abstract">Abstract</option>
                        <option value="Lettering">Lettering</option>
                        <option value="Dotwork">Dotwork</option>
                    </select>
                </div>
                <div>
                    <label for="placement">Placement of the tattoo?</label>
                    <select id="placement" name="placement" required>
                        <option value="Arm">Arm</option>
                        <option value="Leg">Leg</option>
                        <option value="Back">Back</option>
                        <option value="Chest">Chest</option>
                        <option value="Shoulder">Shoulder</option>
                        <option value="Forearm">Forearm</option>
                        <option value="Thigh">Thigh</option>
                        <option value="Hand">Hand</option>
                        <option value="Foot">Foot</option>
                    </select>
                </div>
            </div><br>
            <label for="idea">Whats the Tattoo Idea?</label>
            <textarea id="idea" name="idea" rows="4" required></textarea><br>
        </section>

        <section class="sectionb" id="section4">
        </section>

        <section class="sectionf" id="section5">
            <h1>More Details:</h1>
            <div id="dropzone" class="dropzone">
                Drag and drop an image or click to select
            </div>
            <input type="file" id="image" name="image" style="display:none;" required><br><br>
            <div class="sibling">
                <div>
                    <label for="color">Color:</label>
                    <select id="color" name="color">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select><br><br>
                </div>
                <div>
                    <label for="size">Size of Tattoo:</label>
                    <select id="size" name="size" required>
                        <option value="Tiny / minimalist">Tiny / minimalist</option>
                        <option value="Small (<5 cm / 2'')">Small (&lt;5 cm / 2'')</option>
                        <option value="Medium (up to 10 cm / 4'')">Medium (up to 10 cm / 4'')</option>
                        <option value="Large (up to 20 cm / 8'')">Large (up to 20 cm / 8'')</option>
                        <option value="Half sleeve">Half sleeve</option>
                        <option value="Full limb">Full limb</option>
                        <option value="Full back">Full back</option>
                    </select><br><br>
                </div>
            </div>
            <label for="budget">Your Budget ($):</label>
            <input type="number" id="budget" name="budget" min="30" required><br><br>
        </section>

        <section class="sectionb" id="section6">
        </section>

        <section class="sectionf" id="section7">
            <h1>Final Details</h1>
            <label for="dates">Preferred Dates:</label>
            <div class="final_dates">
                <input type="checkbox" id="flexible" name="dates[]" value="flexible">
                <label for="flexible">I am flexible</label>
                <input type="checkbox" id="monday" name="dates[]" value="Mon">
                <label for="monday">Monday</label>
                <input type="checkbox" id="tuesday" name="dates[]" value="Tue">
                <label for="tuesday">Tuesday</label>
                <input type="checkbox" id="wednesday" name="dates[]" value="Wed">
                <label for="wednesday">Wednesday</label>
                <input type="checkbox" id="thursday" name="dates[]" value="Thu">
                <label for="thursday">Thursday</label>
                <input type="checkbox" id="friday" name="dates[]" value="Fri">
                <label for="friday">Friday</label>
                <input type="checkbox" id="saturday" name="dates[]" value="Sat">
                <label for="saturday">Saturday</label>
                <input type="checkbox" id="sunday" name="dates[]" value="Sun">
                <label for="sunday">Sunday</label><br>
            </div>
            <label for="times">Available Times:</label><br>
            <div class="final_times">
                <input type="checkbox" id="time_flexible" name="times[]" value="flexible">
                <label for="time_flexible">I am flexible</label><br>
                <input type="checkbox" id="time_8_12" name="times[]" value="8am-12pm">
                <label for="time_8_12">8am-12pm</label><br>
                <input type="checkbox" id="time_12_4" name="times[]" value="12pm-4pm">
                <label for="time_12_4">12pm-4pm</label><br>
                <input type="checkbox" id="time_4_8" name="times[]" value="4pm-8pm">
                <label for="time_4_8">4pm-8pm</label><br>
                <input type="checkbox" id="time_8_2" name="times[]" value="8pm-2am">
                <label for="time_8_2">8pm-2am</label><br><br>
            </div>
            <label for="additional_info">Is there anything else you'd like us to know?</label>
            <textarea id="additional_info" name="additional_info" rows="4"></textarea><br><br>
            <p>Thank you for choosing InkVibe. We look forward to creating something amazing with you!</p>

            <?php if (!$canBook) { ?>
                <button type="submit">Submit Booking</button>
            <?php } else { ?>
                <a href="sign-up.php" style="text-decoration: none; background-color: #B09F72; padding: 5px 10px 5px 10px; border-radius: 5px; color: white">Submit Booking</a>
            <?php } ?>
        </section>
    </form>
    <?php include 'footer.php'; ?>
</body>

</html>