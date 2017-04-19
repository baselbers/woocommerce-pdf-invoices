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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		public function __construct() {
			$this->define_constants();
			$this->load_plugin_textdomain();
			do_action( 'bewpi_after_init_settings' );
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
			define( 'BEWPI_URL', plugins_url( '', BEWPI_FILE ) . '/' );
			/**
			 * Templates dir.
			 *
			 * @deprecated instead use WPI_DIR.
			 *
			 * @since 2.7.0
			 */
			define( 'BEWPI_TEMPLATES_DIR', BEWPI_DIR . 'includes/templates' );
			/**
			 * Custom templates directory.
			 *
			 * @deprecated instead use WPI_TEMPLATES_DIR.
			 *
			 * @since 2.7.0 moved to uploads/woocommerce-pdf-invoices/invoices.
			 */
			define( 'BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-templates/invoices' );
			/**
			 * Attachments/invoices directory.
			 *
			 * @deprecated use WPI_ATTACHMENTS_DIR instead.
			 *
			 * @since 2.7.0 moved to uploads/woocommerce-pdf-invoices/attachments.
			 */
			define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices' );

			define( 'WPI_URL', untrailingslashit( plugins_url( '', WPI_FILE ) ) );
			define( 'WPI_UPLOADS_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices' );
			define( 'WPI_TEMPLATES_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices/templates' );
			define( 'WPI_ATTACHMENTS_DIR', $wp_upload_dir['basedir'] . '/woocommerce-pdf-invoices/attachments' );
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
		 * Initialize hooks and filters.
		 *
		 * @since 2.5.0
		 */
		private function init_hooks() {
			if ( ! is_admin() ) {
				add_action( 'init', array( $this, 'frontend_pdf_callback' ) );
			}

			if ( ! file_exists( WPI_UPLOADS_DIR ) ) {
				add_action( 'admin_init', array( $this, 'setup_directories' ) );
			}

			add_action( 'admin_init', array( $this, 'admin_pdf_callback' ) );
			add_action( 'admin_init', array( $this, 'admin_init_hooks' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

			// woocommerce.
			add_action( 'admin_menu', array( $this, 'add_wc_submenu_options_page' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_admin_order_pdf' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_admin_order_pdf_meta_box' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ), 999 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_number_column_data' ), 2 );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_my_account_pdf' ), 10, 2 );
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
			// Add plugin action links on "Plugins" page.
			add_filter( 'plugin_action_links_' . plugin_basename( WPI_FILE ), array( $this, 'add_plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
			// delete invoice if deleting order.
			add_action( 'wp_trash_post', array( $this, 'delete_invoice' ), 10, 1 );
			add_action( 'before_delete_post', array( $this, 'delete_invoice' ), 10, 1 );
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
			$order_id = bewpi_get_id( $order );
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
					$packing_slip = new BEWPI_Packing_Slip( $order_id );
					$packing_slip->generate( 'D' );
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
		 * Need to delete invoice after deleting WooCommerce order or else we get invoice number conflicts.
		 *
		 * @param int $post_id Post ID.
		 */
		public function delete_invoice( $post_id ) {
			$type = get_post_type( $post_id );
			// is post a WooCommerce order?
			if ( 'shop_order' === $type ) {
				BEWPI_Invoice::delete( $post_id );
			}
		}

		/**
		 * Add submenu to WooCommerce menu and display options page.
		 */
		public function add_wc_submenu_options_page() {
			add_submenu_page( 'woocommerce', __( 'Invoices', 'woocommerce-pdf-invoices' ), __( 'Invoices', 'woocommerce-pdf-invoices' ), 'manage_options', 'bewpi-invoices', array(
				$this,
				'options_page',
			) );
		}

		/**
		 * Admin scripts
		 */
		public function load_admin_scripts() {
			wp_enqueue_script( 'bewpi_admin_settings_script', WPI_URL . '/assets/js/admin.js', array(), WPI_VERSION, true );
			wp_localize_script( 'bewpi_admin_settings_script', 'BEWPI_AJAX', array(
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'deactivation_nonce'    => wp_create_nonce( 'deactivation-notice' ),
					'dismiss_nonce'         => wp_create_nonce( 'dismiss-notice' ),
				)
			);
			wp_register_style( 'bewpi_admin_settings_css', WPI_URL . '/assets/css/admin.css', false, WPI_VERSION );
			wp_enqueue_style( 'bewpi_admin_settings_css' );
		}

		/**
		 * WooCommerce PDF Invoices settings page.
		 */
		public function options_page() {
			$tabs = apply_filters( 'bewpi_settings_tabs', array(
				'bewpi_general_settings' => __( 'General', 'woocommerce-pdf-invoices' ),
				'bewpi_template_settings' => __( 'Template', 'woocommerce-pdf-invoices' ),
			) );

			$current_tab = 'bewpi_general_settings';
			if ( isset( $_GET['tab'] ) ) {
				$current_tab = sanitize_key( $_GET['tab'] );
			} ?>
			<div class="wrap">
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tabs as $tab_key => $tab_caption ) {
						$active = $current_tab === $tab_key ? 'nav-tab-active' : '';
						printf( '<a class="nav-tab %1$s" href="?page=bewpi-invoices&tab=%2$s">%3$s</a>', esc_attr( $active ), esc_attr( $tab_key ), esc_html( $tab_caption ) );
					} ?>
				</h2>
				<form class="bewpi-settings-form" method="post" action="options.php"
				      enctype="multipart/form-data">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $current_tab ); ?>
					<?php do_settings_sections( $current_tab ); ?>
					<?php submit_button(); ?>
				</form>

				<?php if ( ! is_plugin_active( 'woocommerce-pdf-invoices-premium/bootstrap.php' ) ) {
					include BEWPI_DIR . 'includes/admin/views/html-sidebar.php';
				} ?>
			</div>
			<?php
			// add rate plugin text in footer.
			add_filter( 'admin_footer_text', array( $this, 'plugin_review_text' ), 50 );
			add_filter( 'update_footer', array( $this, 'plugin_version' ), 50 );
		}

		/**
		 * Add rate plugin text to footer of settings page.
		 *
		 * @return string
		 */
		public function plugin_review_text() {
			return sprintf( __( 'If you like <strong>WooCommerce PDF Invoices</strong> please leave us a <a href="%s">★★★★★</a> rating. A huge thank you in advance!', 'woocommerce-pdf-invoices' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform' );
		}

		/**
		 * Plugin version text in footer of settings page.
		 *
		 * @return string
		 */
		public function plugin_version() {
			return sprintf( __( 'Version %s', 'woocommerce-pdf-invoices' ), WPI_VERSION );
		}

		/**
		 * Add "Email It In" email address as BCC to WooCommerce email.
		 *
		 * @param array  $headers email headers.
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
			$order_id = bewpi_get_id( $order );
			$transient_name = sprintf( 'bewpi_emailitin_processed-%1$s', $order_id );
			if ( get_transient( $transient_name ) ) {
				return $headers;
			}

			$general_options = get_option( 'bewpi_general_settings' );
			$emailitin_account = $general_options['bewpi_email_it_in_account'];
			// Email It In option enabled?
			if ( ! $general_options['bewpi_email_it_in'] || empty( $emailitin_account ) ) {
				return $headers;
			}

			// check if current email type is enabled.
			if ( ! isset( $general_options[ $status ] ) || ! $general_options[ $status ] ) {
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
		 * @return array|mixed|void
		 */
		public function attach_invoice_to_email( $attachments, $status, $order ) {
			// only attach to emails with WC_Order object.
			if ( ! $order instanceof WC_Order ) {
				return $attachments;
			}

			$skip = apply_filters( 'bewpi_skip_invoice_generation', false, $status, $order );
			if ( $skip ) {
				return $attachments;
			}

			$general_options = get_option( 'bewpi_general_settings' );
			if ( $order->get_total() === 0.00 && (bool) $general_options['bewpi_disable_free_products'] ) {
				return $attachments;
			}

			// WC backwards compatibility.
			$payment_method = method_exists( 'WC_Order', 'get_payment_method' ) ? $order->get_payment_method() : $order->get_payment_method;
			// payment methods for which the invoice generation should be cancelled.
			$payment_methods = apply_filters( 'bewpi_attach_invoice_excluded_payment_methods', array() );
			if ( in_array( $payment_method, $payment_methods, true ) ) {
				return $attachments;
			}

			// check if email is enabled.
			if ( ! isset( $general_options[ $status ] ) || ! $general_options[ $status ] ) {
				return $attachments;
			}

			$order_id = bewpi_get_id( $order );
			$invoice = new BEWPI_Invoice( $order_id );
			if ( ! $invoice->exists( $order_id ) ) {
				$full_path = $invoice->generate();
			} else {
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
				array( 'bewpi_invoice_number' => __( 'Invoice No.', 'woocommerce-pdf-invoices' ) ),
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
			$order_id = bewpi_get_id( $order );

			if ( BEWPI_Invoice::exists( $order_id ) ) {
				$this->show_invoice_button(
					__( 'View invoice', 'woocommerce-pdf-invoices' ),
					$order_id,
					'view',
					array(
						'class="button tips bewpi-admin-order-create-invoice-btn"',
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
				'display_admin_order_pdf_actions',
			), 'shop_order', 'side', 'high' );
		}

		/**
		 * Display invoice date and formatted number on "Order Details" page.
		 *
		 * @param string $date date of invoice.
		 * @param int    $number formatted invoice number.
		 */
		private function show_invoice_number_info( $date, $number ) {
			?>
			<table class="invoice-info" width="100%">
				<tr>
					<td><?php echo esc_html( __( 'Invoiced on:', 'woocommerce-pdf-invoices' ) ); ?></td>
					<td align="right"><b><?php echo esc_html( $date ); ?></b></td>
				</tr>
				<tr>
					<td><?php echo esc_html( __( 'Invoice number:', 'woocommerce-pdf-invoices' ) ); ?></td>
					<td align="right"><b><?php echo esc_html( $number ); ?></b></td>
				</tr>
			</table>
			<?php
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
		public function display_admin_order_pdf_actions( $post ) {
			if ( ! BEWPI_Invoice::exists( $post->ID ) ) {
				$this->show_invoice_button( __( 'Create', 'woocommerce-pdf-invoices' ), $post->ID, 'create', array( 'class="invoice-btn button grant_access"' ) );
				return;
			}

			$invoice = new BEWPI_Invoice( $post->ID );

			// invoice exists so display invoice info.
			$this->show_invoice_number_info( $invoice->get_formatted_invoice_date(), $invoice->get_formatted_number() );

			// display button to view invoice.
			$this->show_invoice_button( __( 'View', 'woocommerce-pdf-invoices' ), $post->ID, 'view', array(
				'class="invoice-btn button grant_access"',
				'target="_blank"',
			) );

			// display button to cancel invoice.
			$this->show_invoice_button( __( 'Cancel', 'woocommerce-pdf-invoices' ), $post->ID, 'cancel', array(
				'class="invoice-btn button grant_access"',
				'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', 'woocommerce-pdf-invoices' ) . '\')"',
			) );
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

			$order_id = bewpi_get_id( $order );
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

			$order_id = bewpi_get_id( $order );
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
				'name' => apply_filters( 'bewpi_my_account_pdf_name', sprintf( __( 'Invoice %s', 'woocommerce-pdf-invoices' ), $invoice->get_formatted_number() ) ),
			);

			return $actions;
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
