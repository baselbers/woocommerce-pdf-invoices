(function() {
    'use strict';

    var setting = {};

    setting.removeCompanyLogo = function () {
        var elem = document.getElementById('bewpi-company-logo-wrapper');
        elem.parentNode.removeChild(elem);
        document.getElementById('bewpi-company-logo-value').value = '';
    };

    setting.enableDisableNextInvoiceNumbering = function (elem) {
        document.getElementById('bewpi-next-invoice-number').readOnly = ! elem.checked;
    };

    setting.deactivatePlugin = function(element) {
        element.onclick = null;
        element.click();
    };

    setting.switchSettings = function(event) {
        var display = (event.target.value === 'minimal') ? 'none' : 'table-row';
        var settings = ['bewpi-theme-text-black', 'bewpi-display-prices-incl-tax', 'bewpi-shipping-taxable', 'bewpi-company-details', 'bewpi-intro-text', 'bewpi-right-footer-column', 'bewpi-show-sku', 'bewpi-show-tax', 'bewpi-show-tax-row', 'bewpi-show-discount', 'bewpi-show-shipping'];

        settings.forEach(function (settingId){
            var settingElem = document.getElementById(settingId);
            if (settingElem) {
                settingElem.parentElement.parentElement.style.display = display;
            }
        })
    };

    var notice = {};

    notice.dismiss = function(event) {
        event.preventDefault();
        var attrValue, optionName, dismissableLength, data;

        attrValue = event.target.parentElement.getAttribute('data-dismissible').split('-');

        // remove the dismissible length from the attribute value and rejoin the array.
        dismissableLength = attrValue.pop();
        optionName = attrValue.join('-');

        var params = 'action=dismiss-notice&option_name=' + optionName + '&dismissible_length=' + dismissableLength + '&nonce=' + BEWPI_AJAX.dismiss_nonce;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', BEWPI_AJAX.ajaxurl, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(params);
    };

    notice.deactivate = function(event) {
        var isNoticeActive = document.querySelector('tr.plugin-update-tr[data-plugin="woocommerce-pdf-invoices/bootstrap.php"]');
        if (isNoticeActive) {
            return true;
        }

        // display notice.
        event.preventDefault();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', BEWPI_AJAX.ajaxurl + '?action=deactivation-notice&_wpnonce=' + BEWPI_AJAX.deactivation_nonce, true);

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

    window.onload = function() {
        // add click listener to dismiss notice.
        var notice = document.querySelector('div[data-dismissible] button.notice-dismiss');
        if (notice !== null) {
            notice.onclick = bewpi.notice.dismiss;
        }

        // add click listener to display notice on deactivation of plugin.
        var deactivate = document.querySelector('tr[data-plugin="woocommerce-pdf-invoices/bootstrap.php"] span.deactivate a');
        if (deactivate !== null) {
            deactivate.onclick = bewpi.notice.deactivate;
        }

        var template = document.querySelector('select#bewpi-template-name');
        if (template !== null) {
            template.onchange = bewpi.setting.switchSettings;
            var event = new Event('change');
            template.dispatchEvent(event);
        }
    };

    window.bewpi = {};
    window.bewpi.notice = notice;
    window.bewpi.setting = setting;
})();