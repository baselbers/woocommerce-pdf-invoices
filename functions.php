<?php
/**
 * WooCommerce 2.2 and lower support.
 */
if ( ! function_exists( 'wc_tax_enabled' ) ) {
	function wc_tax_enabled() {
		return get_option( 'woocommerce_calc_taxes' ) === 'yes';
	}
}

function base64_encode_image( $image_url ) {
	$type   = pathinfo( $image_url, PATHINFO_EXTENSION );
	$data   = wp_remote_fopen( $image_url );

	if ( ! $data ) {
		$data = get_file_content_fopen( $image_url );
	}

	$src = sprintf( 'data:image/%s;base64,%s', $type, base64_encode( $data ) );

	return $src;
}

/**
 * 
 * @param $uri
 *
 * @return mixed|string
 */
function get_file_content_fopen( $uri ) {
	$data = '';

	if ( function_exists( 'curl_version' ) ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $uri );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
		$data = curl_exec( $curl );
		curl_close( $curl );
		return $data;
	}

	if ( ini_get( 'allow_url_fopen' ) ) {
		$data = file_get_contents( $uri );
	}

	return $data;
}
