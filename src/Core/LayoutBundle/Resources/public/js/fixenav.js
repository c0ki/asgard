// global fixe node function
function fixedNode() {
    var node = document.querySelector('body > aside');
    node.classList.remove('fixed');
    if (node.getBoundingClientRect().top < 0) {
        node.classList.add('fixed');
    }
}
fixedNode();
window.addEventListener('scroll', fixedNode);



