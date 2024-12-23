<?php
session_start();
require 'db_connect.php';
require 'common_functions.php';

$fname = $lname = $femail = $fpassword = "";
$ferr = $nameErr = $femailErr = $fpasswordErr = $fpassErr = "";
$lemail = $lpassword = "";
$lerr = $lemailErr = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST['form_id'] == "signup") {
        if (empty(trim($_POST["fname"])) || empty(trim($_POST["lname"])) || empty(trim($_POST["femail"])) || empty(trim($_POST["fpassword"]))) {
            $ferr = "---All fields are required---";
        } else {
            $fname = test_input($_POST["fname"]);
            $lname = test_input($_POST["lname"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $fname) || !preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
                $nameErr = "<p>Only letters and white space allowed</p>";
            }
            $femail = test_input($_POST["femail"]);
            if (!filter_var($femail, FILTER_VALIDATE_EMAIL)) {
                $femailErr = "<p>Invalid email format</p>";
            }
            $fpassword = test_input($_POST["fpassword"]);
            $fpasswordErr = isValidPassword($fpassword);
            if (!empty($fpasswordErr)) {
                foreach ($fpasswordErr as $error) {
                    $fpassErr .= "<p>$error</p> <br>";
                }
            }
            if (empty($nameErr) && empty($femailErr) && empty($fpassErr)) {
                $fpasswordHashed = password_hash($fpassword, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (fname, lname, email, password) VALUES ('$fname', '$lname', '$femail', '$fpasswordHashed')";
                if (!mysqli_query($conn, $sql)) {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            }
        }
    }

    if ($_POST['form_id'] == "login") {
        if (empty(trim($_POST["lemail"])) || empty(trim($_POST["lpassword"]))) {
            $lerr = "---All fields are required---";
        } else {
            $lemail = test_input($_POST["lemail"]);
            if (!filter_var($lemail, FILTER_VALIDATE_EMAIL)) {
                $lemailErr = "<p>Invalid email format</p>";
            }
            $lpassword = test_input($_POST["lpassword"]);
            if (empty($lemailErr)) {
                $sql = "SELECT users.*, roles.role as role_txt
                        FROM users
                        INNER JOIN roles
                        ON users.role_id = roles.id
                        WHERE users.email='$lemail'";
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);

                if ($user && password_verify($lpassword, $user['password'])) {
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['fname'] = $user['fname'];
                    $_SESSION['role_txt'] = $user['role_txt'];

                    if ($user['role_txt'] == 'admin') {
                        header("Location: dashboard_admin.php");
                    } else if ($user['role_txt'] == 'artist') {
                        header("Location: dashboard_artist.php");
                    } else {
                        header("Location: dashboard_customer.php");
                    }
                    exit();
                } else {
                    $lerr = "Invalid email or password.";
                }
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
</head>

<body>
    <a href="index.php" id="home">&#8592; Go back home</a>
    <div class="error2">
        <?php
        echo $nameErr;
        echo $femailErr;
        echo $fpassErr;
        echo $lemailErr;
        ?>
    </div>
    <div id="form_Wrapper">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-input-container" id="signupForm">
            <div class="top">
                <p>Have an account? <a href="#" class="link" onclick="showLoginForm()">Login</a></p>
                <header>Sign Up</header>
                <span class="error"><?php echo $ferr; ?></span>
            </div>
            <div class="inputs_container">
                <input type="hidden" name="form_id" value="signup">
                <div class="name-inputs">
                    <input type="text" name="fname" class="input names" id="fname" placeholder="Firstname" required>

                    <input type="text" name="lname" class="input names" id="lname" placeholder="Lastname" required>
                </div>

                <input type="email" name="femail" class="input input-field" id="email" placeholder="Email" required>

                <input type="password" name="fpassword" class="input input-field" id="password" placeholder="Password" autocomplete="current-password" required>

                <input type="submit" class="input submit" value="Register">
            </div>
            <div class="bottom">
                <div class="bottom1">
                    <input type="checkbox" name="" class="link" id="Signin-check">
                    <label for="Signin-check">Remember Me</label>
                </div>
                <div class="bottom2">
                    <label><a href="#" class="link">Terms &amp; conditions</a></label>
                </div>
            </div>
        </form>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-input-container" id="loginForm">
            <div class="top">
                <p>Don't have an account? <a href="#" class="slink" onclick="hideLoginForm()" tabindex="-1">Register</a>
                </p>
                <header>Login</header>
                <span class="error"><?php echo $lerr; ?></span>
            </div>
            <div class="inputs_container">
                <input type="hidden" name="form_id" value="login">
                <input type="email" name="lemail" class="sinput input-field" id="lemail" placeholder="Email" tabindex="-1" required>

                <input type="password" name="lpassword" class="sinput input-field" id="lpassword" placeholder="Password" tabindex="-1" autocomplete="current-password" required>

                <input type="submit" class="sinput submit" value="Sign In" tabindex="-1">
            </div>
            <div class="bottom">
                <div class="bottom1">
                    <input type="checkbox" class="slink" name="" id="lSignin-check" tabindex="-1">
                    <label for="lSignin-check">Remember Me</label>
                </div>
                <div class="bottom2">
                    <label><a href="#" class="slink" tabindex="-1">Forgot password</a></label>
                </div>
            </div>
        </form>

    </div>
</body>

</html>