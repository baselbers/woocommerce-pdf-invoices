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
		 * Plugin slug.
		 */
		const PLUGIN_SLUG = 'woocommerce-pdf-invoices';

		/**
		 * Prefix
		 */
		const PREFIX = 'wpi_';

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
		 * Setup multisite sepcific upload dir.
		 *
		 * @param array $upload_dir uploads dir data.
		 *
		 * @return array
		 */
		public function setup_multisite_upload_dir( $upload_dir ) {
			$upload_dir['path'] = $upload_dir['basedir'] . $upload_dir['subdir'];
			$upload_dir['url']  = $upload_dir['baseurl'] . $upload_dir['subdir'];

			return $upload_dir;
		}

		/**
		 * WooCommerce Constructor.
		 */
		private function __construct() {
			if ( is_multisite() ) {
				add_filter( 'upload_dir', array( $this, 'setup_multisite_upload_dir' ) );
			}

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
			$locale = get_locale();
			if ( is_admin() ) {
				$locale = get_user_locale();
			}

			$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce-pdf-invoices' );

			if ( ! load_textdomain( 'woocommerce-pdf-invoices', WP_LANG_DIR . '/loco/plugins/woocommerce-pdf-invoices-' . $locale . '.mo' ) ) {
				load_plugin_textdomain( 'woocommerce-pdf-invoices', false, 'woocommerce-pdf-invoices/lang' );
			}
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
			add_filter( 'plugin_action_links_' . plugin_basename( WPI_FILE ), array(
				$this,
				'add_plugin_action_links',
			) );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );

			BEWPI_Abstract_Settings::init_hooks();

			// Fair enough to let people disable relatively 'annoying' notices.
			if ( defined( 'DISABLE_NAG_NOTICES' ) && false === DISABLE_NAG_NOTICES ) {
				BEWPI_Admin_Notices::init_hooks();
			}

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
			$current_year = date_i18n( 'Y', current_time( 'timestamp' ) );
			$directories  = apply_filters( 'bewpi_uploads_directories', array(
				WPI_UPLOADS_DIR . '/attachments/'                       => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/attachments/' . $current_year . '/' => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/fonts/'                             => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/mpdf/ttfontdata/'                   => array(
					'.htaccess',
					'index.php',
				),
				WPI_UPLOADS_DIR . '/templates/invoice/simple/'          => array(),
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
		 * Setup default options.
		 */
		public function setup_options() {
			BEWPI_Abstract_Settings::load_setting_tabs();

			foreach ( BEWPI_Abstract_Settings::$setting_tabs as $id => $tab ) {
				new $tab['class']();
			}
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
			$order   = wc_get_order( $post_id );
			if ( ! $order ) {
				wp_die( 'Order not found.' );
			}

			// check if user has ordered order.
			$user             = wp_get_current_user();
			$order_id         = BEWPI_WC_Order_Compatibility::get_id( $order );
			$customer_user_id = (int) get_post_meta( $order_id, '_customer_user', true );
			if ( $user->ID !== $customer_user_id ) {
				wp_die( 'Access denied' );
			}

			$full_path = BEWPI_Abstract_Invoice::exists( $order_id );
			if ( false === $full_path ) {
				wp_die( 'PDF invoice not found.' );
			}

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
			$nonce  = sanitize_key( $_GET['nonce'] );
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Invalid request.' );
			}

			// validate allowed user roles.
			$user          = wp_get_current_user();
			$allowed_roles = apply_filters( 'bewpi_allowed_roles_to_download_invoice', array(
				'administrator',
				'shop_manager',
			) );

			if ( ! array_intersect( $allowed_roles, $user->roles ) && ! user_can( $user, 'manage_network_snippets' ) ) {
				wp_die( 'Access denied' );
			}

			$order_id = intval( $_GET['post'] );

			// execute invoice action.
			switch ( $action ) {
				case 'view':
					$full_path = BEWPI_Abstract_Invoice::exists( $order_id );
					if ( false === $full_path ) {
						wp_die( 'PDF invoice not found.' );
					}

					BEWPI_Invoice::view( $full_path );
					break;
				case 'view_packing_slip':
					$view_mode    = 'download' === WPI()->get_option( 'general', 'view_pdf' ) ? 'D' : 'I';
					$packing_slip = new BEWPI_Packing_Slip( $order_id );
					$packing_slip->generate( $view_mode );
					break;
				case 'delete':
					BEWPI_Invoice::delete( $order_id );
					break;
				case 'create':
					$invoice = new BEWPI_Invoice( $order_id );
					$invoice->generate();
					break;
				case 'update':
					$invoice = new BEWPI_Invoice( $order_id );
					$invoice->update();
					break;
				case 'debug':
					$invoice   = new BEWPI_Invoice( $order_id );
					$full_path = $invoice->update();
					BEWPI_Invoice::view( $full_path );
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
		public function add_plugin_action_links( $links ) {
			// add settings link.


			$settings_url   = add_query_arg( array( 'page' => WPI()->get_plugin_slug() ), admin_url( 'admin.php' ) );
			$settings_title = __( 'Settings', 'woocommerce-pdf-invoices' );
			array_unshift( $links, sprintf( '<a href="%1$s">%2$s</a>', $settings_url, $settings_title ) );

			return $links;
		}

		/**
		 * Add links to row meta on plugins.php page.
		 *
		 * @param array  $links row meta.
		 * @param string $file  plugin basename.
		 *
		 * @return array
		 */
		public static function add_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( WPI_FILE ) === $file ) {
				// add premium plugin link.
				$premium_url   = 'http://wcpdfinvoices.com';
				$premium_title = __( 'Premium', 'woocommerce-pdf-invoices' );
				$links[]       = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $premium_url, $premium_title );
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
				'woocommerce_page_' . WPI()->get_plugin_slug(),
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

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			if ( in_array( $screen_id, self::get_screen_ids(), true ) ) {
				wp_enqueue_style( 'bewpi_settings_css' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
			}
		}

		/**
		 * Load admin scripts.
		 */
		public function admin_scripts() {
			wp_register_script( 'bewpi_admin_js', WPI_URL . '/assets/js/admin.js', array(), WPI_VERSION, true );
			wp_enqueue_script( 'bewpi_admin_js' );
			wp_localize_script( 'bewpi_admin_js', 'BEWPI_AJAX', array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'deactivation_nonce' => wp_create_nonce( 'deactivation-notice' ),
					'dismiss_nonce'      => wp_create_nonce( 'dismiss-notice' ),
				)
			);
			wp_register_script( 'bewpi_settings_js', WPI_URL . '/assets/js/settings.js', array(), WPI_VERSION, true );
			wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.js', array(
				'jquery',
				'jquery-ui-sortable',
				version_compare( WC()->version, '3.2.0', '>=' ) ? 'selectWoo' : 'select2',
			), WC()->version );

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
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
		 * @param string $status  email name.
		 * @param object $order   WooCommerce order.
		 *
		 * @return string
		 */
		public function add_emailitin_as_recipient( $headers, $status, $order ) {
			// Only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $headers;
			}

			// make sure invoice got only send once for each order.
			$order_id       = BEWPI_WC_Order_Compatibility::get_id( $order );
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

			// check if email is enabled.
			if ( ! WPI()->is_email_enabled( $status ) ) {
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
		 * @param string $status      name of email.
		 * @param object $order       order.
		 *
		 * @return array.
		 */
		public function attach_invoice_to_email( $attachments, $status, $order ) {
			// only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $attachments;
			}

			// check if email is enabled.
			if ( ! WPI()->is_email_enabled( $status ) ) {
				return $attachments;
			}

			// Skip invoice generation.
			$skip = apply_filters( 'bewpi_skip_invoice_generation', false, $status, $order );
			if ( $skip ) {
				return $attachments;
			}

			// Skip invoice generation for free orders.
			if ( 0.00 === (double) WPI()->get_prop( $order, 'total' ) && WPI()->get_option( 'general', 'disable_free_products' ) ) {
				return $attachments;
			}

			// payment methods for which the invoice generation should be cancelled.
			$payment_methods = apply_filters( 'bewpi_attach_invoice_excluded_payment_methods', array() );
			if ( in_array( WPI()->get_prop( $order, 'payment_method' ), $payment_methods, true ) ) {
				return $attachments;
			}

			$order_id  = BEWPI_WC_Order_Compatibility::get_id( $order );
			$invoice   = new BEWPI_Invoice( $order_id );
			$full_path = $invoice->get_full_path();
			if ( ! $full_path ) {
				$full_path = $invoice->generate();
			} elseif ( ! $invoice->is_sent() ) {
				// Only update PDF invoice when client doesn't got it already.
				$full_path = $invoice->update();
			}

			if ( apply_filters( 'wpi_skip_pdf_invoice_attachment', false, $status, $order ) ) {
				return $attachments;
			}

			// Attach invoice to email.
			$attachments[] = $full_path;

			/**
			 * Check if current email is a customer email.
			 *
			 * @var WC_Email $email
			 */
			$is_customer_email = false;
			foreach ( WC()->mailer()->get_emails() as $email ) {
				if ( $email->id === $status ) {
					$is_customer_email = $email->is_customer_email();
					break;
				}
			}

			// Only mark the invoice as sent for customer emails.
			if ( $is_customer_email ) {
				update_post_meta( $order_id, 'bewpi_pdf_invoice_sent', 1 );
			}

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
			if ( ! (bool) WPI()->get_option( 'general', 'invoice_number_column' ) ) {
				return $columns;
			}

			$actions_column = BEWPI_WC_Core_Compatibility::is_wc_version_gt( '3.2.6' ) ? 'wc_actions' : 'order_actions';
			// Splice columns at 'Actions' column, add 'Invoice No.' column and merge with last part.
			$offset  = array_search( $actions_column, array_keys( $columns ), true );
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
				echo esc_html( get_post_meta( $post->ID, '_bewpi_invoice_number', true ) );
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
		 * @param string $title      title attribute of button.
		 * @param int    $order_id   WC_ORDER id.
		 * @param string $action     action create, view or cancel.
		 * @param array  $attributes additional attributes.
		 */
		private function show_invoice_button( $title, $order_id, $action, $attributes = array() ) {
			$url = wp_nonce_url( add_query_arg( array(
				'post'         => $order_id,
				'action'       => 'edit',
				'bewpi_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url        = apply_filters( 'bewpi_pdf_invoice_url', $url, $order_id, $action );
			$attr_title = $title . ' ' . __( 'PDF Invoice', 'woocommerce-pdf-invoices' );

			printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $attr_title, join( ' ', $attributes ), $title );
		}

		/**
		 * Display invoice actions on "Order Details" page.
		 *
		 * @param WP_Post $post as WC_Order object.
		 */
		public function display_order_page_pdf_invoice_meta_box( $post ) {
			$invoice = new BEWPI_Invoice( $post->ID );

			if ( ! $invoice->get_full_path() ) {

				$this->show_invoice_button( __( 'Create', 'woocommerce-pdf-invoices' ), $post->ID, 'create', array( 'class="button grant_access order-page invoice wpi"' ) );

			} else {

				$details = array(
					'invoice_date'   => array(
						'title' => __( 'Date:', 'woocommerce-pdf-invoices' ),
						'value' => $invoice->get_formatted_date(),
					),
					'invoice_number' => array(
						'title' => __( 'Number:', 'woocommerce-pdf-invoices' ),
						'value' => $invoice->get_formatted_number(),
					),
				);

				// Backporting --only show when meta exists.
				$is_sent = $invoice->is_sent();
				if ( false !== get_post_meta( $post->ID, 'bewpi_pdf_invoice_sent', true ) ) {
					$details['invoice_sent'] = array(
						'title' => __( 'Sent?', 'woocommerce-pdf-invoices' ),
						'value' => (bool) $is_sent ? __( 'Yes', 'woocommerce-pdf-invoices' ) : __( 'No', 'woocommerce-pdf-invoices' ),
					);
				}

				$details = apply_filters( 'bewpi_order_page_pdf_invoice_meta_box_details', $details, $invoice );
				include WPI_DIR . '/includes/admin/views/html-order-page-pdf-invoice-meta-box.php';

				echo '<p class="invoice-actions">';

				// display button to view invoice.
				$this->show_invoice_button( __( 'View', 'woocommerce-pdf-invoices' ), $post->ID, 'view', array(
					'class="button grant_access order-page invoice wpi"',
					'target="_blank"',
				) );

				// PDF invoice should not be changed when it has been sent to the client already.
				if ( ! $is_sent ) {
					$this->show_invoice_button( __( 'Update', 'woocommerce-pdf-invoices' ), $post->ID, 'update', array(
						'class="button grant_access order-page invoice wpi"',
					) );

					// Create confirm message when deleting PDF invoice.
					$message = __( 'Are you sure to delete the PDF invoice?', 'woocommerce-pdf-invoices' );
					if ( 'sequential_number' === BEWPI_Invoice::get_number_type() && $invoice->get_number() !== BEWPI_Abstract_Invoice::get_max_invoice_number() ) {

						/* translators: $d: invoice number */
						$message .= ' ' . sprintf( __( 'You will be missing a PDF invoice with invoice number %d and thus creating an accounting gap!', 'woocommerce-pdf-invoices' ), $invoice->get_number() );

						/* translators: %s: plugin name. */
						$message .= ' ' . apply_filters( 'wpi_delete_invoice_confirm_message', sprintf( __( 'Instead consider using Cancelled PDF invoices with %s.', 'woocommerce-pdf-invoices' ), 'WooCommerce PDF Invoices Premium' ) );

					}

					// display button to delete invoice.
					$this->show_invoice_button( __( 'Delete', 'woocommerce-pdf-invoices' ), $post->ID, 'delete', array(
						'class="button grant_access order-page invoice wpi"',
						'onclick="return confirm(\'' . $message . '\');"',
					) );
				}

				// display button to view invoice in debug mode.
				if ( (bool) WPI()->get_option( 'debug', 'mpdf_debug' ) ) {
					$this->show_invoice_button( __( 'Debug', 'woocommerce-pdf-invoices' ), $post->ID, 'debug', array(
						'class="button grant_access order-page invoice wpi"',
						'target="_blank"',
					) );
				}

				echo '</p>';

			}

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
			$invoice  = new BEWPI_Invoice( $order_id );
			if ( ! $invoice->get_full_path() ) {
				return;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post'         => $order_id,
				'nonce'        => wp_create_nonce( 'view' ),
			) );

			$tags = array(
				'{formatted_invoice_number}' => $invoice->get_formatted_number(),
				'{order_number}'             => $order_id,
				'{formatted_invoice_date}'   => $invoice->get_formatted_date(),
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
		 * @param WC_Order $order   WooCommerce order object.
		 *
		 * @return mixed
		 */
		public function add_my_account_pdf( $actions, $order ) {
			$order = wc_get_order( $order );

			if ( ! WPI()->get_option( 'general', 'download_invoice_account' ) || ! $order->is_paid() ) {
				return $actions;
			}

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );
			$invoice  = new BEWPI_Invoice( $order_id );
			if ( ! $invoice->get_full_path() ) {
				return $actions;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post'         => $order_id,
				'nonce'        => wp_create_nonce( 'view' ),
			) );

			$actions['invoice'] = array(
				'url'  => $url,
				'name' => apply_filters( 'bewpi_my_account_pdf_name', __( 'Invoice', 'woocommerce-pdf-invoices' ), $invoice ),
			);

			return $actions;
		}

		/**
		 * Check if email is enabled.
		 *
		 * @param string $email Email ID.
		 *
		 * @return bool
		 */
		public function is_email_enabled( $email ) {
			return in_array( $email, (array) $this->get_option( 'general', 'email_types' ), true );
		}

		/**
		 * Get plugin install date.
		 *
		 * @return DateTime|bool
		 */
		public function get_install_date() {
			if ( version_compare( WPI_VERSION, '2.6.1' ) >= 0 ) {
				// since 2.6.1+ option name changed and date has mysql format.
				return DateTime::createFromFormat( 'Y-m-d H:i:s', get_site_option( 'bewpi_install_date' ) );
			}

			return DateTime::createFromFormat( 'Y-m-d', get_site_option( 'bewpi-install-date' ) );
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
		 * @param string $name  Option name (without 'bewpi_' prefix).
		 *
		 * @return bool|mixed
		 */
		public static function get_option( $group, $name = '' ) {
			$option_name = apply_filters( 'wpi_option_name', array(
				'prefix' => 'bewpi_',
				'group'  => $group,
				'suffix' => '_settings',
			) );
			//$option = apply_filters( 'bewpi_option', false, $group, $name );

			$options = get_option( join( '', $option_name ) );
			if ( false === $options ) {
				return false;
			}

			if ( ! isset( $options[ 'bewpi_' . $name ] ) ) {
				return false;
			}

			$option = $options[ 'bewpi_' . $name ];
			$hook   = sprintf( 'bewpi_pre_option-%1$s-%2$s', $group, $name );

			return apply_filters( $hook, $option );
		}

		/**
		 * Get tax or vat label.
		 *
		 * @param bool $incl  Including tax or vat.
		 * @param bool $small text font size.
		 *
		 * @return string
		 */
		public function tax_or_vat_label( $incl = true, $small = true ) {
			if ( $incl ) {
				$label = WC()->countries->inc_tax_or_vat();
			} else {
				$label = WC()->countries->ex_tax_or_vat();
			}

			if ( $small ) {
				$label = sprintf( '<small class="tax_label">%s</small>', $label );
			}

			return $label;
		}

		/**
		 * Get order currency.
		 *
		 * @param WC_Order $order order object.
		 *
		 * @return string
		 */
		public function get_currency( $order ) {
			return BEWPI_WC_Order_Compatibility::get_currency( $order );
		}

		/**
		 * Get order property.
		 *
		 * @param WC_Order $order   order object.
		 * @param string   $prop    order property.
		 * @param string   $context display context.
		 *
		 * @return mixed
		 */
		public function get_prop( $order, $prop, $context = 'edit' ) {
			return BEWPI_WC_Order_Compatibility::get_prop( $order, $prop, $context );
		}

		/**
		 * Get order meta.
		 *
		 * @param WC_Order $order    Order object.
		 * @param string   $meta_key Post meta key.
		 *
		 * @return bool/string
		 */
		public function get_meta( $order, $meta_key ) {
			return get_post_meta( BEWPI_WC_Order_Compatibility::get_id( $order ), $meta_key, true );
		}

		/**
		 * Get all total rows before subtotal.
		 *
		 * @return array
		 */
		public function get_totals_before_subtotal() {
			$selected_totals = self::get_option( 'template', 'totals' );
			$subtotal        = array_search( 'subtotal_ex_vat', $selected_totals, true );

			return array_slice( $selected_totals, 0, $subtotal );
		}

		/**
		 * Check if order has only virtual products.
		 *
		 * @param WC_Order $order order object.
		 *
		 * @return bool
		 */
		public function has_only_virtual_products( $order ) {
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				/**
				 * Product order item object.
				 *
				 * @var WC_Order_Item_Product $item product.
				 */
				$product = $item->get_product();
				if ( ! $product || ! $product->is_virtual() ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Get allowed roles to download/view invoice.
		 *
		 * @return array.
		 */
		public function get_allowed_roles() {
			return apply_filters( 'wpi_allowed_roles_to_download_invoice', array( 'administrator', 'shop_manager' ) );
		}

		/**
		 * Get plugin prefix.
		 *
		 * @return string
		 */
		public function get_plugin_prefix() {
			return self::PREFIX;
		}

		/**
		 * Get plugin slug.
		 *
		 * @return string
		 */
		public function get_plugin_slug() {
			return self::PLUGIN_SLUG;
		}

		/**
		 * Get formatted store address.
		 *
		 * @return string
		 */
		public function get_formatted_base_address() {
			$address = array(
				'company'   => self::get_option( 'template', 'company_name' ),
				'address_1' => WC()->countries->get_base_address(),
				'address_2' => WC()->countries->get_base_address_2(),
				'city'      => WC()->countries->get_base_city(),
				'state'     => WC()->countries->get_base_state(),
				'postcode'  => WC()->countries->get_base_postcode(),
				'country'   => WC()->countries->get_base_country(),
			);

			return WC()->countries->get_formatted_address( $address ) . '<br>';
		}

		/**
		 * Get formatted company address.
		 *
		 * @return string
		 */
		public function get_formatted_company_address() {
			$company_phone         = self::get_option( 'template', 'company_phone' );
			$company_email_address = self::get_option( 'template', 'company_email_address' );
			$company_vat_id        = self::get_option( 'template', 'company_vat_id' );

			if ( BEWPI_WC_Core_Compatibility::is_wc_version_gte_3_0() ) {
				$formatted_company_address = self::get_formatted_base_address();
			} else {
				$formatted_company_address = nl2br( self::get_option( 'template', 'company_address' ) ) . '<br>';
			}

			if ( ! empty( $company_phone ) ) {
				$formatted_company_address .= sprintf( __( 'Phone: %s', 'woocommerce-pdf-invoices' ), $company_phone ) . '<br>';
			}

			if ( ! empty( $company_email_address ) ) {
				$formatted_company_address .= sprintf( __( 'Email: %s', 'woocommerce-pdf-invoices' ), $company_email_address ) . '<br>';
			}

			if ( ! empty( $company_vat_id ) ) {
				$formatted_company_address .= sprintf( __( 'VAT ID: %s', 'woocommerce-pdf-invoices' ), $company_vat_id );
			}

			return $formatted_company_address;
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
