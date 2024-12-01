<?php
include 'authentication_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | About-Us</title>
    <link rel="icon" type="image/x-icon" href="images/icons/logo1.svg">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background-color: var(--secondary-color);
        }

        #ad {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: url("images/icons/star1.svg");
            background-position: center;
            background-repeat: repeat;
            border-top: 2px solid black;
        }

        #ad>h1 {
            font-size: calc(17px + (74 * (100vw - 16em)) / 500);
            background-color: rgb(243, 235, 213, 0.7);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div id="ad">
        <h1>Coming soon</h1>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>