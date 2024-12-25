<footer>
    <div class="footer_wrapper">
        <div class="footer_grid">
            <div class="header1_container_1">
                <img src="images/icons/logo2.svg" alt="LOGO" class="header1_icon">
                <h1 class="header1">THE INKVIBE TATTOO SHOP</h1>
            </div>
            <div class="header2_container_2">
                <h2>CONTACT</h2>
            </div>
            <div class="header3_container_3">
                <h2>OUT LINKS</h2>
            </div>
            <div class="info1_container_4">
                <p>Founded in 1998, InkVIbe shop was made out of love for art of tattoing.
                    Our founder, Charles Chaikofsky, brought the team together and with hard work
                    and dedication we were able to keep a successful business.
                </p>
            </div>
            <div class="info2_container_5">
                <img src="images/icons/footer_location.svg" alt="LOCATION: " class="container_5_img">
                <p>8 Rue Verdun, Beirut, Lebanon</p>
            </div>
            <div class="info3_container_6">
                <img src="images/icons/footer_phone.svg" alt="NUMBER: " class="container_6_img">
                <p>(123) 555-5550 <br> (123) 555-5551</p>
            </div>
            <div class="footer_nav_container_7">
                <ul class="footer_nav">
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="pricing.php">Pricing</a></li>
                    <li><a href="booking.php">Booking</a></li>
                    <li><a href="about.php">About us</a></li>
                    <?php
                    if (isset($_SESSION['email'])) {
                        echo '<li><a href="logout.php">Logout</a></li>';
                    } else {
                        echo '<li><a href="sign-up.php">Register</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="icons_container_8">
                <a href="#"><img src="images/icons/footer_facebook.svg" alt="FACEBOOK"></a>
                <a href="#"><img src="images/icons/footer_instagram.svg" alt="INSTAGRAM"></a>
                <a href="#"><img src="images/icons/footer_twitter.svg" alt="TWITTER"></a>
                <a href="#"><img src="images/icons/footer_linkedin.svg" alt="LINKEDIN"></a>
            </div>
            <div class="info3_container_9">
                <img src="images/icons/footer_email.svg" alt="Email: " class="container_9_img">
                <p>charles.chaikofsky@example.com</p>
            </div>
        </div>
        <div id="copyright">
            <p>Copyright @ 2023 Tattooshop All Rights Reserved</p>
            <p>Artist Charles T. Chaikofsky</p>
        </div>
    </div>
    <a href="backtotop:" id="return-to-top"><i></i></a>
</footer>