<nav id="header_nav">
    <div class="logo">
        <a href="index.php"><img src="images/icons/logo1.svg" alt="Logo"></a>
    </div>
    <div class="hamburger" id="hamburger">
        &#9776;
    </div>
    <ul id="nav-links">
        <li><button onclick="changeColors('dark')">dark</button></li>
        <li><button onclick="changeColors('light')">light</button></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="pricing.php">Pricing</a></li>
        <li><a href="booking.php">Booking</a></li>
        <li><a href="about.php">About us</a></li>
        <?php
        if (isset($_SESSION['email'])) {
            if (is_admin()) {
                echo '<li><a href="admin.php">Admin</a></li>';
            } else if(is_user()){
                echo '<li><a href="user.php">User</a></li>';
            }else{
                echo '<li><a href="profile.php">Profile</a></li>';
            }
        } else {
            echo '<li><a href="sign-up.php">Register</a></li>';
        }
        ?>
    </ul>
</nav>
