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

function clickAfter(event) {
    var node = event.target;
    if (event.currentTarget) {
        node = event.currentTarget;
    }
    var span = node;
    var e = event;
    console.log(e);
    console.log(span);
    if (e.offsetX ? e.offsetX > span.offsetWidth :
        e.pageX - span.offsetLeft > span.offsetWidth) {
        console.log("Click 2");
        span.classList.add('c2');
    } else {
        span.classList.add('c1');
        console.log("Click 1");
    }
    console.log(e.offsetX, span.offsetWidth, e.pageX, e.pageX - span.offsetLeft);
}