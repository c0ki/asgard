function popinOpen(e) {
    // Stop propagation
    e.preventDefault();

    document.getElementById('background').style.display = 'block';
    document.getElementById('popin').style.display = 'flex';
    document.querySelector('#popin [data-type=progress]').style.display = 'flex';
    document.getElementById('popin').addEventListener('click', popinClose);
    document.querySelector('#popin [data-type=close]').addEventListener('click', popinClose);
    document.addEventListener('keydown', popinClose);

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
    if (event.target.id != 'popin' && event.target.dataset.type != 'close'
        && event.target.type != 'reset' && event.keyCode != 27) {
        return;
    }
    popinReset();
    document.getElementById('background').style.display = 'none';
    document.getElementById('popin').style.display = 'none';
    document.getElementById('popin').removeEventListener('click', popinClose);
    document.querySelector('#popin [data-type=close]').removeEventListener('click', popinClose);
    document.removeEventListener('keydown', popinClose);
}

function popinContentLoaded(event) {
    document.querySelector('#popin [data-type=progress]').style.display = 'none';
    document.querySelector('#popin [data-type=content]').insertAdjacentHTML('afterbegin', this.responseText);
    var scriptNodes = document.querySelectorAll('#popin [data-type=content] script');
    Array.prototype.filter.call(scriptNodes, function (node) {
        eval(node.innerHTML);
    });

    //console.log(document.querySelector('#popin [data-type=content]'));
    //eval(document.querySelector('#popin [data-type=content]'));
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