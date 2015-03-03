<?php
class WPI_Order extends WC_Order {
    /*private $number;
    private $formatted_billing_address;
    private $formatted_shipping_address;
    private $items = array();
    private $subtotal;
    private $total_tax;
    private $total;
    private $date;*/

    public function __construct($order = 0) {
        parent::__construct($order);
        /*$this->number = parent::get_order_number();
        $this->formatted_billing_address = parent::get_formatted_billing_address();
        $this->formatted_shipping_address = parent::get_formatted_shipping_address();
        $this->items = parent::get_items();
        $this->subtotal = parent::get_subtotal();
        $this->total_tax = parent::get_total_tax();
        $this->total = parent::get_total();
        $this->date = parent::order_date;*/
    }

    public function get_number() {
        return $this->number;
    }

    public function get_formatted_billing_address() {
        return $this->formatted_billing_address;
    }

    public function get_formatted_shipping_address() {
        return $this->formatted_shipping_address;
    }

    public function get_subtotal() {
        return $this->get_subtotal();
    }

    public function get_total_tax() {
        return $this->total_tax;
    }

    public function get_total() {
        return $this->total;
    }


}