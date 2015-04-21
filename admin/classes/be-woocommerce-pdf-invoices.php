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
		public function __construct() {

			new BEWPI_General_Settings();
			new BEWPI_Template_Settings();

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

			/**
			 * AJAX calls to download invoice
			 */
			add_action( 'wp_ajax_bewpi_download_invoice', array( &$this, 'bewpi_download_invoice' ) );
			add_action( 'wp_ajax_nopriv_bewpi_download_invoice', array( &$this, 'bewpi_download_invoice' ) );
		}

		/**
		 * Some preps
		 */
		public function init() {

			/**
			 * Init invoice actions to view, delete or save invoice.
			 */
			$this->invoice_actions();

			/**
			 * Loads the global textdomain for the plugin.
			 */
			$this->load_textdomain();

			/**
			 * Creates invoices folder in the uploads dir.
			 */
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

			/**
			 * Adds a download link for the pdf invoice on the my account page
			 */
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_my_account_download_pdf_action' ), 10, 2 );
		}

        /**
         * Callback to sniff for specific plugin actions to view, create or delete invoice.
         */
        private function invoice_actions() {
            if( isset( $_GET['bewpi_action'] ) && isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
                $action = $_GET['bewpi_action'];
                $order_id = $_GET['post'];
                $nonce = $_REQUEST["nonce"];

                if ( !wp_verify_nonce( $nonce, $action ) ) {
                    die( 'Invalid request' );
                } else if ( empty( $order_id ) ) {
                    die( 'Invalid order ID' );
                } else {

                    $invoice = new BEWPI_Invoice( $order_id );

                    switch( $_GET['bewpi_action'] ) {
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
            if ( !wp_mkdir_p( BEWPI_INVOICES_DIR . date( 'Y' ) . '/' ) )
		        copy( BEWPI_DIR . 'tmp/.htaccess', BEWPI_INVOICES_DIR . date( 'Y' ) . '/.htaccess' );
		        copy( BEWPI_DIR . 'tmp/index.php', BEWPI_INVOICES_DIR . date( 'Y' ) . '/index.php' );
        }

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
					// Change footer text into rate text for WPI.
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
            $general_options = get_option( 'bewpi_general_settings' );
			if ( $status == $general_options['bewpi_email_type']
			    && $general_options['bewpi_email_it_in']
				&& !empty( $general_options['bewpi_email_it_in_account'] ) ) {
					$email_it_in_account = $general_options['bewpi_email_it_in_account'];
					$headers .= 'BCC: <' . $email_it_in_account . '>' . "\r\n";
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
            $general_options = get_option( 'bewpi_general_settings' );
			if ( $status == $general_options['bewpi_email_type']
				|| $general_options['bewpi_new_order']
				   && $status == "new_order" ) :

				$invoice = new BEWPI_Invoice( $order->id );

				if ( !$invoice->exists() ) :
					$filename = $invoice->save( "F" );
				else :
					$filename = $invoice->get_filename();
				endif;

				$attachments[] = $filename;
			endif;
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
            $invoice = new BEWPI_Invoice( $order->id );
            if( $invoice->exists() )
                $this->show_invoice_button( 'View invoice', $order->id, 'view', '', array('class="button tips wpi-admin-order-create-invoice-btn"') );
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
        private function show_invoice_button( $title, $order_id, $wpi_action, $btn_title, $arr = array() ) {
            $title = __( $title, $this->textdomain );
            $href = admin_url() . 'post.php?post=' . $order_id . '&action=edit&bewpi_action=' . $wpi_action . '&nonce=' . wp_create_nonce($wpi_action);
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
            $invoice = new BEWPI_Invoice( $post->ID );

            if( $invoice->exists() ) {
                $this->show_invoice_number_info( $invoice->get_formatted_invoice_date(), $invoice->get_formatted_number() );
                $this->show_invoice_button( __( 'View invoice', $this->textdomain ), $post->ID, 'view', __( 'View', $this->textdomain ), array('class="invoice-btn"') );
                $this->show_invoice_button( __( 'Cancel invoice', $this->textdomain ), $post->ID, 'cancel', __( 'Cancel', $this->textdomain ), array('class="invoice-btn"', 'onclick="return confirm(\'' . __( 'Are you sure to delete the invoice?', $this->textdomain ) . '\')"' ) );
            } else {
                $this->show_invoice_button( __( 'Create invoice', $this->textdomain ), $post->ID, 'create', __( 'Create', $this->textdomain ), array('class="invoice-btn"') );
            }
		}

		/**
		 * AJAX action to download invoice
		 */
		public function bewpi_download_invoice() {
			if( isset( $_GET['action'] ) && isset( $_GET['order_id'] ) && isset( $_GET['nonce'] ) ) {
				$action = $_GET['action'];
				$order_id = $_GET['order_id'];
				$nonce = $_REQUEST["nonce"];

				if ( !wp_verify_nonce( $nonce, $action ) ) {
					die( 'Invalid request' );
				} else if ( empty( $order_id ) ) {
					die( 'Invalid order ID' );
				} else {
					$invoice = new BEWPI_Invoice( $order_id );
					$invoice->view( true );
				}
			}
		}

		/**
		 * Display download link on My Account page
		 */
		public function add_my_account_download_pdf_action( $actions, $order ) {
			$invoice = new BEWPI_Invoice( $order->id );
			if ( $invoice->exists() && $invoice->is_download_allowed( $order->post_status ) ) {
				$url = admin_url( 'admin-ajax.php?action=bewpi_download_invoice&order_id=' . $order->id . '&nonce=' . wp_create_nonce( 'bewpi_download_invoice' ) );
				$actions['invoice'] = array(
					'url'  => $url,
					'name' => __( 'Download invoice (PDF)', $this->textdomain )
				);
			}
			return $actions;
		}
	}
}