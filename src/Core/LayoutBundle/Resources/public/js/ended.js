function toggle(node) {
    if (node.currentTarget) {
        node = node.currentTarget;
    }
    node.classList.toggle('hide');
}

function innertoggle(node) {
    if (node.currentTarget) {
        node = node.currentTarget;
    }
    node.classList.toggle('innershow');
    var nodes = node.querySelectorAll('.hide,.nohide');
    Array.prototype.filter.call(nodes, function (node) {
        node.classList.toggle('hide');
        node.classList.toggle('nohide');
    });
}
