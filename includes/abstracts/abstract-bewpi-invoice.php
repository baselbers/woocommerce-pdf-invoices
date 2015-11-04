<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Abstract_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Abstract_Invoice extends BEWPI_Abstract_Document {

        /**
         * @var WC_Order
         */
        public $order;

        /**
         * @var array
         */
        public $orders = array();

        /**
         * Invoice number
         * @var integer
         */
        protected $number;

        /**
         * Formatted invoice number with prefix and/or suffix
         * @var string
         */
        protected $formatted_number;

        /**
         * Creation date.
         * @var datetime
         */
        protected $date;

	    /**
	     * Creation year
	     * @var datetime
	     */
	    protected $year;

        /**
         * Number of columns for the products table
         * @var integer
         */
        public $columns_count;

        /**
         * Colspan data for product table cells
         * @var array
         */
        protected $colspan;

        /**
         * Width of the description cell of the product table
         * @var string
         */
        protected $desc_cell_width;

	    /**
	     * Name of the template
	     * @var string
	     */
	    protected $template_name;

	    /**
	     * Type of invoice
	     * @var string
	     */
	    protected $type;

	    /**
	     * Dir of the template
	     * @var string
	     */
	    protected $template_dir_name;

        /**
         * Initialize invoice with WooCommerce order
         * @param string $order
         */
        public function __construct( $order_id, $type, $taxes_count = 0 ) {
	        parent::__construct();
			$this->order                = wc_get_order( $order_id );
	        $this->type                 = $type;
	        $this->columns_count        = $this->get_columns_count( $taxes_count );
	        $this->formatted_number     = get_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', true );
	        $this->template_name        = $this->template_options["bewpi_template_name"];

	        // Check if the invoice already exists.
	        if( ! empty( $this->formatted_number ) || isset( $_GET['bewpi_action'] ) && $_GET['bewpi_action'] !== 'cancel' )
		        $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init() {
	        $this->number               = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
	        $this->year                 = get_post_meta( $this->order->id, '_bewpi_invoice_year', true );
	        $this->filename             = $this->formatted_number . '.pdf';
	        $this->full_path            = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->filename;
	        $this->date                 = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
        }

	    /**
	     * Format the invoice number with prefix and/or suffix.
	     * @return mixed
	     */
	    public function get_formatted_number( $insert = false ) {
            $invoice_number_format = $this->template_options['bewpi_invoice_number_format'];
            // Format number with the number of digits
            $digit_str = "%0" . $this->template_options['bewpi_invoice_number_digits'] . "s";
            $digitized_invoice_number = sprintf( $digit_str, $this->number );
            $year = date('Y');
            $y = date('y');
            $m = date('m');

            // Format invoice number
            $formatted_invoice_number = str_replace(
                array( '[prefix]', '[suffix]', '[number]', '[Y]', '[y]' , '[m]' ),
                array( $this->template_options['bewpi_invoice_number_prefix'], $this->template_options['bewpi_invoice_number_suffix'], $digitized_invoice_number, (string)$year, (string)$y, (string)$m ),
                $invoice_number_format );

		    // Insert formatted invoicenumber into db
		    if ( $insert )
			    add_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', $formatted_invoice_number );

		    return $formatted_invoice_number;
	    }

        /**
         * Format date
         * @param bool $insert
         * @return bool|datetime|string
         */
        public function get_formatted_invoice_date( $insert = false ) {
            $date_format = $this->template_options['bewpi_date_format'];
	        ( !empty( $date_format ) ) ? $this->date = date_i18n( $date_format, strtotime( date( $date_format ) ) ) : $this->date = date_i18n( "d-m-Y", strtotime( date( 'd-m-Y' ) ) );
            if( $insert ) add_post_meta($this->order->id, '_bewpi_invoice_date', $this->date);
            return $this->date;
        }

        /*
         * Format the order date and return
         */
        public function get_formatted_order_date() {
            $order_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->order->order_date );
            if ( ! empty ( $this->template_options['bewpi_date_format'] ) ) {
                $date_format = $this->template_options['bewpi_date_format'];
                $formatted_date = $order_date->format( $date_format );
                return date_i18n( $date_format, strtotime( $formatted_date ) );

            } else {
                $formatted_date = $order_date->format( $order_date, "d-m-Y" );
                return date_i18n( "d-m-Y", strtotime( $formatted_date ) );
            }
        }

	    /**
	     * Reset invoice number counter if user did check the checkbox.
	     * @return bool
	     */
	    private function reset_counter() {
		    // Check if the user resetted the invoice counter and set the number.
		    if ( $this->template_options['bewpi_reset_counter'] ) {
			    if ( $this->template_options['bewpi_next_invoice_number'] > 0 ) {
				    $this->number = $this->template_options['bewpi_next_invoice_number'];
				    $this->template_options['bewpi_reset_counter'] = 0;
				    return true;
			    }
		    }
		    return false;
	    }

	    /**
	     * Reset the invoice number counter if user did check the checkbox.
	     * @return bool
	     */
	    private function new_year_reset() {
		    if ( $this->template_options['bewpi_reset_counter_yearly'] ) {
			    $last_year = ( isset( $this->template_options['bewpi_last_invoiced_year'] ) ) ? $this->template_options['bewpi_last_invoiced_year'] : '';

			    if ( !empty( $last_year ) && is_numeric( $last_year ) ) {
				    $date = getdate();
				    $current_year = $date['year'];
				    if ($last_year < $current_year) {
					    // Set new year as last invoiced year and reset invoice number
					    $this->number = 1;
					    return true;
				    }
			    }
		    }
		    return false;
	    }

        /**
         * Get all html from html files and store as vars
         */
        private function output_template_files_to_buffer( $html_template_files ) {
	        $html_sections = array();

	        foreach ( $html_template_files as $section => $full_path ) {
		        if ( $section === 'style' ) {
			        $html = $this->output_style_to_buffer( $full_path );
		        } else {
			        $html = $this->output_to_buffer( $full_path );
		        }
		        $html_sections[$section] = $html;
	        }

	        return $html_sections;
        }

	    /**
	     * Generates and saves the invoice to the uploads folder.
	     * @param $dest
	     * @return string
	     */
	    protected function save( $dest, $html_templates ) {
		    if ( $this->exists() )
			    wp_die( __( 'Invoice already exists, first delete invoice.', $this->textdomain ) );

		    // If the invoice is manually deleted from dir, delete data from database.
		    $this->delete();

		    if ( $this->template_options['bewpi_invoice_number_type'] === "sequential_number" ) {
			    if ( ! $this->reset_counter() && ! $this->new_year_reset() )
				    $this->number = $this->template_options['bewpi_last_invoice_number'] + 1;
		    } else {
			    $this->number = $this->order->get_order_number();
		    }

            $this->colspan              = $this->get_colspan();
		    $this->formatted_number     = $this->get_formatted_number( true );
		    $this->year                 = date( 'Y' );
		    $this->filename             = $this->formatted_number . '.pdf';
		    $this->full_path            = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->formatted_number . '.pdf';

		    add_post_meta( $this->order->id, '_bewpi_invoice_number', $this->number );
		    add_post_meta( $this->order->id, '_bewpi_invoice_year', $this->year );

		    $this->template_options['bewpi_last_invoice_number']    = $this->number;
		    $this->template_options['bewpi_last_invoiced_year']     = $this->year;

            delete_option( 'bewpi_template_settings' );
		    add_option( 'bewpi_template_settings', $this->template_options );

		    $html_sections  = $this->output_template_files_to_buffer( $html_templates );
		    $paid           = $this->is_paid();

	        do_action( 'bewpi_before_document_generation', array( 'type' => $this->type, 'order_id' => $this->order->id ) );

		    parent::generate( $html_sections, $dest, $paid );

		    return $this->full_path;
	    }

	    /**
	     * Checks if order is paid
	     * @return bool
	     */
	    public function is_paid() {
		    return ( in_array( $this->order->get_status(), array( 'pending', 'on-hold', 'auto-draft' ) ) ) ? false : true;
	    }

	    /**
	     * View or download the invoice.
	     * @param $download
	     */
	    public function view( $download ) {
		    if ( ! $this->exists() ) wp_die( __( 'Invoice not found, first create invoice.', $this->textdomain ) );
		    parent::view( $download );
	    }

	    /**
	     * Delete all invoice data from database and the file.
	     */
	    public function delete() {
		    delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
		    delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
		    delete_post_meta( $this->order->id, '_bewpi_invoice_year' );

		    if ( $this->exists() )
		        parent::delete();
	    }

	    /**
	     * @param $order_status
	     * Customer is only allowed to download invoice if the status of the order matches the email type option.
	     * @return bool
	     */
	    public function is_download_allowed( $order_status ) {
		    $allowed = false;
		    if ( $this->general_options['bewpi_email_type'] === "customer_processing_order"
		        && $order_status === "wc-processing" || $order_status === "wc-completed" ) {
			    $allowed = true;
		    }
		    return $allowed;
	    }

        /**
         * Display company name if logo is not found.
         * Convert image to base64 due to incompatibility of subdomains with MPDF
         */
        public function get_company_logo_html() {
            if ( ! empty( $this->template_options['bewpi_company_logo'] ) ) :
                $image_url = $this->template_options['bewpi_company_logo'];

	            // get the relative path due to slow generation of invoice. Not fully tested yet.
	            //$image_url = '..' . str_replace( get_site_url(), '', $image_url );

	            // not needed anymore if we use the relative path fix.
	            if( ini_get( 'allow_url_fopen' ) ) {
		            $image_url = image_to_base64( $image_url );
	            }

                echo '<img class="company-logo" src="' . $image_url . '"/>';
            else :
                echo '<h1 class="company-logo">' . $this->template_options['bewpi_company_name'] . '</h1>';
            endif;
        }

	    private function output_to_buffer( $full_path ) {
		    ob_start();
		    require_once $full_path;
		    $output = ob_get_contents();
		    ob_end_clean();
		    return $output;
	    }

	    private function output_style_to_buffer( $full_path ) {
		    return '<style>' . file_get_contents( $full_path ) . '</style>';
	    }

	    public function outlining_columns_html() {
		    ?>
		    <style>
			    <?php
				// Create css for outlining the product cells.
				$righter_product_row_tds_css = "";
				for ( $td = $this->colspan['left'] + 1; $td <= $this->columns_count; $td++ ) {
					if ( $td !== $this->columns_count ) {
						$righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . "),";
					} else {
						  $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . ")";
						  $righter_product_row_tds_css .= "{ width: " . ( 50 / $this->colspan['right'] ) . "%; }";
			        }
				}
				echo $righter_product_row_tds_css;
				?>
			    tr.product-row td:nth-child(1) {
				    width: <?php echo $this->desc_cell_width; ?>;
			    }
		    </style>
			<?php
	    }

	    private function get_columns_count( $taxes_count ) {
		    $columns_count = 4;

		    if ( $this->template_options['bewpi_show_sku'] )
			    $columns_count ++;

		    if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) )
			    $columns_count += $taxes_count;

		    return $columns_count;
	    }

	    /**
	     * Calculates colspan for table footer cells
	     * @return array
	     */
	    public function get_colspan() {
		    $colspan = array();
		    $number_of_left_half_columns = 3;
		    $this->desc_cell_width = '30%';

		    // The product table will be split into 2 where on the right 5 columns are the max
		    if ( $this->columns_count <= 4 ) :
			    $number_of_left_half_columns = 1;
			    $this->desc_cell_width = '48%';
		    elseif ( $this->columns_count <= 6 ) :
			    $number_of_left_half_columns = 2;
			    $this->desc_cell_width = '35.50%';
		    endif;

		    $colspan['left'] = $number_of_left_half_columns;
		    $colspan['right'] = $this->columns_count - $number_of_left_half_columns;
		    $colspan['right_left'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_DOWN );
		    $colspan['right_right'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_UP );

		    return $colspan;
	    }

	    /**
	     * Determine if the template is a custom or standard
	     * @param $template_name
	     * @return string
	     */
	    protected function get_template_dir( $template_name ) {
		    $custom_template_dir = BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
		    if ( file_exists( $custom_template_dir ) )
		         return $custom_template_dir;

		    $template_dir = BEWPI_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
		    if ( file_exists( $template_dir ) )
			    return $template_dir;

		    return '';
	    }

	    public function get_full_path() {
		    return $this->full_path;
	    }
    }
}