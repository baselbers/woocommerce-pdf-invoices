<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BEWPI_Template.
 */
class BEWPI_Template {

	/**
	 * @var BEWPI_Template The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main BEWPI_Template Instance.
	 *
	 * Ensures only one instance of BEWPI_Template is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return BEWPI_Template Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public static function get_option( $name, $order_id = null ) {
		$template_options = get_option( 'bewpi_template_settings' );
		if ( ! $template_options ) {
			return '';
		}

		$value = apply_filters( 'bewpi_template_option-' . $name, $template_options[ $name ], $name, $order_id );

		if ( ! is_null( $order_id ) ) {
			$value = self::replace_placeholders( $value, $order_id );
		}

		return $value;
	}

	/**
	 * Display company logo or name
	 */
	public static function print_logo() {
		$logo_url = self::get_option( 'bewpi_company_logo' );
		if ( ! empty( $logo_url ) ) {
			// mPDF' stablest method to display an image is to use their
			// 'Image data as a Variable' (https://mpdf.github.io/what-else-can-i-do/images.html) option.
			$src = apply_filters( 'bewpi_company_logo_url', 'var:company_logo' );
			printf( '<img class="company-logo" src="%s"/>', esc_attr( $src ) );
		} else {
			// show company name if company logo does not exist.
			$company_name = self::get_option( 'bewpi_company_name' );
			printf( '<h1 class="company-logo">%s</h1>', esc_html( $company_name ) );
		}
	}

	private static function replace_placeholders( $value, $order_id ) {
		$order = wc_get_order( $order_id );

		$value = str_replace(
			array(
				'[payment_method]',
				'[shipping_method]'
			),
			array(
				apply_filters( 'bewpi_template_option-payment_method', $order->payment_method_title ),
				apply_filters( 'bewpi_template_option-shipping_method', $order->get_shipping_method() )
			),
			$value
		);

		return $value;
	}
}
