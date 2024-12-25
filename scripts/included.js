// for scroll to top button
window.onscroll = function() {
    const scrollToTopBtn = document.getElementById('return-to-top');
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
      scrollToTopBtn.style.opacity = '1';
      scrollToTopBtn.style.transform = 'translateY(0)';
    } else {
      scrollToTopBtn.style.opacity = '0';
      scrollToTopBtn.style.transform = 'translateY(70px)';
    }
  };

  document.getElementById('return-to-top').addEventListener('click', function(event) {
    event.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
});

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

// here i get the last saved theme in the localstorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.classList.add(savedTheme);
    }
});

//here i change the color to the next theme using a button click
function changeColors() {
    const themes = ['light', 'mid-theme', 'dark-theme'];
    let currentTheme = document.documentElement.classList[0];

    let nextThemeIndex = (themes.indexOf(currentTheme) + 1) % themes.length;
    let nextTheme = themes[nextThemeIndex];

    document.documentElement.classList.remove(currentTheme);
    document.documentElement.classList.add(nextTheme);

    localStorage.setItem('theme', nextTheme);
}