function form_horizontal_multiple_init() {
    var fields = document.querySelectorAll('.form_horizontal_multiple select');
    Array.prototype.filter.call(fields, function(field){
        var name = field.getAttribute('name');
        name = name.replace(/\[/, '_1[');
        field.setAttribute('name', name);
    });
}
form_horizontal_multiple_init();

function form_horizontal_multiple_duplicate_line(line) {
    var newLine = line.cloneNode(true);
    var lineSelects = line.querySelectorAll('select');
    Array.prototype.filter.call(lineSelects, function(selectNode){
        var newSelectNode = newLine.querySelector('select[id="' + selectNode.getAttribute('id') +  '"]');
        var optionsCheck = selectNode.querySelectorAll('option:checked');
        console.log(optionsCheck);
        Array.prototype.filter.call(optionsCheck, function(optionNode){
            newSelectNode.querySelector('option[value="' + optionNode.value + '"]').setAttribute("selected", "selected");
        });
    });
    var indice = line.parentNode.lastElementChild.querySelector('[name]').getAttribute('name').match(/_(\d+)\[/);
    indice = indice ? parseInt(indice[1])+1 : 1;
    var fields = newLine.querySelectorAll('select');
    Array.prototype.filter.call(fields, function(field){
        var name = field.getAttribute('name');
        name = name.replace(/_\d+\[/, '_' + indice + '[');
        field.setAttribute('name', name);
    });
    line.parentNode.appendChild(newLine);
}

function form_horizontal_multiple_remove_line(line) {
    line.parentNode.removeChild(line);
}
//var buttonsMinus = document.querySelectorAll('.icon-minus');
//Array.prototype.filter.call(buttonsMinus, function(button){
//    button.addEventListener('click', form_horizontal_multiple_remove_line);
//});

function form_choice_multiple(choices) {
    var group_choices = choices;
    while (!group_choices.classList.contains('choices_group')) {
        group_choices = group_choices.parentNode;
    }

    var choices_selected = choices.querySelectorAll('option:checked');
    var recap = group_choices.querySelector('ul.choices_selected');
    while (recap.firstChild) {
        recap.removeChild(recap.firstChild);
    }
    Array.prototype.filter.call(choices_selected, function(choice_selected) {
        var new_element = document.createElement("li");
        new_element.textContent = choice_selected.textContent;
        recap.appendChild(new_element);
    });
}
