<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Invoice extends BEWPI_Abstract_Invoice {

	    /**
	     * @var WC_Order
	     */
	    public $order;

	    /**
	     * @var string
	     */
	    protected $type = "simple";

	    /**
	     * BEWPI_Invoice constructor.
	     *
	     * @param int $order_id
	     */
	    public function __construct( $order_id ) {
		    $this->order = wc_get_order( $order_id );
		    $taxes_count = count( $this->order->get_taxes() );
		    parent::__construct( $order_id, $this->type, $taxes_count );
	    }

	    /**
	     * Get the total amount with or without refunds
	     * @return string
	     */
	    public function get_total() {
		    if ( $this->order->get_total_refunded() > 0 ) {
			    $total_after_refund = $this->order->get_total() - $this->order->get_total_refunded();
			     return $total              = '<del class="total-without-refund">' . wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
		    } else {
			    return $total               = $this->order->get_formatted_order_total();
		    }
	    }

	    public function get_subtotal() {
		    $subtotal = $this->order->get_subtotal();

			if ( (bool)$this->template_options["bewpi_shipping_taxable"] )
				$subtotal += $this->order->get_total_shipping();

		    $subtotal -= $this->order->get_total_discount( true );

		    return $subtotal;
	    }

	    public function save( $dest, $html_templates = array() ) {
		    if ( $this->template_name == "" )
			    wp_die( __( 'Whoops, no template found. Please select a template on the Template settings page first.', 'woocommerce-pdf-invoices' ) );

		    $template_dir_name = $this->get_template_dir( $this->template_name );

		    $html_templates = array(
			    "header"    => $template_dir_name . 'header.php',
			    "footer"    => $template_dir_name . 'footer.php',
			    "body"      => $template_dir_name . 'body.php',
			    "style"     => $template_dir_name . 'style.css'
		    );

		    parent::save( $dest, $html_templates );

		    return $this->full_path;
	    }
    }
}