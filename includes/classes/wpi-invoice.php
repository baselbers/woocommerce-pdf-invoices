<?php

class WPI_Invoice {
    private $order;
    private $general_settings;
    private $template_settings;
    private $number;
    private $invoice_number_meta_key = '_bewpi_invoice_number';

    public function __construct( $order = '' ) {
        $this->order = $order;
        $this->general_settings = (array) get_option( 'general_settings' );
        $this->template_settings = (array) get_option( 'template_settings' );

        $this->init();
    }

    private function init() {
        $this->invoice_number_meta_key  = '_bewpi_invoice_number';
        $this->number                   = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
        $this->prefix                   = get_post_meta( $this->order->id, '_bewpi_invoice_prefix', true );
        $this->suffix                   = get_post_meta( $this->order->id, '_bewpi_invoice_suffix', true );
        $this->date                     = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );

        if( $this->number == "" ) {
            $this->get_next_invoice_number($this->order->id);
        }
    }

    public function get_formatted_date() {
        $date_format = $this->template_settings['invoice_date_format'];
        if( $date_format != "" ) {
            return date($date_format, $this->order->order_date);
        }
    }

    public function get_formatted_order_year() {
        return date("Y", strtotime($this->order->order_date));
    }

    private function get_template() {
        return WPI_TEMPLATES_DIR . $this->template_settings['template_filename'];
    }

    function create_invoice_number( $order_id, $number ) {
        global $wpdb;

        // attempt the query up to 3 times for a much higher success rate if it fails (due to Deadlock)
        $success = false;
        for ($i = 0; $i < 3 && !$success; $i++) {
               // this seems to me like the safest way to avoid order number clashes
            $query = $wpdb->prepare(
                "
                INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
                    SELECT %d, %s, IF( MAX( CAST( meta_value as UNSIGNED ) ) IS NULL, %d, MAX( CAST( meta_value as UNSIGNED ) ) + 1 )
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                ",
                $order_id, $this->invoice_number_meta_key, $number, $this->invoice_number_meta_key
            );
            $success = $wpdb->query($query);
        }
    }

    function get_invoice_number( $order_id ) {
        global $wpdb;

        $results = $wpdb->get_results(
            "
            SELECT meta_value
            FROM $wpdb->postmeta
            WHERE post_id = $order_id
            AND meta_key = '_bewpi_invoice_number'
            "
        );

        if(count($results) == 1){
            return $results[0]->meta_value;
        }
    }

    function get_next_invoice_number( $order_id ) {

        if( $this->template_settings['next_invoice_number'] != ""
            && $this->template_settings['next_invoice_number'] > $this->template_settings['invoice_number'] ) {
            $this->number = $this->template_settings['next_invoice_number'];
        }

        $current_year = getdate()['year'];
        if ( $this->template_settings['reset_invoice_number'] ) {
            $last_year = $this->template_settings['last_invoice_year'];

            if ( $last_year != "" && is_numeric( $last_year ) ) {
                if ( $last_year < $current_year ) {
                    //  set new year as last invoiced year and reset invoice number
                    $this->number = 1;
                }
            }
        }

        // Create new invoice number and insert into database.
        $this->create_invoice_number($order_id, $this->number);

        // Set the current year as the last invoiced.
        $this->template_settings['last_invoice_year'] = $current_year;

        // Get the the current invoice number and return.
        $this->number = $this->get_invoice_number($order_id);
        $this->template_settings['invoice_number'] = $this->number;
        update_option( 'template_settings', $this->template_settings );

        return $this->number;
    }

    public function get_formatted_invoice_number() {
        $invoice_number_format = $this->template_settings['invoice_format'];
        //$digit_str = "%0" . $this->template_settings['invoice_number_digits'] . "s";
        //$this->number = sprintf($digit_str, $this->number);

        return $invoice_number_format = str_replace(
            array( '[prefix]', '[suffix]', '[number]' ),
            array( $this->template_settings['invoice_prefix'], $this->template_settings['invoice_suffix'], $this->number ),
            $invoice_number_format );
    }

    public function generate() {
        set_time_limit(0);
        include WPI_DIR . "lib/mpdf/mpdf.php";

        $mpdf = new mPDF('', 'A4', 0, '', 0, 0, 0, 0, 0, 0, '');
        $mpdf->useOnlyCoreFonts = true;    // false is default
        $mpdf->SetTitle("Acme Trading Co. - Invoice");
        $mpdf->SetAuthor("Acme Trading Co.");
        $mpdf->showWatermarkText = false;
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->useSubstitutions = false;
        //$mpdf->simpleTables = true;

        ob_start();

        require_once WPI_TEMPLATES_DIR . $this->template_settings['template_filename'];

        $html = ob_get_contents();

        ob_end_clean();

        $mpdf->WriteHTML($html);

        $mpdf->Output();

        exit;
    }
}