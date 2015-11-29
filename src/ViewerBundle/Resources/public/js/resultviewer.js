// Select default viewer mode when resize
function setviewermode(mode) {
    var listViewerMode = document.getElementsByName('changeviewer[mode]');
    for (var i = 0; i < listViewerMode.length; i++) {
        if (listViewerMode[i].value == mode && !listViewerMode[i].checked) {
            listViewerMode[i].checked = true;
            toggleviewer({ target: listViewerMode[i]});
        }
    }
}
function autoviewer() {
    var listViewerMode = document.getElementsByName('changeviewer[mode]');
    if ("matchMedia" in window) {
        if (window.matchMedia("(max-width: 619px)").matches) {
            setviewermode('mobile');
        }
        else if (window.matchMedia("(max-width: 767px)").matches) {
            setviewermode('tablet');
        }
        else if (window.matchMedia("(max-width: 999px)").matches) {
            setviewermode('tabletlandscape');
        }
        else {
            setviewermode('desktop');
        }
    }
}
window.addEventListener('resize', autoviewer, false);
autoviewer();

function toggleviewer (event) {
    if (event.target.checked) {
        document.getElementById('viewerurl-result').className = event.target.value;
    }
}

var listViewerMode = document.getElementsByName('changeviewer[mode]');
for (var i = 0; i < listViewerMode.length; i++) {
    listViewerMode[i].addEventListener ("RadioStateChange", toggleviewer, false);
}


function needreloadiframe(event) {
    if (event.target.contentDocument == null) {
        event.target.src = event.target.dataset.srcerror;
        console.log('needreloadiframe');
    }
}

var listIframe = document.getElementsByTagName('iframe');
for (var i = 0; i < listIframe.length; i++) {
    listIframe[i].addEventListener ("load", needreloadiframe, false);
}
