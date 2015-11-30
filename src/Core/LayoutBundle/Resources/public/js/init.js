
function needreload(event) {
    if (event.target.contentDocument == null) {
        event.target.src = event.target.dataset.srcerror;
    }
}