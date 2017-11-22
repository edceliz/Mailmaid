// Creates a link upon submission
document.getElementById('createLink').addEventListener('click', function() {
    var link = document.getElementById('link').value;
    var alert = document.getElementById('alert');
    var id = document.getElementById('campaignId').value;
    if (!validateURL(link)) {
        alert.className = 'alert';
        return;
    }
    var container = document.getElementById('links');
    if (alert.className == 'alert') {
        alert.className += ' disabled'
    }
    // Creates a shortened version of link and add it to the view
    var ajax = ajaxObj('POST', '/mailmaid/campaign/trace');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var json = JSON.parse(ajax.responseText);
            var card = document.createElement('div');
            card.className = 'link';
            card.setAttribute('id', 'link-' + json.id);
            var cardLink = document.createElement('span');
            cardLink.innerHTML = json.link;
            var cardDirection = document.createElement('span');
            cardDirection.innerHTML = link;
            var cardOperation = document.createElement('span');
            var operationButton = document.createElement('button');
            operationButton.innerHTML = 'Delete';
            operationButton.setAttribute('type', 'button');
            operationButton.setAttribute('id', json.id);
            card.appendChild(cardLink);
            card.appendChild(cardDirection);
            cardOperation.appendChild(operationButton);
            card.appendChild(cardOperation);
            container.appendChild(card);
        }
    }
    ajax.send('link=' + link + '&id=' + id);
    document.getElementById('link').value = '';
});

// Deletes a link upon confirmation
document.getElementById('links').addEventListener('click', function(event) {
    event.preventDefault();
    if(!confirm('Are you sure to delete this link?')) {
        return false;
    }
    var e = event.target;
    if (e.tagName == 'BUTTON') {
        var ajax = ajaxObj('POST', '/mailmaid/campaign/untrace');
        ajax.onreadystatechange = function() {
            if (ajaxReturn(ajax) == true && JSON.parse(ajax.responseText).status) {
                var card = document.getElementById('link-' + e.id);
                card.parentNode.removeChild(card);
            }
        }
        ajax.send('id=' + e.id);
    }
});

/**
 * Checks if the submitted link is valid
 * @param   {string} value - A link
 * @returns {boolean}
 */
function validateURL(value) {
  return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
}
