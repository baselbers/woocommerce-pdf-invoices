<?php
/*
 * WooCommerce 2.2 and lower support.
 */
if ( ! function_exists( 'wc_tax_enabled' ) ) {
    function wc_tax_enabled() {
        return get_option( 'woocommerce_calc_taxes' ) === 'yes';
    }
}