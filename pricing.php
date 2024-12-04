<?php
include 'authentication_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Pricing</title>
    <link rel="icon" type="image/x-icon" href="images/icons/t4.png">
    <link rel="stylesheet" href="styles/style_pricing.css">
    <link rel="stylesheet" href="styles/included.css">
    <script src="scripts/pricing_Calculator.js"></script>
    <script defer src="scripts/included.js"></script>
</head>

<body>
    <header>
        <?php include 'navbar.php'; ?>
    </header>
    <div class="calculator">
        <div id="calculator__headers">
            <h2 class="calculator__head" id="card_header1">How much does a tattoo cost?</h2>
            <h5 class="calculator__head" id="card_header2" style="text-wrap: pretty;">
                Get an <span id="card_header2_text">estimation</span> based on the
                profile of your tattoo (color, size, detail).</h5>
        </div>
        <!-- color -->
        <div id="radio_container">
            <label class="radio_label">Color</label>
            <div class="item2"></div>
            <label class="radio_lbl" id="radio_lbl_noColors">
                <input type="radio" name="color" value="black" class="radioin" id="noColors" checked onclick="calculatePriceEstimate()" checked>
                Black &amp; Grey
            </label>
            <label class="radio_lbl" id="radio_lbl_color">
                <input type="radio" name="color" value="black" class="radioin" id="colors" onclick="calculatePriceEstimate()">
                Color
            </label>
        </div>

        <!-- sliders -->
        <!-- size -->
        <div class="slider_container">
            <label for="size" class="sliderlbl">Size</label>
            <p class="slider_result" id="size_text">Tiny / minimalist</p>
            <input type="range" min="1" max="7" value="1" class="slider" id="size" onchange="calculatePriceEstimate()">
        </div>
        <!-- detail -->
        <div class="slider_container">
            <label for="detail" class="sliderlbl">Amount of Detail</label><br>
            <p class="slider_result" id="detial_text">Basic</p>
            <input type="range" min="1" max="4" value="1" class="slider" id="detail" onchange="calculatePriceEstimate()">
        </div>
        <!-- final price -->
        <div id="price_container">
            <label for="price" id="p2">Price</label><br>
            <input type="text" id="priceBox" name="price" value="$27 - $45" disabled>
        </div>
    </div>

    <main>
        <h1>How detailed is the piece?</h1>
        <div class="detail_section_container">
            <h2>Basic</h2>
            <p class="detail_section_categories">Minimalist Tribal Lettering Illustrative Geometric</p>
            <div class="img_container">
                <img src="images/New folder/basic prince range for how much a a tattoo will cost.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/basic simple tattoo with color price quote.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/basic tattoo price.jpeg" alt="" class="detail_section_img">
            </div>
        </div>
        <div class="detail_section_container">
            <h2>Some details</h2>
            <p class="detail_section_categories">Blackwork Japanese Trash-Polka Floral New-School Traditional Sketch
                Illustrative</p>
            <div class="img_container">
                <img src="images/New folder/some detail BzrnWFVl4pZ.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/some detailed tattoo price.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/some details and color tattoo cost.jpeg" alt="" class="detail_section_img">
            </div>
        </div>
        <div class="detail_section_container">
            <h2>Detailed</h2>
            <p class="detail_section_categories">Dotwork Watercolour Neotraditional Black & Grey</p>
            <div class="img_container">
                <img src="images/New folder/detailed tattoo of nightmare price.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/detailed tattoo of wolf cost.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/detailed tattoo with color .jpeg" alt="" class="detail_section_img">
            </div>
        </div>
        <div class="detail_section_container">
            <h2>Very complex</h2>
            <p class="detail_section_categories">Realism Micro-realism Dotwork Black & Gray</p>
            <div class="img_container">
                <img src="images/New folder/very complex bxa.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/very complex highly detailed tattoo.jpeg" alt="" class="detail_section_img">
                <img src="images/New folder/very complex xU.jpeg" alt="" class="detail_section_img">
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>