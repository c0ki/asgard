function popinOpen(e) {
    // Stop propagation
    e.preventDefault();

    var nodeBackground = document.getElementById('background');
    nodeBackground.style.display = 'block';
    nodeBackground.addEventListener('click', popinClose);
    document.querySelector('#popin [data-type=close]').addEventListener('click', popinClose);
    document.getElementById('popin').style.display = 'flex';
    document.querySelector('#popin [data-type=progress]').style.display = 'flex';

    var node = e.target;
    var url = node.getAttribute('href');
    if (url.indexOf('?') !== false) {
        url += '?popin';
    }
    else {
        url += '&popin';
    }

    // Call ajax
    var oReq = new XMLHttpRequest();
    oReq.addEventListener("progress", popinContentProgress);
    oReq.addEventListener("load", popinContentLoaded);
    oReq.open('GET', url);
    oReq.send();
}

function popinReset() {
    var nodeProgress = document.querySelector('#popin [data-type=progress]');
    if (nodeProgress.childNodes.length > 1) {
        nodeProgress.removeChild(nodeProgress.lastChild);
    }
    document.querySelector('#popin [data-type=content]').innerHTML = '';
}

function popinClose(event) {
    var nodeBackground = document.getElementById('background');
    nodeBackground.style.display = 'none';
    nodeBackground.removeEventListener('click', popinClose);
    popinReset();
    document.querySelector('#popin [data-type=close]').removeEventListener('click', popinClose);
    document.getElementById('popin').style.display = 'none';
}

function popinContentLoaded(event) {
    document.querySelector('#popin [data-type=progress]').style.display = 'none';
    document.querySelector('#popin [data-type=content]').insertAdjacentHTML('afterbegin', this.responseText);
}

function popinContentProgress(event) {
    if (event.lengthComputable) {
        var percentComplete = event.loaded / event.total;
        var percentText = (percentComplete * 100) + '%';
        var nodeProgress = document.querySelector('#popin [data-type=progress]');
        if (nodeProgress.childNodes.length > 1) {
            nodeProgress.lastChild.innerHTML = percentText;
        }
        else {
            nodeProgress.insertAdjacentHTML('beforeend', '<span>' + percentText + '</span>');
        }
    }
}

var popinElements = document.querySelectorAll('a[data-target=popin]');
Array.prototype.filter.call(popinElements, function (link) {
    link.addEventListener('click', popinOpen);
});