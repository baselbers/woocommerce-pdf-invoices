<?php

if ( ! class_exists( 'WPI_Settings' ) ) {

    class WPI_Settings {
        private $options_key = 'wpi-invoices';
        protected $settings_tabs = array();
        public $general;
        public $template;

        public function __construct() {
            $this->general = new WPI_General_Settings();
            $this->settings_tabs[$this->general->settings_key] = 'General'; // Add tab

            $this->template = new WPI_Template_Settings();
            $this->settings_tabs[$this->template->settings_key] = 'Template'; // Add tab

            add_action('admin_menu', array($this, 'add_woocommerce_submenu_page'));
            add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_notices', array(&$this, 'admin_notices' ) );
        }

        public function add_woocommerce_submenu_page() {
            add_submenu_page('woocommerce', 'Invoices by Bas Elbers', 'Invoices', 'manage_options', $this->options_key, array($this, 'options_page'));
        }

        public function admin_enqueue_scripts() {
            wp_enqueue_script( 'admin_settings_script', WPI_URL . '/assets/js/admin.js' );
            wp_register_style( 'admin_settings_css', WPI_URL . '/assets/css/admin.css', false, '1.0.0' );
            wp_enqueue_style( 'admin_settings_css' );
        }

        function plugin_options_tabs() {
            $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general->settings_key;

            screen_icon();
            echo '<h2 class="nav-tab-wrapper">';
            foreach ( $this->settings_tabs as $tab_key => $tab_caption ) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
            echo '</h2>';
        }

        function options_page() {
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general->settings_key;
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
    }
}