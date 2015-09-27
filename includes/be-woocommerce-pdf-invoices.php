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

		private $options_key = 'bewpi-invoices';
		public $settings_tabs = array();
		public $textdomain = 'be-woocommerce-pdf-invoices';
		public $general_options = array();
		public $template_options = array();

		/**
		 * Initialize plugin and register actions and filters.
		 *
		 * @param $general_settings
		 * @param $template_settings
		 */
		public function __construct() {
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

			add_filter( 'plugin_action_links_woocommerce-pdf-invoices/bootstrap.php', array( $this, 'your_plugin_action_links' ) );
		}

		public static function plugin_activation() {
			self::insert_install_date();
		}

		function your_plugin_action_links( $links ) {
			return array_merge( array(
				'<a href="' . admin_url( 'admin.php?page=bewpi-invoices' ) . '">' . __( 'Settings' ) . '</a>',
				'<a href="http://wcpdfinvoices.com" target="_blank">' . __( 'Premium' ) . '</a>'), $links );
		}

		public static function plugin_deactivation() {
			global $current_user;
			$user_id = $current_user->ID;
			update_user_meta( $user_id, 'bewpi_hide_activation_notice', '0' );
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
						<?php printf( __( 'Alrighty then! <a href="%s">Let\'s start configuring <strong>WooCommerce PDF Invoices</strong></a>.' ), admin_url() . 'admin.php?page=bewpi-invoices' ); ?>
					</p>
					<?php printf( '<a href="%1$s" class="notice-dismiss"></a>', '?bewpi_hide_activation_notice=0' ); ?>
				</div>
			<?php
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
		}

		public function init_settings_tabs() {
			$this->settings_tabs['bewpi_general_settings']  = __( 'General', $this->textdomain );
			$this->settings_tabs['bewpi_template_settings'] = __( 'Template', $this->textdomain );

			$this->settings_tabs = apply_filters( 'bewpi_settings_tabs', $this->settings_tabs );
		}

		/**
		 * Creates invoices dir in uploads folder
		 */
		private function create_bewpi_dirs() {
			// bewpi-invoices
			wp_mkdir_p( BEWPI_INVOICES_DIR . date( 'Y' ) . '/' );
			copy( BEWPI_DIR . 'tmp/.htaccess', BEWPI_INVOICES_DIR . date( 'Y' ) . '/.htaccess' );
			copy( BEWPI_DIR . 'tmp/index.php', BEWPI_INVOICES_DIR . date( 'Y' ) . '/index.php' );

			wp_mkdir_p( BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . 'simple/' );

			do_action( 'mk_custom_template_invoices_dir' );
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
				<aside class="bewpi_sidebar premium">
					<h3><?php _e( 'Invoicing on Steroids', $this->textdomain ); ?></h3>
					<p><?php _e( 'We\'re working on a more powerful version of this invoicing plugin. Be the first to know when we\'re ready to launch and receive a unique one-time discount!', $this->textdomain ); ?></p>
					<!-- Begin MailChimp Signup Form -->
					<link href="//cdn-images.mailchimp.com/embedcode/slim-081711.css" rel="stylesheet" type="text/css">
					<style type="text/css">
						#mc_embed_signup{background: #222; clear:left;}
						#mc-embedded-subscribe:hover { background-color: #4ca55e !important; }
						#mc_embed_signup input.button { margin: 0 !important; }
						/* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
						   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
					</style>
					<div id="mc_embed_signup">
						<form action="//wcpdfinvoices.us11.list-manage.com/subscribe/post?u=f270649bc41a9687a38a8977f&amp;id=395e1e319a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate style="padding: 0">
							<div id="mc_embed_signup_scroll">
								<?php $user_email = get_the_author_meta( 'user_email', get_current_user_id() ) ?>
								<input style="width: 100%; border-radius: 0; margin-top: 20px; border: 1px solid #ccc;" type="email" value="<?php if( $user_email !== "" ) echo $user_email; ?>" name="EMAIL" class="email" id="mce-EMAIL" placeholder="<?php _e( 'Your email address', $this->textdomain ); ?>" required>
								<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
								<div style="position: absolute; left: -5000px;"><input type="text" name="b_f270649bc41a9687a38a8977f_395e1e319a" tabindex="-1" value=""></div>
								<div class="clear"><input style="width: 100%; background-color: #5dc372; border-radius: 0; height: 37px;box-shadow: none;" type="submit" value="<?php _e( 'Signup', $this->textdomain ); ?>" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
								<div style="font-size: 11px; text-align: center; margin-top: 1px !important;"><?php _e( 'No spam, ever. Unsubscribe at any time', $this->textdomain ); ?></div>
							</div>
						</form>
					</div>

					<!--End mc_embed_signup-->
				</aside>
				<aside class="bewpi_sidebar about">
					<h3><?php _e( 'About', $this->textdomain ); ?></h3>
					<p><?php _e( 'This plugin is an open source project wich aims to fill the invoicing gap of <a href="http://www.woothemes.com/woocommerce">WooCommerce</a>.' , $this->textdomain ); ?></p>
					<?php _e( '<b>Version</b>: ' . BEWPI_VERSION, $this->textdomain ); ?>
					<br/>
					<?php _e( '<b>Author</b>: <a href="https://github.com/baselbers">Bas Elbers</a>', $this->textdomain ); ?>
				</aside>
				<aside class="bewpi_sidebar support">
					<h3><?php _e( 'Support', $this->textdomain ); ?></h3>
					<p><?php _e( 'We will never ask for donations, but to garantee future development, we do need your support. Please show us your appreciation by leaving a <a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform">★★★★★</a> rating and vote for <a href="https://wordpress.org/plugins/woocommerce-pdf-invoices/">works</a>.', $this->textdomain ); ?></p>
					<!-- Github star -->
					<div class="github btn">
						<iframe src="https://ghbtns.com/github-btn.html?user=baselbers&repo=woocommerce-pdf-invoices&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>
					</div>
					<!-- FB share -->
					<div class="btn">
						<div id="fb-root"></div>
						<script>(function(d, s, id) {
								var js, fjs = d.getElementsByTagName(s)[0];
								if (d.getElementById(id)) return;
								js = d.createElement(s); js.id = id;
								js.src = "//connect.facebook.net/nl_NL/sdk.js#xfbml=1&version=v2.4&appId=483906578380615";
								fjs.parentNode.insertBefore(js, fjs);
							}(document, 'script', 'facebook-jssdk'));</script>
						<div class="fb-share-button" data-href="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-layout="button_count"></div>
					</div>
					<!-- Tweet -->
					<div class="twitter btn">
						<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-text="Checkout this amazing free WooCommerce PDF Invoices plugin for WordPress!">Tweet</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
					</div>
				</aside>
				<aside class="bewpi_sidebar need-help">
					<h3><?php _e( 'Need Help?', $this->textdomain ); ?></h3>
					<ul>
						<li><a href="https://wordpress.org/plugins/woocommerce-pdf-invoices/faq/"><?php _e( 'Frequently Asked Questions', $this->textdomain ); ?> </a></li>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Support forum', $this->textdomain ); ?></a></li>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Request a feature', $this->textdomain ); ?></a></li>
						<li><a href="mailto:baselbers@hotmail.com"><?php _e( 'Email us', $this->textdomain ); ?></a></li>
					</ul>
				</aside>
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

			if ( $status == $general_options['bewpi_email_type'] || $general_options['bewpi_new_order'] && $status == "new_order" ) {
				$invoice = new BEWPI_Invoice( $order->id );
				if ( ! $invoice->exists() ) {
					$full_path = $invoice->save( "F" );
				} else {
					$full_path = $invoice->get_full_path();
				}
				$attachments[] = $full_path;
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
			if ( $invoice->exists() )
				$this->show_invoice_button( 'View invoice', $order->id, 'view', '', array( 'class="button tips wpi-admin-order-create-invoice-btn"' ) );
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

			if ( current_user_can( 'install_plugins' ) && $hide_notice == '0' ) {
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
		public function display_admin_notice() {
			$query_params = $this->get_admin_querystring_array();
			$query_string = '?' . http_build_query( array_merge( $query_params, array( self::OPTION_ADMIN_NOTICE_KEY => '1' ) ) );

			echo '<div class="updated"><p>';
			printf( __( "You are working with <b>WooCommerce PDF Invoices</b> for some time now. We really need your ★★★★★ rating. It will support future development big-time. A huge thanks in advance and keep up the good work! <br /> <a href='%s' target='_blank'>Yes, will do it right away!</a> - <a href='%s'>No, already done it!</a>" ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform', $query_string );
			echo "</p></div>";
		}
	}
}