<?php
include 'authentication_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Home</title>
    <link rel="icon" type="image/x-icon" href="images/icons/t4.png">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <div class="wrapper">
        <header>
            <?php include 'navbar.php'; ?>
            <div class="grid-header-section">
                <div class="headers_container grid-items">
                    <h1>The <br> <span>InkVibe</span> <br>Tattoo shop</h1>
                </div>
                <div class="artist_container grid-items">
                    <img src="images/man3.jpg" alt="">
                </div>
                <div class="tattoo_container grid-items">
                    <img src="images/tat7.jpg" alt="">
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                        Cupiditate, rem laudantium Facere, dicta.</p>
                </div>
                <div class="announcement_container grid-items">
                    <h2>OFFICALLY OPEN NOW!</h2>
                    <p><span>8 Rue Verdun, Beirut, Lebanon</span></p>
                    <a href="booking.php"><button type="button">Book Your Tattoo</button></a>
                </div>
            </div>
        </header>
        <div class="offering-section">
            <h1 id="offering_header">Check our gallery</h1>
            <div class="offering-grid">
                <div class="img1_container">
                    <img class="img1" src="images/offering-man3.jpg" alt="" loading="lazy">
                </div>
                <div class="img2_container">
                    <img class="img2" src="images/mountain.jpg" alt="" loading="lazy">
                </div>
                <div class="img3_container">
                    <img class="img3" src="images/offering-circle2.jpg" alt="" loading="lazy">
                </div>
                <div class="img4_container">
                    <img class="img4" src="images/offering-woman3.jpg" alt="" loading="lazy">
                </div>
                <div class="img5_container">
                    <img class="img5" src="images/tat10.jpg" alt="" loading="lazy">
                </div>
                <div class="img6_container">
                    <img class="img6" src="images/offering-man1.jpg" alt="" loading="lazy">
                </div>
                <div class="img7_container">
                    <img class="img7" src="images/Geometric/minimalist-geometric-arrow-tattoo.jpg" alt="" loading="lazy">
                </div>
                <div class="img8_container">
                    <div id="img8_content">
                        <h2 class="img8_header">Tattoo gallery</h2>
                        <a href="gallery.php"><button class="img8_button" tabindex="-1">See more</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>