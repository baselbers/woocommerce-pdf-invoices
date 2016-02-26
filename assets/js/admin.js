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