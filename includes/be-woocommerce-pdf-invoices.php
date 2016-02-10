<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

	/**
	 * Implements main function for attaching invoice to email and show invoice buttons.
	 */
	final class BE_WooCommerce_PDF_Invoices {

		const OPTION_INSTALL_DATE = 'bewpi-install-date';
		const OPTION_ADMIN_NOTICE_KEY = 'bewpi-hide-notice';
		const OPTION_ADMIN_ACTIVATION_NOTICE_KEY = 'bewpi-hide-activation-notice';

		private $lang_code = 'en-US';
		private $options_key = 'bewpi-invoices';
		public $settings_tabs = array();
		public $general_options = array();
		public $template_options = array();

		/**
		 * Initialize plugin and register actions and filters.
		 *
		 * @param $general_settings
		 * @param $template_settings
		 */
		public function __construct() {
			$this->lang_code = get_bloginfo( "language" );
			new BEWPI_General_Settings();
			new BEWPI_Template_Settings();

			do_action( 'bewpi_after_init_settings' );

			/**
			 * Initialize plugin
			 */
			add_action( 'init', array( &$this, 'init' ) );

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			add_action( 'admin_init', array( &$this, 'catch_hide_notice' ) );

			/**
			 * Adds Invoices submenu to WooCommerce menu.
			 */
			add_action( 'admin_menu', array( &$this, 'add_woocommerce_submenu_page' ) );

			/**
			 * Enqueue admin scripts
			 */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

			/**
			 * Add actions to overview order page.
			 */
			add_action( 'woocommerce_admin_order_actions_end', array(
				&$this,
				'woocommerce_order_page_action_view_invoice'
			) );

			/**
			 * Adds a meta box to the order details page.
			 */
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box_to_order_page' ) );

			/**
			 * Adds the Email It In email as an extra recipient
			 */
			add_filter( 'woocommerce_email_headers', array(
				&$this,
				'add_email_it_in_account_to_email_headers'
			), 10, 2 );

			/**
			 * Attach invoice to a specific WooCommerce email
			 */
			add_filter( 'woocommerce_email_attachments', array( &$this, 'attach_invoice_to_email' ), 99, 3 );

			/**
			 * Adds a download link for the pdf invoice on the my account page
			 */
			add_filter( 'woocommerce_my_account_my_orders_actions', array(
				&$this,
				'add_my_account_download_pdf_action'
			), 10, 2 );

			/**
			 * Shortcode to display invoice from view
			 */
			add_shortcode( 'bewpi-download-invoice', array( $this, 'bewpi_download_invoice_func' ) );
		}

		public function init() {
			$this->load_textdomain();
			$this->init_settings_tabs();
			$this->create_bewpi_dirs();
			$this->invoice_actions();

			add_action( 'admin_notices', array( $this, 'display_activation_admin_notice' ) );
		}

		public function admin_init() {
			$this->plugin_activation_notice_catch_hide();
			$this->init_review_admin_notice();

			add_filter( 'plugin_action_links_woocommerce-pdf-invoices/bootstrap.php', array( $this, 'add_plugin_action_links' ) );
		}

		public static function plugin_activation() {
			self::insert_install_date();
		}

		public function display_activation_admin_notice() {
			global $pagenow;
			if ( $pagenow != 'plugins.php' )
				return;

			global $current_user;
			$user_id = $current_user->ID;
			if ( ! get_user_meta( $user_id, 'bewpi_hide_activation_notice', true ) ) {
				?>
				<div id="bewpi-plugin-activated-notice" class="updated notice is-dismissible">
					<p>
						<?php printf( __( 'Alrighty then! <a href="%s">Let\'s start configuring <strong>WooCommerce PDF Invoices</strong></a>.', 'woocommerce-pdf-invoices' ), admin_url() . 'admin.php?page=bewpi-invoices' ); ?>
					</p>
					<?php printf( '<a href="%1$s" class="notice-dismiss"></a>', '?bewpi_hide_activation_notice=0' ); ?>
				</div>
			<?php
			}
		}

		function add_plugin_action_links( $links ) {
			return array_merge( array(
				'<a href="' . admin_url( 'admin.php?page=bewpi-invoices' ) . '">' . __( 'Settings', 'woocommerce-pdf-invoices' ) . '</a>',
				'<a href="http://wcpdfinvoices.com" target="_blank">' . __( 'Premium', 'woocommerce-pdf-invoices' ) . '</a>'
			), $links );
		}

		public function bewpi_download_invoice_func( $atts ) {
			$order_id   = $atts[ 'order_id' ];
			$title      = $atts[ 'title' ];
			$order      = wc_get_order( $order_id );
			$invoice    = new BEWPI_Invoice( $order->id );

			if ( $invoice->exists() && $invoice->is_download_allowed( $order->post_status ) ) {
				$url = admin_url( 'admin-ajax.php?bewpi_action=view&post=' . $order->id . '&nonce=' . wp_create_nonce( 'view' ) );

				$tags = array (
					'{formatted_invoice_number}'    => $invoice->get_formatted_number(),
					'{order_number}'                => $order->id,
					'{formatted_invoice_date}'      => $invoice->get_formatted_invoice_date(),
					'{formatted_order_date}'        => $invoice->get_formatted_order_date()
				);
				foreach ( $tags as $key => $value )
					$title = str_replace( $key, $value, $title );

				// example: Download (PDF) Invoice {formatted_invoice_number}
				echo '<a href="' . $url . '" alt="' . $title . '">' . $title . '</a>';
			}
		}

		public function plugin_activation_notice_catch_hide() {
			global $current_user;
            $user_id = $current_user->ID;
			if ( isset($_GET['bewpi_hide_activation_notice']) && '0' == $_GET['bewpi_hide_activation_notice'] ) {
				update_user_meta( $user_id, 'bewpi_hide_activation_notice', '1' );
			}
		}

		/**
		 * Callback to sniff for specific plugin actions to view, create or delete invoice.
		 */
		private function invoice_actions() {
			if ( isset( $_GET['bewpi_action'] ) && isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
				$action   = $_GET['bewpi_action'];
				$order_id = $_GET['post'];
				$nonce    = $_REQUEST['nonce'];

				if ( ! wp_verify_nonce( $nonce, $action ) )
					wp_die( __( 'Invalid request', 'woocommerce-pdf-invoices' ) );

				if ( empty( $order_id ) )
					wp_die( __( 'Invalid order ID', 'woocommerce-pdf-invoices' ) );

				$user = wp_get_current_user();
				$allowed_roles = array( 'administrator', 'shop_manager' );
				$customer_user_id = get_post_meta( $order_id, '_customer_user', true );
				if (  ! array_intersect( $allowed_roles, $user->roles ) && get_current_user_id() != $customer_user_id  )
					wp_die( __( 'Access denied', 'woocommerce-pdf-invoices' ) );

				$invoice = new BEWPI_Invoice( $order_id );
				switch ( $_GET['bewpi_action'] ) {
					case "view":
						$invoice->view();
						break;
					case "cancel":
						$invoice->delete();
						break;
					case "create":
						$invoice->save( "F" );
						break;
				}

			}
		}

		/**
		 * Loads the textdomain and localizes the plugin options tabs.
		 */
		public function load_textdomain() {
			$lang_dir = (string) BEWPI_LANG_DIR;
			load_plugin_textdomain( 'woocommerce-pdf-invoices', false, apply_filters( 'bewpi_lang_dir', $lang_dir ) );
		}

		public function init_settings_tabs() {
			$this->settings_tabs['bewpi_general_settings']  = __( 'General', 'woocommerce-pdf-invoices' );
			$this->settings_tabs['bewpi_template_settings'] = __( 'Template', 'woocommerce-pdf-invoices' );

			$this->settings_tabs = apply_filters( 'bewpi_settings_tabs', $this->settings_tabs );
		}

		/**
		 * Creates invoices dir in uploads folder
		 */
		private function create_bewpi_dirs() {
			// invoices
			$current_year_dir = BEWPI_INVOICES_DIR . date_i18n( 'Y', current_time( 'timestamp' ) ) . "/";

			wp_mkdir_p( $current_year_dir );

			if ( ! file_exists ( $current_year_dir . ".htaccess" ) ) {
				copy( BEWPI_DIR . 'tmp/.htaccess', $current_year_dir . ".htaccess" );
			}

			if ( ! file_exists( $current_year_dir . "index.php" ) ) {
				copy( BEWPI_DIR . 'tmp/index.php', $current_year_dir . "index.php" );
			}

			// custom templates
			wp_mkdir_p( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . 'simple/' );

			do_action( 'mk_custom_template_invoices_dir' );
		}

		/**
		 * Adds submenu to WooCommerce menu.
		 */
		public function add_woocommerce_submenu_page() {
			add_submenu_page( 'woocommerce', __( 'Invoices', 'woocommerce-pdf-invoices' ), __( 'Invoices', 'woocommerce-pdf-invoices' ), 'manage_options', $this->options_key, array(
				&$this,
				'options_page'
			) );
		}

		/**
		 * Admin scripts
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'bewpi_admin_settings_script', BEWPI_URL . '/assets/js/admin.js' );
			wp_register_style( 'bewpi_admin_settings_css', BEWPI_URL . '/assets/css/admin.css', false, '1.0.0' );
			wp_enqueue_style( 'bewpi_admin_settings_css' );
		}

		/**
		 * Callback function for adding plugin options tabs.
		 */
		private function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'bewpi_general_settings';

			screen_icon();
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . 'bewpi-invoices' . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}

		/**
		 * The options page..
		 */
		public function options_page() {
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'bewpi_general_settings';
			?>
			<script type="text/javascript">
				window.onload = function () {
					// Change footer text into rate text for WPI.
					document.getElementById("footer-thankyou").innerHTML = "<?php printf( __( 'If you like <strong>WooCommerce PDF Invoices</strong> please leave us a %s★★★★★%s rating. A huge thank you in advance!', 'woocommerce-pdf-invoices' ), '<a href=\'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform\'>', '</a>' ); ?>";
					document.getElementById("footer-upgrade").innerHTML = "<?php printf( __( 'Version %s', 'woocommerce-pdf-invoices' ), BEWPI_VERSION ); ?>";
				};
			</script>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form class="bewpi-settings-form" method="post" action="options.php"
				      enctype="multipart/form-data">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>

				<?php if ( ! is_plugin_active( 'woocommerce-pdf-invoices-premium/bootstrap.php' ) ) {
					$this->options_page_sidebar_html();
				} ?>

			</div>
		<?php
		}

		private function options_page_sidebar_html() {
			include( BEWPI_DIR . 'includes/partials/settings-sidebar.php' );
		}

		/**
		 * Adds the Email It In email as an extra recipient
		 *
		 * @param $headers
		 * @param $status
		 *
		 * @return string
		 */
		function add_email_it_in_account_to_email_headers( $headers, $status ) {
			$general_options        = get_option( 'bewpi_general_settings' );
			$email_it_in_account    = $general_options['bewpi_email_it_in_account'];

			if ( $status !== $general_options['bewpi_email_type'] )
				return $headers;

			if ( ! (bool)$general_options['bewpi_email_it_in'] || empty( $email_it_in_account ) )
				return $headers;

			$headers .= 'BCC: <' . $email_it_in_account . '>' . "\r\n";

			return $headers;
		}

		/**
		 * Attaches invoice to a specific WooCommerce email. Invoice will only be generated when it does not exists already.
		 *
		 * @param $attachments
		 * @param $status
		 * @param $order
		 *
		 * @return array
		 */
		function attach_invoice_to_email( $attachments, $status, $order ) {
			$general_options = get_option( 'bewpi_general_settings' );

			if ( $status == $general_options[ 'bewpi_email_type'] || $general_options['bewpi_new_order'] && $status == "new_order" ) {
				$invoice = new BEWPI_Invoice( $order->id );
				// create new invoice if doesn't exists, else get the full path..
				$full_path = ( ! $invoice->exists() ) ? $invoice->save( "F" ) : $invoice->get_full_path();
				$attachments[] = $full_path;
			}

			return $attachments;
		}

		/**
		 * Adds a box to the main column on the Post and Page edit screens.
		 */
		function add_meta_box_to_order_page() {
			add_meta_box( 'order_page_create_invoice', __( 'PDF Invoice', 'woocommerce-pdf-invoices' ), array(
				&$this,
				'woocommerce_order_details_page_meta_box_create_invoice'
			), 'shop_order', 'side', 'high' );
		}

		/**
		 * Shows the view invoice button on the all orders page.
		 *
		 * @param $order
		 */
		public function woocommerce_order_page_action_view_invoice( $order ) {
			$invoice = new BEWPI_Invoice( $order->id );
			if ( $invoice->exists() )
				$this->show_invoice_button( 'View invoice', $order->id, 'view', '', array( 'class="button tips wpi-admin-order-create-invoice-btn"', 'target="_blank"' ) );
		}

		/**
		 * Shows invoice number info on the order details page.
		 *
		 * @param $date
		 * @param $number
		 */
		private function show_invoice_number_info( $date, $number ) {
			echo '<table class="invoice-info" width="100%">
                <tr>
                    <td>' . __( 'Invoiced on:', 'woocommerce-pdf-invoices' ) . '</td>
                    <td align="right"><b>' . $date . '</b></td>
                </tr>
                <tr>
                    <td>' . __( 'Invoice number:', 'woocommerce-pdf-invoices' ) . '</td>
                    <td align="right"><b>' . $number . '</b></td>
                </tr>
            </table>';
		}

		/**
		 * Show a specific invoice button to for example view, create or delete an invoice.
		 *
		 * @param $title
		 * @param $order_id
		 * @param $wpi_action
		 * @param $btn_title
		 * @param array $arr
		 */
		private function show_invoice_button( $title, $order_id, $wpi_action, $btn_title, $arr = array() ) {
			$title      = __( $title, 'woocommerce-pdf-invoices');
			$href       = admin_url() . 'post.php?post=' . $order_id . '&action=edit&bewpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce( $wpi_action );
			$btn_title  = __( $btn_title, 'woocommerce-pdf-invoices');
			$attr       = '';

			foreach ( $arr as $str ) {
				$attr .= $str . ' ';
			}

			echo $btn = '<a title="' . $title . '" href="' . $href . '" ' . $attr . '>' . $btn_title . '</a>';
		}

		/**
		 * Show all the meta box actions/buttons on the order details page to create, view or cancel/delete an invoice.
		 *
		 * @param $post
		 */
		public function woocommerce_order_details_page_meta_box_create_invoice( $post ) {
			$invoice = new BEWPI_Invoice( $post->ID );

			if ( $invoice->exists() ) {
				$this->show_invoice_number_info( $invoice->get_formatted_invoice_date(), $invoice->get_formatted_number() );
				$this->show_invoice_button( __( 'View invoice', 'woocommerce-pdf-invoices'), $post->ID, 'view', __( 'View', 'woocommerce-pdf-invoices'), array( 'class="invoice-btn button grant_access"', 'target="_blank"' ) );
				$this->show_invoice_button( __( 'Cancel invoice', 'woocommerce-pdf-invoices'), $post->ID, 'cancel', __( 'Cancel', 'woocommerce-pdf-invoices' ), array(
					'class="invoice-btn button grant_access"',
					'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', 'woocommerce-pdf-invoices') . '\')"'
				) );
			} else {
				$this->show_invoice_button( __( 'Create invoice', 'woocommerce-pdf-invoices'), $post->ID, 'create', __( 'Create', 'woocommerce-pdf-invoices'), array( 'class="invoice-btn button grant_access"' ) );
			}
		}

		/**
		 * Display download link on My Account page
		 */
		public function add_my_account_download_pdf_action( $actions, $order ) {
			$general_options = get_option( 'bewpi_general_settings' );

			if ( ! (bool)$general_options[ 'bewpi_download_invoice_account' ] )
				return $actions;

			$invoice = new BEWPI_Invoice( $order->id );
			if ( ! $invoice->exists() )
				return $actions;

			if ( ! $invoice->is_download_allowed( $order->post_status ) )
				return $actions;

			$url = admin_url( 'admin-ajax.php?bewpi_action=view&post=' . $order->id . '&nonce=' . wp_create_nonce( 'view' ) );
			$actions[ 'invoice' ] = array(
				'url'  => $url,
				'name' => sprintf( __( 'Invoice %s (PDF)', 'woocommerce-pdf-invoices' ), $invoice->get_formatted_number() )
			);

			return $actions;
		}


		/**
		 * Check if we should show the admin notice
		 * @return bool|void
		 */
		public function init_review_admin_notice() {
			// Check if user is an administrator
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			$current_user = wp_get_current_user();
			$hide_notice  = get_user_meta( $current_user->ID, self::OPTION_ADMIN_NOTICE_KEY, true );

			if ( current_user_can( 'install_plugins' ) && $hide_notice == '' ) {
				// Get installation date
				$datetime_install = $this->get_install_date();
				$datetime_past    = new DateTime( '-10 days' );
				//$datetime_past    = new DateTime( '-10 second' );

				if ( $datetime_past >= $datetime_install ) {
					// 10 or more days ago, show admin notice
					add_action( 'admin_notices', array( &$this, 'display_rate_admin_notice' ) );
				}
			}

			// Don't add admin bar option in admin panel
			if ( is_admin() ) {
				return;
			}
		}

		/**
		 * @return string
		 */
		private static function insert_install_date() {
			$datetime_now = new DateTime();
			$date_string  = $datetime_now->format( 'Y-m-d' );
			update_site_option( self::OPTION_INSTALL_DATE, $date_string, '', 'no' );

			return $date_string;
		}

		/**
		 * Get the installation date of the plugin
		 * @return DateTime
		 */
		private function get_install_date() {
			$date_string = get_site_option( self::OPTION_INSTALL_DATE, '' );
			if ( $date_string == '' ) {
				// There is no install date, plugin was installed before version 2.2.1. Add it now.
				$date_string = self::insert_install_date();
			}

			return new DateTime( $date_string );
		}

		/**
		 * @return mixed
		 */
		private function get_admin_querystring_array() {
			parse_str( $_SERVER['QUERY_STRING'], $params );

			return $params;
		}

		/**
		 * Callback to hide the admin notice.
		 */
		public function catch_hide_notice() {
			if ( isset( $_GET[ self::OPTION_ADMIN_NOTICE_KEY ] ) && current_user_can( 'install_plugins' ) ) {
				// Add user meta
				global $current_user;
				//add_user_meta( $current_user->ID, self::OPTION_ADMIN_NOTICE_KEY, '1', true );
				update_user_meta( $current_user->ID, self::OPTION_ADMIN_NOTICE_KEY, '1' );

				// Build redirect URL
				$query_params = $this->get_admin_querystring_array();
				unset( $query_params[ self::OPTION_ADMIN_NOTICE_KEY ] );
				$query_string = http_build_query( $query_params );
				if ( $query_string != '' ) {
					$query_string = '?' . $query_string;
				}

				$redirect_url = 'http';
				if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
					$redirect_url .= 's';
				}
				$redirect_url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $query_string;

				// Redirect
				wp_redirect( $redirect_url );
				exit;
			}
		}

		/**
		 * Ask admin to review plugin.
		 */
		public function display_rate_admin_notice() {
			$query_params = $this->get_admin_querystring_array();
			$query_string = '?' . http_build_query( array_merge( $query_params, array( self::OPTION_ADMIN_NOTICE_KEY => '1' ) ) );

			echo '<div class="updated"><p>';
			printf( __( "You are working with <b>WooCommerce PDF Invoices</b> for some time now. We really need your ★★★★★ rating. It will support future development big-time. A huge thanks in advance and keep up the good work! <br /> <a href='%s' target='_blank'>Yes, will do it right away!</a> - <a href='%s'>No, already done it!</a>", 'woocommerce-pdf-invoices' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform', $query_string );
			echo "</p></div>";
		}
	}
}