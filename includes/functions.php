<?php

/**
 * Backwards compatibility to get the WC_Order ID.
 *
 * @param WC_Order $order WooCommerce Order object.
 *
 * @return int/bool WC_Order ID or false on failure.
 */
function bewpi_get_id( $order ) {
	$reflection = new ReflectionObject( $order );

	if ( $reflection->hasMethod( 'get_id' ) ) {
		return $reflection->getMethod( 'get_id' )->invoke( $order );
	}

	if ( $reflection->hasProperty( 'id' ) ) {
		return $reflection->getProperty( 'id' )->getValue( $order );
	}

	return false;
}
