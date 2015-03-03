<?php

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

	class BE_WooCommerce_PDF_Invoices {
		public $settings;

		public function __construct() {
			$this->settings = new WPI_Settings();
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
			add_filter( 'woocommerce_email_attachments', array($this, 'woocommerce_email_attachements',10,3 ));
		}

		public function plugins_loaded() {
			if ( class_exists('WC_Order') ) {
				$invoice = new WPI_Invoice(new WC_Order(12));
				//$invoice->generate();
			}
		}

		public function woocommerce_email_attachements( $attachments, $status , $order ) {}
	}
}