(function() {
    'use strict';

    window.bewpi.setting.deactivatePlugin = function(element) {
        element.onclick = null;
        element.click();
    };

    window.bewpi.notice.deactivate = function(event) {
        var isNoticeActive = document.querySelector('tr.plugin-update-tr[data-plugin="woocommerce-pdf-invoices/bootstrap.php"]');
        if (isNoticeActive) {
            return true;
        }

        // display notice.
        event.preventDefault();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', BEWPI_AJAX.ajaxurl + '?action=deactivation-notice&_wpnonce=' + WPI_DEACTIVATE.nonce, true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {

                    var adminNotice = xhr.responseText;
                    if ( adminNotice === 0 ) {
                        setting.deactivatePlugin(event.target);
                    }

                    // create node from admin notice.
                    var tr = document.createElement('tr');
                    tr.setAttribute('class', 'plugin-update-tr active updated');
                    tr.setAttribute('data-slug', 'woocommerce-pdf-invoices');
                    tr.setAttribute('data-plugin', 'woocommerce-pdf-invoices/bootstrap.php');

                    var td = document.createElement('td');
                    td.setAttribute('colspan', '3');
                    td.setAttribute('class', 'plugin-update colspanchange');

                    var div = document.createElement('div');
                    div.innerHTML = adminNotice;
                    var notice = div.firstChild;

                    td.appendChild(notice);
                    tr.appendChild(td);

                    var plugin = document.querySelector('tr[data-plugin="woocommerce-pdf-invoices/bootstrap.php"]');
                    if (plugin) {
                        plugin.parentNode.insertBefore(tr, plugin.nextSibling);
                        plugin.className += ' updated';
                        return;
                    }

                    // skip admin notice and just deactivate plugin.
                    setting.deactivatePlugin(event.target);
                } else {
                    setting.deactivatePlugin(event.target);
                }
            }
        };

        xhr.send();
    };

    window.addEventListener('load', function () {
        if ( pagenow === 'plugins' ) {
            // Add click listener to display notice on deactivation of plugin.
            var deactivate = document.querySelector('tr[data-plugin="woocommerce-pdf-invoices/bootstrap.php"] span.deactivate a');
            if (deactivate !== null) {
                deactivate.onclick = bewpi.notice.deactivate;
            }
        }
    });
})();
