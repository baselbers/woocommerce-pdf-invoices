<?php

/**
 * WooCommerce 2.2 and lower support.
 */
if ( ! function_exists( 'wc_tax_enabled' ) ) {
	function wc_tax_enabled() {
		return get_option( 'woocommerce_calc_taxes' ) === 'yes';
	}
}

function image_to_base64( $image_url ) {
	$type   = pathinfo( $image_url, PATHINFO_EXTENSION );
	$data   = file_get_contents( $image_url );
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode( $data );

	return $base64;
}