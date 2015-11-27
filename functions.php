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
	$data   = file_get_contents_curl( $image_url );
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode( $data );

	return $base64;
}

function file_get_contents_curl( $url ) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); //Set curl to return the data instead of printing it to the browser.
	curl_setopt( $ch, CURLOPT_URL, $url );
	$data = curl_exec( $ch );
	curl_close( $ch );
	return $data;
}
