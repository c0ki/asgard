// Select default display mode when resize
function setdisplaymode(mode) {
    var listDisplayMode = document.getElementsByName('changedisplay[mode]');
    for (var i = 0; i < listDisplayMode.length; i++) {
        if (listDisplayMode[i].value == mode && !listDisplayMode[i].checked) {
            listDisplayMode[i].checked = true;
            toggledisplay({ target: listDisplayMode[i]});
        }
    }
}
function autodisplay() {
    var listDisplayMode = document.getElementsByName('changedisplay[mode]');
    if ("matchMedia" in window) {
        if (window.matchMedia("(max-width: 619px)").matches) {
            setdisplaymode('mobile');
        }
        else if (window.matchMedia("(max-width: 767px)").matches) {
            setdisplaymode('tablet');
        }
        else if (window.matchMedia("(max-width: 999px)").matches) {
            setdisplaymode('tabletlandscape');
        }
        else {
            setdisplaymode('desktop');
        }
    }
}
window.addEventListener('resize', autodisplay, false);
autodisplay();

function toggledisplay (event) {
    if (event.target.checked) {
        document.getElementById('displayurl-result').className = event.target.value;
    }
}

var listDisplayMode = document.getElementsByName('changedisplay[mode]');
for (var i = 0; i < listDisplayMode.length; i++) {
    listDisplayMode[i].addEventListener ("RadioStateChange", toggledisplay, false);
}

