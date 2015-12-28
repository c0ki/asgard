function needreload(event) {
    if (event.target.contentDocument == null) {
        event.target.src = event.target.dataset.srcerror;
    }
}

function cancel(e) {
    // Stop propagation
    e.preventDefault();

    var node = e.target;
    while (node.parentNode && node.parentNode.getAttribute) {
        console.log(node.parentNode);
        if (node.parentNode.getAttribute('id') == 'popin') {
            popinClose(e);
            return;
        }
        node = node.parentNode;
    }
    history.back();
}