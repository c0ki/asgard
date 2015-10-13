// config
var maxBreakpoint = 640; // maximum breakpoint
var targetID = 'navigation'; // target ID (must be present in DOM)

// targeting navigation
var n = document.getElementById(targetID);

// targetID will be initially closed if JS enabled
n.classList.add('is-closed');

// global navigation function
function navi() {
    // when small screen, create a switch button, and toggle targetID class
    if (window.matchMedia("(max-width:" + maxBreakpoint + "px)").matches && document.getElementById("toggle-button") == undefined) {
        n.insertAdjacentHTML('afterBegin', '<button id="toggle-button" aria-label="open/close navigation"></button>');
        t = document.getElementById("toggle-button");
        t.onclick = function() {
            n.classList.toggle('is-closed');
        }
    }
    // when big screen, delete switch button, and toggle navigation class
    var minBreakpoint = maxBreakpoint + 1;
    if (window.matchMedia("(min-width: " + minBreakpoint + "px)").matches && document.getElementById("toggle-button")) {
        document.getElementById("toggle-button").outerHTML = "";
    }
}
navi();
// when resize or orientation change, reload function
window.addEventListener('resize', navi);