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
		public $formatted_number;

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
		 * Number of taxes in WooCommerce order.
		 *
		 * @var int
		 */
		protected $tax_count;

		/**
		 * BEWPI_Abstract_Invoice constructor.
		 *
		 * @param int    $order_id WooCommerce Order ID.
		 * @param string $type Type of invoice.
		 */
		public function __construct( $order_id, $type ) {
			parent::__construct();
			$this->order            = wc_get_order( $order_id );
			$this->type             = $type;
			$this->columns_count    = $this->get_columns_count( $this->tax_count );
			$this->formatted_number = get_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', true );
			$this->template_name    = $this->template_options['bewpi_template_name'];

			// Check if the invoice already exists.
			if ( ! empty( $this->formatted_number ) || isset( $_GET['bewpi_action'] ) && 'cancel' !== $_GET['bewpi_action'] ) {
				$this->init();
			}
		}

		/**
		 * Gets all the existing invoice data from database or creates new invoice number.
		 */
		private function init() {
			$this->number    = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
			$this->year      = get_post_meta( $this->order->id, '_bewpi_invoice_year', true );
			$this->filename  = $this->formatted_number . '.pdf';
			$this->full_path = BEWPI_INVOICES_DIR . (string) $this->year . '/' . $this->filename;
			$this->date      = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
		}

		/**
		 * Format the invoice number with prefix and/or suffix.
		 *
		 * @return mixed|void
		 */
		public function get_formatted_number() {
			// format number with the number of digits.
			$digitized_invoice_number = sprintf( '%0' . $this->template_options['bewpi_invoice_number_digits'] . 's', $this->number );
			$formatted_invoice_number = str_replace( array(
					'[prefix]',
					'[suffix]',
					'[number]',
					'[order-date]',
					'[order-number]',
					'[Y]',
					'[y]',
					'[m]',
				), array(
					$this->template_options['bewpi_invoice_number_prefix'],
					$this->template_options['bewpi_invoice_number_suffix'],
					$digitized_invoice_number,
					$this->get_formatted_order_date(),
					$this->order->get_order_number(),
					date_i18n( 'Y' ),
					date_i18n( 'y' ),
					date_i18n( 'm' ),
				),
				$this->template_options['bewpi_invoice_number_format']
			);

			return apply_filters( 'bewpi_formatted_invoice_number', $formatted_invoice_number, $this->type );
		}

		/**
		 * Format date.
		 *
		 * @return string
		 */
		public function get_formatted_invoice_date() {
			$date_format = $this->get_date_format();
			return date_i18n( $date_format, current_time( 'timestamp' ) );
		}

		/**
		 * Get date format.
		 *
		 * @return string
		 */
		private function get_date_format() {
			$date_format = $this->template_options['bewpi_date_format'];
			if ( ! empty( $date_format ) ) {
				return (string) $date_format;
			}

			return (string) get_option( 'date_format' );
		}

		/**
		 * Get the order date by order id.
		 *
		 * @param int $order_id WC_Order ID.
		 *
		 * @return string
		 */
		private function get_order_date( $order_id = 0 ) {
			if ( empty( $order_id ) ) {
				return $this->order->order_date;
			}

			$order = wc_get_order( $order_id );
			return $order->order_date;
		}

		/**
		 * Format order date.
		 *
		 * @param int $order_id WC_Order ID.
		 *
		 * @return string
		 */
		public function get_formatted_order_date( $order_id = 0 ) {
			$order_date = $this->get_order_date( $order_id );
			$date_format = $this->get_date_format();
			return date_i18n( $date_format, strtotime( $order_date ) );
		}

		/**
		 * Output template files to buffer.
		 *
		 * @param array $html_template_files template file paths.
		 *
		 * @return array
		 */
		private function output_template_files_to_buffer( $html_template_files ) {
			do_action( 'bewpi_before_output_template_to_buffer', array( 'order_id' => $this->order->id ) );

			$html_sections = array();
			foreach ( $html_template_files as $section => $full_path ) {
				$html_sections[ $section ] = ( 'style' === $section ) ? $this->output_style_to_buffer( $full_path ) : $this->output_to_buffer( $full_path );
			}

			do_action( 'bewpi_after_output_template_to_buffer' );
			return $html_sections;
		}

		/**
		 * Delete invoice PDF files.
		 */
		private function delete_pdf_invoices() {
			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// get pdf files from year folder.
				$current_year       = (int) date_i18n( 'Y', current_time( 'timestamp' ) );
				$bewpi_invoices_dir = BEWPI_INVOICES_DIR . $current_year . '/*.pdf';
			} else {
				// get all pdf files.
				$bewpi_invoices_dir = BEWPI_INVOICES_DIR . '*.pdf';
			}

			$files = glob( $bewpi_invoices_dir );
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					wp_delete_file( $file );
				}
			}
		}

		/**
		 * Delete invoice post meta information.
		 */
		private function delete_invoice_meta() {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// delete by year.
				$query = $wpdb->prepare(
					"DELETE pm2 FROM $wpdb->postmeta pm1
					INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s'
			            AND pm1.meta_value = %d
			            AND (pm2.meta_key LIKE '%s' OR pm2.meta_key LIKE '%s')",
					'_bewpi_invoice_year',
					(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
					'_bewpi_invoice_%',
					'_bewpi_formatted_%'
				);
			} else {
				// delete all.
				$query = $wpdb->prepare(
					"DELETE FROM $wpdb->postmeta
			        WHERE meta_key = '%s'
			          OR meta_key = '%s'
			          OR meta_key = '%s'
			          OR meta_key = '%s'",
					'_bewpi_invoice_number',
					'_bewpi_formatted_invoice_number',
					'_bewpi_invoice_date',
					'_bewpi_invoice_year'
				);
			}

			$wpdb->query( $query );
		}

		/**
		 * Get next invoice number from db.
		 *
		 * @return int
		 */
		private function get_next_invoice_number() {
			// uses WooCommerce order numbers as invoice numbers?
			if ( 'sequential_number' !== $this->template_options['bewpi_invoice_number_type'] ) {
				return (int) $this->order->get_order_number();
			}

			// check if user did a counter reset.
			if ( $this->template_options['bewpi_reset_counter'] && $this->template_options['bewpi_next_invoice_number'] > 0 ) {
				$this->delete_pdf_invoices();
				$this->delete_invoice_meta();

				// unset option.
				$this->template_options['bewpi_reset_counter'] = 0;
				update_option( 'bewpi_template_settings', $this->template_options );

				return $this->template_options['bewpi_next_invoice_number'];
			}

			$max_invoice_number = $this->get_max_invoice_number();
			return $max_invoice_number + 1;
		}

		/**
		 * Return highest invoice number.
		 *
		 * @return int
		 */
		public function get_max_invoice_number() {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// get by year.
				$query = $wpdb->prepare(
					"SELECT max(cast(pm2.meta_value as unsigned)) as last_invoice_number
					FROM $wpdb->postmeta pm1 INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s'
			            AND pm1.meta_value = %d
			            AND pm2.meta_key = '%s';",
					'_bewpi_invoice_year',
					(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
					'_bewpi_invoice_number'
				);
			} else {
				// get all.
				$query = $wpdb->prepare(
					"SELECT max(cast(pm2.meta_value as unsigned)) as last_invoice_number
					FROM $wpdb->postmeta pm1 INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s' AND pm2.meta_key = '%s';",
					'_bewpi_invoice_year',
					'_bewpi_invoice_number'
				);
			}

			return intval( $wpdb->get_var( $query ) );
		}

		/**
		 * Generates and saves the invoice to the uploads folder.
		 *
		 * @param $dest
		 *
		 * @return string
		 */
		protected function save( $dest, $html_templates ) {
			$this->general_options  = get_option( 'bewpi_general_settings' );
			$this->template_options = get_option( 'bewpi_template_settings' );

			do_action( "bewpi_before_invoice_content", $this->order->id );

			if ( $this->exists() ) {
				// delete postmeta and PDF
				$this->delete();
			}

			$this->number           = $this->get_next_invoice_number();
			$this->formatted_number = $this->get_formatted_number();
			$this->filename         = $this->formatted_number . '.pdf';
			$this->year             = date_i18n( 'Y', current_time( 'timestamp' ) );
			$this->full_path        = BEWPI_INVOICES_DIR . (string) $this->year . '/' . $this->filename;

			// update invoice data in db
			update_post_meta( $this->order->id, '_bewpi_formatted_invoice_number', $this->formatted_number );
			update_post_meta( $this->order->id, '_bewpi_invoice_number', $this->number );
			update_post_meta( $this->order->id, '_bewpi_invoice_year', $this->year );
			$this->date = $this->get_formatted_invoice_date();
			update_post_meta( $this->order->id, '_bewpi_invoice_date', $this->date );

			$this->colspan = $this->get_colspan();
			$html_sections = $this->output_template_files_to_buffer( $html_templates );
			$is_paid       = $this->order->is_paid();

			do_action( 'bewpi_before_document_generation', array(
				'type'     => $this->type,
				'order_id' => $this->order->id
			) );

			parent::generate( $html_sections, $dest, $is_paid );

			do_action( "bewpi_after_invoice_content", $this->order->id );

			return $this->full_path;
		}

		public function view() {
			if ( ! $this->exists() ) {
				wp_die( sprintf( __( 'Invoice with invoice number %s not found. First create invoice and try again.', 'woocommerce-pdf-invoices' ), $this->formatted_number ),
					'',
					array( 'response' => 200, 'back_link' => true )
				);
			}

			parent::view();
		}

		/**
		 * Delete all invoice data from database and the file.
		 */
		public function delete() {
			// remove all invoice data from db
			delete_post_meta( $this->order->id, '_bewpi_invoice_number' );
			delete_post_meta( $this->order->id, '_bewpi_formatted_invoice_number' );
			delete_post_meta( $this->order->id, '_bewpi_invoice_date' );
			delete_post_meta( $this->order->id, '_bewpi_invoice_year' );

			do_action( 'bewpi_after_post_meta_deletion', $this->order->id );

			// delete file
			if ( $this->exists() ) {
				parent::delete();
			}
		}

		/**
		 * Display company logo or name
		 */
		public function get_company_logo_html() {
			$logo_url = $this->template_options['bewpi_company_logo'];
			if ( ! empty( $logo_url ) ) {
				// mPDF' stablest method to display an image is to use their "Image data as a Variable" (https://mpdf.github.io/what-else-can-i-do/images.html) option.
				$src = apply_filters( 'bewpi_company_logo_url', 'var:company_logo' );
				printf( '<img class="company-logo" src="%s"/>', esc_attr( $src ) );
			} else {
				// show company name if company logo isn't uploaded.
				$company_name = $this->template_options['bewpi_company_name'];
				printf( '<h1 class="company-logo">%s</h1>', esc_html( $company_name ) );
			}
		}

		/**
		 * Get VAT number from WooCommerce EU VAT Number plugin
		 */
		public function display_vat_number() {
			$vat_number = get_post_meta( $this->order->id, '_vat_number', true );
			if ( $vat_number !== '' ) {
				echo '<span>' . sprintf( __( 'VAT Number: %s', 'woocommerce-pdf-invoices' ), $vat_number ) . '</span>';
			}
		}

		/**
		 * Get PO Number from WooCommerce Purchase Order Gateway plugin
		 */
		public function display_purchase_order_number() {
			if ( isset( $this->order->payment_method ) && $this->order->payment_method === 'woocommerce_gateway_purchase_order' ) {
				$po_number = get_post_meta( $this->order->id, '_po_number', true );
				if ( $po_number !== '' ) {
					echo '<span>' . sprintf( __( 'Purchase Order Number: %s', 'woocommerce-gateway-purchase-order' ), $po_number ) . '</span>';
				}
			}
		}

		private function output_to_buffer( $full_path ) {
			ob_start();
			require_once( $full_path );
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

		private function get_columns_count( $tax_count = 0 ) {
			$columns_count = 4;

			if ( $this->template_options['bewpi_show_sku'] ) {
				$columns_count ++;
			}

			if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) ) {
				$columns_count += $tax_count;
			}

			return $columns_count;
		}

		/**
		 * Calculates colspan for table footer cells
		 * @return array
		 */
		public function get_colspan() {
			$colspan                     = array();
			$number_of_left_half_columns = 3;
			$this->desc_cell_width       = '30%';

			// The product table will be split into 2 where on the right 5 columns are the max
			if ( $this->columns_count <= 4 ) :
				$number_of_left_half_columns = 1;
				$this->desc_cell_width       = '48%';
			elseif ( $this->columns_count <= 6 ) :
				$number_of_left_half_columns = 2;
				$this->desc_cell_width       = '35.50%';
			endif;

			$colspan['left']        = $number_of_left_half_columns;
			$colspan['right']       = $this->columns_count - $number_of_left_half_columns;
			$colspan['right_left']  = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_DOWN );
			$colspan['right_right'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_UP );

			return $colspan;
		}

		/**
		 * Determine if the template is a custom or standard
		 *
		 * @param $template_name
		 *
		 * @return string
		 */
		protected function get_template_dir( $template_name ) {
			// check if a custom template exists.
			$custom_template_dir = BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
			if ( file_exists( $custom_template_dir ) ) {
				return $custom_template_dir;
			}

			$template_dir = BEWPI_TEMPLATES_DIR . 'invoices/' . $this->type . '/' . $template_name . '/';
			if ( file_exists( $template_dir ) ) {
				return $template_dir;
			}
		}

		public function get_full_path() {
			return $this->full_path;
		}

		public function left_footer_column_html() {
			$left_footer_column_text = $this->template_options['bewpi_left_footer_column'];
			if ( ! empty( $left_footer_column_text ) ) {
				echo '<p>' . nl2br( $this->replace_placeholders( $left_footer_column_text ) ) . '</p>';
			}
		}

		public function right_footer_column_html() {
			$right_footer_column_text = $this->template_options['bewpi_right_footer_column'];
			if ( ! empty( $right_footer_column_text ) ) {
				echo '<p>' . nl2br( $this->replace_placeholders( $right_footer_column_text ) ) . '</p>';
			} else {
				echo '<p>' . sprintf( __( '%s of %s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ) . '</p>';
			}
		}

		private function replace_placeholders( $str ) {
			$placeholders = apply_filters( 'bewpi_placeholders', array(
				'[payment_method]'  => $this->order->payment_method_title,
				'[shipping_method]' => $this->order->get_shipping_method()
			), $this->order->id );

			foreach ( $placeholders as $placeholder => $value ) {
				$str = str_replace( $placeholder, $value, $str );
			}

			return $str;
		}

		/**
		 * Checks if invoice needs to have a zero rated VAT.
		 *
		 * @return bool
		 */
		public function display_zero_rated_vat() {
			$is_vat_valid = get_post_meta( $this->order->id, '_vat_number_is_valid', true );
			if ( ! $is_vat_valid ) {
				return false;
			}

			$is_tax_removed = count( $this->order->get_tax_totals() ) === 0;
			if ( ! $is_tax_removed ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if order has only virtual products.
		 *
		 * @return bool
		 * @since 2.5.3
		 */
		protected function has_only_virtual_products() {
			$virtual_products_count = 0;
			foreach ( $this->order->get_items( 'line_item' ) as $item ) {
				$product_id = $item['product_id'];
				$product    = wc_get_product( $product_id );
				// product could be removed.
				if ( null === $product ) {
					continue;
				}

				if ( $product->is_virtual() ) {
					$virtual_products_count ++;
				}
			}

			return count( $this->order->get_items() ) === $virtual_products_count;
		}
	}
}
