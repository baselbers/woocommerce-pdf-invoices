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

		private $textdomain = 'be-woocommerce-pdf-invoices';

		public function __construct($general_settings, $template_settings) {

			$this->general_settings = $general_settings;

			$this->template_settings = $template_settings;

            add_action( 'init', array( &$this, 'init_plugin_actions' ) );

            add_action( 'init', array( &$this, 'init_ajax_calls' ) );

			add_action( 'init', array( &$this, 'init_load_textdomain' ) );

			//add_action( 'admin_init', array( &$this, 'delete_pdf_invoices' ) );

			add_action( 'admin_menu', array( &$this, 'add_woocommerce_submenu_page' ) );

			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

			add_filter( 'woocommerce_email_headers', array( &$this, 'add_recipient_to_email_headers' ), 10, 2 );

			add_filter( 'woocommerce_email_attachments', array( &$this, 'attach_invoice_to_email' ), 99, 3 );

			add_action( 'woocommerce_admin_order_actions_end', array( &$this, 'woocommerce_order_page_action_create_invoice' ) );

			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box_to_order_page' ) );
		}

        public function init_ajax_calls() {
            add_action( 'wp_ajax_wpi_show_invoice', array( &$this, 'wpi_show_invoice' ) );
            add_action( 'wp_ajax_nopriv_wpi_show_invoice', array( &$this, 'wpi_show_invoice' ) );
        }

		public function init_load_textdomain() {
			load_plugin_textdomain( $this->textdomain, false, WPI_LANG_DIR );
			$this->settings_tabs['general_settings'] = __( 'General', $this->textdomain );
			$this->settings_tabs['template_settings'] = __( 'Template', $this->textdomain );
		}

		public function delete_pdf_invoices() {
			array_map('unlink', glob( WPI_TMP_DIR . "*.pdf"));
		}

		public function add_woocommerce_submenu_page() {
			add_submenu_page( 'woocommerce', __( 'Invoices', $this->textdomain ), __( 'Invoices', $this->textdomain ), 'manage_options', $this->options_key, array( &$this, 'options_page' ) );
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
				<form class="be_woocommerce_pdf_invoices_settings_form" method="post" action="options.php" enctype="multipart/form-data">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		<?php
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
				$invoice = new WPI_Invoice($order, $this->textdomain);
				$path_to_pdf = $invoice->generate("F");
				$attachments[] = $path_to_pdf;
			}
			return $attachments;
		}

		/**
		 * Adds a box to the main column on the Post and Page edit screens.
		 */
		function add_meta_box_to_order_page() {
			add_meta_box(
				'order_page_create_invoice',
				__( 'PDF Invoice', $this->textdomain ),
				array( &$this, 'woocommerce_order_details_page_meta_box_create_invoice' ),
				'shop_order',
				'side',
				'high'
			);
		}

		function woocommerce_order_page_action_create_invoice( $order ) {
			?>
			<a title="<?php _e( 'Show invoice', $this->textdomain ); ?>" href="<?php echo admin_url('admin-ajax.php'); ?>?action=wpi_show_invoice&order_id=<?php echo $order->id; ?>&nonce=<?php echo wp_create_nonce('wpi_show_invoice'); ?>" class="button tips wpi-admin-order-create-invoice-btn" target="_blank"></a>
		<?php
		}

        private function show_invoice_number_info($date, $number) {
            echo '<table class="invoice-info">
                <tr>
                    <td>Invoiced on:</td>
                    <td>' . $date . '</td>
                </tr>
                <tr>
                    <td>Invoice number:</td>
                    <td>' . $number . '</td>
                </tr>
            </table>';
        }

        private function show_invoice_button($title, $wpi_action, $btn_title) {
            $title = __( $title, $this->textdomain );
            $href = admin_url() . 'post.php?post=' . $_GET['post'] . '&action=edit&wpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce($wpi_action);
            $btn_title = __( $btn_title, $this->textdomain );
            echo '<a title="' . $title . '" href="' . $href . '"><button type="button">' . $btn_title . '</button></a>';
        }

		function woocommerce_order_details_page_meta_box_create_invoice( $post ) {
            $invoice = new WPI_Invoice(new WC_Order($post->ID), $this->textdomain);

            $this->show_invoice_number_info(
                $invoice->get_formatted_date(),
                $invoice->get_formatted_invoice_number()
            );

            if( $invoice->exists() ) {
                $this->show_invoice_button('View invoice', 'view', 'View' );
                $this->show_invoice_button('Cancel invoice', 'cancel', 'Cancel' );
            } else {
                $this->show_invoice_button('Create invoice', 'create', 'Create' );
            }
		}

        public function init_plugin_actions() {
            if( isset( $_GET['wpi_action'] ) && isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
                $action = $_GET['wpi_action'];
                $order_id = $_GET['post'];
                $nonce = $_REQUEST["nonce"];

                if (!wp_verify_nonce($nonce, $action)) {
                    die( 'Invalid request' );
                } else if( empty($order_id) ) {
                    die( 'Invalid order ID');
                } else {
                    $invoice = new WPI_Invoice(new WC_Order($order_id), $this->textdomain);
                    switch( $_GET['wpi_action'] ) {
                        case "view":
                            $invoice->view_invoice( true );
                            break;
                        case "cancel":
                            if( $invoice->exists() )
                                unlink( $invoice->get_file() );
                            break;
                        case "create":
                            if( !$invoice->exists() )
                                $invoice->generate("F");
                            break;
                    }
                }
            }
        }

        public function wpi_show_invoice() {
            $action = $_REQUEST["action"];
            $order_id = $_REQUEST["order_id"];
            $nonce = $_REQUEST["nonce"];
            if (!wp_verify_nonce($nonce, $action)) {
                die( 'Invalid request' );
            } else if( empty( $order_id ) ) {
                die( 'Invalid order id' );
            } else {
                $invoice = new WPI_Invoice(new WC_Order($order_id), $this->textdomain);
                $invoice->view_invoice( true );
            }
        }
	}
}