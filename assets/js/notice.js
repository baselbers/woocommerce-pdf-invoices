(function() {
    'use strict';

    var notice = {};

    notice.dismiss = function(event) {
        event.preventDefault();
        var attrValue, optionName, dismissableLength, data;

        attrValue = event.target.parentElement.getAttribute('data-dismissible').split('-');

        // remove the dismissible length from the attribute value and rejoin the array.
        dismissableLength = attrValue.pop();
        optionName = attrValue.join('-');

        var params = 'action=bewpi_dismiss_admin_notice&option_name=' + optionName + 'dismissible_length' + dismissableLength + '&nonce=' + bewpiAdminNotice.nonce;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(params);
    };

    window.onload = function() {
        var notice = document.querySelector('div[data-dismissible] button.notice-dismiss');
        if (notice !== null) {
            notice.addEventListener('click', bewpi.notice.dismiss);
        }
    };

    window.bewpi = {};
    window.bewpi.notice = notice;
})();