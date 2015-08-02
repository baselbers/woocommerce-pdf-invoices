<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Invoice extends BEWPI_Abstract_Invoice {

	    protected $type = "simple";

	    private $template_dir_name;

	    public function __construct( $order_id ) {
		    parent::__construct( $order_id, $this->type );
	    }

	    /**
	     * Get the total amount with or without refunds
	     * @return string
	     */
	    public function get_total() {
		    $total = '';

		    if ( $this->order->get_total_refunded() > 0 ) {
			    $total_after_refund = $this->order->get_total() - $this->order->get_total_refunded();
			    $total              = '<del class="total-without-refund">' . strip_tags( $this->order->get_formatted_order_total() ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
		    } else {
			    $total              = $this->order->get_formatted_order_total();
		    }

		    return $total;
	    }

	    /**
	     * Get the subtotal without discount and shipping, but including tax.
	     * @return mixed|void
	     */
	    public function get_subtotal_incl_tax() {
		    return $this->order->get_subtotal() + $this->order->get_total_tax();
	    }

	    public function save( $dest, $html_templates = array() ) {
		    $template_name = apply_filters( 'bewpi_invoice_template_name', $this->template_name, $this->type );
		    $this->template_dir_name    = BEWPI_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';

		    $html_templates = array(
			    "header"    => $this->template_dir_name . 'header.php',
			    "footer"    => $this->template_dir_name . 'footer.php',
			    "body"      => $this->template_dir_name . 'body.php',
			    "style"     => $this->template_dir_name . 'style.css'
		    );

		    parent::save( $dest, $html_templates );
	    }
    }
}