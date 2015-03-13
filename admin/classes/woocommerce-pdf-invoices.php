<?php

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

	class BE_WooCommerce_PDF_Invoices {

		public $general_settings;

		public $template_settings;

		private $options_key = 'wpi-invoices';

		private $settings_tabs = array(
			'general_settings' => 'General',
			'template_settings' => 'Template'
		);

		public function __construct($general_settings, $template_settings) {

			$this->cleanup_tmp_dir();

			$this->general_settings = $general_settings;

			$this->template_settings = $template_settings;

			add_action( 'admin_menu', array(&$this, 'add_woocommerce_submenu_page'));

			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

			add_filter( 'woocommerce_email_headers', array( &$this, 'add_recipient_to_email_headers' ), 10, 2);

			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_invoice_to_email' ), 99, 3 );

			add_action( 'admin_notices', array(&$this, 'admin_notices' ) );

			add_shortcode( 'foobar', array(&$this, 'foobar_func') );

			add_action('wp_ajax_wpi_create_invoice', array($this, 'wpi_create_invoice'));

			add_action('wp_ajax_nopriv_wpi_create_invoice', array($this, 'wpi_create_invoice'));

			add_action('woocommerce_admin_order_actions_end', array($this, 'woocommerce_admin_order_actions_end'));

			add_action( 'add_meta_boxes', array(&$this, 'add_meta_box_to_order_page' ) );
		}

		public function add_woocommerce_submenu_page() {
			add_submenu_page('woocommerce', 'Invoices by Bas Elbers', 'Invoices', 'manage_options', $this->options_key, array($this, 'options_page'));
		}

		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'admin_settings_script', WPI_URL . '/assets/js/admin.js' );
			wp_register_style( 'admin_settings_css', WPI_URL . '/assets/css/admin.css', false, '1.0.0' );
			wp_enqueue_style( 'admin_settings_css' );
		}

		private function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general_settings';

			screen_icon();
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . 'wpi-invoices' . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}

		public function options_page() {
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general_settings';
			?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" enctype="multipart/form-data">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		<?php
		}

		function admin_notices() {
			settings_errors( 'wpi_notices' );
		}

		function add_recipient_to_email_headers($headers, $status) {
			if( $status == $this->general_settings->settings['email_type'] ) {
				if( $this->general_settings->settings['email_it_in']
					&& $this->general_settings->settings['email_it_in_account'] != "" ) {
					$email_it_in_account = $this->general_settings->settings['email_it_in_account'];
					$headers .= 'BCC: <' . $email_it_in_account . '>' . "\r\n";
				}
			}
			return $headers;
		}

		function attach_invoice_to_email( $attachments, $status, $order ) {
			if( $status == $this->general_settings->settings['email_type']
				|| $this->general_settings->settings['new_order'] && $status == "new_order" ) {
				$invoice = new WPI_Invoice($order);
				$path_to_pdf = $invoice->generate("F");
				$attachments[] = $path_to_pdf;
			}
			return $attachments;
		}

		function cleanup_tmp_dir() {
			array_map('unlink', glob( WPI_TMP_DIR . "*.pdf"));
		}

		/**
		 * Adds a box to the main column on the Post and Page edit screens.
		 */
		function add_meta_box_to_order_page() {
			add_meta_box(
				'order_page_create_invoice',
				__( 'PDF Invoice', 'myplugin_textdomain' ),
				array( &$this, 'show_order_page_create_invoice' ),
				'shop_order',
				'side',
				'high'
			);
		}

		function woocommerce_admin_order_actions_end( $order ) {
			?>
			<a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpi_create_invoice&order_id=<?php echo $order->id; ?>&nonce=<?php echo wp_create_nonce('wpi_create_invoice'); ?>" class="button tips wpi-admin-order-create-invoice-btn" target="_blank"></a>
		<?php
		}

		function show_order_page_create_invoice( $post ) {
			?>
			<a href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpi_create_invoice&order_id=<?php echo $post->ID; ?>&nonce=<?php echo wp_create_nonce('wpi_create_invoice'); ?>" target="_blank"><button type="button">Invoice</button></a>
			<?php
		}

		function wpi_create_invoice() {
			$action = $_REQUEST["action"];
			$order_id = $_REQUEST["order_id"];
			$nonce = $_REQUEST["nonce"];
			if (!wp_verify_nonce($nonce, $action)) {
				die( 'Invalid request' );
			} else if( empty($order_id) ) {
				die( 'Invalid order id');
			} else {
				// Valid request so generate the invoice.
				$invoice = new WPI_Invoice(new WC_Order($order_id));
				$invoice->generate("D");
			}
		}
	}
}