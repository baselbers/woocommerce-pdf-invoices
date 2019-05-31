<?php
/**
 * Invoice Class for different types of (invoice) documents.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Makes the invoice.
 * Class BEWPI_Invoice
 */
abstract class BEWPI_Abstract_Invoice extends BEWPI_Abstract_Document {

	/**
	 * Invoice number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * MySQL invoice date.
	 *
	 * @var string
	 */
	protected $date = '0000-00-00 00:00:00';

	/**
	 * Year of invoice.
	 *
	 * @var string
	 */
	protected $year;

	/**
	 * Number of columns from products table.
	 *
	 * @deprecated outlining columns will be refactored.
	 * @var int
	 */
	public $columns_count;

	/**
	 * Colspan data to outline products table columns.
	 *
	 * @var int
	 */
	public $colspan = 1;

	/**
	 * Width of the description cell of the product table.
	 *
	 * @deprecated outlining columns will be refactored.
	 * @var string
	 */
	protected $desc_cell_width;

	/**
	 * BEWPI_Abstract_Invoice constructor.
	 *
	 * @param int $order_id WooCommerce Order ID.
	 */
	public function __construct( $order_id ) {
		parent::__construct();
		$this->date      = get_post_meta( $order_id, '_bewpi_invoice_date', true );
		$this->number    = get_post_meta( $order_id, '_bewpi_invoice_number', true );
		$this->year      = date_i18n( 'Y', strtotime( $this->date ) );
		$this->full_path = self::exists( $order_id );
		$this->filename  = basename( $this->full_path );
	}

	/**
	 * Invoice number.
	 *
	 * @return int
	 */
	public function get_number() {
		return (int) $this->number;
	}

	/**
	 * Format invoice number with placeholders.
	 *
	 * @return string
	 */
	public function get_formatted_number() {
		// Format number with the number of digits.
		$digitized_invoice_number = sprintf( '%0' . WPI()->get_option( 'template', 'invoice_number_digits' ) . 's', $this->number );
		$formatted_invoice_number = str_replace(
			array( '[number]', '[order-date]', '[order-number]', '[Y]', '[y]', '[m]' ),
			array(
				$digitized_invoice_number,
				date_i18n( apply_filters( 'bewpi_formatted_invoice_number_order_date_format', 'Y-m-d' ), strtotime( $this->date ) ),
				$this->order->get_order_number(),
				$this->year,
				date_i18n( 'y', strtotime( $this->date ) ),
				date_i18n( 'm', strtotime( $this->date ) ),
			),
			WPI()->get_option( 'template', 'invoice_number_format' )
		);

		// Since 2.8.1 [prefix] and [suffix] were removed.
		$formatted_invoice_number = str_replace(
			array( '[prefix]', '[suffix]' ),
			array( '', '' ),
			$formatted_invoice_number
		);

		// Add prefix and suffix directly.
		$formatted_invoice_number = WPI()->get_option( 'template', 'invoice_number_prefix' ) . $formatted_invoice_number . WPI()->get_option( 'template', 'invoice_number_suffix' );

		return apply_filters( 'bewpi_formatted_invoice_number', $formatted_invoice_number, $this->type );
	}

	/**
	 * Format and localize (MySQL) invoice date.
	 *
	 * @deprecated Use get_formatted_date instead.
	 * @return string
	 */
	public function get_formatted_invoice_date() {
		return date_i18n( $this->get_date_format(), strtotime( $this->date ) );
	}

	/**
	 * Format and localize (MySQL) invoice date.
	 *
	 * @return string
	 */
	public function get_formatted_date() {
		return date_i18n( $this->get_date_format(), strtotime( $this->date ) );
	}

	/**
	 * Delete invoice PDF files.
	 *
	 * @param int $from_number Invoice number where to start from.
	 */
	private function delete_pdf_invoices( $from_number = 0 ) {
		global $wpdb;

		if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
			// get formatted numbers by year and greater then given invoice number.
			$files = $wpdb->get_col( $wpdb->prepare(
				"SELECT pm3.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN wp_postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_date' AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = '_bewpi_invoice_number' AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = '_bewpi_invoice_pdf_path')",
				(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
				$from_number
			) ); // db call ok; no-cache ok.
		} else {
			// get formatted numbers greater then given invoice number.
			$files = $wpdb->get_col( $wpdb->prepare(
				"SELECT pm2.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_number' AND pm1.meta_value >= %d)
      						AND (pm2.meta_key = '_bewpi_invoice_pdf_path')",
				$from_number
			) ); // db call ok; no-cache ok.
		}

		// delete pdf files.
		foreach ( $files as $pdf_path ) {
			parent::delete( WPI_ATTACHMENTS_DIR . '/' . $pdf_path );
		}
	}

