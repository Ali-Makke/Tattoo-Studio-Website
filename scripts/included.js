document.getElementById('hamburger').addEventListener('click', function() {
    var navLinks = document.getElementById('nav-links');
    var hamburger = document.getElementById('hamburger');
    
    if (navLinks.classList.contains('show')) {
        navLinks.classList.remove('show');
        navLinks.style.zIndex = "-1";
        hamburger.style.color = "#363636";
        hamburger.innerHTML = '&#9776;'; 
    } else {
        navLinks.classList.add('show');
        navLinks.style.zIndex = "50";
        hamburger.style.zIndex = "51";
        hamburger.style.color = "white";
        hamburger.innerHTML = '&#10006;';
    }
});

function changeColors(theme) {
    const root = document.documentElement;
    if (theme === 'dark') {
        root.classList.add('dark-theme');
    } else {
        root.classList.remove('dark-theme');
    }
}
