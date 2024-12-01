let loginForm = document.getElementById("loginForm");
let signinForm = document.getElementById("signupForm");

let links = document.getElementsByClassName("link");
let inputs = document.getElementsByClassName("input");
let elements = Array.from(links).concat(Array.from(inputs));
let slinks = document.getElementsByClassName("slink")
let sinputs = document.getElementsByClassName("sinput");
let selements = Array.from(slinks).concat(Array.from(sinputs));

function showLoginForm() {
    signinForm.style.opacity = "0";
    signinForm.style.transform = "translate(150%, -50%)";
    elements.forEach(element => {
        element.tabIndex = -1;
    });


    loginForm.style.opacity = "1";
    loginForm.style.transform = "translate(-50%, -50%)";
    selements.forEach(selement => {
        selement.tabIndex = 0;
    });

}

function hideLoginForm() {
    signinForm.style.opacity = "1";
    signinForm.style.transform = "translate(-50%, -50%)";
    elements.forEach(element => {
        element.tabIndex = 0;
    });

    loginForm.style.opacity = "0";
    loginForm.style.transform = "translate(-200%, -50%)";
    selements.forEach(selement => {
        selement.tabIndex = -1;
    });
}