	/**
	 * Delete invoice post meta information.
	 *
	 * @param int $from_number Invoice number from which to delete.
	 *
	 * @return false|int
	 */
	private function delete_invoice_meta( $from_number = 0 ) {
		global $wpdb;

		if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
			// delete by year and greater then given invoice number.
			$query = $wpdb->prepare(
				"DELETE pm1, pm2, pm3 FROM $wpdb->postmeta pm1
  						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN $wpdb->postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = %s AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = %s AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = %s)",
				'_bewpi_invoice_date',
				(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
				'_bewpi_invoice_number',
				$from_number,
				'_bewpi_invoice_pdf_path'
			);
		} else {
			// delete by greater then given invoice number.
			$query = $wpdb->prepare(
				"DELETE pm1, pm2 FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = %s AND pm1.meta_value >= %d)
							AND (pm2.meta_key = %s OR pm2.meta_key = %s)",
				'_bewpi_invoice_number',
				$from_number,
				'_bewpi_invoice_date',
				'_bewpi_invoice_pdf_path'
			);
		}

		return $wpdb->query( $query ); // db call ok; no-cache ok. WPCS: unprepared SQL OK.
	}

	/**
	 * Get next invoice number from db.
	 *
	 * @return int
	 */
	protected function get_next_invoice_number() {
		// check if user did a counter reset.
		$next_number = get_transient( 'bewpi_next_invoice_number' );
		if ( false !== $next_number ) {
			$this->delete_pdf_invoices( $next_number );
			$this->delete_invoice_meta( $next_number );

			delete_transient( 'bewpi_next_invoice_number' );

			return $next_number;
		}

		$max_invoice_number = self::get_max_invoice_number();
		$next_number        = $max_invoice_number + 1;

		return $next_number;
	}

	/**
	 * Return highest invoice number.
	 *
	 * @return int
	 */
	public static function get_max_invoice_number() {
		global $wpdb;

		if ( false === (bool) WPI()->get_option( 'reset_counter_yearly' ) ) {
			// get by year.
			$query = $wpdb->prepare(
				"SELECT MAX(CAST(pm2.meta_value AS UNSIGNED)) AS last_invoice_number
					FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE pm1.meta_key = %s AND YEAR(pm1.meta_value) = %d AND pm2.meta_key = %s",
				'_bewpi_invoice_date',
				(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
				'_bewpi_invoice_number'
			);
		} else {
			// get all.
			$query = $wpdb->prepare(
				"SELECT MAX(CAST(pm2.meta_value AS UNSIGNED)) AS last_invoice_number
					FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE pm2.meta_key = %s",
				'_bewpi_invoice_number'
			);
		}

		return intval( $wpdb->get_var( $query ) ); // db call ok; no-cache ok. WPCS: unprepared SQL OK.
	}

	/**
	 * Save invoice.
	 *
	 * @param string $destination pdf generation mode.
	 *
	 * @return string
	 */
	public function generate( $destination = 'F' ) {
		if ( false !== $this->full_path ) {
			parent::delete( $this->full_path );
		}

		if ( empty( $this->number ) ) {
			// Use WooCommerce order numbers as invoice numbers?
			if ( 'woocommerce_order_number' === WPI()->get_option( 'template', 'invoice_number_type' ) ) {
				$this->number = $this->order->get_order_number();
			} else {
				$this->number = $this->get_next_invoice_number();
			}
		}

		if ( empty( $this->date ) ) {
			$this->date = apply_filters( 'wpi_invoice_date', current_time( 'mysql' ), $this );
		}

		$pdf_path        = $this->get_rel_pdf_path() . '/' . $this->get_formatted_number() . '.pdf';
		$this->full_path = WPI_ATTACHMENTS_DIR . '/' . $pdf_path;
		$this->filename  = basename( $this->full_path );

		// update invoice data in db.
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );
		update_post_meta( $order_id, '_bewpi_invoice_date', $this->date );
		update_post_meta( $order_id, '_bewpi_invoice_number', $this->number );
		update_post_meta( $order_id, '_bewpi_invoice_pdf_path', $pdf_path );

		do_action( 'bewpi_before_document_generation', $this->type, $order_id );

		parent::generate( $destination );

		return $this->full_path;
	}

	/**
	 * Get relative PDF path.
	 *
	 * @return string
	 */
	public function get_rel_pdf_path() {
		// yearly sub-folders.
		if ( false === (bool) WPI()->get_option( 'template', 'reset_counter_yearly' ) ) {
			return '';
		}

		$year_subdir = WPI_ATTACHMENTS_DIR . '/' . $this->year;
		if ( ! is_dir( $year_subdir ) ) {
			wp_mkdir_p( $year_subdir );
		}

		return $this->year;
	}

	/**
	 * Update invoice.
	 *
	 * @param string $destination pdf generation mode.
	 *
	 * @return string $full_path Full path to PDF invoice file.
	 */
	public function update( $destination = 'F' ) {
		parent::delete( $this->full_path );
		parent::generate( $destination );

		return $this->full_path;
	}

	/**
	 * Delete all invoice data from database and pdf file.
	 *
	 * @param int $order_id WooCommerce Order ID.
	 */
	public static function delete( $order_id ) {
		// Remove pdf file.
		$full_path = WPI_ATTACHMENTS_DIR . '/' . get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
		parent::delete( $full_path );

		// Remove invoice postmeta from database.
		delete_post_meta( $order_id, '_bewpi_invoice_number' );
		delete_post_meta( $order_id, '_bewpi_invoice_date' );
		delete_post_meta( $order_id, '_bewpi_invoice_pdf_path' );

		// Version 2.6+ not used anymore.
		delete_post_meta( $order_id, '_bewpi_formatted_invoice_number' );
		delete_post_meta( $order_id, '_bewpi_invoice_year' );

		do_action( 'bewpi_after_post_meta_deletion', $order_id );
	}

	/**
	 * Check if invoice exists.
	 *
	 * @param int $order_id WooCommerce Order ID.
	 *
	 * @return bool|string false when no pdf invoice exists or full path when exists.
	 */
	public static function exists( $order_id ) {
		// pdf data exists in database?
		$pdf_path = get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
		if ( ! $pdf_path ) {
			return false;
		}

		return parent::exists( WPI_ATTACHMENTS_DIR . '/' . $pdf_path );
	}

	/**
	 * Add column header data.
	 *
	 * @param array  $data        Column header data.
	 * @param string $key         Column name.
	 * @param string $label       Column title.
	 * @param string $tax_display Display tax label.
	 */
	public function add_column( &$data, $key, $label, $tax_display = '' ) {
		if ( ! empty( $tax_display ) ) {
			$label .= '&nbsp;' . WPI()->tax_or_vat_label( 'incl' === $tax_display );
		}

		$data[ $key ] = $label;
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array();

		$this->add_column( $columns, 'description', __( 'Description', 'woocommerce-pdf-invoices' ) );
		$this->add_column( $columns, 'quantity', __( 'Qty', 'woocommerce-pdf-invoices' ) );
		$this->add_column( $columns, 'total', __( 'Total', 'woocommerce-pdf-invoices' ) );

		return apply_filters( 'wpi_get_invoice_columns', $columns, $this );
	}

	/**
	 * Adds line item description to columns data array.
	 *
	 * @param array         $data    line item data.
	 * @param int           $item_id item ID.
	 * @param WC_Order_Item $item    item object.
	 */
	public function add_description_column_data( &$data, $item_id, $item ) {
		ob_start();
		echo esc_html( $item['name'] );

		do_action( 'wpi_order_item_meta_start', $item, $this->order );
		do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $this->order );

		WPI()->templater()->display_item_meta( $item );

		do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $this->order );
		$description = ob_get_contents();
		ob_end_clean();

		$data['description'] = apply_filters( 'wpi_item_description_data', $description, $item_id, $item );
	}

	/**
	 * Adds line item quantity to columns data array.
	 *
	 * @param array  $data    line item data.
	 * @param int    $item_id item ID.
	 * @param object $item    item object.
	 */
	public function add_quantity_column_data( &$data, $item_id, $item ) {
		$data['quantity'] = $item['qty'];
	}

	/**
	 * Adds line item total incl. tax to columns data array.
	 *
	 * @param array  $data    line item data.
	 * @param int    $item_id item ID.
	 * @param object $item    item object.
	 * @param bool   $ex_tax  Excluding tax.
	 */
	public function add_total_column_data( &$data, $item_id, $item, $ex_tax = true ) {
		$data['total'] = $this->order->get_formatted_line_subtotal( $item, $ex_tax ? 'excl' : 'incl' );
	}

	/**
	 * Get line item data for all user selected columns.
	 *
	 * @param array $items line items.
	 *
	 * @return array
	 */
	public function get_columns_data( $items = array() ) {
		// Make backwards compatible with older custom templates.
		if ( count( $items ) === 0 ) {
			$items = $this->order->get_items( 'line_item' );
		}

		$rows               = array();
		$prices_include_tax = WPI()->get_prop( $this->order, 'prices_include_tax' );

		foreach ( $items as $item_id => $item ) {
			$row = array();

			$this->add_description_column_data( $row, $item_id, $item );
			$this->add_quantity_column_data( $row, $item_id, $item );
			$this->add_total_column_data( $row, $item_id, $item, ! $prices_include_tax );

			$rows[] = apply_filters( 'wpi_get_invoice_columns_data_row', $row, $item_id, $item, $this );
		}

		return $rows;
	}

	/**
	 * Add total row for subtotal.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_subtotal_row( &$total_rows, $tax_display ) {
		$subtotal = $this->order->get_subtotal_to_display( false, $tax_display );
		if ( $subtotal ) {
			$total_rows['cart_subtotal'] = array(
				'label' => __( 'Subtotal:', 'woocommerce-pdf-invoices' ),
				'value' => $subtotal,
			);
		}
	}

	/**
	 * Add total row for discounts.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_discount_row( &$total_rows, $tax_display ) {
		if ( $this->order->get_total_discount() > 0 ) {
			$total_rows['discount'] = array(
				'label' => __( 'Discount:', 'woocommerce-pdf-invoices' ),
				'value' => '-' . $this->order->get_discount_to_display( $tax_display ),
			);
		}
	}

	/**
	 * Add total row for shipping.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_shipping_row( &$total_rows, $tax_display ) {
		if ( $this->order->get_shipping_method() ) {
			$total_rows['shipping'] = array(
				'label' => __( 'Shipping:', 'woocommerce-pdf-invoices' ),
				'value' => $this->order->get_shipping_to_display( $tax_display ),
			);
		}
	}

	/**
	 * Add total row for fees.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_fee_rows( &$total_rows, $tax_display ) {
		$fees = $this->order->get_fees();
		if ( $fees ) {
			/**
			 * Fee annotations.
			 *
			 * @var string            $id  WooCommerce ID.
			 * @var WC_Order_Item_Fee $fee WooCommerce Fee.
			 */
			foreach ( $fees as $id => $fee ) {
				if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
					continue;
				}

				$total_rows[ 'fee_' . $id ] = array(
					'label' => $fee['name'] . ':',
					'value' => wc_price( 'excl' === $tax_display ? $fee['line_total'] : (double) $fee['line_total'] + (double) $fee['line_tax'], array(
							'currency' => WPI()->get_currency( $this->order ),
						)
					),
				);
			}
		}
	}

	/**
	 * Add total row for taxes.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_tax_rows( &$total_rows, $tax_display ) {
		// Tax for tax exclusive prices.
		if ( 'excl' === $tax_display ) {
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( $this->order->get_tax_totals() as $code => $tax ) {
					$total_rows[ sanitize_title( $code ) ] = array(
						'label' => $tax->label . ':',
						'value' => $tax->formatted_amount,
					);
				}
			} else {
				$total_rows['tax'] = array(
					'label' => WC()->countries->tax_or_vat() . ':',
					'value' => wc_price( $this->order->get_total_tax(), array(
							'currency' => WPI()->get_currency( $this->order ),
						)
					),
				);
			}
		}
	}

	/**
	 * Add total row for grand total.
	 *
	 * @param array  $total_rows  totals.
	 * @param string $tax_display 'excl' or 'incl'.
	 */
	protected function add_order_item_totals_total_row( &$total_rows, $tax_display ) {
		$total_rows['order_total'] = array(
			'label' => __( 'Total:', 'woocommerce-pdf-invoices' ),
			'value' => $this->order->get_formatted_order_total( $tax_display, false ),
		);
	}

	/**
	 * Get totals for display.
	 *
	 * @param string $tax_display 'excl' or 'incl' tax.
	 *
	 * @return array
	 */
	public function get_order_item_totals( $tax_display = '' ) {
		$tax_display = $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' );
		$total_rows  = array();

		$this->add_order_item_totals_subtotal_row( $total_rows, $tax_display );
		$this->add_order_item_totals_discount_row( $total_rows, $tax_display );
		$this->add_order_item_totals_shipping_row( $total_rows, $tax_display );
		$this->add_order_item_totals_fee_rows( $total_rows, $tax_display );
		$this->add_order_item_totals_tax_rows( $total_rows, $tax_display );
		$this->add_order_item_totals_total_row( $total_rows, $tax_display );

		return apply_filters( 'wpi_get_invoice_total_rows', $total_rows, $this );
	}

	/**
	 * Backwards compatibility.
	 *
	 * @deprecated Use `generate()` instead.
	 *
	 * @param string $destination pdf generation mode.
	 */
	public function save( $destination = 'F', $html_templates = array() ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8', 'generate' );
		$this->generate( $destination );
	}

	/**
	 * Display company logo or name
	 *
	 * @deprecated See minimal template.
	 */
	public function get_company_logo_html() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8', 'WPI()->templater()->get_meta( \'_vat_number\' )' );
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
	 * Get VAT number from WooCommerce EU VAT Number plugin.
	 *
	 * @deprecated See minimal template.
	 */
	public function display_vat_number() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8', 'WPI()->templater()->get_meta( \'_vat_number\' )' );
		// WC backwards compatibility.
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

		$vat_number = get_post_meta( $order_id, '_vat_number', true );
		if ( ! empty( $vat_number ) ) {
			echo '<span>' . sprintf( __( 'VAT Number: %s', 'woocommerce-pdf-invoices' ), $vat_number ) . '</span>';
		}
	}

	/**
	 * Get PO Number from WooCommerce Purchase Order Gateway plugin.
	 *
	 * @deprecated See minimal template.
	 */
	public function display_purchase_order_number() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8', 'WPI()->templater()->get_meta( \'_po_number\' )' );
		// WC backwards compatibility.
		$payment_method = BEWPI_WC_Order_Compatibility::get_prop( $this->order, 'payment_method' );
		if ( isset( $payment_method ) && 'woocommerce_gateway_purchase_order' === $payment_method ) {
			// WC backwards compatibility.
			$order_id  = BEWPI_WC_Order_Compatibility::get_id( $this->order );
			$po_number = get_post_meta( $order_id, '_po_number', true );
			if ( ! empty( $po_number ) ) {
				echo '<span>' . sprintf( __( 'Purchase Order Number: %s', 'woocommerce-gateway-purchase-order' ), $po_number ) . '</span>';
			}
		}
	}

	/**
	 * Outline columns for within pdf template files.
	 *
	 * @param int $taxes_count number of tax classes.
	 *
	 * @deprecated See minimal template.
	 */
	public function outlining_columns_html( $taxes_count = 0 ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
		$columns_count = $this->get_columns_count( $taxes_count );
		$colspan       = $this->get_colspan( $columns_count );
		?>
		<style>
			<?php
			// Create css for outlining the product cells.
			$righter_product_row_tds_css = "";
			for ( $td = $colspan['left'] + 1; $td <= $columns_count; $td++ ) {
				if ( $td !== $columns_count ) {
					$righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . "),";
				} else {
					  $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . ")";
					  $righter_product_row_tds_css .= "{ width: " . ( 50 / $colspan['right'] ) . "%; }";
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

	/**
	 * Number of columns.
	 *
	 * @param int $tax_count number of taxes.
	 *
	 * @deprecated See minimal template.
	 * @return int
	 */
	public function get_columns_count( $tax_count = 0 ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
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
	 *
	 * @deprecated See minimal template solution.
	 *
	 * @param int $columns_count number of columns.
	 *
	 * @return array
	 */
	public function get_colspan( $columns_count = 0 ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
		$colspan                     = array();
		$number_of_left_half_columns = 3;
		$this->desc_cell_width       = '30%';

		// The product table will be split into 2 where on the right 5 columns are the max.
		if ( $columns_count <= 4 ) :
			$number_of_left_half_columns = 1;
			$this->desc_cell_width       = '48%';
		elseif ( $columns_count <= 6 ) :
			$number_of_left_half_columns = 2;
			$this->desc_cell_width       = '35.50%';
		endif;

		$colspan['left']        = $number_of_left_half_columns;
		$colspan['right']       = $columns_count - $number_of_left_half_columns;
		$colspan['right_left']  = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_DOWN );
		$colspan['right_right'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_UP );

		return $colspan;
	}

	/**
	 * Check if order has only virtual products.
	 *
	 * @return bool
	 *
	 * @deprecated moved to WPI()->templater().
	 * @since      2.5.3
	 */
	public function has_only_virtual_products() {
		foreach ( $this->order->get_items( 'line_item' ) as $item ) {
			$product = $this->order->get_product_from_item( $item );
			if ( ! $product || ! $product->is_virtual() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if invoice needs zero rated vat.
	 *
	 * @return bool
	 */
	public function is_vat_exempt() {
		// @todo 'yes' === $this->get_meta( 'is_vat_exempt' ) or get_is_vat_exempt.
		$is_vat_exempt = count( $this->order->get_taxes() ) === 0 && 'true' === WPI()->get_meta( $this->order, '_vat_number_is_valid' );
		if ( apply_filters( 'wpi_is_vat_exempt', $is_vat_exempt, $this ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get invoice date.
	 *
	 * @return DateTime
	 */
	public function get_date() {
		return new DateTime( $this->date );
	}

	/**
	 * Checks if invoice needs to have a zero rated VAT.
	 *
	 * @deprecated See minimal template.
	 * @return bool
	 */
	public function display_zero_rated_vat() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
		// WC backwards compatibility.
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

		$is_vat_valid = get_post_meta( $order_id, '_vat_number_is_valid', true );
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
	 * @param $str
	 *
	 * @deprecated moved to BEWPI_Template Class.
	 *
	 * @return mixed
	 */
	private function replace_placeholders( $str ) {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
		// WC backwards compatibility.
		$order_id = BEWPI_WC_Order_Compatibility::get_id( $this->order );

		$placeholders = apply_filters( 'bewpi_placeholders', array(
			'[payment_method]'  => BEWPI_WC_Order_Compatibility::get_prop( $this->order, 'payment_method_title' ),
			'[shipping_method]' => $this->order->get_shipping_method(),
		), $order_id );

		foreach ( $placeholders as $placeholder => $value ) {
			$str = str_replace( $placeholder, $value, $str );
		}

		return $str;
	}

	/**
	 * @deprecated instead use 'WPI()->templater()->get_option()'.
	 */
	public function left_footer_column_html() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8', 'WPI()->templater()->get_option( \'bewpi_left_footer_column\' )' );
		$left_footer_column_text = $this->template_options['bewpi_left_footer_column'];
		if ( ! empty( $left_footer_column_text ) ) {
			echo '<p>' . nl2br( $this->replace_placeholders( $left_footer_column_text ) ) . '</p>';
		}
	}

	/**
	 * @deprecated instead use 'WPI()->templater()->get_option()'.
	 */
	public function right_footer_column_html() {
		_deprecated_function( __FUNCTION__, 'WooCommerce PDF Invoices v2.8' );
		$right_footer_column_text = $this->template_options['bewpi_right_footer_column'];
		if ( ! empty( $right_footer_column_text ) ) {
			echo '<p>' . nl2br( $this->replace_placeholders( $right_footer_column_text ) ) . '</p>';
		} else {
			echo '<p>' . sprintf( __( '%s of %s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ) . '</p>';
		}
	}

	/**
	 * Set order item totals colspan.
	 *
	 * @param int $colspan Order item totals table colspan.
	 */
	public function set_colspan( $colspan ) {
		$this->colspan = $colspan;
	}
}
