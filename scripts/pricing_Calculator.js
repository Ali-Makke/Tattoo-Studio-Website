// calculator functions
function calculatePriceEstimate() {
    // size slider
    let sizes = document.getElementById("size");
    let sizeText = document.getElementById("size_text");
    let size = 30;
    let detailRate = 0;

    switch (sizes.value) {
        case "1": sizeText.innerText = "Tiny / minimalist"; size = 30; break;
        case "2": sizeText.innerText = "Small (<5 cm / 2'')"; size = 50; break;
        case "3": sizeText.innerText = "Medium (up to 10 cm / 4'')"; size = 150; break;
        case "4": sizeText.innerText = "Large (up to 20 cm / 8'')"; size = 250; break;
        case "5": sizeText.innerText = "Half sleeve"; size = 300; break;
        case "6": sizeText.innerText = "Full limb"; size = 400; break;
        case "7": sizeText.innerText = "Full back"; size = 500; break;
        default: break;
    }

    //Amount of detail slider
    let details = document.getElementById("detail");
    let detailText = document.getElementById("detial_text");

    switch (details.value) {
        case "1": detailText.innerText = "Basic"; detailRate = 0; break;
        case "2": detailText.innerText = "Some details"; detailRate = 2; break;
        case "3": detailText.innerText = "Detailed"; detailRate = 3; break;
        case "4": detailText.innerText = "Very complex"; detailRate = 4; break;
        default: break;
    }
    
    //radio buttons
    let noColor = document.getElementById("noColors");
    let noColorLbl = document.getElementById("radio_lbl_noColors");
    let colorLbl = document.getElementById("radio_lbl_color");
    // Change color of radio lbls
    if (noColor.checked) {
        colorLbl.style.backgroundColor = "rgb(245, 167, 173)";
        noColorLbl.style.backgroundColor = "#EA4E5B";
    } else {
        noColorLbl.style.backgroundColor = "rgb(245, 167, 173)";
        colorLbl.style.backgroundColor = "#EA4E5B";
    }

    // Calculate tattoo finalPrice estimation
    let colorRate = noColor.checked ? 0 : 0.25;
    let totalPrice = size * (1 + detailRate) * (1 + colorRate);
    console.log(totalPrice);
    let lowerPrice = Math.trunc(totalPrice * (1 - 0.1));
    let upperPrice = Math.trunc(totalPrice * (1 + 0.5));
    // Display estimates
    let totalPriceBox = document.getElementById("priceBox");
    totalPriceBox.value = "$" + lowerPrice + " - $" + upperPrice;
}