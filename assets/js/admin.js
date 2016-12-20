(function() {
    'use strict';

    var Settings = {};

    Settings.removeCompanyLogo = function () {
        var elem = document.getElementById('bewpi-company-logo-wrapper');
        elem.parentNode.removeChild(elem);
        document.getElementById('bewpi-company-logo-value').value = '';
    };

    Settings.enableDisableNextInvoiceNumbering = function (elem) {
        document.getElementById('bewpi-next-invoice-number').disabled = ! elem.checked;
    };

    Settings.deactivatePlugin = function() {
        var deactivate = document.getElementById('bewpi-deactivate');
        deactivate.removeAttribute('onclick');
        deactivate.click();
    };

    Settings.displayDeactivationNotice = function () {
        event.preventDefault();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', BEWPI_AJAX.ajaxurl + '?action=bewpi_deactivation_notice&_wpnonce=' + BEWPI_AJAX.deactivation_nonce, true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {

                    var adminNotice = xhr.responseText;
                    if ( adminNotice === 0 ) {
                        Settings.deactivatePlugin();
                        return;
                    }

                    var isNoticeActive = document.getElementById('bewpi-deactivation-notice');
                    if (isNoticeActive !== null) {
                        window.scrollTo(0, 0);
                        return;
                    }

                    // create node from admin notice.
                    var div = document.createElement('div');
                    div.innerHTML = adminNotice;
                    var node = div.firstChild;

                    // first try to insert element before activation message
                    var message = document.getElementById('message');
                    if (message !== null) {
                        message.parentNode.insertBefore(node, message.nextSibling);
                        window.scrollTo(0, 0);
                        return;
                    }

                    // insert before screen-reader-text h2 element.
                    var beforeElem = document.getElementsByClassName('subsubsub');
                    for (var i = 0; i < beforeElem.length; i++) {
                        if (beforeElem[i].previousSibling.className === "screen-reader-text" ) {
                            beforeElem[i].parentNode.insertBefore(node, beforeElem[i].previousSibling);
                            window.scrollTo(0, 0);
                            return;
                        }
                        break;
                    }

                    // skip admin notice and just deactivate plugin.
                    Settings.deactivatePlugin();
                } else {
                    Settings.deactivatePlugin();
                }
            }
        };

        xhr.send();
    };

    // Expose variables
    window.BEWPI = {};
    window.BEWPI.Settings = Settings;
})();
