<?php
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
			$this->includes();
			$this->load_textdomain();
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

			define( 'BEWPI_URL', plugins_url( '', BEWPI_FILE ) . '/' );
			define( 'BEWPI_TEMPLATES_DIR', BEWPI_DIR . 'includes/templates/' );
			define( 'BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-templates/invoices/' );
			define( 'BEWPI_INVOICES_DIR', $wp_upload_dir['basedir'] . '/bewpi-invoices/' );
		}

		/**
		 * Include core backend and frontend files.
		 *
		 * @since 2.5.0
		 */
		public function includes() {
			// Constants are not resolving in PHPStorm while requiring files.
			// Issue will be fixed in PHPStorm version 2016.3 as stated https://youtrack.jetbrains.com/issue/WI-31754.
			require_once BEWPI_DIR . 'includes/abstracts/abstract-bewpi-document.php';
			require_once BEWPI_DIR . 'includes/abstracts/abstract-bewpi-invoice.php';
			require_once BEWPI_DIR . 'includes/abstracts/abstract-bewpi-setting.php';
			require_once BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-general.php';
			require_once BEWPI_DIR . 'includes/admin/settings/class-bewpi-admin-settings-template.php';
			require_once BEWPI_DIR . 'includes/admin/class-bewpi-admin-notices.php';
			require_once BEWPI_DIR . 'includes/class-bewpi-invoice.php';
		}

		/**
		 * Load plugin textdomain from /lang dir.
		 *
		 * @since 2.5.0
		 */
		private function load_textdomain() {
			$lang_dir = basename( dirname( BEWPI_FILE ) ) . '/lang';
			load_plugin_textdomain( 'woocommerce-pdf-invoices', false, apply_filters( 'bewpi_lang_dir', $lang_dir ) );
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

			add_action( 'admin_init', array( $this, 'admin_pdf_callback' ) );
			add_action( 'admin_init', array( $this, 'admin_init_hooks' ) );
			add_action( 'admin_init', array( $this, 'setup_directories' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

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
			add_filter( 'plugin_action_links_' . BEWPI_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
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
			if ( BEWPI_PLUGIN_BASENAME === $file ) {
				// add premium plugin link.
				$premium_url = 'http://wcpdfinvoices.com';
				$premium_title = __( 'Premium', 'woocommerce-pdf-invoices' );
				$links[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $premium_url, $premium_title );
			}

			return $links;
		}

		/**
		 * Create Shop Order column for Invoice Number.
		 *
		 * @param array $columns Shop Order columns.
		 *
		 * @return array
		 */
		public function add_invoice_number_column( $columns ) {
			// invoice number column enabled by user?
			$general_settings = get_option( 'bewpi_general_settings' );
			if ( empty( $general_settings['bewpi_invoice_number_column'] ) ) {
				return $columns;
			}

			// put the column after the Status column.
			$new_columns = array_slice( $columns, 0, 2, true ) +
			               array( 'bewpi_invoice_number' => __( 'Invoice No.', 'woocommmerce-pdf-invoices' ) ) +
			               array_slice( $columns, 2, count( $columns ) - 1, true );
			return $new_columns;
		}

		/**
		 * Display Invoice Number in Shop Order column (if available).
		 *
		 * @param string $column column slug.
		 */
		public function invoice_number_column_data( $column ) {
			global $post;

			if ( 'bewpi_invoice_number' === $column ) {
				echo get_post_meta( $post->ID, '_bewpi_invoice_number', true );
			}
		}

		/**
		 * Frontend pdf actions callback.
		 * Customers only have permission to view invoice, so invoice should be created by system/admin.
		 */
		public function frontend_pdf_callback() {
			if ( ! isset( $_GET['bewpi_action'] ) || ! isset( $_GET['post'] ) || ! isset( $_GET['nonce'] ) ) {
				return;
			}

			// verify nonce.
			$action = sanitize_key( $_GET['bewpi_action'] );
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
			$customer_user_id = (int) get_post_meta( $order->id, '_customer_user', true );
			if ( $user->ID !== $customer_user_id ) {
				wp_die( 'Access denied' );
			}

			$invoice = new BEWPI_Invoice( $order->id );
			$invoice->view();
		}

		/**
		 * Admin pdf actions callback.
		 * Within admin by default only administrator and shop managers have permission to view, create, cancel invoice.
		 */
		public function admin_pdf_callback() {
			if ( ! isset( $_GET['bewpi_action'] ) || ! isset( $_GET['post'] ) || ! isset( $_GET['nonce'] ) ) {
				return;
			}

			// sanitize data and verify nonce.
			$action = sanitize_key( $_GET['bewpi_action'] );
			$nonce = sanitize_key( $_GET['nonce'] );
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Invalid request.' );
			}

			// validate woocommerce order.
			$post_id = intval( $_GET['post'] );
			$order = wc_get_order( $post_id );
			if ( ! $order ) {
				wp_die( 'Order not found.' );
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

			// execute invoice action.
			$invoice = new BEWPI_Invoice( $order->id );
			switch ( $action ) {
				case 'view':
					$invoice->view();
					break;
				case 'cancel':
					$invoice->delete();
					break;
				case 'create':
					$invoice->save( 'F' );
					break;
			}
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
				$invoice = new BEWPI_Invoice( $post_id );
				$invoice->delete();
			}
		}

		/**
		 * Creates invoices dir in uploads folder.
		 */
		public function setup_directories() {
			// make invoices dir.
			$current_year_dir = BEWPI_INVOICES_DIR . date_i18n( 'Y', current_time( 'timestamp' ) ) . '/';
			wp_mkdir_p( $current_year_dir );

			// prevent direct access to invoices.
			if ( ! file_exists( $current_year_dir . '.htaccess' ) ) {
				copy( BEWPI_DIR . 'tmp/.htaccess', $current_year_dir . '.htaccess' );
			}

			if ( ! file_exists( $current_year_dir . 'index.php' ) ) {
				copy( BEWPI_DIR . 'tmp/index.php', $current_year_dir . 'index.php' );
			}

			// make custom templates dir.
			wp_mkdir_p( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . 'simple/' );

			do_action( 'bewpi_after_setup_directories' );
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
		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'bewpi_admin_settings_script', BEWPI_URL . 'assets/js/admin.js', array(), BEWPI_VERSION, true );
			wp_localize_script( 'bewpi_admin_settings_script', 'BEWPI_AJAX', array(
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'deactivation_nonce'    => wp_create_nonce( 'deactivation-notice' ),
					'dismiss_nonce'         => wp_create_nonce( 'dismiss-notice' ),
				)
			);
			wp_register_style( 'bewpi_admin_settings_css', BEWPI_URL . 'assets/css/admin.css', false, BEWPI_VERSION );
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
					include BEWPI_DIR . 'includes/partials/settings-sidebar.php';
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
			return sprintf( __( 'Version %s', 'woocommerce-pdf-invoices' ), BEWPI_VERSION );
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
			// make sure invoice got only send once for each order.
			$transient_name = sprintf( 'bewpi_emailitin_processed-%1$s', $order->id );
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
			// payment methods for which the invoice generation should be cancelled.
			$payment_methods = apply_filters( 'bewpi_attach_invoice_excluded_payment_methods', array() );
			if ( in_array( $order->payment_method, $payment_methods, true ) ) {
				return $attachments;
			}

			$general_options = get_option( 'bewpi_general_settings' );
			// check if email is enabled.
			if ( ! isset( $general_options[ $status ] ) || ! $general_options[ $status ] ) {
				return $attachments;
			}

			$invoice = new BEWPI_Invoice( $order->id );
			if ( $invoice->exists() ) {
				$full_path = $invoice->get_full_path();
			} else {
				$full_path = $invoice->save( 'F' );
			}

			$attachments[] = $full_path;
			return $attachments;
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
		 * Display PDF button on "Orders" page to view invoice.
		 *
		 * @param WC_ORDER $order WooCommerce Order.
		 */
		public function add_admin_order_pdf( $order ) {
			$invoice = new BEWPI_Invoice( $order->id );
			if ( $invoice->exists() ) {
				$this->show_invoice_button(
					__( 'View invoice', 'woocommerce-pdf-invoices' ),
					$order->id,
					'view',
					array(
						'class="button tips bewpi-admin-order-create-invoice-btn"',
						'target="_blank"',
						)
				);
			}
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

			printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $title, join( ' ', $attributes ), $title );
		}

		/**
		 * Display invoice actions on "Order Details" page.
		 *
		 * @param WP_Post $post as WC_Order object.
		 */
		public function display_admin_order_pdf_actions( $post ) {
			$invoice = new BEWPI_Invoice( $post->ID );

			if ( ! $invoice->exists() ) {
				$this->show_invoice_button( __( 'Create', 'woocommerce-pdf-invoices' ), $post->ID, 'create', array( 'class="invoice-btn button grant_access"' ) );
				return;
			}

			// invoice exists so display invoice info.
			$this->show_invoice_number_info( $invoice->get_formatted_invoice_date(), $invoice->formatted_number );
			// display button to view invoice.
			$this->show_invoice_button( __( 'View', 'woocommerce-pdf-invoices' ), $post->ID, __( 'view', 'woocommerce-pdf-invoices' ), array(
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

			$invoice = new BEWPI_Invoice( $order->id );
			if ( ! $invoice->exists() ) {
				return;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post' => $order->id,
				'nonce' => wp_create_nonce( 'view' ),
			) );

			$tags = array(
				'{formatted_invoice_number}' => $invoice->get_formatted_number(),
				'{order_number}'             => $order->id,
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

			$invoice = new BEWPI_Invoice( $order->id );
			if ( ! $invoice->exists() ) {
				return $actions;
			}

			$url = add_query_arg( array(
				'bewpi_action' => 'view',
				'post' => $order->id,
				'nonce' => wp_create_nonce( 'view' ),
			) );

			$actions['invoice'] = array(
				'url'  => $url,
				'name' => sprintf( __( 'Invoice %s (PDF)', 'woocommerce-pdf-invoices' ), $invoice->formatted_number ),
			);

			return $actions;
		}
	}
}

/**
 * Main instance of BE_WooCommerce_PDF_Invoices.
 *
 * @since  2.5.0
 * @return BE_WooCommerce_PDF_Invoices
 */
function BEWPI() {
	return BE_WooCommerce_PDF_Invoices::instance();
}
BEWPI();
