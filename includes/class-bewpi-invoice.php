<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BEWPI_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Invoice extends BEWPI_Abstract_Invoice {

	    public $order;

	    protected $type = "simple";

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
			     return $total              = '<del class="total-without-refund">' . strip_tags( $this->order->get_formatted_order_total() ) . '</del> <ins>' . wc_price( $total_after_refund, array( 'currency' => $this->order->get_order_currency() ) ) . '</ins>';
		    } else {
			    return $total              = $this->order->get_formatted_order_total();
		    }
	    }

	    /**
	     * Get the subtotal without discount and shipping, but including tax.
	     * @return mixed|void
	     */
	    public function get_subtotal_incl_tax() {
		    return $this->order->get_subtotal() + $this->order->get_total_tax();
	    }

	    public function save( $dest, $html_templates = array() ) {
		    //$template_name = apply_filters( 'bewpi_invoice_template_name', $this->template_name );
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

	    public function get_payment_status() {
		    if ( $this->order->get_status() === "completed" || $this->order->get_status() === "refunded"  ) {
			    return __( 'PAID', $this->textdomain );
		    } else {
			    return __( 'UNPAID', $this->textdomain );
		    }
	    }
    }
}