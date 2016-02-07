// config
var maxBreakpoint = 640; // maximum breakpoint
var navSelectors = 'body > header nav, body > aside';

// targeting navigation
var navNodes = document.querySelectorAll(navSelectors);
// targetID will be initially closed if JS enabled
Array.prototype.filter.call(navNodes, function (node) {
    node.classList.add('all-closed');
});

// global navigation function
function showNavs() {
    // when small screen, create a switch button, and toggle targetID class
    if (window.matchMedia("(max-width:" + maxBreakpoint + "px)").matches && document.getElementById("toggle-button") == undefined) {
        navNodes.item(0).insertAdjacentHTML('afterBegin', '<button id="toggle-button" aria-label="open/close navigation"></button>');
        t = document.getElementById("toggle-button");
        t.onclick = function () {
            if (navNodes.item(0).classList.contains('all-closed')) {
                Array.prototype.filter.call(navNodes, function (node) {
                    node.classList.toggle('all-closed');
                    node.classList.toggle('main-opened');
                });
            }
            else if (navNodes.item(0).classList.contains('main-opened')) {
                Array.prototype.filter.call(navNodes, function (node) {
                    node.classList.toggle('main-opened');
                    node.classList.toggle('sub-opened');
                });
            }
            else if (navNodes.item(0).classList.contains('sub-opened')) {
                Array.prototype.filter.call(navNodes, function (node) {
                    node.classList.toggle('sub-opened');
                    node.classList.toggle('all-closed');
                });
            }
        }
    }
    // when big screen, delete switch button, and toggle navigation class
    var minBreakpoint = maxBreakpoint + 1;
    if (window.matchMedia("(min-width: " + minBreakpoint + "px)").matches && document.getElementById("toggle-button")) {
        document.getElementById("toggle-button").outerHTML = "";
    }
}
showNavs();
// when resize or orientation change, reload function
window.addEventListener('resize', showNavs);