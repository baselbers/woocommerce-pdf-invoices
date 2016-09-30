<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Abstract_Invoice' ) ) {

    /**
     * Makes the invoice.
     * Class BEWPI_Invoice
     */
    class BEWPI_Abstract_Invoice extends BEWPI_Abstract_Document
    {

        /**
         * @var WC_Order
         */
        public $order;

        /**
         * @var array
         */
        public $orders = array();

        /**
         * Invoice number
         * @var integer
         */
        protected $number;

        /**
         * Formatted invoice number with prefix and/or suffix
         * @var string
         */
        protected $formatted_number;

        /**
         * Creation date.
         * @var datetime
         */
        protected $date;

        /**
         * Creation year
         * @var datetime
         */
        protected $year;

        /**
         * Number of columns for the products table
         * @var integer
         */
        public $columns_count;

        /**
         * Colspan data for product table cells
         * @var array
         */
        protected $colspan;

        /**
         * Width of the description cell of the product table
         * @var string
         */
        protected $desc_cell_width;

        /**
         * Name of the template
         * @var string
         */
        protected $template_name;

        /**
         * Type of invoice
         * @var string
         */
        protected $type;

        /**
         * Dir of the template
         * @var string
         */
        protected $template_dir_name;

        /**
         * Next invoice counter reset enabling
         * @var bool
         */
        protected $counter_reset = false;

        /***
         * BEWPI_Abstract_Invoice constructor.
         * @param $order_id
         * @param $type
         * @param int $taxes_count
         */
        public function __construct($order_id, $type, $taxes_count = 0)
        {
            parent::__construct();
            $this->order = wc_get_order($order_id);
            $this->type = $type;
            $this->columns_count = $this->get_columns_count($taxes_count);
            $this->formatted_number = get_post_meta($this->order->id, '_bewpi_formatted_invoice_number', true);
            $this->template_name = $this->template_options["bewpi_template_name"];

            // Check if the invoice already exists.
            if (!empty($this->formatted_number) || isset($_GET['bewpi_action']) && $_GET['bewpi_action'] !== 'cancel')
                $this->init();
        }

        /**
         * Gets all the existing invoice data from database or creates new invoice number.
         */
        private function init()
        {
            $this->number = get_post_meta($this->order->id, '_bewpi_invoice_number', true);
            $this->year = get_post_meta($this->order->id, '_bewpi_invoice_year', true);
            $this->filename = $this->formatted_number . '.pdf';
            $this->full_path = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->filename;
            $this->date = get_post_meta($this->order->id, '_bewpi_invoice_date', true);
        }

        /**
         * Format the invoice number with prefix and/or suffix.
         * @return mixed
         */
        public function get_formatted_number()
        {
            // Check if the users uses a third-party numbering plugin
            if ( $this->template_options[ 'bewpi_invoice_number_type' ] == "third_party" ) {
                return apply_filters( 'woocommerce_invoice_number',
                                      $this->order->id, // Default is order ID
                                      $this->order->id );
            }
            $invoice_number_format = $this->template_options['bewpi_invoice_number_format'];
            // Format number with the number of digits
            $digit_str = "%0" . $this->template_options['bewpi_invoice_number_digits'] . "s";
            $digitized_invoice_number = sprintf($digit_str, $this->number);
            $year = date_i18n('Y');
            $y = date_i18n('y');
            $m = date_i18n('m');

            // Format invoice number
            $formatted_invoice_number = str_replace(
                array('[prefix]', '[suffix]', '[number]', '[Y]', '[y]', '[m]'),
                array($this->template_options['bewpi_invoice_number_prefix'], $this->template_options['bewpi_invoice_number_suffix'], $digitized_invoice_number, (string)$year, (string)$y, (string)$m),
                $invoice_number_format);

            return $formatted_invoice_number;
        }

        /**
         * Format date
         * @param bool $insert
         * @return bool|datetime|string
         */
        public function get_formatted_invoice_date()
        {
            $date_format = $this->template_options['bewpi_date_format'];
            return (!empty($date_format)) ? date_i18n($date_format, current_time('timestamp')) : date_i18n("d-m-Y", current_time('timestamp'));
        }

        /*
         * Format the order date and return
         */
        public function get_formatted_order_date($order_id = 0)
        {
            if ($order_id != 0) {
                // format date for global invoice
                $order = wc_get_order($order_id);
                $order_date = $order->order_date;
            } else {
                $order_date = $this->order->order_date;
            }

            $order_date = DateTime::createFromFormat('Y-m-d H:i:s', $order_date);
            if (!empty ($this->template_options['bewpi_date_format'])) {
                $date_format = $this->template_options['bewpi_date_format'];
                $formatted_date = $order_date->format($date_format);
                return date_i18n($date_format, strtotime($formatted_date));
            } else {
                $formatted_date = $order_date->format('d-m-Y');
                return date_i18n("d-m-Y", strtotime($formatted_date));
            }
        }

        /**
         * Get all html from html files and store as vars
         */
        private function output_template_files_to_buffer($html_template_files)
        {
            do_action('bewpi_before_output_template_to_buffer', array('order_id' => $this->order->id));
            $html_sections = array();

            foreach ($html_template_files as $section => $full_path) {
                $html = ($section === 'style') ? $this->output_style_to_buffer($full_path) : $this->output_to_buffer($full_path);
                $html_sections[$section] = $html;
            }

            do_action('bewpi_after_output_template_to_buffer');

            return $html_sections;
        }

        private function delete_pdf_invoices()
        {
            if ((bool)$this->template_options['bewpi_reset_counter_yearly']) {
                $current_year = (int)date_i18n('Y', current_time('timestamp'));
                $bewpi_invoices_dir = BEWPI_INVOICES_DIR . $current_year . '/*.pdf';
            } else {
                $bewpi_invoices_dir = BEWPI_INVOICES_DIR . '*.pdf';
            }

            $files = glob($bewpi_invoices_dir);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        private function delete_invoice_meta()
        {
            global $wpdb;

            if ((bool)$this->template_options['bewpi_reset_counter_yearly']) {
                // delete all by year
                $query = $wpdb->prepare(
                    "
					DELETE pm2 FROM $wpdb->postmeta pm1
					INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s'
			            AND pm1.meta_value = %d
			            AND (pm2.meta_key LIKE '%s' OR pm2.meta_key LIKE '%s')
			        ",
                    "_bewpi_invoice_year",
                    (int)date_i18n('Y', current_time('timestamp')),
                    "_bewpi_invoice_%",
                    "_bewpi_formatted_%"
                );
            } else {
                // delete all
                $query = $wpdb->prepare(
                    "
					DELETE FROM $wpdb->postmeta
			        WHERE meta_key = '%s'
			          OR meta_key = '%s'
			          OR meta_key = '%s'
			          OR meta_key = '%s'
			        ",
                    "_bewpi_invoice_number",
                    "_bewpi_formatted_invoice_number",
                    "_bewpi_invoice_date",
                    "_bewpi_invoice_year"
                );
            }

            $wpdb->query($query);
        }

        private function get_next_invoice_number()
        {
            // Check if the users uses a third-party numbering plugin
            if ( $this->template_options[ 'bewpi_invoice_number_type' ] == "third_party" ) {
                return apply_filters( 'woocommerce_generate_invoice_number',
                                      $this->order->id, // Default is order ID
                                      $this->order );
            }

            // check if user uses the built in WooCommerce order numbers
            if ($this->template_options['bewpi_invoice_number_type'] !== "sequential_number")
                return $this->order->get_order_number();

            // check if user did a counter reset
            if ($this->template_options['bewpi_reset_counter'] && $this->template_options['bewpi_next_invoice_number'] > 0) {
                $this->counter_reset = true;

                $this->delete_pdf_invoices();
                $this->delete_invoice_meta();

                // uncheck option to actually change the value
                $this->template_options['bewpi_reset_counter'] = 0;
                update_option('bewpi_template_settings', $this->template_options);

                return $this->template_options['bewpi_next_invoice_number'];
            }

            $last_invoice_number = $this->get_max_invoice_number();
            return (empty($last_invoice_number)) ? 1 : (int)$last_invoice_number + 1;
        }

        public function get_max_invoice_number()
        {
            global $wpdb;

            if ((bool)$this->template_options['bewpi_reset_counter_yearly']) {
                // get all by year
                $query = $wpdb->prepare(
                    "
					SELECT max(cast(pm2.meta_value as unsigned)) as last_invoice_number
					FROM $wpdb->postmeta pm1 INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s'
			            AND pm1.meta_value = %d
			            AND pm2.meta_key = '%s'
			        ",
                    "_bewpi_invoice_year",
                    (int)date_i18n('Y', current_time('timestamp')),
                    "_bewpi_invoice_number"
                );
            } else {
                // get all
                $query = $wpdb->prepare(
                    "
					SELECT max(cast(pm2.meta_value as unsigned)) as last_invoice_number
					FROM $wpdb->postmeta pm1 INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
			        WHERE pm1.meta_key = '%s' AND pm2.meta_key = '%s'
			        ",
                    "_bewpi_invoice_year",
                    "_bewpi_invoice_number"
                );
            }

            return $wpdb->get_var($query);
        }

        /**
         * Generates and saves the invoice to the uploads folder.
         * @param $dest
         * @return string
         */
        protected function save($dest, $html_templates)
        {
            $this->general_options = get_option('bewpi_general_settings');
            $this->template_options = get_option('bewpi_template_settings');

            do_action("bewpi_before_invoice_content", $this->order->id);

            if ($this->exists()) {
                // delete postmeta and PDF
                $this->delete();
            }

            $this->number = $this->get_next_invoice_number();
            $this->formatted_number = $this->get_formatted_number();
            $this->filename = $this->formatted_number . '.pdf';
            $this->year = date_i18n('Y', current_time('timestamp'));
            $this->full_path = BEWPI_INVOICES_DIR . (string)$this->year . '/' . $this->filename;

            // update invoice data in db
            update_post_meta($this->order->id, '_bewpi_formatted_invoice_number', $this->formatted_number);
            update_post_meta($this->order->id, '_bewpi_invoice_number', $this->number);
            update_post_meta($this->order->id, '_bewpi_invoice_year', $this->year);
            $this->date = $this->get_formatted_invoice_date();
            update_post_meta($this->order->id, '_bewpi_invoice_date', $this->date);

            $this->colspan = $this->get_colspan();
            $html_sections = $this->output_template_files_to_buffer($html_templates);
            $paid = $this->is_paid();

            do_action('bewpi_before_document_generation', array('type' => $this->type, 'order_id' => $this->order->id));

            parent::generate($html_sections, $dest, $paid);

            do_action("bewpi_after_invoice_content", $this->order->id);

            return $this->full_path;
        }

        /**
         * Checks if order is paid
         * @return bool
         */
        public function is_paid()
        {
            $payment_methods = apply_filters('bewpi_paid_watermark_excluded_payment_methods', array('bacs', 'cod', 'cheque'), $this->order->id);
            if (in_array($this->order->payment_method, $payment_methods)) {
                return false;
            }

            $order_statuses = apply_filters('bewpi_paid_watermark_excluded_order_statuses', array('pending', 'on-hold', 'auto-draft'), $this->order->id);
            return (!in_array($this->order->get_status(), $order_statuses));
        }

        public function view()
        {
            if (!$this->exists()) {
                wp_die(sprintf(__('Invoice with invoice number %s not found. First create invoice and try again.', 'woocommerce-pdf-invoices'), $this->formatted_number),
                    '',
                    array('response' => 200, 'back_link' => true)
                );
            }

            parent::view();
        }

        /**
         * Delete all invoice data from database and the file.
         */
        public function delete()
        {
            // remove all invoice data from db
            delete_post_meta($this->order->id, '_bewpi_invoice_number');
            delete_post_meta($this->order->id, '_bewpi_formatted_invoice_number');
            delete_post_meta($this->order->id, '_bewpi_invoice_date');
            delete_post_meta($this->order->id, '_bewpi_invoice_year');

            do_action('bewpi_after_post_meta_deletion', $this->order->id);

            // delete file
            if ($this->exists())
                parent::delete();
        }

        /**
         * @param $order_status
         * Customer is only allowed to download invoice if the status of the order matches the email type option.
         * @return bool
         */
        public function is_download_allowed($order_status)
        {
            if ($order_status === "wc-completed") {
                return true;
            }

            // if user selected email type 'Cutomer Processing Order' download is also allowed.
            return ($order_status === "wc-processing" && $this->general_options['bewpi_email_type'] === "customer_processing_order");
        }

        /**
         * Display company name if logo is not found.
         * Convert image to base64 due to incompatibility of subdomains with MPDF
         */
        public function get_company_logo_html()
        {
            if (!empty($this->template_options['bewpi_company_logo'])) {
                // get the relative path due to slow generation of invoice.
                $image_path = str_replace(get_site_url(), '..', $this->template_options['bewpi_company_logo']);
                // give the user the option to change the image (path/url) due to some errors of mPDF.
                $image_url = apply_filters('bewpi_company_logo_url', $image_path);

                echo '<img class="company-logo" src="' . $image_url . '"/>';
            } else {
                echo '<h1 class="company-logo">' . $this->template_options['bewpi_company_name'] . '</h1>';
            }
        }

        /**
         * Get VAT number from WooCommerce EU VAT Number plugin
         */
        public function display_vat_number()
        {
            $vat_number = get_post_meta($this->order->id, '_vat_number', true);
            if ($vat_number !== '') {
                echo '<span>' . sprintf(__('VAT Number: %s', 'woocommerce-pdf-invoices'), $vat_number) . '</span>';
            }
        }

        /**
         * Get PO Number from WooCommerce Purchase Order Gateway plugin
         */
        public function display_purchase_order_number()
        {
            if (isset($this->order->payment_method) && $this->order->payment_method === 'woocommerce_gateway_purchase_order') {
                $po_number = get_post_meta($this->order->id, '_po_number', true);
                if ($po_number !== '') {
                    echo '<span>' . sprintf(__('Purchase Order Number: %s', 'woocommerce-gateway-purchase-order'), $po_number) . '</span>';
                }
            }
        }

        private function output_to_buffer($full_path)
        {
            ob_start();
            require_once($full_path);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        private function output_style_to_buffer($full_path)
        {
            return '<style>' . file_get_contents($full_path) . '</style>';
        }

        public function outlining_columns_html()
        {
            ?>
            <style>
                <?php
                // Create css for outlining the product cells.
                $righter_product_row_tds_css = "";
                for ( $td = $this->colspan['left'] + 1; $td <= $this->columns_count; $td++ ) {
                    if ( $td !== $this->columns_count ) {
                        $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . "),";
                    } else {
                          $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . ")";
                          $righter_product_row_tds_css .= "{ width: " . ( 50 / $this->colspan['right'] ) . "%; }";
                    }
                }
                echo $righter_product_row_tds_css;
                ?>
                tr.product-row td:nth-child(1) {
                    width: <?php echo $this->desc_cell_width; ?>;
                }
            </style>
            <?php
        }

        private function get_columns_count($taxes_count)
        {
            $columns_count = 4;

            if ($this->template_options['bewpi_show_sku'])
                $columns_count++;

            if ($this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty($legacy_order))
                $columns_count += $taxes_count;

            return $columns_count;
        }

        /**
         * Calculates colspan for table footer cells
         * @return array
         */
        public function get_colspan()
        {
            $colspan = array();
            $number_of_left_half_columns = 3;
            $this->desc_cell_width = '30%';

            // The product table will be split into 2 where on the right 5 columns are the max
            if ($this->columns_count <= 4) :
                $number_of_left_half_columns = 1;
                $this->desc_cell_width = '48%';
            elseif ($this->columns_count <= 6) :
                $number_of_left_half_columns = 2;
                $this->desc_cell_width = '35.50%';
            endif;

            $colspan['left'] = $number_of_left_half_columns;
            $colspan['right'] = $this->columns_count - $number_of_left_half_columns;
            $colspan['right_left'] = round(($colspan['right'] / 2), 0, PHP_ROUND_HALF_DOWN);
            $colspan['right_right'] = round(($colspan['right'] / 2), 0, PHP_ROUND_HALF_UP);

            return $colspan;
        }

        /**
         * Determine if the template is a custom or standard
         * @param $template_name
         * @return string
         */
        protected function get_template_dir($template_name)
        {
            // check if a custom template exists.
            $custom_template_dir = BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
            if (file_exists($custom_template_dir)) {
                return $custom_template_dir;
            }

            $template_dir = BEWPI_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
            if (file_exists($template_dir)) {
                return $template_dir;
            }
        }

        public function get_full_path()
        {
            return $this->full_path;
        }

        public function left_footer_column_html()
        {
            $left_footer_column_text = $this->template_options['bewpi_left_footer_column'];
            if (!empty($left_footer_column_text)) {
                echo '<p>' . nl2br($this->replace_placeholders($left_footer_column_text)) . '</p>';
            }
        }

        public function right_footer_column_html()
        {
            $right_footer_column_text = $this->template_options['bewpi_right_footer_column'];
            if (!empty($right_footer_column_text)) {
                echo '<p>' . nl2br($this->replace_placeholders($right_footer_column_text)) . '</p>';
            } else {
                echo '<p>' . sprintf(__('%s of %s', 'woocommerce-pdf-invoices'), '{PAGENO}', '{nbpg}') . '</p>';
            }
        }

        private function replace_placeholders($str)
        {
            $placeholders = apply_filters('bewpi_placeholders', array(
                '[payment_method]' => $this->order->payment_method_title,
                '[shipping_method]' => $this->order->get_shipping_method()
            ), $this->order->id);

            foreach ($placeholders as $placeholder => $value) {
                $str = str_replace($placeholder, $value, $str);
            }

            return $str;
        }
    }
}