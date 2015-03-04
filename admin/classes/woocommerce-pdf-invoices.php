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
			$this->general_settings = $general_settings;
			$this->template_settings = $template_settings;
			add_action( 'admin_menu', array(&$this, 'add_woocommerce_submenu_page'));
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_notices', array(&$this, 'admin_notices' ) );
			//add_action('plugins_loaded', array($this, 'plugins_loaded') );
			add_shortcode( 'foobar', array(&$this, 'foobar_func') );
			add_filter( 'woocommerce_email_attachments', array($this, 'woocommerce_email_attachements',10,3 ));
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

		public function woocommerce_email_attachements( $attachments, $status , $order ) {}
		public function plugins_loaded() {}

		function foobar_func( $atts ){
			if ( class_exists('WC_Order') ) {
				$invoice = new WPI_Invoice(new WC_Order(124));
				$invoice->generate();
			}
		}
	}
}