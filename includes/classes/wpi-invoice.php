<?php

class WPI_Invoice {

    private $order;

    private $textdomain;

    private $general_settings;

    private $template_settings;

    private $number;

    private $formatted_number;

    private $invoice_number_meta_key = '_bewpi_invoice_number';

    private $file;

    private $date;

    public function __construct( $order = '', $textdomain ) {
        $this->order = $order;
        $this->textdomain = $textdomain;
        $this->general_settings = (array) get_option( 'general_settings' );
        $this->template_settings = (array) get_option( 'template_settings' );

        $this->init();
    }

    private function init() {
        $this->number = get_post_meta( $this->order->id, $this->invoice_number_meta_key, true );
        $this->formatted_number = get_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', true );
        $this->date = get_post_meta( $this->order->id, '_bewpi_invoice_date', true);

        // No invoice generated yet
        if( empty( $this->number ) || empty( $this->date ) ) {
            $this->create_next_invoice_number($this->order->id);
            $this->date = $this->create_formatted_date();
        }
    }

    public function create_formatted_date() {
        $date_format = $this->template_settings['invoice_date_format'];
        //$date = DateTime::createFromFormat('Y-m-d H:i:s', $this->order->order_date);
        //$date = date( $date_format );

        if( $date_format != "" ) {
            //$formatted_date = $date->format($date_format);
            $formatted_date = date( $date_format );
        } else {
            //$formatted_date = $date->format($date, "d-m-Y");
            $formatted_date = date( 'd-m-Y' );
        }

        add_post_meta( $this->order->id, '_bewpi_invoice_date', $formatted_date );

        return $formatted_date;
    }

    public function get_formatted_date() {
        return $this->date;
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
            AND meta_key = $this->invoice_number_meta_key
            "
        );

        if(count($results) == 1){
            return $results[0]->meta_value;
        }
    }

    function create_next_invoice_number( $order_id ) {

        if( $this->template_settings['next_invoice_number'] != ""
            && $this->template_settings['next_invoice_number'] > $this->template_settings['invoice_number'] ) {
            $this->number = $this->template_settings['next_invoice_number'];
        }

        $current_year = getdate()['year'];
        if ( $this->template_settings['reset_invoice_number'] ) {
            $last_year = $this->template_settings['last_invoiced_year'];

            if ( $last_year != "" && is_numeric( $last_year ) ) {
                if ( $last_year < $current_year ) {
                    //  set new year as last invoiced year and reset invoice number
                    $this->number = 1;
                }
            }
        }

        if( empty( $this->number ) && empty( $this->template_settings['invoice_number'] ) ) {
            $this->number = 1;
        }

        // Create new invoice number and insert into database.
        $this->create_invoice_number($order_id, $this->number);

        // Set the current year as the last invoiced.
        $this->template_settings['last_invoiced_year'] = $current_year;

        // Get the invoice number.
        $this->number = $this->get_invoice_number($order_id);
        $this->template_settings['invoice_number'] = $this->number;
        update_option( 'template_settings', $this->template_settings );

        // Format invoice number
        $this->formatted_number = $this->format_invoice_number();
        add_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', $this->formatted_number );
    }

    private function format_invoice_number() {
        $invoice_number_format = $this->template_settings['invoice_format'];
        $digit_str = "%0" . $this->template_settings['invoice_number_digits'] . "s";
        $this->number = sprintf($digit_str, $this->number);

        return $invoice_number_format = str_replace(
            array( '[prefix]', '[suffix]', '[number]' ),
            array( $this->template_settings['invoice_prefix'], $this->template_settings['invoice_suffix'], $this->number ),
            $invoice_number_format );
    }

    public function get_formatted_invoice_number() {
        return $this->formatted_number;
    }

    public function generate($dest) {
        set_time_limit(0);
        include WPI_DIR . "lib/mpdf/mpdf.php";

        $mpdf = new mPDF('', 'A4', 0, '', 17, 17, 20, 50, 0, 0, '');
        $mpdf->useOnlyCoreFonts = true;    // false is default
        $mpdf->SetTitle(($this->template_settings['company_name'] != "") ? $this->template_settings['company_name'] . " - Invoice" : "Invoice");
        $mpdf->SetAuthor(($this->template_settings['company_name'] != "") ? $this->template_settings['company_name'] : "");
        $mpdf->showWatermarkText = false;
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->useSubstitutions = false;
        //$mpdf->simpleTables = true;

        ob_start();

        require_once WPI_TEMPLATES_DIR . $this->template_settings['template_filename'];

        $html = ob_get_contents();

        ob_end_clean();

        $footer = $this->get_footer();

        $mpdf->SetHTMLFooter($footer);

        $mpdf->WriteHTML($html);

        $file = WPI_TMP_DIR . $this->formatted_number . ".pdf";

        $mpdf->Output($file, $dest);

        return $file;
    }

    public function view_invoice( $download ) {
        $file = WPI_TMP_DIR . $this->formatted_number . ".pdf";
        $filename = $this->formatted_number . ".pdf";

        if( $this->exists() ) {

            if( $download ) {
                header( 'Content-type: application / pdf' );
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header( 'Content-Transfer-Encoding: binary' );
                header('Content-Length: ' . filesize($file));
                header( 'Accept-Ranges: bytes' );
            } else {
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');
            }

            @readfile($file);
            exit;

        } else {
            die('No invoice found.');
        }
    }

    public function delete() {
        if( $this->exists() )
            unlink( $this->file );
    }

    public function exists() {
        $this->file = WPI_TMP_DIR . $this->get_formatted_invoice_number() . ".pdf";
        return file_exists( $this->file );
    }

    public function get_file() {
        return $this->file;
    }

    function get_footer() {
        ob_start(); ?>

        <table class="foot">
            <tbody>
            <tr>
                <td class="border" colspan="2">
                    <?php echo $this->template_settings['terms']; ?><br/>
                    <?php if( count($this->order->get_customer_order_notes() ) > 0 ) { ?>
                        <p>
                            <strong><?php _e( 'Customer note', $this->textdomain); ?> </strong><?php echo $this->order->get_customer_order_notes()[0]->comment_content; ?>
                        </p>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td class="company-details">
                    <p>
                        <?php echo nl2br( $this->template_settings['company_details'] ); ?>
                    </p>
                </td>
                <td class="payment">
                    <p>
                        <strong>Payment</strong> via <?php echo $this->order->payment_method_title; ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>

        <?php $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }
}