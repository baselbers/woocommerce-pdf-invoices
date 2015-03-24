<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BE_WooCommerce_PDF_Invoices' ) ) {

    /**
     * Implements main function for attaching invoice to email and show invoice buttons.
     */
	class BE_WooCommerce_PDF_Invoices {

        /**
         * All general user settings
         * @var array
         */
		public $general_settings = array();

        /**
         * All template user settings
         * @var array
         */
		public $template_settings = array();

        /**
         * Constant options key
         * @var string
         */
		private $options_key = 'wpi-invoices';

        /**
         * All the settings tabs for the settings page.
         * @var array
         */
		private $settings_tabs = array(
			'general_settings' => 'General',
			'template_settings' => 'Template'
		);

        /**
         * Default textdomain.
         * @var string
         */
		private $textdomain = 'be-woocommerce-pdf-invoices';

        /**
         * Initialize plugin and register actions and filters.
         *
         * @param $general_settings
         * @param $template_settings
         */
		public function __construct($general_settings, $template_settings) {

			$this->general_settings = $general_settings;

			$this->template_settings = $template_settings;

            /**
             * Plugin actions to create, view or delete invoice.
             */
            add_action( 'init', array( &$this, 'init_plugin_actions' ) );

            /**
             * Initialize all the ajax calls.
             */
            //add_action( 'init', array( &$this, 'init_ajax_calls' ) );

            /**
             * Set textdomain.
             */
			add_action( 'init', array( &$this, 'init_load_textdomain' ) );

            /**
             * Delete invoices from tmp folder.
             */
			//add_action( 'admin_init', array( &$this, 'delete_pdf_invoices' ) );

            /**
             * Adds submenu to WooCommerce menu.
             */
			add_action( 'admin_menu', array( &$this, 'add_woocommerce_submenu_page' ) );

            /**
             * Enqueue admin scripts
             */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

            /**
             * Adds the Email It In email as an extra recipient
             */
			add_filter( 'woocommerce_email_headers', array( &$this, 'add_recipient_to_email_headers' ), 10, 2 );

            /**
             * Attach invoice to a specific WooCommerce email
             */
			add_filter( 'woocommerce_email_attachments', array( &$this, 'attach_invoice_to_email' ), 99, 3 );

            /**
             * Adds some actions to the all orders page.
             */
			add_action( 'woocommerce_admin_order_actions_end', array( &$this, 'woocommerce_order_page_action_view_invoice' ) );

            /**
             * Adds a meta box to the order details page.
             */
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box_to_order_page' ) );
		}

        /**
         * Callback to sniff for specific plugin actions to view, create or delete invoice.
         */
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
                            $invoice->delete();
                            break;
                        case "create":
                            $invoice->generate("F");
                            break;
                    }
                }
            }
        }

        /**
         * No need for AJAX calls right now.
         */
        /*public function init_ajax_calls() {
            add_action( 'wp_ajax_wpi_show_invoice', array( &$this, 'wpi_show_invoice' ) );
            add_action( 'wp_ajax_nopriv_wpi_show_invoice', array( &$this, 'wpi_show_invoice' ) );
        }*/

        /**
         * Loads the textdomain and localizes the plugin options tabs.
         */
		public function init_load_textdomain() {
			load_plugin_textdomain( $this->textdomain, false, WPI_LANG_DIR );
			$this->settings_tabs['general_settings'] = __( 'General', $this->textdomain );
			$this->settings_tabs['template_settings'] = __( 'Template', $this->textdomain );
		}

        /**
         * Delete pdf invoices from the tmp folder.
         */
		/*public function delete_pdf_invoices() {
			array_map('unlink', glob( WPI_TMP_DIR . "*.pdf"));
		}*/

        /**
         * Adds submenu to WooCommerce menu.
         */
		public function add_woocommerce_submenu_page() {
			add_submenu_page( 'woocommerce', __( 'Invoices', $this->textdomain ), __( 'Invoices', $this->textdomain ), 'manage_options', $this->options_key, array( &$this, 'options_page' ) );
		}

        /**
         * Admin scripts
         */
		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'admin_settings_script', WPI_URL . '/assets/js/admin.js' );
			wp_register_style( 'admin_settings_css', WPI_URL . '/assets/css/admin.css', false, '1.0.0' );
			wp_enqueue_style( 'admin_settings_css' );
		}

        /**
         * Callback function for adding plugin options tabs.
         */
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

        /**
         * The options page..
         */
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

        /**
         * Adds the Email It In email as an extra recipient
         *
         * @param $headers
         * @param $status
         * @return string
         */
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

        /**
         * Attaches invoice to a specific WooCommerce email. Invoice will only be generated when it does not exists already.
         * @param $attachments
         * @param $status
         * @param $order
         * @return array
         */
		function attach_invoice_to_email( $attachments, $status, $order ) {
			if( $status == $this->general_settings->settings['email_type']
				|| $this->general_settings->settings['new_order'] && $status == "new_order" ) {

                $invoice = new WPI_Invoice($order, $this->textdomain);

                if( $invoice->exists() ) {
                    $path_to_pdf = WPI_TMP_DIR . $invoice->get_formatted_invoice_number() . ".pdf";
                } else {
                    $path_to_pdf = $invoice->generate("F");
                }

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

        /**
         * Shows the view invoice button on the all orders page.
         *
         * @param $order
         */
		public function woocommerce_order_page_action_view_invoice( $order ) {
            $invoice = new WPI_Invoice(new WC_Order($order->id), $this->textdomain);
            if( $invoice->exists() ) {
                $this->show_invoice_button('View invoice', $order->id, 'view', '', ['class="button tips wpi-admin-order-create-invoice-btn"'] );
            }
		}

        /**
         * Shows invoice number info on the order details page.
         * @param $date
         * @param $number
         */
        private function show_invoice_number_info($date, $number) {
            echo '<table class="invoice-info" width="100%">
                <tr>
                    <td>Invoiced on:</td>
                    <td align="right">' . $date . '</td>
                </tr>
                <tr>
                    <td>Invoice number:</td>
                    <td align="right">' . $number . '</td>
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
        private function show_invoice_button($title, $order_id, $wpi_action, $btn_title, $arr = []) {
            $title = __( $title, $this->textdomain );
            $href = admin_url() . 'post.php?post=' . $order_id . '&action=edit&wpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce($wpi_action);
            $btn_title = __( $btn_title, $this->textdomain );

            $attr = '';
            foreach($arr as $str) {
                $attr .= $str . ' ';
            }

            echo '<a title="' . $title . '" href="' . $href . '" ' . $attr . '><button type="button" class="button grant_access">' . $btn_title . '</button></a>';
        }

        /**
         * Show all the meta box actions/buttons on the order details page to create, view or cancel/delete an invoice.
         * @param $post
         */
		public function woocommerce_order_details_page_meta_box_create_invoice( $post ) {
            $invoice = new WPI_Invoice(new WC_Order($post->ID), $this->textdomain);

            if( $invoice->exists() ) {
                $this->show_invoice_number_info($invoice->get_formatted_date(), $invoice->get_formatted_invoice_number());
                $this->show_invoice_button('View invoice', $post->ID, 'view', 'View' );
                $this->show_invoice_button('Cancel invoice', $post->ID, 'cancel', 'Cancel', ['onclick="return confirm(\'Are you sure to delete the invoice?\')"'] );
            } else {
                $this->show_invoice_button('Create invoice', $post->ID, 'create', 'Create' );
            }
		}
	}
}