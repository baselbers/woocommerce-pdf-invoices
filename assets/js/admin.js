(function() {
    'use strict';

    var setting = {};

    setting.settings = ['bewpi-theme-text-black', 'bewpi-display-prices-incl-tax', 'bewpi-shipping-taxable', 'bewpi-company-details', 'bewpi-intro-text', 'bewpi-right-footer-column', 'bewpi-show-sku', 'bewpi-show-tax', 'bewpi-show-tax-row', 'bewpi-show-discount', 'bewpi-show-shipping'];

    setting.enableDisableNextInvoiceNumbering = function (elem) {
        document.getElementById('bewpi-next-invoice-number').readOnly = ! elem.checked;
    };

    setting.switchSettings = function(event) {
        var display = (event.target.value.toLowerCase().indexOf( 'minimal' ) !== -1) ? 'none' : 'table-row';

        setting.settings.forEach(function (settingId){
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

    window.addEventListener('load', function () {
        // Add click listener to dismiss notice.
        var notice = document.querySelector('div[data-dismissible] button.notice-dismiss');
        if (notice !== null) {
            notice.onclick = bewpi.notice.dismiss;
        }

        if ( pagenow === 'woocommerce_page_woocommerce-pdf-invoices' ) {
            var template = document.querySelector('select#bewpi-template-name');
            if (template !== null) {

                template.addEventListener('change', bewpi.setting.switchSettings );

                var event = new Event('change');
                template.dispatchEvent(event);
            }
        }
    });

    window.bewpi = {};
    window.bewpi.notice = notice;
    window.bewpi.setting = setting;
})();
