<nav id="header_nav">
    <div class="logo">
        <a href="index.php"><img src="images/icons/t4.png" alt="Logo"></a>
    </div>
    <div class="hamburger" id="hamburger">
        &#9776;
    </div>
    <ul id="nav-links">
        <li><button onclick="changeColors()" id="theme-toggle">Theme</button></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="pricing.php">Pricing</a></li>
        <li><a href="booking.php">Booking</a></li>
        <li><a href="about.php">About us</a></li>
        <?php
        if (isset($_SESSION['email'])) {
            if (is_admin()) {
                echo '<li><a href="dashboard_admin.php">Admin</a></li>';
            } else if(is_artist()){
                echo '<li><a href="dashboard_artist.php">Profile</a></li>';
            }else{
                echo '<li><a href="dashboard_customer.php">Profile</a></li>';
            }
        } else {
            echo '<li><a href="sign-up.php">Register</a></li>';
        }
        ?>
    </ul>
</nav>
