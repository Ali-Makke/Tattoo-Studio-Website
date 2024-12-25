<?php
session_start();
require 'db_connect.php';
require 'common_functions.php';

$successMessage = $errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = uniqid();
    $formType = $_POST['form_id'] ?? '';

    if ($formType === "signup") {
        // Collect and sanitize inputs
        $fname = test_input($_POST["fname"] ?? "");
        $lname = test_input($_POST["lname"] ?? "");
        $femail = test_input($_POST["femail"] ?? "");
        $fpassword = test_input($_POST["fpassword"] ?? "");

        if (empty($fname) || empty($lname) || empty($femail) || empty($fpassword)) {
            $errorMessage = "All fields are required.";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $fname) || !preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
            $errorMessage = "Only letters and white space allowed in names.";
        } elseif (!filter_var($femail, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format.";
        } elseif ($passwordErr = isValidPassword($fpassword)) {
            $errorMessage = implode("<br>", $passwordErr);
        } else {
            // Hash the password
            $fpasswordHashed = password_hash($fpassword, PASSWORD_DEFAULT);

            // check if the email is already found
            $sqlCheckEmail = "SELECT NULL 
                              FROM users
                              WHERE users.email = '$femail'";
            $sqlEmailFound = mysqli_query($conn, $sqlCheckEmail);
            if (mysqli_num_rows($sqlEmailFound) > 0) {
                $errorMessage = "An account is already available for this email.";
            } else { //if no duplicate email found
                // Insert data into the database
                $sqlAddUserCustomer = "INSERT INTO users (id, fname, lname, email, password) VALUES ('$userId', '$fname', '$lname', '$femail', '$fpasswordHashed')";
                $sqlAddCustomer = "INSERT INTO customers (user_id) VALUES ('$userId')";

                if (mysqli_query($conn, $sqlAddUserCustomer) && mysqli_query($conn, $sqlAddCustomer)) {
                    $successMessage = "Customer account created successfully.";
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $femail;
                    $_SESSION['fname'] = $fname;
                    $_SESSION['lname'] = $lname;
                    $_SESSION['role_txt'] = 'customer';
                    header("Location: index.php");
                    exit();
                } else {
                    $errorMessage = "Error: " . mysqli_error($conn);
                }
            }
        }
    }

    if ($formType === "login") {
        // Collect and sanitize inputs
        $lemail = test_input($_POST["lemail"] ?? "");
        $lpassword = test_input($_POST["lpassword"] ?? "");

        // Basic validation
        if (empty($lemail) || empty($lpassword)) {
            $errorMessage = "All fields are required.";
        } elseif (!filter_var($lemail, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format.";
        } else {
            // Query user
            $sql = "SELECT users.*, roles.role as role_txt
                    FROM users
                    INNER JOIN roles ON users.role_id = roles.id
                    WHERE users.email = '$lemail'";
            $result = mysqli_query($conn, $sql);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($lpassword, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['fname'] = $user['fname'];
                $_SESSION['lname'] = $user['lname'];
                $_SESSION['role_txt'] = $user['role_txt'];

                if ($user['role_txt'] === 'admin') {
                    header("Location: dashboard_admin.php");
                } elseif ($user['role_txt'] === 'artist') {
                    header("Location: dashboard_artist.php");
                } else {
                    header("Location: dashboard_customer.php");
                }
                exit();
            } else {
                $errorMessage = "Invalid email or password.";
            }
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
    <title>InvVibe | Sign-in</title>
    <link rel="icon" type="image/x-icon" href="images/icons/t4.png">
    <link rel="stylesheet" href="styles/style_signup.css">
    <script defer src="scripts/signup.js"></script>
    <script>
        window.onload = function() {
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        };
    </script>
</head>

<body>
    <a href="index.php" id="home">&#8592; Go back home</a>
    <div id="form_Wrapper">
        <!-- Signup Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="signupForm" class="form-input-container">
            <div class="top">
                <p>Have an account? <a href="#" class="link" onclick="showLoginForm()">Login</a></p>
                <header>Sign Up</header>
            </div>
            <div class="inputs_container">
                <input type="hidden" name="form_id" value="signup">
                <div class="name-inputs">
                    <label for="fname" hidden></label>
                    <input type="text" name="fname" class="input names" id="fname" placeholder="Firstname" required>
                    <label for="lname" hidden></label>
                    <input type="text" name="lname" class="input names" id="lname" placeholder="Lastname" required>
                </div>
                <label for="email" hidden></label>
                <input type="email" name="femail" class="input input-field" id="email" placeholder="Email" required>
                <label for="password" hidden></label>
                <input type="password" name="fpassword" class="input input-field" id="password" placeholder="Password" required>
                <input type="submit" class="input submit" value="Register">
            </div>
        </form>

        <!-- Login Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" class="form-input-container">
            <div class="top">
                <p>Don't have an account? <a href="#" class="slink" onclick="hideLoginForm()" tabindex="-1">Register</a></p>
                <header>Login</header>
            </div>
            <div class="inputs_container">
                <input type="hidden" name="form_id" value="login">
                <label for="lemail" hidden></label>
                <input type="email" name="lemail" class="sinput input-field" id="lemail" placeholder="Email" tabindex="-1" required>
                <label for="lpassword" hidden></label>
                <input type="password" name="lpassword" class="sinput input-field" id="lpassword" placeholder="Password" tabindex="-1" autocomplete="current-password" required>
                <input type="submit" class="sinput submit" value="Sign In" tabindex="-1">
            </div>
        </form>
    </div>
</body>

</html>