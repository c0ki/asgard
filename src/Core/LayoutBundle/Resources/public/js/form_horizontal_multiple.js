function form_horizontal_multiple_init() {
    var fields = document.querySelectorAll('.form_horizontal_multiple select');
    Array.prototype.filter.call(fields, function(field){
        var name = field.getAttribute('name');
        name = name.replace(/\[/, '_1[');
        field.setAttribute('name', name);
    });
}
form_horizontal_multiple_init();

function form_horizontal_multiple_duplicate_line(e) {
    var line = e.target.parentNode.parentNode;
    var newLine = line.cloneNode(true);
    var indice = line.parentNode.lastElementChild.querySelector('[name]').getAttribute('name').match(/_(\d+)\[/);
    indice = indice ? parseInt(indice[1])+1 : 1;
    var fields = newLine.querySelectorAll('select');
    Array.prototype.filter.call(fields, function(field){
        var name = field.getAttribute('name');
        name = name.replace(/_\d+\[/, '_' + indice + '[');
        field.setAttribute('name', name);
    });

    line.parentNode.appendChild(newLine);
    newLine.querySelector('.icon-plus').addEventListener('click', form_horizontal_multiple_duplicate_line);
    newLine.querySelector('.icon-minus').addEventListener('click', form_horizontal_multiple_remove_line);
}

function form_horizontal_multiple_remove_line(e) {
    var line = e.target.parentNode.parentNode;
    line.parentNode.removeChild(line);
}

var buttonsMinus = document.querySelectorAll('.icon-minus');
Array.prototype.filter.call(buttonsMinus, function(button){
    button.addEventListener('click', form_horizontal_multiple_remove_line);
});
var buttonsPlus = document.querySelectorAll('.icon-plus');
Array.prototype.filter.call(buttonsPlus, function(button){
    button.addEventListener('click', form_horizontal_multiple_duplicate_line);
});
