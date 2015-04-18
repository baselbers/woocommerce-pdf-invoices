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
		public $general_settings;

        /**
         * All template user settings
         * @var array
         */
		public $template_settings;

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
			'bewpi_general_settings' => 'General',
			'bewpi_template_settings' => 'Template'
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

			/*
			 * Initialize WooCommerce PDF Invoices
			 */
			add_action( 'init', array( &$this, 'init' ) );

            /**
             * Adds the Email It In email as an extra recipient
             */
			add_filter( 'woocommerce_email_headers', array( &$this, 'add_recipient_to_email_headers' ), 10, 2 );

            /**
             * Attach invoice to a specific WooCommerce email
             */
			add_filter( 'woocommerce_email_attachments', array( &$this, 'attach_invoice_to_email' ), 99, 3 );
		}

		/**
		 * Some preps
		 */
		public function init() {

			$this->invoice_actions();

			$this->load_textdomain();

			$this->create_invoices_dir();

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
			add_action( 'woocommerce_admin_order_actions_end', array( &$this, 'woocommerce_order_page_action_view_invoice' ) );

			/**
			 * Adds a meta box to the order details page.
			 */
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box_to_order_page' ) );

		}

        /**
         * Callback to sniff for specific plugin actions to view, create or delete invoice.
         */
        private function invoice_actions() {
            if( isset( $_GET['wpi_action'] ) && isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
                $action = $_GET['wpi_action'];
                $order_id = $_GET['post'];
                $nonce = $_REQUEST["nonce"];

                if (!wp_verify_nonce($nonce, $action)) {
                    die( 'Invalid request' );
                } else if( empty($order_id) ) {
                    die( 'Invalid order ID');
                } else {

                    $invoice = new BEWPI_Invoice(
	                    new WC_Order($order_id), $this->textdomain
                    );

                    switch( $_GET['wpi_action'] ) {
                        case "view":
                            $invoice->view_invoice( true );
                            break;
                        case "cancel":
                            $invoice->delete();
                            break;
                        case "create":
                            $invoice->generate( "F" );
                            break;
                    }
                }
            }
        }

        /**
         * Loads the textdomain and localizes the plugin options tabs.
         */
		private function load_textdomain() {
			load_plugin_textdomain( $this->textdomain, false, BEWPI_LANG_DIR );
			$this->settings_tabs['bewpi_general_settings'] = __( 'General', $this->textdomain );
			$this->settings_tabs['bewpi_template_settings'] = __( 'Template', $this->textdomain );
		}

        /**
         * Creates invoices dir in uploads folder
         */
        private function create_invoices_dir() {
            wp_mkdir_p( BEWPI_INVOICES_DIR . date( 'Y' ) . '/' );
        }

        /**
         * Delete pdf invoices from the tmp folder.
         */
		/*public function delete_pdf_invoices() {
			array_map('unlink', glob( BEWPI_INVOICES_DIR . "*.pdf"));
		}*/

        /**
         * Adds submenu to WooCommerce menu.
         */
		public function add_woocommerce_submenu_page() {
			add_submenu_page(
				'woocommerce',
				__( 'Invoices', $this->textdomain ),
				__( 'Invoices', $this->textdomain ),
				'manage_options', $this->options_key,
				array( &$this, 'options_page' )
			);
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
					document.getElementById("footer-thankyou").innerHTML = "If you like <strong>WooCommerce PDF Invoices</strong> please leave us a <a href='https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform'>★★★★★</a> rating. A huge thank you in advance!";
					document.getElementById("footer-upgrade").innerHTML = "Version <?php echo BEWPI_VERSION; ?>";
				};
			</script>
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
            $general_settings = (array) $this->general_settings->settings;
			if( $status == $general_settings['email_type'] ) {
				if( $general_settings['email_it_in']
					&& $general_settings['email_it_in_account'] != "" ) {
					$email_it_in_account = $general_settings['email_it_in_account'];
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
            $general_settings = $this->general_settings->settings;
			if( $status == $general_settings['email_type']
				|| $general_settings['new_order'] && $status == "new_order" ) {

                $invoice = new BEWPI_Invoice($order, $this->textdomain);

                if( $invoice->exists() ) {
                    $path_to_pdf = BEWPI_INVOICES_DIR . $invoice->get_formatted_invoice_number() . ".pdf";
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
            $invoice = new BEWPI_Invoice(new WC_Order($order->id), $this->textdomain);
            if( $invoice->exists() ) {
                $this->show_invoice_button('View invoice', $order->id, 'view', '', array('class="button tips wpi-admin-order-create-invoice-btn"') );
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
                    <td>' . __('Invoiced on:', $this->textdomain ) . '</td>
                    <td align="right"><b>' . $date . '</b></td>
                </tr>
                <tr>
                    <td>' . __('Invoice number:', $this->textdomain ) . '</td>
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
        private function show_invoice_button($title, $order_id, $wpi_action, $btn_title, $arr = array()) {
            $title = __( $title, $this->textdomain );
            $href = admin_url() . 'post.php?post=' . $order_id . '&action=edit&wpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce($wpi_action);
            $btn_title = __( $btn_title, $this->textdomain );

            $attr = '';
            foreach($arr as $str) {
                $attr .= $str . ' ';
            }

            $btn = '<a title="' . $title . '" href="' . $href . '" ' . $attr . '><button type="button" class="button grant_access">' . $btn_title . '</button></a>';
            echo $btn;
        }

        /**
         * Show all the meta box actions/buttons on the order details page to create, view or cancel/delete an invoice.
         * @param $post
         */
		public function woocommerce_order_details_page_meta_box_create_invoice( $post ) {
            $invoice = new BEWPI_Invoice(new WC_Order($post->ID), $this->textdomain);

            if( $invoice->exists() ) {
                $this->show_invoice_number_info( $invoice->get_formatted_invoice_date(), $invoice->get_formatted_number() );
                $this->show_invoice_button( __( 'View invoice', $this->textdomain ), $post->ID, 'view', __( 'View', $this->textdomain ), array('class="invoice-btn"') );
                $this->show_invoice_button( __( 'Cancel invoice', $this->textdomain ), $post->ID, 'cancel', __( 'Cancel', $this->textdomain ), array('class="invoice-btn"', 'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', $this->textdomain ) . '\')"' ) );
            } else {
                $this->show_invoice_button( __( 'Create invoice', $this->textdomain ), $post->ID, 'create', __( 'Create', $this->textdomain ), array('class="invoice-btn"') );
            }
		}
	}
}