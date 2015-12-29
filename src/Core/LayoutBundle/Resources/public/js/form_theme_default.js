function addCollectionElementForm(e) {
    // Stop propagation
    e.preventDefault();

    // Get collectionNode from button
    var collectionNode = e.target;
    do {
        collectionNode = collectionNode.parentNode;
    } while (!collectionNode.dataset.prototype);

    // Get the data-prototype explained earlier
    var prototype = collectionNode.dataset.prototype;

    // get the new index
    var index = parseInt(collectionNode.dataset.index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    var range = document.createRange();
// make the parent of the first div in the document becomes the context node
//    range.selectNode(document.getElementsByTagName("div").item(0));
    var newFormNode = range.createContextualFragment(newForm);
    // Add click listener
    newFormNode.querySelector('a.icon-minus').addEventListener('click', removeCollectionElementForm);
    //document.body.appendChild(documentFragment);


    // Increase the index with one for the next item
    collectionNode.dataset.index = index + 1;

    // Display the form in the page in an li, before the "Add a tag" link li
    var insertedNode = collectionNode.insertBefore(newFormNode, collectionNode.lastElementChild);

}

var collectionElementsAdds = document.querySelectorAll('form.form_theme_default .collection a.icon-plus');
Array.prototype.filter.call(collectionElementsAdds, function (button) {
    button.addEventListener('click', addCollectionElementForm);
});

function removeCollectionElementForm(e) {
    // Stop propagation
    e.preventDefault();

    // Get lineNode from button
    var lineNode = e.target;
    while (!lineNode.parentNode.dataset.prototype) {
        lineNode = lineNode.parentNode;
    }

    // Remove the line
    lineNode.parentNode.removeChild(lineNode);
}

var collectionElementsRemoves = document.querySelectorAll('form.form_theme_default .collection a.icon-minus');
Array.prototype.filter.call(collectionElementsRemoves, function (button) {
    button.addEventListener('click', removeCollectionElementForm);
});

function formReset(e) {
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

var resetButtons = document.querySelectorAll('button[type=reset]');
Array.prototype.filter.call(resetButtons, function (button) {
    button.addEventListener('click', formReset);
});