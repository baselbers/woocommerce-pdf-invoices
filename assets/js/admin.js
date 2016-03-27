var Settings = {};

Settings.removeCompanyLogo = function () {
    var elem = document.getElementById('bewpi-company-logo-wrapper');
    elem.parentNode.removeChild(elem);
    document.getElementById('bewpi-company-logo-value').value = '';
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

Settings.enableDisableNextInvoiceNumbering = function (elem) {
    var nextInvoiceNumberInput = document.getElementById('bewpi-next-invoice-number');
    ( elem.checked ) ? nextInvoiceNumberInput.disabled = false : nextInvoiceNumberInput.disabled = true;
};

Settings.showHideInvoiceNumberOptions = function (elem) {
    // The Element.closest function is new in DOM5, so to be sure, we don't use it yet
    var closest = function(el, tag) {
        for ( ; el && el !== document; el = el.parentNode ) {
        if ( el.tagName.toLowerCase() === tag ) {
                return el;
            }
        }
        return false;
    };
    var showHideControl = function(id, show) {
        var e = document.getElementById(id);
        // Also hide the whole table row
        closest(e, 'tr').style.display = (show) ? '' : 'none';
    };
    if (!elem) return;
    var val = (elem.options[elem.selectedIndex].value);
    // Show / hide the table rows with controls that do not apply to the current invoice numbering type
    showHideControl('bewpi-reset-counter', val == "sequential_number");
    showHideControl('bewpi-next-invoice-number', val == "sequential_number");
    showHideControl('bewpi-invoice-number-digits', val != "third_party");
    showHideControl('bewpi-invoice-number-prefix', val != "third_party");
    showHideControl('bewpi-invoice-number-suffix', val != "third_party");
    showHideControl('bewpi-invoice-number-format', val != "third_party");
    showHideControl('bewpi-reset-counter-yearly', val == "sequential_number");
};


jQuery( function ( $ ) {
// Tooltips
    var tiptip_args = {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    };
    $('.tips, .help_tip, .woocommerce-help-tip').tipTip(tiptip_args);

// Add tiptip to parent element for widefat tables
    $('.parent-tips').each(function () {
        $(this).closest('a, th').attr('data-tip', $(this).data('tip')).tipTip(tiptip_args).css('cursor', 'help');
    });
});