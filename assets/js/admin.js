var Settings = {};

Settings.removeCompanyLogo = function () {
    var elem = document.getElementById('company-logo-wrapper');
    elem.parentNode.removeChild(elem);
    document.getElementById('company-logo-value').value = '';
};

Settings.previewInvoice = function (data) {
    // construct an HTTP request
    var xhr = new XMLHttpRequest();
    xhr.open("GET", ajax_url + "?action=wpi_preview_invoice&security=" + nonce, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');

    xhr.send();

    xhr.onloadend = function () {
        // done
    };
};
