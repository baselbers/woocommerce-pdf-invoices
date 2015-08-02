<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

	/**
	 * Implements main function for attaching invoice to email and show invoice buttons.
	 */
	final class BE_WooCommerce_PDF_Invoices {

		/**
		 * Constant options key
		 * @var string
		 */
		private $options_key = 'bewpi-invoices';

		/**
		 * All the settings tabs for the settings page.
		 * @var array
		 */
		private $settings_tabs = array(
			'bewpi_general_settings'  => 'General',
			'bewpi_template_settings' => 'Template'
		);

		/**
		 * Default textdomain.
		 * @var string
		 */
		public $textdomain = 'be-woocommerce-pdf-invoices';

		public $general_options = array();

		public $template_options = array();

		/**
		 * Install date constant for admin notice
		 */
		const OPTION_INSTALL_DATE = 'bewpi-install-date';

		/**
		 * Admin notice key constant
		 */
		const OPTION_ADMIN_NOTICE_KEY = 'bewpi-hide-notice';

		/**
		 * Initialize plugin and register actions and filters.
		 *
		 * @param $general_settings
		 * @param $template_settings
		 */
		public function __construct() {
			new BEWPI_General_Settings();
			new BEWPI_Template_Settings();

			/**
			 * Review admin notice
			 */
			register_activation_hook( __FILE__, array( 'BE_WooCommerce_PDF_Invoices', 'plugin_activation' ) );

			/**
			 * Initialize plugin
			 */
			add_action( 'init', array( &$this, 'init' ) );

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
			 * AJAX calls to download invoice
			 */
			add_action( 'wp_ajax_bewpi_download_invoice', array( &$this, 'bewpi_download_invoice' ) );
			add_action( 'wp_ajax_nopriv_bewpi_download_invoice', array( &$this, 'bewpi_download_invoice' ) );

			/**
			 * Adds a download link for the pdf invoice on the my account page
			 */
			add_filter( 'woocommerce_my_account_my_orders_actions', array(
				&$this,
				'add_my_account_download_pdf_action'
			), 10, 2 );

			add_action( 'admin_footer-edit.php', array( &$this, 'add_custom_order_bulk_action' ) );

			add_action( 'load-edit.php', array( &$this, 'load_bulk_actions' ) );

		}

		/**
		 * Initialize...
		 */
		public function init() {

			$this->load_textdomain();

			$this->create_invoices_dir();

			$this->invoice_actions();

			$this->init_review_admin_notice();

		}

		/**
		 * Get installation date on activation
		 */
		public static function plugin_activation() {
			self::insert_install_date();
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

			// Admin notice hide catch
			add_action( 'admin_init', array( &$this, 'catch_hide_notice' ) );

			$current_user = wp_get_current_user();
			$hide_notice  = get_user_meta( $current_user->ID, self::OPTION_ADMIN_NOTICE_KEY, true );

			if ( current_user_can( 'install_plugins' ) && $hide_notice == '' ) {
				// Get installation date
				$datetime_install = $this->get_install_date();
				$datetime_past    = new DateTime( '-10 days' );
				//$datetime_past    = new DateTime( '-10 second' );

				if ( $datetime_past >= $datetime_install ) {
					// 10 or more days ago, show admin notice
					add_action( 'admin_notices', array( &$this, 'display_admin_notice' ) );
				}
			}

			// Don't add admin bar option in admin panel
			if ( is_admin() ) {
				return;
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

				if ( ! wp_verify_nonce( $nonce, $action ) ) {
					die( 'Invalid request' );
				}

				if ( empty( $order_id ) ) {
					die( 'Invalid order ID' );
				}

				$invoice = new BEWPI_Invoice( $order_id );

				switch ( $_GET['bewpi_action'] ) {
					case "view":
						$invoice->view( true );
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
			load_plugin_textdomain( $this->textdomain, false, apply_filters( 'bewpi_lang_dir', $lang_dir ) );
			$this->settings_tabs['bewpi_general_settings']  = __( 'General', $this->textdomain );
			$this->settings_tabs['bewpi_template_settings'] = __( 'Template', $this->textdomain );
		}

		/**
		 * Creates invoices dir in uploads folder
		 */
		private function create_invoices_dir() {
			//if ( !wp_mkdir_p( BEWPI_INVOICES_DIR . date( 'Y' ) . '/' ) ) htaccess doesn't copy...
			wp_mkdir_p( BEWPI_INVOICES_DIR . date( 'Y' ) . '/' );
			copy( BEWPI_DIR . 'tmp/.htaccess', BEWPI_INVOICES_DIR . date( 'Y' ) . '/.htaccess' );
			copy( BEWPI_DIR . 'tmp/index.php', BEWPI_INVOICES_DIR . date( 'Y' ) . '/index.php' );
		}

		/**
		 * Adds submenu to WooCommerce menu.
		 */
		public function add_woocommerce_submenu_page() {
			add_submenu_page( 'woocommerce', __( 'Invoices', $this->textdomain ), __( 'Invoices', $this->textdomain ), 'manage_options', $this->options_key, array(
				&$this,
				'options_page'
			) );
		}

		/**
		 * Admin scripts
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'admin_settings_script', BEWPI_URL . '/assets/js/admin.js' );
			wp_register_style( 'admin_settings_css', BEWPI_URL . '/assets/css/admin.css', false, '1.0.0' );
			wp_enqueue_style( 'admin_settings_css' );
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
					document.getElementById("footer-thankyou").innerHTML = "If you like <strong>WooCommerce PDF Invoices</strong> please leave us a <a href='https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform'>★★★★★</a> rating. A huge thank you in advance!";
					document.getElementById("footer-upgrade").innerHTML = "Version <?php echo BEWPI_VERSION; ?>";
				};
			</script>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form class="be_woocommerce_pdf_invoices_settings_form" method="post" action="options.php"
				      enctype="multipart/form-data">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		<?php
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
			$general_options = get_option( 'bewpi_general_settings' );

			if ( $status == $general_options['bewpi_email_type'] && $general_options['bewpi_email_it_in'] && ! empty( $general_options['bewpi_email_it_in_account'] ) ) {
				$email_it_in_account = $general_options['bewpi_email_it_in_account'];
				$headers .= 'BCC: <' . $email_it_in_account . '>' . "\r\n";
			}

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

			if ( $status == $general_options['bewpi_email_type'] || $general_options['bewpi_new_order'] && $status == "new_order" ) {
				$invoice = new BEWPI_Invoice( $order->id );
				if ( ! $invoice->exists() ) {
					$filename = $invoice->save( "F" );
				} else {
					$filename = $invoice->get_filename();
				}
				$attachments[] = $filename;
			}

			return $attachments;
		}

		/**
		 * Adds a box to the main column on the Post and Page edit screens.
		 */
		function add_meta_box_to_order_page() {
			add_meta_box( 'order_page_create_invoice', __( 'PDF Invoice', $this->textdomain ), array(
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
			if ( $invoice->exists() ) {
				$this->show_invoice_button( 'View invoice', $order->id, 'view', '', array( 'class="button tips wpi-admin-order-create-invoice-btn"' ) );
			}
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
                    <td>' . __( 'Invoiced on:', $this->textdomain ) . '</td>
                    <td align="right"><b>' . $date . '</b></td>
                </tr>
                <tr>
                    <td>' . __( 'Invoice number:', $this->textdomain ) . '</td>
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
			$title      = __( $title, $this->textdomain );
			$href       = admin_url() . 'post.php?post=' . $order_id . '&action=edit&bewpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce( $wpi_action );
			$btn_title  = __( $btn_title, $this->textdomain );
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
				$this->show_invoice_button( __( 'View invoice', $this->textdomain ), $post->ID, 'view', __( 'View', $this->textdomain ), array( 'class="invoice-btn button grant_access"' ) );
				$this->show_invoice_button( __( 'Cancel invoice', $this->textdomain ), $post->ID, 'cancel', __( 'Cancel', $this->textdomain ), array(
					'class="invoice-btn button grant_access"',
					'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', $this->textdomain ) . '\')"'
				) );
			} else {
				$this->show_invoice_button( __( 'Create invoice', $this->textdomain ), $post->ID, 'create', __( 'Create', $this->textdomain ), array( 'class="invoice-btn button grant_access"' ) );
			}
		}

		/**
		 * AJAX action to download invoice
		 */
		public function bewpi_download_invoice() {
			if ( isset( $_GET['action'] ) && isset( $_GET['order_id'] ) && isset( $_GET['nonce'] ) ) {
				$action   = $_GET['action'];
				$order_id = $_GET['order_id'];
				$nonce    = $_REQUEST["nonce"];

				if ( ! wp_verify_nonce( $nonce, $action ) )
					die( 'Invalid request' );

				if ( empty( $order_id ) )
					die( 'Invalid order ID' );

				$invoice = new BEWPI_Invoice( $order_id );
				$invoice->view( true );
			}
		}

		/**
		 * Display download link on My Account page
		 */
		public function add_my_account_download_pdf_action( $actions, $order ) {
			$invoice = new BEWPI_Invoice( $order->id );
			if ( $invoice->exists() && $invoice->is_download_allowed( $order->post_status ) ) {
				$url                = admin_url( 'admin-ajax.php?action=bewpi_download_invoice&order_id=' . $order->id . '&nonce=' . wp_create_nonce( 'bewpi_download_invoice' ) );
				$actions['invoice'] = array(
					'url'  => $url,
					'name' => sprintf( __( 'Invoice %s (PDF)', $this->textdomain ), $invoice->get_formatted_number() )
				);
			}

			return $actions;
		}

		/**
		 * @return string
		 */
		private static function insert_install_date() {
			$datetime_now = new DateTime();
			$date_string  = $datetime_now->format( 'Y-m-d' );
			add_site_option( self::OPTION_INSTALL_DATE, $date_string, '', 'no' );

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
		public function display_admin_notice() {
			$query_params = $this->get_admin_querystring_array();
			$query_string = '?' . http_build_query( array_merge( $query_params, array( self::OPTION_ADMIN_NOTICE_KEY => '1' ) ) );

			echo '<div class="updated"><p>';
			printf( __( "Thank you for using <b>WooCommerce PDF Invoices</b> for some time now. Please show us your appreciation by leaving a ★★★★★ rating. A huge thank you in advance! <br /> <a href='%s' target='_blank'>Yes, will do it right away!</a> - <a href='%s'>No, already done it!</a>" ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform', $query_string );
			echo "</p></div>";
		}

		public function add_custom_order_bulk_action() {
			global $post_type;

			if ( $post_type == 'shop_order' ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery('<option>').val('generate_global_invoice').text('<?php _e( 'Generate global invoice' )?>').appendTo("select[name='action']");
					});
				</script>
			<?php
			}
		}

		public function load_bulk_actions() {
			global $typenow;
			$post_type = $typenow;

			// Are we on order page?
			if ( $post_type == 'shop_order' ) {

				// Get the action
				$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
				$action        = $wp_list_table->current_action();

				$allowed_actions = array( "generate_global_invoice" );
				if ( ! in_array( $action, $allowed_actions ) ) {
					return;
				}

				// Security check
				check_admin_referer( 'bulk-posts' );

				// Make sure ids are submitted. Depending on the resource type, this may be 'media' or 'ids'
				if ( isset( $_REQUEST['post'] ) ) {
					$post_ids = array_map( 'intval', $_REQUEST['post'] );
				}

				if ( empty( $post_ids ) ) {
					return;
				}

				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array( 'generated', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
				if ( ! $sendback ) {
					$sendback = admin_url( "edit.php?post_type=$post_type" );
				}

				$pagenum  = $wp_list_table->get_pagenum();
				$sendback = add_query_arg( 'paged', $pagenum, $sendback );

				switch ( $action ) {
					case 'generate_global_invoice':

						$global_invoice = new BEWPI_Global_Invoice( $post_ids );
						$global_invoice->save( "D" );

						$sendback = add_query_arg( array( 'ids' => join( ',', $post_ids ) ), $sendback );
						break;

					default:
						return;
				}

				$sendback = remove_query_arg( array(
					'action',
					'action2',
					'tags_input',
					'post_author',
					'comment_status',
					'ping_status',
					'_status',
					'post',
					'bulk_edit',
					'post_view'
				), $sendback );

				wp_redirect( $sendback );
				exit();
			}
		}
	}
}