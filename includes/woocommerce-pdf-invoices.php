<?php
/**
 * Final WooCommerce PDF Invoices Class.
 *
 * Processes several hooks and filter callbacks.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

	/**
	 * Implements main function for attaching invoice to email and show invoice buttons.
	 */
	final class BE_WooCommerce_PDF_Invoices {

		/**
		 * Main BE_WooCommerce_PDF_Invoices instance.
		 *
		 * @var BE_WooCommerce_PDF_Invoices
		 * @since 2.5.0
		 */
		protected static $_instance = null;

		/**
		 * Settings classes.
		 *
		 * @var array.
		 */
		public $settings = array();

		/**
		 * Main BE_WooCommerce_PDF_Invoices instance.
		 *
		 * @return BE_WooCommerce_PDF_Invoices
		 * @since 2.5.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * WooCommerce Constructor.
		 */
		private function __construct() {
			$this->define_constants();
			$this->load_plugin_textdomain();
			$this->init_hooks();
		}

		/**
		 * Define WooCommerce PDF Invoices Constants.
		 *
		 * @since 2.5.0
		 */
		private function define_constants() {
			$wp_upload_dir = wp_upload_dir();

			/**
			 * URL.
			 *
			 * @deprecated instead use WPI_URL.
			 *
			 * @since 2.7.0
			 */
			if ( ! defined( 'BEWPI_URL' ) ) {
				define( 'BEWPI_URL', plugins_url( '', BEWPI_FILE ) . '/' );
			}

			/**
			 * Templates dir.
			 *
			 * @deprecated instead use WPI_DIR.
			 *
			 * @since 2.7.0
			 */
			if ( ! defined( 'BEWPI_TEMPLATES_DIR' ) ) {
				define( 'BEWPI_TEMPLATES_DIR', BEWPI_DIR . 'includes/templates' );
			}

			/**
			 * Custom templates directory.
			 *
			 * @deprecated instead use WPI_TEMPLATES_DIR.
			 *
			 * @since 2.7.0 moved to uploads/woocommerce-pdf-invoices/invoices.
			 */
			if ( ! defined( 'BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR' ) ) {
				define( 'BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-templates/invoices' );
			}

			/**
			 * Attachments/invoices directory.
			 *
			 * @deprecated use WPI_ATTACHMENTS_DIR instead.
			 *
			 * @since 2.7.0 moved to uploads/woocommerce-pdf-invoices/attachments.
			 */
			if ( ! defined( 'BEWPI_INVOICES_DIR' ) ) {
				define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices' );
			}

			if ( ! defined( 'WPI_URL' ) ) {
				define( 'WPI_URL', untrailingslashit( plugins_url( '', WPI_FILE ) ) );
			}

			if ( ! defined( 'WPI_UPLOADS_DIR' ) ) {
				define( 'WPI_UPLOADS_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices' );
			}

			if ( ! defined( 'WPI_TEMPLATES_DIR' ) ) {
				define( 'WPI_TEMPLATES_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices/templates' );
			}

			if ( ! defined( 'WPI_ATTACHMENTS_DIR' ) ) {
				define( 'WPI_ATTACHMENTS_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices/attachments' );
			}
		}

		/**
		 * Load the translation / textdomain files
		 *
		 * @since 2.6.5 removed 'bewpi_lang_dir' filter. WordPress made update-safe WP_LANG_DIR directory.
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-pdf-invoices' );

			load_textdomain( 'woocommerce-pdf-invoices', WP_LANG_DIR . '/plugins/woocommerce-pdf-invoices-' . $locale . '.mo' );
			load_plugin_textdomain( 'woocommerce-pdf-invoices', false, 'woocommerce-pdf-invoices/lang' );
		}

		/**
		 * Initialize hooks and filters.
		 *
		 * @since 2.5.0
		 */
		private function init_hooks() {
			if ( is_admin() ) {
				$this->admin_init_hooks();
			} else {
				$this->frontend_init_hooks();
			}

			add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'set_order' ), 10, 1 );

			// @todo Move to BEWPI_Invoice class.
			add_filter( 'woocommerce_email_headers', array( $this, 'add_emailitin_as_recipient' ), 10, 3 );
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_invoice_to_email' ), 99, 3 );
			add_shortcode( 'bewpi-download-invoice', array( $this, 'download_invoice_shortcode' ) );
		}

		/**
		 * Initialize admin.
		 *
		 * @since 2.5.0
		 */
		public function admin_init_hooks() {
			add_action( 'admin_init', array( $this, 'admin_pdf_callback' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( WPI_FILE ), array( $this, 'add_plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );

			BEWPI_Abstract_Settings::init_hooks();
			BEWPI_Admin_Notices::init_hooks();
			BEWPI_Invoice::init_hooks();
			BEWPI_Packing_Slip::init_hooks();

			// @todo Move to BEWPI_Invoice class.
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_admin_order_pdf' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_admin_order_pdf_meta_box' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ), 999 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_number_column_data' ), 2 );
		}

		/**
		 * Initialize non-admin.
		 */
		private function frontend_init_hooks() {
			add_action( 'init', array( $this, 'frontend_pdf_callback' ) );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_my_account_pdf' ), 10, 2 );
		}

		/**
		 * Creates invoices dir in uploads folder.
		 */
		public static function setup_directories() {
			$current_year       = date_i18n( 'Y', current_time( 'timestamp' ) );
			$directories        = apply_filters( 'bewpi_uploads_directories', array(
				WPI_UPLOADS_DIR . '/attachments/' => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/attachments/' . $current_year . '/' => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/fonts/' => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/mpdf/ttfontdata/' => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/templates/invoice/simple/' => array(),
			) );

			// Create directories and copy files.
			foreach ( $directories as $directory => $files ) {
				if ( ! file_exists( $directory ) ) {
					wp_mkdir_p( $directory );
				}

				foreach ( $files as $file ) {
					$destination_file = $directory . basename( $file );
					if ( file_exists( $destination_file ) ) {
						continue;
					}

					$source_file = WPI_DIR . '/tmp/' . $file;
					copy( $source_file, $destination_file );
				}
			}

			// Copy fonts from tmp directory to uploads/woocommerce-pdf-invoices/fonts.
			$font_files = array_merge( glob( WPI_DIR . '/tmp/fonts/*.ttf' ), glob( WPI_DIR . '/tmp/fonts/*.otf' ) );
			foreach ( $font_files as $font_file ) {
				$destination_file = WPI_UPLOADS_DIR . '/fonts/' . basename( $font_file );
				if ( file_exists( $destination_file ) ) {
					continue;
				}

				copy( $font_file, $destination_file );
			}

			do_action( 'bewpi_after_setup_directories' );
		}

		/**
		 * Check if request is PDF action.
		 *
		 * @return bool
		 */
		private static function is_pdf_request() {
			return ( isset( $_GET['post'] ) && isset( $_GET['bewpi_action'] ) && isset( $_GET['nonce'] ) );
		}

		/**
		 * Frontend pdf actions callback.
		 * Customers only have permission to view invoice, so invoice should be created by system/admin.
		 */
		public function frontend_pdf_callback() {
			if ( ! self::is_pdf_request() ) {
				return;
			}

			// verify nonce.
			$action = sanitize_key( $_GET['bewpi_action'] );
			if ( 'view' !== $action ) {
				return;
			}

			$nonce = sanitize_key( $_GET['nonce'] );
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Invalid request.' );
			}

			if ( ! is_user_logged_in() ) {
				wp_die( 'Access denied' );
			}

			// verify woocommerce order.
			$post_id = intval( $_GET['post'] );
			$order = wc_get_order( $post_id );
			if ( ! $order ) {
				wp_die( 'Order not found.' );
			}

			// check if user has ordered order.
			$user = wp_get_current_user();
			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			$customer_user_id = (int) get_post_meta( $order_id, '_customer_user', true );
			if ( $user->ID !== $customer_user_id ) {
				wp_die( 'Access denied' );
			}

			$invoice = new BEWPI_Invoice( $order_id );
			$full_path = $invoice->update();
			BEWPI_Invoice::view( $full_path );
		}

		/**
		 * Admin pdf actions callback.
		 * Within admin by default only administrator and shop managers have permission to view, create, cancel invoice.
		 */
		public function admin_pdf_callback() {
			if ( ! self::is_pdf_request() ) {
				return;
			}

			// sanitize data and verify nonce.
			$action = sanitize_key( $_GET['bewpi_action'] );
			$nonce = sanitize_key( $_GET['nonce'] );
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Invalid request.' );
			}

			// validate allowed user roles.
			$user = wp_get_current_user();
			$allowed_roles = apply_filters( 'bewpi_allowed_roles_to_download_invoice', array(
				'administrator',
				'shop_manager',
			) );
			if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
				wp_die( 'Access denied' );
			}

			$order_id = intval( $_GET['post'] );

			// execute invoice action.
			switch ( $action ) {
				case 'view':
					$invoice = new BEWPI_Invoice( $order_id );
					$full_path = $invoice->update();
					BEWPI_Invoice::view( $full_path );
					break;
				case 'view_packing_slip':
					$view_mode = 'download' === WPI()->get_option( 'general', 'view_pdf' ) ? 'D' : 'I';
					$packing_slip = new BEWPI_Packing_Slip( $order_id );
					$packing_slip->generate( $view_mode );
					break;
				case 'cancel':
					BEWPI_Invoice::delete( $order_id );
					break;
				case 'create':
					$invoice = new BEWPI_Invoice( $order_id );
					$invoice->generate();
					break;
			}

			do_action( 'bewpi_admin_pdf_callback_end', $action, $order_id );
		}

		/**
		 * Add plugin action links on plugin.php page.
		 *
		 * @param array $links action links.
		 *
		 * @return array
		 */
		function add_plugin_action_links( $links ) {
			// add settings link.
			$settings_url       = add_query_arg( array( 'page' => 'bewpi-invoices' ), admin_url( 'admin.php' ) );
			$settings_title     = __( 'Settings', 'woocommerce-pdf-invoices' );
			array_unshift( $links, sprintf( '<a href="%1$s">%2$s</a>', $settings_url, $settings_title ) );

			return $links;
		}

		/**
		 * Add links to row meta on plugins.php page.
		 *
		 * @param array  $links row meta.
		 * @param string $file plugin basename.
		 *
		 * @return array
		 */
		public static function add_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( WPI_FILE ) === $file ) {
				// add premium plugin link.
				$premium_url = 'http://wcpdfinvoices.com';
				$premium_title = __( 'Premium', 'woocommerce-pdf-invoices' );
				$links[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $premium_url, $premium_title );
			}

			return $links;
		}

		/**
		 * Get plugin screen ids.
		 *
		 * @return array
		 */
		public static function get_screen_ids() {
			$screen_ids = array(
				'woocommerce_page_bewpi-invoices',
				'edit-shop_order',
				'shop_order',
			);

			return $screen_ids;
		}

		/**
		 * Load admin styles.
		 */
		public function admin_styles() {
			wp_register_style( 'bewpi_settings_css', WPI_URL . '/assets/css/admin.css', false, WPI_VERSION );
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC()->version );

			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			if ( in_array( $screen_id, self::get_screen_ids(), true ) ) {
				wp_enqueue_style( 'bewpi_settings_css' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
			}
		}

		/**
		 * Load admin scripts.
		 */
		public function admin_scripts() {
			wp_register_script( 'bewpi_settings_js', WPI_URL . '/assets/js/admin.js', array(), WPI_VERSION, true );
			wp_localize_script( 'bewpi_settings_js', 'BEWPI_AJAX', array(
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'deactivation_nonce'    => wp_create_nonce( 'deactivation-notice' ),
					'dismiss_nonce'         => wp_create_nonce( 'dismiss-notice' ),
				)
			);
			wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.js', array( 'jquery', 'jquery-ui-sortable', 'select2' ), WC()->version );

			$screen = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			if ( in_array( $screen_id, self::get_screen_ids(), true ) ) {
				wp_enqueue_script( 'bewpi_settings_js' );
				wp_enqueue_script( 'wc-enhanced-select' );
				wp_enqueue_script( 'jquery-ui-sortable' );
			}
		}

		/**
		 * Add "Email It In" email address as BCC to WooCommerce email.
		 *
		 * @param string $headers email headers.
		 * @param string $status email name.
		 * @param object $order WooCommerce order.
		 *
		 * @return string
		 */
		function add_emailitin_as_recipient( $headers, $status, $order ) {
			// Only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $headers;
			}

			// make sure invoice got only send once for each order.
			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			$transient_name = sprintf( 'bewpi_emailitin_processed-%1$s', $order_id );
			if ( get_transient( $transient_name ) ) {
				return $headers;
			}

			$emailitin_account = WPI()->get_option( 'general', 'email_it_in_account' );
			$emailitin_enabled = WPI()->get_option( 'general', 'email_it_in' );
			// Email It In option enabled?
			if ( ! $emailitin_enabled || empty( $emailitin_account ) ) {
				return $headers;
			}

			// check if current email type is enabled.
			$email_types = WPI()->get_option( 'general', 'email_types' );
			if ( ! isset( $email_types[ $status ] ) || ! $email_types[ $status ] ) {
				return $headers;
			}

			set_transient( $transient_name, true, 20 );

			$headers .= 'BCC: <' . $emailitin_account . '>' . "\r\n";

			return $headers;
		}

		/**
		 * Attach a generated invoice to WooCommerce emails.
		 *
		 * @param array  $attachments attachments.
		 * @param string $status name of email.
		 * @param object $order order.
		 *
		 * @return array.
		 */
		public function attach_invoice_to_email( $attachments, $status, $order ) {
			// only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $attachments;
			}

			$skip = apply_filters( 'bewpi_skip_invoice_generation', false, $status, $order->get_total() );
			if ( $skip ) {
				return $attachments;
			}

			$order_total = BEWPI_WC_Order_Compatibility::get_prop( $order, 'total' );
			if ( 0.00 === (double) $order_total && WPI()->get_option( 'general', 'disable_free_products' ) ) {
				return $attachments;
			}

			// WC backwards compatibility.
			$payment_method = BEWPI_WC_Order_Compatibility::get_prop( $order, 'payment_method' );
			// payment methods for which the invoice generation should be cancelled.
			$payment_methods = apply_filters( 'bewpi_attach_invoice_excluded_payment_methods', array() );
			if ( in_array( $payment_method, $payment_methods, true ) ) {
				return $attachments;
			}

			// check if email is enabled.
			$email_types = WPI()->get_option( 'general', 'email_types' );
			if ( ! isset( $email_types[ $status ] ) || ! $email_types[ $status ] ) {
				return $attachments;
			}

			$order_id       = BEWPI_WC_Order_Compatibility::get_id( $order );
			$transient_name = sprintf( 'bewpi_pdf_invoice_generated-%s', $order_id );
			$full_path      = BEWPI_Invoice::exists( $order_id );
			$invoice        = new BEWPI_Invoice( $order_id );
			if ( ! $full_path ) {
				$full_path = $invoice->generate();
				set_transient( $transient_name, true, 60 );
			} elseif ( $full_path && ! get_transient( $transient_name ) ) {
				// No need to update for same request.
				$full_path = $invoice->update();
			}

			$attachments[] = $full_path;

			return $attachments;
		}

		/**
		 * Create Shop Order column for Invoice Number and place it before 'Actions' column.
		 *
		 * @param array $columns Shop Order columns.
		 *
		 * @return array
		 */
		public function add_invoice_number_column( $columns ) {
			// invoice number column enabled by user?
			$general_options = get_option( 'bewpi_general_settings' );
			if ( ! $general_options['bewpi_invoice_number_column'] ) {
				return $columns;
			}

			// Splice columns at 'Actions' column, add 'Invoice No.' column and merge with last part.
			$offset = array_search( 'order_actions', array_keys( $columns ), true );
			$columns = array_merge(
				array_splice( $columns, 0, $offset ),
				array(
					'bewpi_invoice_number' => __( 'Invoice No.', 'woocommerce-pdf-invoices' ),
					),
				$columns
			);

			return $columns;
		}

		/**
		 * Display Invoice Number in Shop Order column (if available).
		 *
		 * @param string $column column slug.
		 */
		public function invoice_number_column_data( $column ) {
			global $post;

			if ( 'bewpi_invoice_number' !== $column ) {
				return;
			}

			if ( BEWPI_Invoice::exists( $post->ID ) ) {
				echo get_post_meta( $post->ID, '_bewpi_invoice_number', true );
			} else {
				echo '-';
			}
		}

		/**
		 * Display PDF button on "Orders" page to view invoice.
		 *
		 * @param WC_ORDER $order WooCommerce Order.
		 */
		public function add_admin_order_pdf( $order ) {
			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );

			if ( BEWPI_Invoice::exists( $order_id ) ) {
				$this->show_invoice_button(
					__( 'View invoice', 'woocommerce-pdf-invoices' ),
					$order_id,
					'view',
					array(
						'class="button shop-order-action invoice wpi"',
						'target="_blank"',
					)
				);
			}
		}

		/**
		 * Add meta box to "Order Details" page to create, view and cancel PDF invoice.
		 */
		function add_admin_order_pdf_meta_box() {
			add_meta_box( 'order_page_create_invoice', __( 'PDF Invoice', 'woocommerce-pdf-invoices' ), array(
				$this,
				'display_order_page_pdf_invoice_meta_box',
			), 'shop_order', 'side', 'high' );
		}

		/**
		 * Display invoice button html.
		 *
		 * @param string $title title attribute of button.
		 * @param int    $order_id WC_ORDER id.
		 * @param string $action action create, view or cancel.
		 * @param array  $attributes additional attributes.
		 */
		private function show_invoice_button( $title, $order_id, $action, $attributes = array() ) {
			$url = wp_nonce_url( add_query_arg( array(
				'post' => $order_id,
				'action' => 'edit',
				'bewpi_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url = apply_filters( 'bewpi_pdf_invoice_url', $url, $order_id, $action );

			printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $title, join( ' ', $attributes ), $title );
		}

		/**
		 * Display invoice actions on "Order Details" page.
		 *
		 * @param WP_Post $post as WC_Order object.
		 */
		public function display_order_page_pdf_invoice_meta_box( $post ) {
			if ( ! BEWPI_Invoice::exists( $post->ID ) ) {
				$this->show_invoice_button( __( 'Create', 'woocommerce-pdf-invoices' ), $post->ID, 'create', array( 'class="button grant_access order-page invoice wpi"' ) );
				return;
			}

			$invoice = new BEWPI_Invoice( $post->ID );

			$details = apply_filters( 'bewpi_order_page_pdf_invoice_meta_box_details', array(
				'invoice_date' => array(
					'title' => __( 'Invoiced on:', 'woocommerce-pdf-invoices' ),
					'value' => $invoice->get_formatted_invoice_date(),
				),
				'invoice_number' => array(
					'title' => __( 'Invoice number:', 'woocommerce-pdf-invoices' ),
					'value' => $invoice->get_formatted_number(),
				),
			), $invoice );

			include WPI_DIR . '/includes/admin/views/html-order-page-pdf-invoice-meta-box.php';

			// display button to view invoice.
			$this->show_invoice_button( __( 'View', 'woocommerce-pdf-invoices' ), $post->ID, 'view', array(
				'class="button grant_access order-page invoice wpi"',
				'target="_blank"',
			) );

			// display button to cancel invoice.
			/*$this->show_invoice_button( __( 'Cancel', 'woocommerce-pdf-invoices' ), $post->ID, 'cancel', array(
				'class="button grant_access order-page invoice wpi"',
				'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', 'woocommerce-pdf-invoices' ) . '\')"',
			) );*/

			do_action( 'bewpi_order_page_after_meta_box_details_end', $post->ID );
		}

		/**
		 * Shortcode to download invoice.
		 *
		 * @param array $atts shortcode attributes.
		 */
		public function download_invoice_shortcode( $atts ) {
			if ( ! isset( $atts['order_id'] ) || 0 === intval( $atts['order_id'] ) ) {
				return;
			}

			// by default order status should be Processing or Completed.
			$order = wc_get_order( $atts['order_id'] );
			if ( ! $order->is_paid() ) {
				return;
			}

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			if ( ! BEWPI_Invoice::exists( $order_id ) ) {
				return;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post' => $order_id,
				'nonce' => wp_create_nonce( 'view' ),
			) );

			$invoice = new BEWPI_Invoice( $order_id );
			$tags = array(
				'{formatted_invoice_number}' => $invoice->get_formatted_number(),
				'{order_number}'             => $order_id,
				'{formatted_invoice_date}'   => $invoice->get_formatted_invoice_date(),
				'{formatted_order_date}'     => $invoice->get_formatted_order_date(),
			);
			// find and replace placeholders.
			$title = str_replace( array_keys( $tags ), array_values( $tags ), $atts['title'] );
			printf( '<a href="%1$s">%2$s</a>', esc_attr( $url ), esc_html( $title ) );
		}

		/**
		 * Display download link on My Account page.
		 *
		 * @param array    $actions my account order table actions.
		 * @param WC_Order $order WooCommerce order object.
		 *
		 * @return mixed
		 */
		public function add_my_account_pdf( $actions, $order ) {
			$order = wc_get_order( $order );
			$general_options = get_option( 'bewpi_general_settings' );

			if ( ! $general_options['bewpi_download_invoice_account'] || ! $order->is_paid() ) {
				return $actions;
			}

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			if ( ! BEWPI_Invoice::exists( $order_id ) ) {
				return $actions;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post' => $order_id,
				'nonce' => wp_create_nonce( 'view' ),
			) );

			$invoice = new BEWPI_Invoice( $order_id );
			$actions['invoice'] = array(
				'url'  => $url,
				'name' => apply_filters( 'bewpi_my_account_pdf_name', __( 'Invoice', 'woocommerce-pdf-invoices' ), $invoice ),
			);

			return $actions;
		}

		/**
		 * Get invoice by order ID.
		 *
		 * @param int $order_id order ID.
		 *
		 * @return BEWPI_Abstract_Invoice
		 */
		public function get_invoice( $order_id ) {

			return new BEWPI_Invoice( $order_id );
		}

		/**
		 * Set order for templater directly after creation to fetch order data.
		 *
		 * @since 2.9.3 Do not use second and third parameters since several plugins do not use them. This prevents a fatal error.
		 *
		 * @param int $order_id WC_Order ID.
		 */
		public static function set_order( $order_id ) {
			$order = wc_get_order( $order_id );
			WPI()->templater()->set_order( $order );
		}

		/**
		 * Get option by group and name.
		 *
		 * @param string $group Option group name (without 'bewpi_' prefix and '_settings' suffix). Available groups are: 'general', 'template' and 'premium'.
		 * @param string $name Option name (without 'bewpi_' prefix).
		 *
		 * @return bool|mixed
		 */
		public static function get_option( $group, $name = '' ) {
			$option = apply_filters( 'bewpi_option', false, $group, $name );

			if ( false === $option ) {
				$options = get_option( 'bewpi_' . $group . '_settings' );
				if ( false === $options ) {
					return false;
				}

				if ( ! isset( $options[ 'bewpi_' . $name ] ) ) {
					return false;
				}

				$option = $options[ 'bewpi_' . $name ];
			}

			$hook = sprintf( 'bewpi_pre_option-%1$s-%2$s', $group, $name );

			return apply_filters( $hook, $option );
		}

		/**
		 * Templater instance.
		 *
		 * @return BEWPI_Template.
		 */
		public function templater() {
			return BEWPI_Template::instance();
		}

		/**
		 * Logger instance;
		 *
		 * @return BEWPI_Debug_Log.
		 */
		public function logger() {
			return BEWPI_Debug_Log::instance();
		}
	}
}
