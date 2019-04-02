function getParam(paramName) {
    var urlParts = document.location.href.split('?');
    if (urlParts.length === 2) {
        urlParts = urlParts[1].split('&');
    }
    var result = '';

    urlParts.forEach(function (part) {
        var parts = part.split('=');

        if (parts.length === 2 && parts[0] === paramName) {
            result = parts[1];
        }
    });

    return result;
}

function getLastKey(data) {
    var result = undefined;

    for (var key in data) {
        result = key;
    }

    return result;
}